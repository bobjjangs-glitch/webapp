<?php
// api/check.php - 파일 구조 확인용 (확인 후 삭제)
header('Content-Type: text/plain; charset=utf-8');

$base = '/hosting/bobjjangs1231/html/tiretop';

echo "=== 폴더 구조 확인 ===\n\n";

$dirs = ['/', '/api', '/js', '/admin', '/css'];
foreach ($dirs as $dir) {
    $path = $base . $dir;
    echo "[$dir]\n";
    if (is_dir($path)) {
        $files = scandir($path);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $fullPath = $path . '/' . $f;
            $size = is_file($fullPath) ? ' (' . filesize($fullPath) . ' bytes)' : ' [DIR]';
            echo "  $f$size\n";
        }
    } else {
        echo "  ❌ 폴더 없음\n";
    }
    echo "\n";
}
