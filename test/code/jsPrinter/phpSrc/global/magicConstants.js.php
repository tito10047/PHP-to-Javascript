<?php
namespace {
    class Foo{
        public function getClassName(){
            return __CLASS__;
        }
        public function getMethodName(){
            return __METHOD__;
        }
    }
    function getFunctionName(){
        return __FUNCTION__;
    }
    $foo = new Foo();
    assert_($foo->getClassName(),"Foo","__CLASS__");
    assert_($foo->getMethodName(),"Foo::getMethodName","__METHOS__");
    assert_(getFunctionName(),"getFunctionName","__FUNCTION__");
}
namespace testMagicConstants{
    class Foo{
        public function getClassName(){
            return __CLASS__;
        }
        public function getMethodName(){
            return __METHOD__;
        }
    }
    function getFunctionName(){
        return __FUNCTION__;
    }
    function getNamespace(){
        return __NAMESPACE__;
    }
    $foo = new Foo();
    assert_($foo->getClassName(),"testMagicConstants\\Foo","__CLASS__");
    assert_($foo->getMethodName(),"testMagicConstants\\Foo::getMethodName","__METHOS__");
    assert_(getFunctionName(),"testMagicConstants\\getFunctionName","__FUNCTION__");
    assert_(getNamespace(),"testMagicConstants","__NAMESPACE__");
}