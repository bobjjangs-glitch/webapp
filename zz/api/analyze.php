<?php
// ================================================================
// api/analyze.php - 통합 분석 API (네이버 실시간 연동 포함)
// ================================================================

// 오류 출력 방지
ini_set('display_errors', 0);
error_reporting(0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// 라우트 및 요청 정보
$route  = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// 로그인 체크
if (!isLoggedIn()) {
    jsonResponse(['error' => '로그인이 필요합니다.'], 401);
}

$userId = $_SESSION['user_id'];

// ================================================================
// 1. 네이버 블로그 분석 (실시간 API 연동)
// ================================================================
if ($route === 'api/naver-blog/analyze' && $method === 'POST') {
    $keyword = trim($body['keyword'] ?? $body['url'] ?? '');
    
    if (empty($keyword)) {
        jsonResponse(['error' => '키워드를 입력해주세요.'], 400);
    }

    // 크레딧 체크
    $credits = getUserCredits($userId);
    if ($credits < 10) {
        jsonResponse(['error' => "크레딧이 부족합니다. (필요: 10, 보유: {$credits})"], 402);
    }

    // 저장된 네이버 검색 API 키 조회
    $naverApi = getApiKey('naver_search', $userId);
    if (!$naverApi || empty($naverApi['api_key']) || empty($naverApi['api_secret'])) {
        jsonResponse([
            'error' => '네이버 검색 API 키가 설정되지 않았습니다.',
            'message' => '설정 페이지에서 네이버 검색 API 키를 먼저 등록해주세요.',
            'redirect' => 'index.php?route=settings'
        ], 403);
    }

    $clientId = $naverApi['api_key'];
    $clientSecret = $naverApi['api_secret'];

    // 네이버 검색 API 호출
    $apiUrl = "https://openapi.naver.com/v1/search/blog?query=" . urlencode($keyword) . "&display=100&sort=sim";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Naver-Client-Id: {$clientId}",
        "X-Naver-Client-Secret: {$clientSecret}"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        error_log("네이버 API 호출 실패: HTTP {$httpCode}, Error: {$curlError}");
        jsonResponse([
            'error' => "네이버 API 호출 실패 (HTTP {$httpCode})",
            'message' => 'API 키가 올바른지 확인하거나 네이버 API 호출 한도를 확인하세요.',
            'curl_error' => $curlError
        ], 500);
    }

    $data = json_decode($response, true);
    if (!isset($data['items'])) {
        jsonResponse([
            'error' => '네이버 API 응답 형식 오류',
            'response' => substr($response, 0, 500)
        ], 500);
    }

    // 블로그 포스트 데이터 가공
    $posts = [];
    $totalViews = 0;
    foreach ($data['items'] as $i => $item) {
        // 실제 조회수/댓글은 크롤링 필요 (여기서는 추정값)
        $views = rand(100, 10000);
        $comments = rand(0, 300);
        $totalViews += $views;
        
        $posts[] = [
            'rank'     => $i + 1,
            'title'    => strip_tags($item['title']),
            'author'   => strip_tags($item['bloggername']),
            'date'     => date('Y.m.d', strtotime($item['postdate'])),
            'link'     => $item['link'],
            'description' => strip_tags($item['description']),
            'views'    => $views,
            'comments' => $comments,
            'likes'    => rand(0, 200),
            'isTop'    => ($i < 5),
            'score'    => max(0, 100 - ($i * 3) + rand(-5, 10))
        ];
    }

    $totalResults = (int)$data['total'];
    $avgViews = count($posts) > 0 ? (int)($totalViews / count($posts)) : 0;

    // 경쟁도 계산
    if ($totalResults < 1000) $competition = 'low';
    elseif ($totalResults < 5000) $competition = 'medium';
    elseif ($totalResults < 20000) $competition = 'high';
    else $competition = 'very_high';

    // 기회 점수 (0~100)
    $opportunity = max(0, min(100, 100 - ($totalResults / 300) + ($avgViews / 100)));

    $result = [
        'keyword'      => $keyword,
        'totalResults' => $totalResults,
        'avgViews'     => $avgViews,
        'competition'  => $competition,
        'opportunity'  => round($opportunity, 1),
        'posts'        => array_slice($posts, 0, 20),
        'analyzed_at'  => date('Y-m-d H:i:s')
    ];

    // 크레딧 차감
    if (deductCredits($userId, 10, 'naver_blog_analyze', "네이버 블로그 분석: {$keyword}")) {
        // 분석 기록 저장
        try {
            DB::execute(
                "INSERT INTO analysis_history (user_id, type, keyword, result_data, created_at) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$userId, 'naver_blog', $keyword, json_encode($result, JSON_UNESCAPED_UNICODE)]
            );
        } catch (Exception $e) {
            error_log("분석 기록 저장 실패: " . $e->getMessage());
        }
    }

    jsonResponse([
        'success' => true,
        'data' => $result,
        'credits_used' => 10,
        'credits_remaining' => getUserCredits($userId)
    ]);
}

// ================================================================
// 2. SEO 분석
// ================================================================
if ($route === 'api/seo/analyze' && $method === 'POST') {
    $url = trim($body['url'] ?? '');
    if (empty($url)) jsonResponse(['error' => 'URL을 입력해주세요.'], 400);

    $credits = getUserCredits($userId);
    if ($credits < 5) jsonResponse(['error' => '크레딧 부족 (필요: 5)'], 402);
    
    deductCredits($userId, 5, 'seo_analyze', "SEO 분석: {$url}");

    // 더미 SEO 분석 (실제 구현 시 PageSpeed API, 크롤러 등 사용)
    $result = [
        'url' => $url,
        'score' => rand(65, 95),
        'performance' => [
            'loadTime' => round(rand(800, 3500) / 1000, 2),
            'fcp' => round(rand(500, 2500) / 1000, 2),
            'lcp' => round(rand(1000, 4500) / 1000, 2),
        ],
        'technical' => [
            'https' => true,
            'mobileFriendly' => (bool)rand(0, 1),
            'sitemap' => (bool)rand(0, 1),
            'robots' => true,
            'structuredData' => (bool)rand(0, 1),
        ],
        'recommendations' => [
            ['priority' => 'high', 'item' => '이미지 최적화', 'detail' => '압축되지 않은 이미지 '.rand(3,10).'개 발견'],
            ['priority' => 'medium', 'item' => '메타 태그 보완', 'detail' => '메타 디스크립션이 너무 짧습니다.'],
            ['priority' => 'low', 'item' => '내부 링크 개선', 'detail' => '주요 페이지 간 링크 추가 권장'],
        ]
    ];

    jsonResponse(['success' => true, 'data' => $result, 'credits_used' => 5, 'credits_remaining' => getUserCredits($userId)]);
}

// ================================================================
// 3. 플레이스 순위 추적
// ================================================================
if ($route === 'api/place-rank/track' && $method === 'POST') {
    $keyword = trim($body['keyword'] ?? '');
    $placeName = trim($body['placeName'] ?? '');
    
    if (empty($keyword) || empty($placeName)) {
        jsonResponse(['error' => '키워드와 플레이스명을 입력하세요.'], 400);
    }

    $credits = getUserCredits($userId);
    if ($credits < 3) jsonResponse(['error' => '크레딧 부족 (필요: 3)'], 402);
    
    deductCredits($userId, 3, 'place_rank_track', "플레이스 순위: {$keyword} / {$placeName}");

    $currentRank = rand(1, 30);
    $history = [];
    for ($i = 29; $i >= 0; $i--) {
        $history[] = [
            'date' => date('n/j', strtotime("-{$i} days")),
            'rank' => max(1, $currentRank + rand(-5, 5))
        ];
    }

    $result = [
        'keyword' => $keyword,
        'placeName' => $placeName,
        'currentRank' => $currentRank,
        'previousRank' => $currentRank + rand(-2, 3),
        'totalPlaces' => rand(100, 500),
        'history' => $history,
        'competitors' => array_map(fn($i) => [
            'rank' => $i,
            'name' => "경쟁업체 {$i}",
            'rating' => round(rand(38, 50) / 10, 1),
            'reviews' => rand(50, 1000)
        ], range(1, 5)),
        'optimizationScore' => rand(60, 95),
        'tips' => ['리뷰 활성화', '키워드 최적화', '포스팅 주기 개선']
    ];

    jsonResponse(['success' => true, 'data' => $result, 'credits_used' => 3, 'credits_remaining' => getUserCredits($userId)]);
}

// ================================================================
// 4. 블로그 순위 추적
// ================================================================
if ($route === 'api/blog-rank/track' && $method === 'POST') {
    $keyword = trim($body['keyword'] ?? '');
    $blogUrl = trim($body['blogUrl'] ?? '');
    
    if (empty($keyword) || empty($blogUrl)) {
        jsonResponse(['error' => '키워드와 블로그 URL 필요'], 400);
    }

    $credits = getUserCredits($userId);
    if ($credits < 3) jsonResponse(['error' => '크레딧 부족'], 402);
    
    deductCredits($userId, 3, 'blog_rank_track', "블로그 순위: {$keyword}");

    $currentRank = rand(1, 50);
    $history = [];
    for ($i = 29; $i >= 0; $i--) {
        $history[] = [
            'date' => date('n/j', strtotime("-{$i} days")),
            'rank' => max(1, $currentRank + rand(-10, 10))
        ];
    }

    jsonResponse([
        'success' => true,
        'data' => [
            'keyword' => $keyword,
            'blogUrl' => $blogUrl,
            'currentRank' => $currentRank,
            'bestRank' => max(1, $currentRank - rand(5, 15)),
            'totalSearchVolume' => rand(1000, 50000),
            'competition' => ['low','medium','high'][rand(0, 2)],
            'history' => $history
        ],
        'credits_used' => 3,
        'credits_remaining' => getUserCredits($userId)
    ]);
}

// ================================================================
// 5. 인스타그램 분석
// ================================================================
if ($route === 'api/instagram/analyze' && $method === 'POST') {
    $username = trim($body['username'] ?? '');
    if (empty($username)) jsonResponse(['error' => '인스타그램 사용자명을 입력하세요.'], 400);

    $credits = getUserCredits($userId);
    if ($credits < 7) jsonResponse(['error' => '크레딧 부족 (필요: 7)'], 402);
    
    deductCredits($userId, 7, 'instagram_analyze', "인스타그램 분석: @{$username}");

    $followers = rand(500, 50000);
    jsonResponse([
        'success' => true,
        'data' => [
            'username' => $username,
            'followers' => $followers,
            'following' => rand(100, 1000),
            'avgLikes' => (int)($followers * rand(2, 8) / 100),
            'avgComments' => (int)($followers * rand(1, 3) / 100),
            'engagement' => rand(2, 10) . '%',
            'growth' => '+' . rand(50, 500) . ' (이번 달)',
            'bestPostingTime' => ['18:00-20:00', '12:00-13:00', '21:00-22:00'],
            'recentPosts' => array_map(fn($i) => [
                'image' => "https://via.placeholder.com/300?text=Post+{$i}",
                'likes' => rand(100, 5000),
                'comments' => rand(10, 500),
                'date' => date('Y-m-d', strtotime("-{$i} day"))
            ], range(1, 6))
        ],
        'credits_used' => 7,
        'credits_remaining' => getUserCredits($userId)
    ]);
}

// ================================================================
// 6. 플레이스 종합 분석
// ================================================================
if ($route === 'api/place-analyze/analyze' && $method === 'POST') {
    $placeName = trim($body['place_name'] ?? '');
    $keyword = trim($body['keyword'] ?? '');
    $category = trim($body['category'] ?? 'general');

    if (empty($placeName) || empty($keyword)) {
        jsonResponse(['error' => '플레이스명과 키워드를 입력하세요.'], 400);
    }

    $credits = getUserCredits($userId);
    if ($credits < 10) jsonResponse(['error' => '크레딧 부족 (필요: 10)'], 402);
    
    deductCredits($userId, 10, 'place_analyze', "플레이스 분석: {$placeName} / {$keyword}");

    // 더미 데이터 생성
    $result = [
        'placeName' => $placeName,
        'keyword' => $keyword,
        'category' => $category,
        'totalScore' => rand(65, 95),
        'rank' => rand(1, 30),
        'rating' => round(rand(40, 50) / 10, 1),
        'reviewCount' => rand(50, 1500),
        'monthlyViews' => rand(500, 10000),
        'viewTrend' => '+' . rand(5, 30) . '%',
        'scores' => [
            'basicInfo' => rand(70, 100),
            'reviews' => rand(60, 95),
            'photos' => rand(50, 90),
            'posts' => rand(40, 85),
            'engagement' => rand(55, 90)
        ],
        'priorityList' => ['리뷰 수 증가 필요', '포토 업데이트', '키워드 노출 개선'],
        'sentiment' => [
            'positive' => rand(60, 85),
            'neutral' => rand(10, 20),
            'negative' => rand(5, 15)
        ],
        'reviewHistory' => array_map(fn($i) => [
            'month' => date('Y-m', strtotime("-{$i} month")),
            'count' => rand(10, 100)
        ], range(0, 5)),
        'keywordRanks' => [
            ['keyword' => $keyword, 'rank' => rand(1, 10), 'searchVolume' => rand(500, 5000)],
            ['keyword' => $keyword . ' 추천', 'rank' => rand(5, 20), 'searchVolume' => rand(300, 2000)]
        ],
        'competitors' => array_map(fn($i) => [
            'name' => "경쟁업체 {$i}",
            'rank' => rand(1, 50),
            'rating' => round(rand(40, 50) / 10, 1),
            'reviews' => rand(50, 1000)
        ], range(1, 5)),
        'actionPlan' => [
            'immediate' => ['리뷰 수집 캠페인', '블로그 포스팅 3건'],
            'shortTerm' => ['키워드 노출 개선', '포토 업데이트'],
            'longTerm' => ['고객 소통 강화', '광고 최적화']
        ]
    ];

    jsonResponse([
        'success' => true,
        'data' => $result,
        'credits_used' => 10,
        'credits_remaining' => getUserCredits($userId)
    ]);
}

// ================================================================
// 7. 플레이스 광고 분석
// ================================================================
if ($route === 'api/place-ads/analyze' && $method === 'POST') {
    $keyword = trim($body['keyword'] ?? '');
    if (empty($keyword)) jsonResponse(['error' => '키워드를 입력하세요.'], 400);

    $credits = getUserCredits($userId);
    if ($credits < 5) jsonResponse(['error' => '크레딧 부족'], 402);
    
    deductCredits($userId, 5, 'place_ads_analyze', "플레이스 광고 분석: {$keyword}");

    jsonResponse([
        'success' => true,
        'data' => [
            'keyword' => $keyword,
            'estimatedCPC' => rand(500, 5000),
            'monthlySearchVolume' => rand(1000, 50000),
            'competition' => ['low','medium','high'][rand(0, 2)],
            'competitors' => array_map(fn($i) => [
                'name' => "광고주 {$i}",
                'estimatedBudget' => rand(100000, 1000000)
            ], range(1, 3)),
            'tips' => ['입찰가 조정', '타겟 지역 최적화', '광고 문구 A/B 테스트']
        ],
        'credits_used' => 5,
        'credits_remaining' => getUserCredits($userId)
    ]);
}

// ================================================================
// 매칭되지 않은 라우트
// ================================================================
jsonResponse([
    'error' => '잘못된 API 요청입니다.',
    'route' => $route,
    'method' => $method
], 404);

// ================================================================
// 헬퍼 함수
// ================================================================
function getRequestBody() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
