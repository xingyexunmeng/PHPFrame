<?php
/*
    事件系统
*/
namespace model\event;
class Index extends \model{
	public function run(){
		if(isset($_GET['rightnow'])){
			$this->tableevent();exit;
		}
		if(time()<$this->App->setting['nexttimeevent']){
			exit;
		}else{
			//写入下次执行时间
			$set = $this->App->setting;
			$set['nexttimeevent']=time()+300;//5分钟一次
			$this->App->getServer('tool')->saveasphpcode($set,BASEPATH."/setting/setting.php");
		}

		$this->waitevent();
		$this->tableevent();
		
	}
	public function waitevent(){
		$filesnames = array_diff(scandir(BASEPATH.'/class/model/event/wait'),array('..','.'));
		foreach($filesnames as $file){
			if(preg_match("/^[0-9]{1,10}/",$file,$match)){
				if($match[0]<time()){
					$res=include __DIR__.'/wait/'.$file;
					if($res===true){
						unlink(__DIR__.'/wait/'.$file);
					}
				}
			}
		}
	}
	public function tableevent(){
		//tableevent项目
		$cpu = $this->db->getonelineBysql("select * from event where ".time()." - 300 > gettime order by cpu limit 1");
		$this->db->updateoneline("event",array('gettime'),array(time()),'id='.$cpu['id']);
		if($cpu){
			exec('wmic OS get FreePhysicalMemory /Value 2>&1', $output, $return);
			$free_memory = substr($output[2],19);
			$free_memory = round($free_memory/1024/1024, 1);
			exec('wmic OS get TotalVisibleMemorySize /Value 2>&1', $output2, $return);
			$total_memory = substr($output2[2],23);
			$total_memory= round($total_memory/1024/1024, 1);
			exec('wmic cpu get LoadPercentage', $p);
			$use_memory = $total_memory-$free_memory;
			$per = round($use_memory/$total_memory * 100,0);
			if($cpu['cpu']>=$p[1] && $cpu['mem']>=$per){
				//运行
				$this->db->deleteoneline('event','id='.$cpu['id']);
				$this->execEvent($cpu['fun']);
				$this->App->getServer('tool')->runEvent();
			}else{
				$mem = $App->db->getonelineBysql("select * from event where ".time()." - 300 > gettime order by cpu limit 1");
				$App->db->updateoneline("event",array('gettime'),array(time()),'id='.$mem['id']);
				if($mem['cpu']>=$p[1] && $mem['mem']>=$per){
					//运行
					$App->db->deleteoneline('event','id='.$mem['id']);
					$this->execEvent($mem['fun']);
					$this->App->getServer('tool')->runEvent();
				}
			}
		}
	}
	private function execEvent($content){
			if(strlen($content)>3){
				$fun=unserialize($content);
				$this->App->register("\\model\\event\\fun\\{$fun[0]}");
				$obj = $this->App->getServer("\\model\\event\\fun\\{$fun[0]}");
				if(count($fun)==3){
					$obj->$fun[1]($fun[2]);
				}else{
					$obj->$fun[1]();
				}
			}
		}
}