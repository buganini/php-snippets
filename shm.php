<?php
############################
# shmop wrapper to simulate shm_*
# author: buganini@gmail.com
############################

$_shm_size=10000;

if(!function_exists('shm_attach')){
	function shm_attach($key, $size=Null, $perm=0666){
		global $_shm_size;
		if($size==Null){
			$size=$_shm_size;
		}else{
			$_shm_size=$size;
		}
		return shmop_open($key,'c',$perm,$size);
	}

	function shm_get_var($id,$key){
		if(($dat=@shmop_read($id,0,4))===false){
			return false;
		}
		$len=unpack('L',$dat);
		$len=array_shift($len);
		$dat=unserialize(shmop_read($id,4,$len));
		if(isset($dat[$key])){
			return $dat[$key];
		}
		return false;
	}

	function shm_put_var($id,$key,$val){
		global $_shm_size;
		if(($dat=@shmop_read($id,0,4))===false){
			$dat=array();
		}else{
			$len=unpack('L',$dat);
			$len=array_shift($len);
			$dat=unserialize(shmop_read($id,4,$len));
		}
		$dat[$key]=$val;
		$dat=serialize($dat);
		$l=strlen($dat);
		if($l+4>$_shm_size){
			return false;
		}
		$mem=pack('L',strlen($dat)).$dat;
		if(@shmop_write($id,$mem,0)!==false){
			return true;
		}
		return false;
	}

	function shm_detach($id){
		shmop_close($id);
		return true;
	}

	function shm_remove_var($id,$key){
		global $_shm_size;
		if(($dat=@shmop_read($id,0,4))===false){
			$dat=array();
		}else{
			$len=unpack('L',$dat);
			$len=array_shift($len);
			$dat=unserialize(shmop_read($id,4,$len));
		}
		if(isset($dat[$key])){
			unset($dat[$key]);
		}else{
			return false;
		}
		$dat=serialize($dat);
		$l=strlen($dat);
		if($l+4>$_shm_size){
			return false;
		}
		$dat=pack('L',strlen($dat)).$dat;
		if(@shmop_write($id,$dat,0)!==false)
			return true;
		return false;
	}

	function shm_remove($id){
		return shmop_delete($id);
	}
}
?>