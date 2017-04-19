<?php
/*
    路由类
*/
namespace frame;
class Route extends \base{
	public function run($page,$para=null) {
		$temp=explode('/',$page);
		$temp[count($temp)-1] = ucfirst($temp[count($temp)-1]);
		$page=implode('\\',$temp);
		if(is_file(__DIR__."/../model/{$page}.class.php")){
			$this->App->register("\\model\\{$page}");
			$model = $this->App->getServer("\\model\\{$page}");
			if(isset($_GET['action'])&&method_exists($model,$_GET['action'])){
				return $model->$_GET['action']($para);
			}else{
				if(method_exists($model,"run")){
					return $model->run($para);
				}
			}
		}
		$this->setStatus('404');
    }
	public function setStatus($code){
		switch($code){
			case '404':
				header("HTTP/1.1 404 Not Found");  
				header("Status: 404 Not Found");  
				exit;  
				break;
			default:
				return false;
		}
	}

	public function setNotice($mess,$time=2500,$url='javascript:window.history.go(-1)'){
		if($time<10&&$time>0){
			$time*=1000;
		}
		include VIEWPATH."Notice.global.php";exit;
	}
	public function go($url){
		header('Location:'.$url);exit;
	}
}