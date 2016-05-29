<?php
namespace anonymusClass {
	class AAA {
	}

	interface BBB {
	}

	interface CCC {
	}


	$a = new class {
	};
	$b = new class extends AAA implements BBB, CCC {
	};
	$c = new class($a) extends AAA {
		private $a;

		public function __construct($a) {
			$this->a = $a;
		}
	};
}