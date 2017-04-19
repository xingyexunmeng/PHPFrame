<?php
/*
    系统容器
        服务容器
        变量容器:设置变量/全局变量
*/
namespace frame;
class App {
    /* 容器单例 */
    private static $instance = null;
    //防止实例化
    private function __construct(){}
    //实例获取
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new App();
        }
        return self::$instance;
    }


    /* 服务容器 */
    //存储服务的容器
    private static $servers = array();
    //容器注册
    public static function register($name,$class=null,$para=null){
		if($class==null){$class=$name;}
        if(isset(self::$servers[$name]))return false;
        self::$servers[$name]=array('class'=>$class,'ins'=>null,'para'=>$para);
    }
    //获取容器实例(未实现依赖自动注入)
    public function getServer($name,$para=null,$singleton=true){
        if(!isset(self::$servers[$name]))return false;
        if(!$singleton || self::$servers[$name]['ins']===null){
            if($para===null)$para=self::$servers[$name]['para'];else{ self::$servers[$name]['para']=$para;}
            self::$servers[$name]['ins'] = new self::$servers[$name]['class']($this,$para);
        }
        return self::$servers[$name]['ins'];
    }
    

    /* 变量容器 */
    //获取变量
    public function __get($name){ 
        //setting 目录下变量处理
        $dir=__DIR__ . '/../../setting/'.$name.'.php';
        if(file_exists($dir)){ 
            $this->$name = include($dir);
            return $this->$name;
        }
    }
    //设置变量
    public function __set($name,$value){
        if(!isset($this->$name)){
            $this->$name = $value;
        }
    }

}