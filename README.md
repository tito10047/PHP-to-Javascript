PHP to JavaScript convertor
===================
#### See playground: [Online Convertor](http://phptojs.mostka.com/)####

####Suports####
- Namespaces, use
- Class, abstract class
- extends and interfaces
- constants and define
- Exceptions and catch
- continue<num> ,break<num>
- anonymous classes
- magic constants
- list()
- magic methods __get __set and __call (only in ES6 [see Proxy in compatibility table](https://kangax.github.io/compat-table/es6/#test-Proxy))
- private methods and properties (only in ES6 [see WeakMap in compatibility table](https://kangax.github.io/compat-table/es6/#test-WeakMap))

####Planed####
- include and require
- rpc library
- class generation


####Not suport####
- trait
- goto
- declare(ticks)
- yield

#### Usage####
```php
    $parser = (new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7);
    $jsPrinter = new \phptojs\JsPrinter\NonPrivate();

    $phpCode = file_get_contents('path/to/phpCode');
    $stmts = $parser->parse($phpCode);
    $jsCode = $jsPrinter->jsPrint($stmts);
```
Example
===================

```php
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
```

Is converted to
```javascript
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
    }).call(N.foo.foo1);

    document.write(N.foo.foo1.AInt.FOO)
```
[More Examples](https://github.com/tito10047/PhpTpJs/tree/master/test/code/jsPrinter/jsSrc/generated/NonPrivate)
