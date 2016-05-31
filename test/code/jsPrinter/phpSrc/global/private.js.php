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
	public $publicChildren="publicParent";
	
	private function getPrivateFunc(){
		return $this->privateParent;
	}
	
	public function getPublicFunc(){
		return $this->getPrivateFunc();
	}
}

class Children extends ParentClass{
	private $privateChildren="privateChildren";
	public $publicChildren="publicChildren";

	private function getPrivateFunc(){
		return $this->publicChildren;
	}

	public function getPublicFunc(){
		return $this->privateChildren;
	}

	public function testParentPublicFunc(){
		return parent::getPublicFunc();
	}
}

$children = new Children();

//assert_($children->testParentPublicFunc(),"privateParent","testParentPublicFunc");
assert_($children->getPublicFunc(),"privateChildren","getPublicFunc");