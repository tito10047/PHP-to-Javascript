<?php
$a = 5;
$b = $c = $d = 6;

$foo1 = function ($b, $c = 5) use (&$a) {
    $d = 5;
    $c = 6;
    return $a + $b + $c + $d;
};
$foo2 = function () use ($a) {
    return $a;
};
$foo3 = function () {
    $a = 7;
    return $a;
};

class Foo
{
    function foo1()
    {
        $a = 8;
        return $a;
    }

    function foo2()
    {
        $a = 8;
        $foo = function () use (&$a) {
            $a = 9;
        };
        $foo();
        return $a;
    }

    function foo3()
    {
        $a = 8;
        $foo = function () use ($a) {
            return $a;
        };
        return $foo();
    }
}

$a = 6;
assert_($foo1(5), 6 + 16, 'anonymous function use by reference');
assert_($foo2(), 5, 'anonymous function use ');
assert_($foo3(), 7, 'anonymous function');

$foo = new Foo();
$foo->foo1();
assert_($foo->foo2(), 9, 'anonymous function in class use by reference');
assert_($foo->foo3(), 8, 'anonymous function in class use ');

assert_($a, 6, 'closure variable no changed');
