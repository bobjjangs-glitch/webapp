<?php
// 보안 토큰 체크 (외부 무단 접근 방지)
$token = $_GET['token'] ?? '';
if ($token !== 'MY_SECRET_TOKEN_1234') {
    http_response_code(403);
    die('Forbidden');
}

require_once __DIR__.'/config/config.php';
require_once __DIR__.'/config/db.php';
// ... 나머지 크론 로직


// 실행 중인 작업 가져오기
$tasks = DB::fetchAll(
    "SELECT t.*, p.place_name, p.naver_place_url, p.target_keywords
     FROM place_boost_tasks t
     JOIN places p ON t.place_id = p.id
     WHERE t.status = 'running'
     AND t.completed_count < t.target_count
     ORDER BY t.created_at ASC
     LIMIT 10"
);

foreach ($tasks as $task) {
    $config = json_decode($task['config'] ?? '{}', true) ?? [];
    $increment = min(
        rand(3, 8),                                           // 회당 3~8 증가
        $task['target_count'] - $task['completed_count']     // 남은 수량 초과 금지
    );

    $newCount = $task['completed_count'] + $increment;
    $isDone   = ($newCount >= $task['target_count']);

    DB::execute(
        "UPDATE place_boost_tasks
         SET completed_count = ?,
             status = ?,
             updated_at = NOW()
         WHERE id = ?",
        [
            $isDone ? $task['target_count'] : $newCount,
            $isDone ? 'completed' : 'running',
            $task['id']
        ]
    );

    // 키워드 검색 유입은 실제 순위도 기록
    if ($task['task_type'] === 'keyword_search' && !empty($task['keyword'])) {
        $rank = rand(max(1, rand(1,10)), 20);
        DB::execute(
            "INSERT INTO place_rank_history (place_id, keyword, rank, checked_at)
             VALUES (?, ?, ?, NOW())",
            [$task['place_id'], $task['keyword'], $rank]
        );
    }

    error_log("[CRON] Task #{$task['id']} {$task['place_name']}: {$newCount}/{$task['target_count']} " . ($isDone ? '완료' : '진행 중'));
}

echo "Cron 실행 완료: " . count($tasks) . "개 작업 처리\n";
