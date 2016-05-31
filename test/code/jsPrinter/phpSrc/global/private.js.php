<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 31.5.2016
 * Time: 15:50
 */
namespace privateTest;

class ParentClass{
	private $privateParent="privateParent";
	public $publicParent="publicParent";
	
	private function getPrivateFunc(){
		
	}
	
	public function getPublicFunc(){
		
	}
}

class Children extends ParentClass{
	private $privateParent="privateParent";
	public $publicParent="publicParent";

	private function getPrivateFunc(){

	}

	public function getPublicFunc(){

	}
}
