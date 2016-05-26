var FooInt = (function() {
	function FooInt( /*constructor arguments*/ ) {
		__INTERFACE_NEW__();
	}
	FooInt.prototype.fooIntFunc1 = function(a, b) {
		__INTERFACE_FUNC__();
	};
	return FooInt;
})();
var FooAbs = (function() {
	function FooAbs( /*constructor arguments*/ ) {}
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
	function FooParent( /*constructor arguments*/ ) {
		parent.call(this /*constructor arguments*/ );
		this.foo = 5;
	}
	__extends(FooParent, parent);
	FooParent.prototype.fooAbsFunc1 = function(a, b) {
		return a + b;
	};
	FooParent.prototype.fooIntFunc1 = function(a, b) {
		if (typeof b == 'undefined') b = 5;
		return a + b + 5;
	};
	return FooParent;
})(FooAbs);
var FooChild = (function(parent) {
	function FooChild( /*constructor arguments*/ ) {
		parent.call(this /*constructor arguments*/ );
		this.foo = 6;
	}
	__extends(FooChild, parent);
	FooChild.prototype.fooIntFunc1 = function(a, b) {
		if (typeof b == 'undefined') b = 5;
		return a + b;
	};
	FooChild.prototype.testParent = function() {
		assert_(this.fooIntFunc1(5, 5), 10, 'testParent 1');
		assert_(parent.prototype.fooIntFunc1(5, 5), 15, 'testParent 2');
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
fooChild.testParent();