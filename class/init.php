<?php
//类自动加载函数
function autoload($class){
	if(is_file(__DIR__ . "/{$class}.class.php")){
		require __DIR__ . "/{$class}.class.php";
	}
}
spl_autoload_register('autoload');
//常量定义
define("BASEPATH",dirname(__DIR__));
define("VIEWPATH",dirname(__DIR__).'\view\\');

//开启session
session_start();

//获取APP实例
$App = \frame\App::getInstance();

//常用服务自动注册
foreach($App -> autoregister as $k => $v){
	$App -> register($k, $v[0]);
}

//事件
$nowtime = time();
if($nowtime>$App->setting['nexttimeevent']&&!isset($_GET['inevent'])){
    $fp=fsockopen('127.0.0.1',80,$errno,$errstr,3);
    if($fp){
        $out = "GET /event/index.do?inevent=1  / HTTP/1.1\r\n";
        $out .= "Host: ".str_replace("http://","",$App->setting['weburl'])."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        fclose($fp);
    }
}
return $App;