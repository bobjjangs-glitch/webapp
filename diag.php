<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== 1. PHP 환경 ===\n";
echo "PHP: ".PHP_VERSION."\n";
echo "ob_get_level: ".ob_get_level()."\n";

echo "\n=== 2. 파일 존재 확인 ===\n";
$files = [
    '/html/zz/config/config.php',
    '/html/zz/config/db.php',
    '/html/zz/includes/auth.php',
    '/html/zz/includes/helpers.php',
    '/html/zz/api/analyze.php',
    '/html/zz/index.php',
];
foreach($files as $f){
    echo basename($f).": ".(file_exists($f)?"✅ ".filesize($f)."bytes":"❌ 없음")."\n";
}

echo "\n=== 3. config/config.php 로드 ===\n";
try{
    require_once '/html/zz/config/config.php';
    echo "✅ config.php 로드 성공\n";
    echo "DEBUG_MODE: ".(defined('DEBUG_MODE')?var_export(DEBUG_MODE,true):'미정의')."\n";
}catch(Throwable $e){ echo "❌ ".$e->getMessage()."\n"; }

echo "\n=== 4. DB 연결 ===\n";
try{
    require_once '/html/zz/config/db.php';
    $pdo = DB::connect();
    $GLOBALS['pdo'] = $pdo;
    echo "✅ DB 연결 성공\n";
    $r = $pdo->query("SELECT 1")->fetch();
    echo "✅ 쿼리 테스트: ".json_encode($r)."\n";
}catch(Throwable $e){ echo "❌ ".$e->getMessage()."\n"; }

echo "\n=== 5. helpers.php 로드 ===\n";
try{
    require_once '/html/zz/includes/helpers.php';
    echo "✅ helpers.php 로드\n";
    echo "jsonResponse 함수: ".(function_exists('jsonResponse')?"✅ 있음":"❌ 없음")."\n";
    echo "deductCredits 함수: ".(function_exists('deductCredits')?"✅ 있음":"❌ 없음")."\n";
}catch(Throwable $e){ echo "❌ ".$e->getMessage()." at ".$e->getFile().":".$e->getLine()."\n"; }

echo "\n=== 6. analyze.php 직접 include 테스트 ===\n";
$_SESSION['user_id'] = 3;
$_SERVER['REQUEST_METHOD'] = 'POST';
$GLOBALS['__route'] = 'api/naver-blog/analyze';

// getRequestBody 오버라이드
if(!function_exists('getRequestBody')){
    function getRequestBody(){ return ['keyword'=>'맛집']; }
}

ob_start();
try{
    include '/html/zz/api/analyze.php';
}catch(Throwable $e){
    echo "❌ EXCEPTION: ".$e->getMessage()."\n";
    echo "File: ".$e->getFile()." Line: ".$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}
$output = ob_get_clean();

echo "출력 길이: ".strlen($output)."바이트\n";
echo "출력 앞 500자:\n".substr($output,0,500)."\n";

echo "\n=== 7. JSON 파싱 ===\n";
// BOM, 공백 제거 후 JSON 추출
$clean = ltrim($output, "\xEF\xBB\xBF \r\n\t");
$pos = strpos($clean, '{');
if($pos !== false){
    $jsonStr = substr($clean, $pos);
    $parsed = json_decode($jsonStr, true);
    if($parsed){
        echo "✅ JSON 파싱 성공\n";
        echo "success: ".var_export($parsed['success']??null, true)."\n";
        if(isset($parsed['error'])) echo "error: ".$parsed['error']."\n";
        if(isset($parsed['data']['totalResults'])) echo "totalResults: ".$parsed['data']['totalResults']."\n";
        if(isset($parsed['data']['posts'])) echo "posts 수: ".count($parsed['data']['posts'])."\n";
    }else{
        echo "❌ JSON 파싱 실패: ".json_last_error_msg()."\n";
        echo "JSON 앞 200자: ".substr($jsonStr,0,200)."\n";
    }
}else{
    echo "❌ JSON { 없음\n";
    echo "전체출력:\n".$output."\n";
}

echo "\n=== 8. analyze.php 코드 분석 ===\n";
$code = file_get_contents('/html/zz/api/analyze.php');
echo "파일크기: ".strlen($code)."bytes\n";
// 주석 제거 후 ob_clean 체크
$noComment = preg_replace('!/\*.*?\*/!s', '', $code);
$noComment = preg_replace('!//[^\n]*!', '', $noComment);
$noComment = preg_replace('!#[^\n]*!', '', $noComment);
echo "실제 ob_clean() 호출: ".(preg_match('/\bob_clean\s*\(/', $noComment)?"❌ 있음":"✅ 없음")."\n";
echo "실제 ob_start() 호출: ".(preg_match('/\bob_start\s*\(/', $noComment)?"있음":"없음")."\n";
echo "jsonResponse 정의: ".(strpos($code,'function jsonResponse')!==false?"✅ 있음":"❌ 없음")."\n";
echo "GLOBALS pdo: ".(strpos($code,"GLOBALS['pdo']")!==false?"✅ 있음":"없음")."\n";
echo "_naverSimulate: ".(strpos($code,'_naverSimulate')!==false?"✅ 있음":"❌ 없음")."\n";
echo "analyze-url 라우트: ".(strpos($code,'analyze-url')!==false?"✅ 있음":"❌ 없음")."\n";
echo "rss.blog.naver.com: ".(strpos($code,'rss.blog.naver.com')!==false?"✅있음":"❌없음")."\n";

echo "\n=== 9. index.php 코드 분석 ===\n";
$idx = file_get_contents('/html/zz/index.php');
echo "파일크기: ".strlen($idx)."bytes\n";
$checks = [
    'session_start' => 'session_start',
    'DB::connect' => 'DB::connect',
    'GLOBALS[\'pdo\']' => "GLOBALS['pdo']",
    'ob_end_clean' => 'ob_end_clean',
    'register_shutdown' => 'register_shutdown_function',
    'handleApiRoute' => 'handleApiRoute',
    'analyze-url 라우트' => 'analyze-url',
];
foreach($checks as $label=>$needle){
    echo $label.": ".(strpos($idx,$needle)!==false?"✅ 있음":"❌ 없음")."\n";
}

echo "\n✅ 진단 완료\n";
unlink(__FILE__);
echo "🗑️ diag.php 삭제됨\n";
?>
