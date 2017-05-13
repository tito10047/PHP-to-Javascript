<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 5/13/2017
 * Time: 23:56
 */

class Foo1{
	function getArg1($arg){
		return $arg;
	}
}
function getArg1($arg){
	return $arg;
}

$foo1=new Foo1();
assert_(call_user_func_array([$foo1, "getArg1"],[1]),1,"foo1=>getArg1");
// not working with nodejs. method must be exported to global
//assert_(call_user_func_array("getArg1",[1]),1,"getArg1");