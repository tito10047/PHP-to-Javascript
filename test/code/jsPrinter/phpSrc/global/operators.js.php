<?php

$a=0;
$b=1;
$c=2;
class Foo{};
++$a;
--$a;
$a++;
$a--;

(int) $a;
(integer) $a;
(float) $a;
(double) $a;
(string) $a;
(bool) $a;
(boolean) $a;

$a * $b;
$a / $b;
$a % $b;
$a + $b;
$a - $b;
$a . $b;
$a << $b;
$a >> $b;
$a < $b;
$a <= $b;
$a > $b;
$a >= $b;
$a == $b;
$a != $b;
$a <> $b;
$a === $b;
$a !== $b;
$a & $b;
$a ^ $b;
$a | $b;
$a && $b;
$a || $b;
$a ? $b : $c;

$a = $b;
$a *= $b;
$a /= $b;
$a %= $b;
$a += $b;
$a -= $b;
$a .= $b;
$a <<= $b;
$a >>= $b;
$a &= $b;
$a ^= $b;
$a |= $b;

$a and $b;
$a xor $b;
$a or $b;
$a instanceof Foo;
