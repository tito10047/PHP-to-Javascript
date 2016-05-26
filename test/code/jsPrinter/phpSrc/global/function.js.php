<?php
namespace fooFunctions {
    class B
    {
    }
}
namespace functions {

    class A
    {
        function f1()
        {
        }

        function f2($a, $b)
        {
        }

        function f4(\fooFunctions\B $a)
        {
        }

        function f5(callable $a)
        {
        }

        function f7(...$a)
        {
        }

        function f10(A ...$qq)
        {
        }

        function f11(A $a, $b, A ...$qq)
        {
        }

        function f13($a) : array
        {
        }

        function f14($a) : callable
        {
        }

        function f15($a) : \fooFunctions\B
        {
        }

        function f16(array $a)
        {
        }
    }
}