<?php

function	paramTest($object, $otherVar){
	return $object;
}


class TestClass{

	public $five = 5;

	function	getThis(){
		return $this;
	}

	function	getThis2(){
		return paramTest($this, 'ignored var');
	}

	function	getThis3(){
		$returnValue = $this;
		return $returnValue;
	}

	function	getClassName(){
		$className = get_class($this);
		return $className;
	}

	function	getValue(){
		return $this->five;
	}


}

$testClass = new TestClass();

assert_($testClass->getThis(), $testClass);
assert_($testClass->getThis2(), $testClass);
assert_($testClass->getThis3(), $testClass);
assert_($testClass->getClassName(), 'TestClass');
assert_($testClass->getValue(), 5);


?>