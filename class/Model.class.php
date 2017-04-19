<?php
/*
    model基类
*/
abstract class Model extends \base{
	protected $view = array('para'=>array());
	public function view($view=null,$para=null){
		if($view==null){
			$view = substr(static::class, 6);
		}
		$this->view['fun']=function($var=null) use($view,$para){
			if($para!=null)extract($para);
			if($var!=null)extract($var);
			require __DIR__."/../view/{$view}.view.php";
		};
		return $this;
	}
	public function with($para){
		$this->view['para'] = array_merge($this->view['para'],$para);
		return $this;
	}
	public function show(){
		$this->view['fun']($this->view['para']);
	}
}