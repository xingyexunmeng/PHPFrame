<?php
/*
    验证器类
*/
namespace frame;
class Validator extends \base{
	/*
		根据$valid过滤数组
		@return : 
			false，错误，错误信息将存储在 $errMess
			数组 ：过滤后数组信息

		$valid 例子：
			array(
				"name"=>"required|min:3|max:255",
				"userid"=>"required",
				"pic"=>"",
			}
		注：会自动进行trim操作，有任何条件既默认有 required
	*/
	public static $errMess = null;
	public static function filter($arr,$valid){
		$res = array();
		foreach($valid as $k => $v){
			$v = trim($v);
			if(isset($arr[$k])&&($length = strlen($arr[$k]=trim($arr[$k])))>0){
				$preg = explode('|',$v);
				foreach($preg as $preg_item){
					$temp = explode(':',$preg_item);
					switch($temp[0]){
						case 'required':
							break;
						case 'min':
							if($length<$temp[1]){
								self::$errMess=$k." 长度为{$length}，小于{$temp[1]}";
								return false;
							}
							break;
						case 'max':
							if($length>$temp[1]){
								self::$errMess=$k." 长度为{$length}，大于{$temp[1]}";
								return false;
							}
							break;
						default:
							break;
					}
				}
				$res[$k] = $arr[$k];
			}else{
				if(strlen($v)>0){
					self::$errMess=$k.' 不能为空';
					return false;
				}
			}
		}
		return $res;
    }
}