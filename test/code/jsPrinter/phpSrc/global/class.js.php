<?php
class Foo{
    const CONSTANT="CONSTANT";
    public static $publicStaticVar="publicStaticVar";
    public $publicVar = "publicVar";
    public function publicFunc($publicVar){
        $this->publicVar=$publicVar;
    }
    public static function publicStaticFunc(){
        return 5;
    }
}

$foo1 = new Foo();
$foo2 = new Foo();

$foo1->publicFunc(5);

assert_($foo1->publicVar,5,"foo1 publicvar");
assert_($foo2->publicVar,"publicVar","foo1 publicvar");

assert_(Foo::$publicStaticVar,'publicStaticVar','publicStaticVar');

assert_(Foo::publicStaticFunc(),5,"publicStaticFunc");
