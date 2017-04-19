<?php
/*
    内存缓存类
*/
namespace frame;
class Memcache extends \base{
	private $connect =  null;
	public function __construct($app){
		parent::__construct($app);
		$this->connect = memcache_connect('localhost', 11211);
    }
	public function get($name){
		return $this->connect->get($name);
	}
	public function set($name,$value,$time=0){
		return $this->connect->set($name,$value,0,$time);
	}
	public function delete($name){
		return $this->connect->delete($name);
	}
}