<?php

namespace {
    const FOO = 1;
}

namespace AAA {
    const FOO = 2;
    assert_(FOO, 2);

    class FooCls
    {
        public static $foo = 4;
        public $foo2;

        public static function fooStatic($foo = 5)
        {
            $aa = 5;
            return $aa + $foo;
        }

        public function fooFunc()
        {
            return $this->foo2;
        }
    }

    assert_(FooCls::$foo, 4);
    assert_(FooCls::fooStatic(), 10);
}

namespace AAA\BBB {
    const FOO = 3;
    assert_(FOO, 3);
}

namespace AAA\BBB\CCC {
    const CCCFOO = 46;
}

namespace {

    use AAA\BBB\CCC as PPP;

    assert_(FOO, 1);
    assert_(\AAA\FOO, 2);
    assert_(\AAA\BBB\FOO, 3);
    assert_(PPP\CCCFOO, 46);

    assert_(\AAA\FooCls::$foo, 4);
    assert_(\AAA\FooCls::fooStatic(), 10);

    $foo = new \AAA\FooCls();
    $foo->foo2 = 6;
    assert_($foo->foo2, 6, 'foo2');
    assert_($foo->fooFunc(), 6, 'fooFunc');
}