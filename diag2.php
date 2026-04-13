<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

$base = __DIR__; // 실제 경로 자동 감지

echo "=== 실제 서버 경로 ===\n";
echo "__DIR__: {$base}\n";

echo "\n=== 파일 존재 확인 ===\n";
$files = [
    'config/config.php',
    'config/db.php',
    'includes/auth.php',
    'includes/helpers.php',
    'api/analyze.php',
    'index.php',
    'pages/naver-blog.php',
];
foreach($files as $f){
    $full = $base.'/'.$f;
    echo $f.": ".(file_exists($full)?"✅ ".filesize($full)."bytes  수정:".date('Y-m-d H:i:s',filemtime($full)):"❌ 없음")."\n";
}

echo "\n=== config.php 로드 ===\n";
try{
    require_once $base.'/config/config.php';
    echo "✅ 로드 성공\n";
    echo "DEBUG_MODE: ".(defined('DEBUG_MODE')?var_export(DEBUG_MODE,true):'미정의')."\n";
    echo "DB_HOST: ".(defined('DB_HOST')?DB_HOST:'미정의')."\n";
}catch(Throwable $e){ echo "❌ ".$e->getMessage()."\n"; }

echo "\n=== DB 연결 ===\n";
try{
    require_once $base.'/config/db.php';
    $pdo = DB::connect();
    $GLOBALS['pdo'] = $pdo;
    echo "✅ DB 연결 성공\n";
    $r = $pdo->query("SELECT 1 as n")->fetch(PDO::FETCH_ASSOC);
    echo "쿼리 테스트: ".json_encode($r)."\n";
}catch(Throwable $e){ echo "❌ ".$e->getMessage()."\n"; }

echo "\n=== helpers.php 로드 ===\n";
try{
    require_once $base.'/includes/helpers.php';
    echo "✅ 로드 성공\n";
    echo "jsonResponse 함수: ".(function_exists('jsonResponse')?"✅":"❌ 없음")."\n";
    echo "deductCredits 함수: ".(function_exists('deductCredits')?"✅":"❌ 없음")."\n";
    echo "getUserCredits 함수: ".(function_exists('getUserCredits')?"✅":"❌ 없음")."\n";
}catch(Throwable $e){ echo "❌ ".$e->getMessage()." (".$e->getFile().":".$e->getLine().")\n"; }

echo "\n=== analyze.php 내용 분석 ===\n";
$code = file_get_contents($base.'/api/analyze.php');
if($code === false){ echo "❌ 파일 읽기 실패\n"; }else{
    echo "파일크기: ".strlen($code)."bytes\n";
    $nc = preg_replace('!/\*.*?\*/!s','',$code);
    $nc = preg_replace('!//[^\n]*!','',$nc);
    echo "ob_clean() 실제호출: ".(preg_match('/\bob_clean\s*\(/',$nc)?"❌ 있음":"✅ 없음")."\n";
    echo "ob_start() 실제호출: ".(preg_match('/\bob_start\s*\(/',$nc)?"있음":"없음")."\n";
    echo "jsonResponse 정의: ".(strpos($code,'function jsonResponse')!==false?"✅":"❌ 없음")."\n";
    echo "GLOBALS pdo: ".(strpos($code,"GLOBALS['pdo']")!==false?"✅":"❌ 없음")."\n";
    echo "_naverSimulate: ".(strpos($code,'_naverSimulate')!==false?"✅":"❌ 없음")."\n";
    echo "analyze-url 라우트: ".(strpos($code,'analyze-url')!==false?"✅":"❌ 없음")."\n";
    echo "rss.blog.naver.com: ".(strpos($code,'rss.blog.naver.com')!==false?"✅":"❌ 없음")."\n";
}

echo "\n=== index.php 내용 분석 ===\n";
$idx = file_get_contents($base.'/index.php');
if($idx === false){ echo "❌ 파일 읽기 실패\n"; }else{
    echo "파일크기: ".strlen($idx)."bytes\n";
    $checks = [
        'session_start'         => 'session_start',
        'DB::connect'           => 'DB::connect',
        "GLOBALS['pdo'] 설정"   => "GLOBALS['pdo']",
        'ob_end_clean'          => 'ob_end_clean',
        'register_shutdown'     => 'register_shutdown_function',
        'handleApiRoute'        => 'handleApiRoute',
        'analyze-url 라우트'    => 'analyze-url',
    ];
    foreach($checks as $label=>$needle){
        echo $label.": ".(strpos($idx,$needle)!==false?"✅ 있음":"❌ 없음")."\n";
    }
}

echo "\n=== analyze.php 직접 실행 테스트 ===\n";
require_once $base.'/includes/auth.php';
session_name('SM_SESSION');
@session_start();
$_SESSION['user_id'] = 3;
$_SERVER['REQUEST_METHOD'] = 'POST';

if(!function_exists('getRequestBody')){
    function getRequestBody(){ return ['keyword'=>'맛집']; }
}

ob_start();
try{
    include $base.'/api/analyze.php';
}catch(Throwable $e){
    echo "❌ EXCEPTION: ".$e->getMessage()."\n";
    echo "File: ".$e->getFile()." Line: ".$e->getLine()."\n";
    echo $e->getTraceAsString()."\n";
}
$raw = ob_get_clean();

echo "출력 길이: ".strlen($raw)."bytes\n";
echo "출력 앞 600자:\n".substr($raw,0,600)."\n";

$clean = ltrim($raw, "\xEF\xBB\xBF \r\n\t");
$pos = strpos($clean,'{');
if($pos !== false){
    $j = json_decode(substr($clean,$pos),true);
    if($j){
        echo "\n✅ JSON 파싱 성공\n";
        echo "success: ".var_export($j['success']??null,true)."\n";
        if(isset($j['error'])) echo "error: ".$j['error']."\n";
        if(isset($j['data']['totalResults'])) echo "totalResults: ".$j['data']['totalResults']."\n";
        if(isset($j['data']['posts'])) echo "posts 수: ".count($j['data']['posts'])."\n";
        if(isset($j['simulation'])) echo "simulation: ".var_export($j['simulation'],true)."\n";
    }else{
        echo "\n❌ JSON 파싱 실패: ".json_last_error_msg()."\n";
        echo "JSON앞200자: ".substr(substr($clean,$pos),0,200)."\n";
    }
}else{
    echo "\n❌ JSON { 없음 - PHP 오류가 섞여있습니다\n";
}

unlink(__FILE__);
echo "\n🗑️ diag2.php 삭제됨\n";
?>
