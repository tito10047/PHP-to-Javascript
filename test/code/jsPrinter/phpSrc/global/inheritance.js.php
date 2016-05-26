<?php

interface FooInt
{
    function fooIntFunc1($a, $b = 5);
}

abstract class FooAbs implements FooInt
{
    abstract function fooAbsFunc1($a, $b);

    function fooAbsFunc2($a, $b)
    {
        return $a + $b + 10;
    }
}

class FooParent extends FooAbs
{
    public $foo = 5;

    function fooAbsFunc1($a, $b)
    {
        return $a + $b;
    }

    function fooIntFunc1($a, $b = 5)
    {
        return $a + $b + 5;
    }
}

class FooChild extends FooParent
{
    public $foo = 6;

    function fooIntFunc1($a, $b = 5)
    {
        return $a + $b;
    }

    function testParent()
    {
        assert_($this->fooIntFunc1(5, 5), 10, 'testParent 1');
        assert_(parent::fooIntFunc1(5, 5), 15, 'testParent 2');
    }
}

$fooParent = new FooParent();
$fooChild = new FooChild();

assert_($fooParent instanceof FooParent, true, 'fooParent instanceof FooParent');
assert_($fooParent instanceof FooInt, true, 'fooParent instanceof FooInt');

assert_($fooChild instanceof FooChild, true, 'fooChild instanceof FooChild');
assert_($fooChild instanceof FooParent, true, 'fooChild instanceof FooParent');
assert_($fooChild instanceof FooAbs, true, 'fooChild instanceof FooAbs');
assert_($fooChild instanceof FooInt, true, 'fooChild instanceof FooInt');

$fooChild->testParent();