<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 29.5.2016
 * Time: 9:54
 */
namespace testMagicMethods{
	/**
	 * @property string test
	 * @method testCall($test)
	 */
	class Foo{
		public $_test;

		public function __construct($test) {
			$this->_test=$test;
		}

		public function tetsFunc(){
			return 5;
		}

		public function __get($name) {
			switch ($name) {
				case "test":
					return $this->_test;
			}
			return undefined;
		}

		public function __set($name, $value) {
			switch ($name) {
				case "test":
					$this->_test=$value;
			}
		}

		public function __call($name, $arguments) {
			switch ($name) {
				case "testCall":
					return $arguments[0];
			}
			return null;
		}
	}

	$foo = new Foo("12345");

	assert_($foo->_test,"12345","12345");
	$foo->_test="6789";
	assert_($foo->_test,"6789","6789");
	assert_($foo->tetsFunc(),5,"testfunc");
	assert_($foo->test,"6789","foo->test 1");
	$foo->test="98765";
	assert_($foo->test,"98765","98765");
	assert_($foo->testCall(5),5,"testCall(5)");
}