<?php
namespace inheritance1 {
	interface FooInt {
		function fooIntFunc1($a, $b=5);
	}

	abstract class FooAbs implements FooInt {
		abstract function fooAbsFunc1($a, $b);

		function fooAbsFunc2($a, $b) {
			return $a+$b+10;
		}
	}
}
namespace inheritance2 {


	class FooParent extends \inheritance1\FooAbs {
		public $foo=5;

		public function __construct() {
			$this->foo=5;
		}

		function fooAbsFunc1($a, $b) {
			parent::fooAbsFunc2(1, 5);
			return $a+$b;
		}

		function fooIntFunc1($a, $b=5) {
			return $a+$b+5;
		}

		public static function fooStatic() {
			return 10;
		}
	}

	class FooChild extends FooParent {
		public $foo=6;

		function fooIntFunc1($a, $b=5) {
			return $a+$b;
		}

		function testParent() {
			assert_($this->fooIntFunc1(5, 5), 10, 'testParent 1');
			assert_(parent::fooIntFunc1(5, 5), 15, 'testParent 2');
		}
	}
}
namespace inheritance3 {

	use inheritance1\FooAbs;
	use inheritance1\FooInt;
	use inheritance2\FooChild;
	use inheritance2\FooParent;

	$fooParent=new FooParent();
	$fooChild=new FooChild();

	assert_($fooParent instanceof FooParent, true, 'fooParent instanceof FooParent');
	assert_($fooParent instanceof FooInt, true, 'fooParent instanceof FooInt');

	assert_($fooChild instanceof FooChild, true, 'fooChild instanceof FooChild');
	assert_($fooChild instanceof FooParent, true, 'fooChild instanceof FooParent');
	assert_($fooChild instanceof FooAbs, true, 'fooChild instanceof FooAbs');
	assert_($fooChild instanceof FooInt, true, 'fooChild instanceof FooInt');

	assert_(FooChild::fooStatic(), 10, "FooChild::fooStatic()");

	$fooChild->testParent();
}