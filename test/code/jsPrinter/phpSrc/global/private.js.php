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
	private $overridePrivateParent="overridePrivateParent";
	
	private function getPrivateFunc(){
		return $this->privateParent;
	}
	
	public function getPublicFuncGetPrivate(){
		return $this->getPrivateFunc();
	}

	public function getPublicFunc(){
		return $this->publicParent;
	}

	public function getOverridePrivateParent(){
		return $this->overridePrivateParent;
	}
}

class Children extends ParentClass{
	private $privateChildren="privateChildren";
	public $publicChildren="publicChildren";
	private $overridePrivateParent="overridePrivateParent in Children";

	private function getPrivateFunc(){
		return $this->publicChildren;
	}

	public function getPublicFunc(){
		return $this->privateChildren;
	}

	public function testParentPublicFuncGetPrivate(){
		return parent::getPublicFuncGetPrivate();
	}

	public function testParentPublicFunc(){
		return parent::getPublicFunc();
	}

	public function testOverridePrivateParent(){
		return parent::getOverridePrivateParent();
	}
}

$children = new Children();

assert_($children->testParentPublicFunc(),"publicParent","testParentPublicFunc");
assert_($children->testParentPublicFuncGetPrivate(),"privateParent","testParentPublicFunc");
assert_($children->getPublicFunc(),"privateChildren","getPublicFunc");
assert_($children->testOverridePrivateParent(),"overridePrivateParent","getPublicFunc");