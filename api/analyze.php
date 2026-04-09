<?php
$route  = $_GET['route'] ?? '';
$method = getRequestMethod();
$body   = getRequestBody();

// POST /api/naver-blog/analyze
if ($route === 'api/naver-blog/analyze') {
    $query = $body['keyword'] ?? $body['url'] ?? '';
    if (!$query) jsonResponse(['error'=>'키워드를 입력해주세요.'], 400);
    $posts = [];
    for ($i=0;$i<10;$i++) {
        $posts[] = ['rank'=>$i+1,'title'=>"{$query} 관련 블로그 포스트 ".($i+1)." - 상세 분석과 후기",'author'=>"블로거".rand(100,999),'date'=>date('Y.m.d',strtotime("-".rand(1,365)." days")),'views'=>rand(1000,50000),'comments'=>rand(0,500),'likes'=>rand(0,1000),'isTop'=>$i<3,'score'=>rand(70,99)];
    }
    jsonResponse(['success'=>true,'data'=>['query'=>$query,'totalResults'=>rand(10000,500000),'avgViews'=>rand(2000,10000),'posts'=>$posts,'competition'=>rand(0,1)?'높음':'보통','opportunity'=>rand(60,95)]]);
}

// POST /api/seo/analyze
if ($route === 'api/seo/analyze') {
    $url = $body['url'] ?? '';
    if (!$url) jsonResponse(['error'=>'URL을 입력해주세요.'], 400);
    jsonResponse(['success'=>true,'data'=>[
        'url'=>$url,'score'=>rand(65,95),
        'performance'=>['loadTime'=>number_format(randomFloat(1,4),2),'fcp'=>number_format(randomFloat(0.5,3),2),'lcp'=>number_format(randomFloat(1,4),2)],
        'technical'=>['https'=>true,'mobileFriendly'=>(bool)rand(0,1),'sitemap'=>(bool)rand(0,1),'robots'=>(bool)rand(0,1)],
        'recommendations'=>[
            ['priority'=>'high','item'=>'메타 디스크립션 최적화','detail'=>'현재 메타 디스크립션이 권장 길이를 초과합니다.'],
            ['priority'=>'medium','item'=>'이미지 alt 태그 추가','detail'=>rand(1,5).'개의 이미지에 alt 태그가 누락되어 있습니다.'],
            ['priority'=>'low','item'=>'내부 링크 구조 개선','detail'=>'더 많은 내부 링크를 추가하세요.'],
            ['priority'=>'high','item'=>'페이지 속도 개선','detail'=>'LCP가 2.5초를 초과합니다.'],
        ],
    ]]);
}

// POST /api/place-rank/track
if ($route === 'api/place-rank/track') {
    $keyword   = $body['keyword'] ?? '';
    $placeName = $body['placeName'] ?? '';
    if (!$keyword || !$placeName) jsonResponse(['error'=>'키워드와 업체명을 입력해주세요.'], 400);
    $history = [];
    $rank = rand(5,15);
    for ($i=29;$i>=0;$i--) {
        $rank = max(1,min(30,$rank+rand(-2,2)));
        $history[] = ['date'=>date('n/j',strtotime("-{$i} days")),'rank'=>$rank,'reviewCount'=>rand(100,500),'rating'=>number_format(randomFloat(4.0,5.0),1)];
    }
    $competitors = [];
    for ($i=0;$i<10;$i++) {
        $competitors[] = ['rank'=>$i+1,'name'=>$i===2?$placeName:"경쟁업체 ".($i+1),'rating'=>number_format(randomFloat(3.5,5.0),1),'reviewCount'=>rand(50,1000),'isTarget'=>$i===2,'score'=>rand(60,99)];
    }
    jsonResponse(['success'=>true,'data'=>['keyword'=>$keyword,'placeName'=>$placeName,'currentRank'=>$rank,'previousRank'=>$rank+rand(0,3),'totalPlaces'=>rand(200,700),'history'=>$history,'competitors'=>$competitors,'optimizationScore'=>rand(60,90),'tips'=>['리뷰 답변을 꾸준히 달아주세요.','최신 사진을 정기적으로 업데이트하세요.','운영시간·메뉴 정보를 최신으로 유지하세요.','블로그 포스팅과 연계하여 노출을 늘리세요.']]]);
}

// POST /api/blog-rank/track
if ($route === 'api/blog-rank/track') {
    $keyword = $body['keyword'] ?? '';
    $blogUrl = $body['blogUrl'] ?? '';
    if (!$keyword || !$blogUrl) jsonResponse(['error'=>'키워드와 블로그 URL을 입력해주세요.'], 400);
    $history = [];
    for ($i=29;$i>=0;$i--) {
        $history[] = ['date'=>date('n/j',strtotime("-{$i} days")),'rank'=>rand(1,30),'views'=>rand(200,5000)];
    }
    jsonResponse(['success'=>true,'data'=>['keyword'=>$keyword,'blogUrl'=>$blogUrl,'currentRank'=>rand(1,20),'bestRank'=>rand(1,10),'totalSearchVolume'=>rand(5000,100000),'competition'=>['낮음','보통','높음','매우 높음'][rand(0,3)],'history'=>$history]]);
}

// POST /api/instagram/analyze
if ($route === 'api/instagram/analyze') {
    $username = $body['username'] ?? '';
    if (!$username) jsonResponse(['error'=>'인스타그램 아이디를 입력해주세요.'], 400);
    $posts = [];
    for ($i=0;$i<12;$i++) {
        $posts[] = ['id'=>$i+1,'likes'=>rand(100,5000),'comments'=>rand(10,500),'engagementRate'=>number_format(randomFloat(1,9),2),'type'=>['IMAGE','VIDEO','CAROUSEL'][rand(0,2)]];
    }
    jsonResponse(['success'=>true,'data'=>['username'=>$username,'followers'=>rand(500,50000),'following'=>rand(100,2000),'totalPosts'=>rand(50,500),'avgLikes'=>rand(200,3000),'avgComments'=>rand(20,200),'engagementRate'=>number_format(randomFloat(1,6),2),'growthRate'=>number_format(randomFloat(-2,10),1),'bestPostTime'=>['오전 9시','오후 12시','오후 6시','오후 9시'][rand(0,3)],'posts'=>$posts,'topHashtags'=>[['tag'=>'#마케팅','avgLikes'=>rand(200,2000),'posts'=>rand(5,30)],['tag'=>'#브랜딩','avgLikes'=>rand(100,1500),'posts'=>rand(3,20)],['tag'=>'#인스타','avgLikes'=>rand(300,2500),'posts'=>rand(10,50)]]]]);
}

// POST /api/place-analyze/analyze  (네이버 플레이스 종합 분석)
if ($route === 'api/place-analyze/analyze') {
    $placeName = $body['place_name'] ?? '';
    $keyword   = $body['keyword']    ?? '';
    $category  = $body['category']   ?? 'restaurant';
    if (!$placeName || !$keyword) jsonResponse(['error'=>'업체명과 키워드를 입력해주세요.'], 400);

    // 항목별 점수 시뮬레이션
    $scores = [
        'info'     => rand(50, 98),
        'photos'   => rand(40, 95),
        'reviews'  => rand(45, 99),
        'keywords' => rand(35, 90),
        'activity' => rand(30, 88),
        'menu'     => rand(20, 95),
    ];
    $totalScore = (int)round(array_sum($scores) / count($scores));
    $rank = rand(3, 25);

    // 리뷰 히스토리 (6개월)
    $reviewHistory = [];
    for ($i = 5; $i >= 0; $i--) {
        $reviewHistory[] = ['month' => date('n월', strtotime("-{$i} months")), 'count' => rand(8, 80)];
    }

    // 키워드 순위
    $keywords = [
        ['keyword' => $keyword,                   'searchVolume' => rand(5000, 200000), 'rank' => $rank,          'change' => rand(-3,  5)],
        ['keyword' => $keyword . ' 추천',           'searchVolume' => rand(2000,  80000), 'rank' => rand(1,  20),   'change' => rand(-2,  6)],
        ['keyword' => $keyword . ' 맛집',           'searchVolume' => rand(3000, 120000), 'rank' => rand(5,  30),   'change' => rand(-4,  4)],
        ['keyword' => substr($keyword,0,2) . ' 맛집','searchVolume' => rand(10000,500000),'rank' => rand(10, 40),   'change' => rand(-5,  3)],
        ['keyword' => $placeName,                  'searchVolume' => rand(500,  20000),  'rank' => rand(1,   5),   'change' => rand(0,   3)],
    ];

    // 경쟁사
    $competitors = [];
    $inserted = false;
    for ($i = 0; $i < 8; $i++) {
        $isTarget = ($i + 1 === $rank);
        $competitors[] = [
            'rank'        => $i + 1,
            'name'        => $isTarget ? $placeName : ($category === 'restaurant' ? ['맛있는집', '원조집', '본가', '할매식당', '청담갈비', '미슐랭', '로컬맛집', '명인요리'][$i] : ['경쟁업체A', '경쟁업체B', '경쟁업체C', '경쟁업체D', '경쟁업체E', '경쟁업체F', '경쟁업체G', '경쟁업체H'][$i]),
            'rating'      => number_format(randomFloat(3.8, 5.0), 1),
            'reviewCount' => rand(50, 1200),
            'photoCount'  => rand(10, 200),
            'isTarget'    => $isTarget,
            'score'       => rand(55, 99),
            'strengths'   => array_slice(['리뷰 많음', '사진 풍부', '빠른 답변', '완성된 메뉴판', '정기 업데이트', '이벤트 활발'], 0, rand(1,3)),
        ];
    }

    // 리뷰 감성
    $positive = rand(60, 85);
    $negative = rand(5, 20);
    $neutral  = 100 - $positive - $negative;

    // 주요 리뷰 샘플
    $reviewSamples = [
        ['rating' => 5, 'text' => '음식이 정말 맛있고 서비스도 친절해요. 재방문 의사 100%!', 'date' => date('Y.m.d', strtotime('-'.rand(1,30).' days'))],
        ['rating' => 4, 'text' => '전반적으로 만족스러웠습니다. 주차 공간이 조금 더 있으면 좋겠어요.', 'date' => date('Y.m.d', strtotime('-'.rand(1,60).' days'))],
        ['rating' => 3, 'text' => '맛은 괜찮은데 가격이 조금 비싼 편이에요.', 'date' => date('Y.m.d', strtotime('-'.rand(1,90).' days'))],
    ];

    // 액션 플랜
    $immediateActions = [];
    $shortTermActions = [];
    $longTermActions  = [];
    if ($scores['photos']   < 70) $immediateActions[] = '고화질 음식/인테리어 사진 최소 20장 이상 등록';
    if ($scores['info']     < 70) $immediateActions[] = '영업시간·전화번호·주소 정확히 입력 확인';
    if ($scores['reviews']  < 70) $immediateActions[] = '최근 3개월 리뷰 전체 답변 달기';
    if (empty($immediateActions))  $immediateActions[] = '현재 정보를 최신 상태로 유지하세요';
    if ($scores['keywords'] < 70) $shortTermActions[] = '업체 소개란에 주요 키워드 자연스럽게 포함';
    if ($scores['menu']     < 70) $shortTermActions[] = '메뉴판·가격표 사진 등록 및 업데이트';
    $shortTermActions[] = '주 1회 이상 네이버 플레이스 소식 포스팅';
    if ($scores['activity'] < 70) $shortTermActions[] = '월 4회 이상 이벤트/소식 등록';
    $longTermActions[]  = '블로그 체험단·리뷰 마케팅으로 리뷰 수 증가';
    $longTermActions[]  = '순위 상승 프로그램으로 키워드 상위 노출 달성';
    $longTermActions[]  = '경쟁사 대비 차별화 콘텐츠 지속 생성';

    jsonResponse(['success' => true, 'data' => [
        'placeName'     => $placeName,
        'keyword'       => $keyword,
        'totalScore'    => $totalScore,
        'rank'          => $rank,
        'rankChange'    => rand(-3, 5),
        'rating'        => number_format(randomFloat(3.8, 5.0), 1),
        'reviewCount'   => rand(50, 800),
        'monthlyViews'  => rand(5000, 80000),
        'viewsTrend'    => rand(-10, 30),
        'scores'        => $scores,
        'reviewHistory' => $reviewHistory,
        'keyReviews'    => $reviewSamples,
        'keywordRanks'  => $keywords,
        'sentiment'     => ['positive'=>$positive,'neutral'=>$neutral,'negative'=>$negative],
        'competitors'   => $competitors,
        'actionPlan'    => [
            'immediate' => $immediateActions,
            'shortTerm' => $shortTermActions,
            'longTerm'  => $longTermActions,
        ],
    ]]);
}

// POST /api/place-ads/analyze
if ($route === 'api/place-ads/analyze') {
    $keyword = $body['keyword'] ?? '';
    $region  = $body['region'] ?? '서울';
    if (!$keyword) jsonResponse(['error'=>'키워드를 입력해주세요.'], 400);
    $competitors = [];
    for ($i=0;$i<8;$i++) {
        $competitors[] = ['rank'=>$i+1,'name'=>"{$region} {$keyword} ".($i+1)."호점",'cpc'=>rand(500,3000),'isAd'=>(bool)rand(0,1),'rating'=>number_format(randomFloat(3.5,5.0),1),'reviews'=>rand(50,800)];
    }
    jsonResponse(['success'=>true,'data'=>['keyword'=>$keyword,'region'=>$region,'estimatedCpc'=>rand(800,2000),'monthlySearchVolume'=>rand(10000,200000),'competition'=>rand(0,1)?'높음':'보통','competitors'=>$competitors,'tips'=>['"'.$keyword.'" 평균 CPC는 '.rand(800,2000).'원입니다.','오전 11시~오후 2시 클릭률이 가장 높습니다.','리뷰 수가 많을수록 광고 효율이 높아집니다.']]]);
}

jsonResponse(['error'=>'잘못된 분석 요청입니다.'], 400);
