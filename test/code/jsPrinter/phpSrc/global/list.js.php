<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 28.5.2016
 * Time: 9:57
 */

list($isNull,$isFoo,$isFive) = [null,"foo",5];

assert_($isNull,null,"is null");
assert_($isFoo,"foo","is foo");
assert_($isFive,5,"is five");

list(,,$isFive) = [null,"foo",5];

assert_($isFive,5,"is five again");