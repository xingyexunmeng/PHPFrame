<?php
/* 
    初始化使用，class目录下唯一不是类的php文件
    包含：类自动加载函数
*/
$App = require __DIR__ . '/class/init.php';

/*
	转交路由处理页面
*/
if(!isset($_GET['page'])){
	$_GET['page']='index';
}
$result = $App->getServer('route')->run($_GET['page']);
if($result!==null){
	echo json_encode($result);
}