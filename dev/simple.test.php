<?php
$aaa=5;
$foo = function() use($aaa){
    $aaa++;
};

class Foo{
    function foo(){

    }
}