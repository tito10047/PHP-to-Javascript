var a;
a = 5;
var b, c, d;
b = c = d = 6;
var foo1;
foo1 = function(b, c) {
	var d;
	d = 5;
	c = 6;
	return a + b + c + d;
};
var foo2;
var a_ = a;
foo2 = function() {
	var a = a_;
	return a;
};
var foo3;
foo3 = function() {
	var a;
	a = 7;
	return a;
};
var testAnonymusFunc;
testAnonymusFunc = 5;
(function() {
	testAnonymusFunc = 6;
})();
assert_(testAnonymusFunc, 6, 'testAnonymusFunc');
var Foo = (function() {
	function Foo() {
		__IS_INHERITANCE__ = false;
	}
	Foo.prototype.foo1 = function() {
		var a;
		a = 8;
		return a;
	};
	Foo.prototype.foo2 = function() {
		var a;
		a = 8;
		var foo;
		foo = function() {
			a = 9;
		};
		foo();
		return a;
	};
	Foo.prototype.foo3 = function() {
		var a;
		a = 8;
		var foo;
		var a_ = a;
		foo = function() {
			var a = a_;
			return a;
		};
		return foo();
	};
	return Foo;
})();
a = 6;
assert_(foo1(5), 6 + 16, 'anonymous function use by reference');
assert_(foo2(), 5, 'anonymous function use ');
assert_(foo3(), 7, 'anonymous function');
var foo;
foo = new Foo();
foo.foo1();
assert_(foo.foo2(), 9, 'anonymous function in class use by reference');
assert_(foo.foo3(), 8, 'anonymous function in class use ');
assert_(a, 6, 'closure variable no changed');