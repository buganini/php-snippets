<?php
/*
This file is in public domain
*/
if(!isset($_SESSION)){
	session_start();
	$token_name = 'token';
	$tolerance = 2;
	$_SESSION[$token_name] = (array) @$_SESSION[$token_name];
	if(count($_SESSION[$token_name])!=$tolerance){
		$_SESSION[$token_name]=array_slice($_SESSION[$token_name],0,$tolerance);
		$_SESSION[$token_name]=array_pad($_SESSION[$token_name],-$tolerance,sha1(rand()));
	}
	if(!in_array(@$_COOKIE[$token_name], $_SESSION[$token_name])){
		session_destroy();
		session_start();
		session_regenerate_id(true);
		setcookie(session_name(), session_id(), 0, '/');
		$_SESSION[$token_name]=array();
		while($tolerance-->0){
			$_SESSION[$token_name][]=sha1(rand().rand().rand().rand());
		}
	}
	setcookie($token_name, $_SESSION[$token_name][]=sha1(str_shuffle(array_shift($_SESSION[$token_name]))), 0, '/');
	unset($token_name);
	unset($tolerance);
}
echo "!!PLEASE REMOVE THIS LINE!!\n";
?>
