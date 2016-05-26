###Fork for create php to js converter. Work in progress.###
===============

####Suports####
- Namespaces, use
- Class, abstract class
- extends and interfaces
- constants and define
- Exceptions and catch
- continue<num> ,break<num>
- anonymous classes
- magic constants

####Planed####
- include and require
- rpc library
- class generation

####Not support now####
- private functions and properties (its possible but required implement different design pattern.This I use is much faster)

####Not suport####
- trait
- goto
- declare(ticks)
- yield

Example
===================

    namespace foo\foo1{
        interface AInt{
            const FOO=1;
            public function funcAInt();
        }

        abstract class BAbs{
            public function funcBAbs(){}
            public abstract function funcBAbsA();
        }

        class C extends BAbs implements AInt{

            public function funcAInt() {}

            public function funcBAbsA() {}
        };
    }
    namespace{
        echo \foo\foo1\AInt::FOO;
    }

Is converted to

    N._INIT_('foo.foo1');
    (function() {
        var AInt = this.AInt = (function(){
            function AInt(){__INTERFACE_NEW__();}
            AInt.prototype.funcAInt = __INTERFACE_FUNC__;
            AInt.FOO=1;
            return AInt;
        })();
        var BAbs = this.BAbs = (function(){
            function BAbs(){}
            BAbs.prototype.funcBAbs = function(){};
            BAbs.prototype.funcBAbsA = __ABSTRACT_FUNC__;
            return BAbs;
        })();
        var C = this.C = (function(parent){
            __extends(C,parent,arguments[1]);
            function C(){
                __extends(this,parent);
            }
            C.prototype.funcAInt = function(){};
            C.prototype.funcBAbsA = function(){};
            return C;
        })(BAbs,[AInt]);
    }).call(foo.foo1);

    document.write(N.foo.foo1.AInt.FOO)

[More Examples](https://github.com/tito10047/PhpTpJs/tree/master/test/code/jsPrinter/jsSrc/generated/NonPrivate)
