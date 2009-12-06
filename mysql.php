<?php
function mksql($pat){
	$sql='';
	$args=func_get_args();
	$len=strlen($pat);
	$b=$len-1;
	for($i=0;$i<$len;++$i){
		if($pat[$i]=='%' && $i<$b){
			++$i;
			switch($pat[$i]){
				#string
				case 's':
					$sql.='"'.sqlslashes(array_shift($args)).'"';
					break;
				#digital
				case 'd':
					$sql.=intval(array_shift($args));
					break;
				#float
				case 'f':
					$sql.=floatval(array_shift($args));
					break;
				#boolean
				case 'b':
					$sql.=array_shift($args)?1:0;
					break;
				#table
				case 't':
					$sql.='`'.sqlslashes(array_shift($args)).'`';
					break;
				#% itself
				case '%':
					$sql.='%';
					break;
				default:
					$sql.='%'.$pat[$i];
			}
		}else{
			$sql.=$pat[$i];
		}
	}
	return $sql;
}

function sql($sql,$renew=NULL){
	#echo $sql."<br />\n";
	global $SQLres;
	if(!isset($SQLres[$sql]) || $renew!==NULL){
		$SQLres[$sql]=mysql_query($sql);
	}
	if($SQLres[$sql]===true){
		unset($SQLres[$sql]);
		return mysql_affected_rows();
	}elseif($SQLres[$sql]){
		$ret=mysql_fetch_assoc($SQLres[$sql]);
		if(!$ret && mysql_num_rows($SQLres[$sql])>0){
			mysql_data_seek($SQLres[$sql],0);
		}
		return $ret;
	}
	return false;
}

function sqlslashes($s){
	return mysql_real_escape_string($s);
}

function sql_insert($table,$res){
	if(count($res)==0){
		return 0;
	}
	$res2=$fields=array();
	$n=0;
	foreach($res as $k=>$v){
		$fields[]='`'.$k.'`';
		if(!is_array($v)){
			$res2[$k]=array($v);
		}else{
			$res2[$k]=$v;
		}
		$t=count($res2[$k]);
		if($n>0 && $n!=$t){
			return false;
		}
		$n=$t;
	}
	$dat=array();
	for($i=0;$i<$n;++$i){
		$t=array();
		foreach($res2 as $v){
			$t[]='"'.sqlslashes($v[$i]).'"';
		}
		$dat[]='('.implode(',',$t).')';
	}
	return sql('INSERT INTO `'.$table.'` ('.implode(',',$fields).') VALUES '.implode(',',$dat));
}

function sql_update($table,$res,$cond){
	$dat=$cnd=array();
	foreach($res as $key => $val){
		$dat[]='`'.$key.'`="'.mysql_real_escape_string($val).'"';
	}
	foreach($cond as $key => $val){
		$cnd[]='`'.$key.'`="'.mysql_real_escape_string($val).'"';
	}
	return sql('UPDATE `'.$table.'` SET '.implode(',',$dat).' WHERE ('.implode(' AND ',$cnd).')');
}
?>
