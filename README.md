PHP to JavaScript convertor
===================
#### See playground: [Online Convertor](http://phptojs.mostka.com/) ####

#### Suports ####
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

#### Planed ####
- include and require
- class generation
- yield

#### Limitations ####
Its there more differences between PHP and JS. Array in PHP is asociate, but in JS is not.
 For that reason you can use [```jsphp\JsArray```](https://github.com/tito10047/PHP-to-Javascript/blob/master/test/code/jsPrinter/phpSrc/global/JsArray.js.php) wich has same funcionality as build in JS Array.
 
In JS you have object wich is similarly to PHP arrays, but there is diferent ordering. Also is not working
 with builtin php functions for manipulating with arrays. So if you need this object, wich working with
 foreach loop, the you can use [```jsphp\JsObject```](https://github.com/tito10047/PHP-to-Javascript/blob/master/test/code/jsPrinter/phpSrc/global/JsClass.js.php). This object has same funcionality as
 JsObject. But if you want extend it, your extended object cant have public or protected members, just use it, but not declare it.

If you need some like associated array you can also use [```jsphp\HashArray```](https://github.com/tito10047/PHP-to-Javascript/blob/master/test/code/jsPrinter/phpSrc/JsPrinter/array.js.php)

You can't define class constant and static properties with same name. in JS will be override.

#### Not suport ####
- trait
- goto
- declare(ticks)

Usage
===================
```php
    $parser = (new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7);
    $jsPrinter = new \phptojs\JsPrinter\JsPrinter();

    $phpCode = file_get_contents('path/to/phpCode');
    $stmts = $parser->parse($phpCode);
    $jsCode = $jsPrinter->jsPrint($stmts);
```
----
### Use auto converter ###
You can create file watcher for auto generation js script from your php
code when is saved.

#### PHPStorm ####
go to `File/Setting/Tools/File Watchers` add custom watcher and set

- File type: PHP
- Scope: Create new scope to your php scripts to convert
- Program: chose yor location to `php.exe`
- Arguments:
  - `-f`
  - `$ProjectFileDir$/../PHP-to-Javascript/bin/phpstormWatcher.php`
  - `$FileName$`
  - `$ProjectFileDir$/phpJs` php scripts to generate
  - `$ProjectFileDir$/public/js/phpjs` output directory
  - `[-p]` enable support of private properties and method. If is disabled, all private fields is converted as public

- Output paths to refresh: `$ProjectFileDir$/public/js/phpjs`



Example
===================

```php
interface FooInt{
    function fooIntFunc1($a, $b = 5);
}

abstract class FooAbs implements FooInt
{
    abstract function fooAbsFunc1($a, $b);

    function fooAbsFunc2($a, $b){
        return $a + $b + 10;
    }
}

class FooParent extends FooAbs{
    public $foo = 5;

    public function __construct() {
		$this->foo=5;
	}

	function fooAbsFunc1($a, $b){
		parent::fooAbsFunc2(1,5);
        return $a + $b;
    }

    function fooIntFunc1($a, $b = 5){
        return $a + $b + 5;
    }

    public static function fooStatic(){
    	return 10;
	}
}

class FooChild extends FooParent
{
    public $foo = 6;

    function fooIntFunc1($a, $b = 5){
        return $a + $b;
    }

    function testParent(){
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

assert_(FooChild::fooStatic(),10, "FooChild::fooStatic()");

$fooChild->testParent();
```

Is converted to
```javascript
var FooInt = (function() {
    function FooInt() {
        window.__IS_INHERITANCE__ = false;
        __INTERFACE_NEW__();
    }
    FooInt.prototype.fooIntFunc1 = function(a, b) {
        __INTERFACE_FUNC__();
    };
    return FooInt;
})();
var FooAbs = (function() {
    function FooAbs() {
        window.__IS_INHERITANCE__ = false;
    }
    __extends(FooAbs, null, arguments[1]);
    FooAbs.prototype.__isAbstract__ = true;
    FooAbs.prototype.fooAbsFunc1 = function(a, b) {
        __ABSTRACT_FUNC__();
    };
    FooAbs.prototype.fooAbsFunc2 = function(a, b) {
        return a + b + 10;
    };
    return FooAbs;
})(null, [FooInt]);
var FooParent = (function(parent) {
    function FooParent() {
        var __isInheritance = __IS_INHERITANCE__;
        window.__IS_INHERITANCE__ = true;
        parent.call(this);
        this.foo = 5;
        if (__isInheritance == false) {
            this.__construct();
        }
    }
    __extends(FooParent, parent);
    FooParent.prototype.__construct = function() {
        this.foo = 5;
    };
    FooParent.prototype.fooAbsFunc1 = function(a, b) {
        parent.prototype.fooAbsFunc2.call(this, 1, 5);
        return a + b;
    };
    FooParent.prototype.fooIntFunc1 = function(a, b) {
        if (typeof b == 'undefined') b = 5;
        return a + b + 5;
    };
    FooParent.fooStatic = function() {
        return 10;
    };
    return FooParent;
})(FooAbs);
var FooChild = (function(parent) {
    function FooChild() {
        window.__IS_INHERITANCE__ = true;
        parent.call(this);
        this.foo = 6;
    }
    __extends(FooChild, parent);
    FooChild.prototype.fooIntFunc1 = function(a, b) {
        if (typeof b == 'undefined') b = 5;
        return a + b;
    };
    FooChild.prototype.testParent = function() {
        assert_(this.fooIntFunc1(5, 5), 10, 'testParent 1');
        assert_(parent.prototype.fooIntFunc1.call(this, 5, 5), 15, 'testParent 2');
    };
    return FooChild;
})(FooParent);
var fooParent;
fooParent = new FooParent();
var fooChild;
fooChild = new FooChild();
assert_(fooParent instanceof FooParent, true, 'fooParent instanceof FooParent');
assert_(fooParent instanceof FooInt, true, 'fooParent instanceof FooInt');
assert_(fooChild instanceof FooChild, true, 'fooChild instanceof FooChild');
assert_(fooChild instanceof FooParent, true, 'fooChild instanceof FooParent');
assert_(fooChild instanceof FooAbs, true, 'fooChild instanceof FooAbs');
assert_(fooChild instanceof FooInt, true, 'fooChild instanceof FooInt');
assert_(FooChild.fooStatic(), 10, 'FooChild::fooStatic()');
fooChild.testParent();
```
[More Examples](https://github.com/tito10047/PHP-to-Javascript/tree/master/test/code/jsPrinter/jsSrc/generated/JsPrinter)
