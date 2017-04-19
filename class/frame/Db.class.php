<?php
/*
    数据库类
*/
namespace frame;
class Db extends \base{
    private $connect = null;
	private $cache = null;
	//缓存
	public function caches($name){
		$c = $this->App->caches[$name];//获取缓存配置
		if($c[2]){//从不同存储方式尝试获取缓存
			//内存
			$res = $this->App->getServer('memcache')->get("caches_{$name}");
			if($res!==false) return $res;
		}else{
			//文件
			if(is_file(BASEPATH."/storage/caches/db/{$name}.php")){
				return include BASEPATH."/storage/caches/db/{$name}.php";
			}
		}
		//数据不存在缓存，从数据库获取
		if($c[1]){
			$db=$this->getmuchlineBysql($c[0]);//获取多条
		}else{
			$db=$this->getonelineBysql($c[0]);//获取单条
		}
		if($c[3]!==null){//若指定index索引则整理数据
			$temp=array();
			foreach($db as $item){
				$temp[$item[$c[3]]]=$item;
			}
			$db = $temp;
		}
		//数据存储到缓存
		if($c[2]){
			$this->App->getServer('memcache')->set("caches_{$name}",$db);//内存
		}else{
			$this->saveasphpcode($db,BASEPATH."/storage/caches/db/{$name}.php");//文件
		}
		return $db;
	}
	//清除缓存
	public function flush_cache($name){
		$c = $this->App->caches[$name];
		if($c[2]){
			//内存
			return $this->App->getServer('memcache')->delete("caches_{$name}");
		}else{
			//文件
			unlink(BASEPATH."/storage/caches/db/{$name}.php");
		}
	}
	//使用memcache缓存
	public function cache($time=0){
		$this->cache = $time;
		return $this;
	}

    public function __construct($app){
		//parent::__construct($app);
		$this->App = $app;
		$this->connect = mysqli_connect($this->App->mysql['DB_HOST'],$this->App->mysql['DB_USER'],$this->App->mysql['DB_PASS']);
		mysqli_query($this->connect,"use {$this->App->mysql['DB_NAME']}");
		mysqli_query($this->connect,'set names "utf8"');
    }

	public function getInstance(){
		return $this->connect;
	}

	//获取一行数据
    public function getoneline($table,$condition,$filed="*"){
        $osql='select '.$filed.' from '.$table.' where '.$condition;
        return $this->sql_get($osql);
    }

	//通过sql获取一条数据
    public function getonelineBysql($SQL){
        return $this->sql_get($SQL);
    }

    //获取一条数据的第一个字段
    public function getonelinefirstBysql($SQL){
        $result=$this->sql_get($SQL,'array',MYSQLI_NUM);
        return $result[0];
    }
    //获取数据数组
    public function getmuchline($table,$condition="",$filed="*"){
        $osql='select '.$filed.' from '.$table;
        if($condition){$osql.=" {$condition}";}
        return $this->sql_get($osql,'all');
    }
    //通过sql获取数据数组
    public function getmuchlineBysql($SQL){
        return $this->sql_get($SQL,'all');
    }
    //更新一条数据
    public function updateoneline($table,$datetitle,$datecontent,$condition){
        $this->checkfirewall($datecontent);
		$datecontent = $this->SQLsecurity($datecontent);
        $osql="update {$table} set {$datetitle[0]}='{$datecontent[0]}'";
        for($i=1;$i<count($datetitle);$i++){
            $osql.=",{$datetitle[$i]}='{$datecontent[$i]}'";
        }
        $osql.=" where {$condition}";
        return $this->execSql($osql);
    }
    //插入一条数据
    public function insertoneline($table,$datetitle,$datecontent){
        $this->checkfirewall($datecontent);
		$datecontent = $this->SQLsecurity($datecontent);
        $osql="insert into {$table} ({$datetitle[0]}";
        for($i=1;$i<count($datetitle);$i++){
            $osql.=",{$datetitle[$i]}";
        }
        $osql.=") values ('{$datecontent[0]}'";
        for($i=1;$i<count($datecontent);$i++){
            $osql.=",'{$datecontent[$i]}'";
        }
        $osql.=')';
        //echo $osql;exit;
        return $this->execSql($osql);
    }
    //删除一条数据
    public function deleteoneline($table,$datecontent){
        $osql='delete from '.$table.' where '.$datecontent;
        return $this->execSql($osql);
    }
	public function insertID(){
		return mysqli_insert_id($this->connect);
	}
    public function begintransaction(){
        mysqli_query($this->connect,'START TRANSACTION') or exit(mysqli_error()); 
    }
    public function rollback(){
        mysqli_query($this->connect,'ROLLBACK');
    }
    public function overtransaction(){
        mysqli_query($this->connect,'COMMIT') or exit(mysqli_error());
    }
	
	//获取数据,基于缓存之上
	/*
		$type : array 获取一条
				all 获取全部
	*/
	private function sql_get($sql,$type='array',$result_type=MYSQLI_ASSOC){
		$type='mysqli_fetch_'.$type;
		if($this->cache===null){
			return $type(mysqli_query($this->connect,$sql),$result_type);
		}else{
			$this->cache=null;
			$md5=md5("{$type}{$sql}");
			$memSer=$this->App->getServer('memcache');
			$res = $memSer->get($md5);
			if($res!==false){
				return $res;
			}else{
				$res=$type(mysqli_query($this->connect,$sql),$result_type);
				$memSer->set($md5,$res,$this->cache);
			}
			return $res;
		}
	}
	//执行一条数据
	public function execSql($sql){
		return mysqli_query($this->connect,$sql);
	}
	//SQL防注入安全
	public function SQLsecurity($arr){
		if(is_array($arr)){
			foreach($arr as $k => $v){
				if(is_array($v)){
					$arr[$k] = $this->SQLsecurity($v);
				}else{
					$arr[$k] = mysqli_real_escape_string($this->connect,$v);
				}
			}
		}else{
			$arr = mysqli_real_escape_string($this->connect,$arr);
		}
		return $arr;
	}
	
    //检测拦截词库
    private function checkfirewall($arr){
		$fireword = $this->App->firewall;
        foreach ($arr as $item) {
            foreach ($fireword as $key) {
                if(strpos($item, $key) !== false){
					$this->App->getServer('route')->setNotice("发现非法词或敏感词：".$key."，操作失败。");
                }
            }
        }
        return true;
    }



    //过滤所有html标签,标题类使用
    public function security_html($content){
        $content=htmlspecialchars($content);
        return $content;
    }
    //过滤xss保留安全html(富文本类)
    public function security_xss($content){
        if(is_file(BASEPATH.'/assets/lib/htmlpurifier/library/HTMLPurifier.auto.php')){
            include BASEPATH.'/assets/lib/htmlpurifier/library/HTMLPurifier.auto.php';
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);
            $content = $purifier->purify($content);
        }
        return $content;
    }
    //安全函数,支持多维数组,包含过滤sql $type 为 html(标题或类似字段使用)  xss(富文本使用)
    public function security(&$content,$type='xss'){
        if($type!='xss')$type='html';
        if(is_array($content)){
			$fun="security_all_".$type;
			foreach($content as $k => $v){
				$this->$fun($content[$k]);
			}
        }else{
            $type="security_".$type;
            $content = $this->$type($content);
        }
    }
    private function security_all_html(&$content){//配合security
        $this->security($content,"html");
    }
    private function security_all_xss(&$content){//配合security
        $this->security($content,"xss");
    }
	
	//返回影响行数
	public function affected_rows(){
		return mysqli_affected_rows($this->connect);
	}
	
    //将数组保存到php可直接include的文件
    public static function saveasphpcode($arr,$file){
        $text='<?php return '.var_export($arr,true).';';
        if(false!==fopen($file,'w+')){
            file_put_contents($file,$text);
            return true;
        }else{ 
            return false;
        }
    }
}