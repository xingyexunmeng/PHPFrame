<?php
/*
    基类
*/
abstract class Base{
	public function __construct(\frame\App $app){
		$this->App = $app;
	}

	/* 输出json字符串并结束 */
    public final static function rt(Array $arr){
        echo json_encode($arr);
        exit;
    }
}