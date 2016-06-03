function paramTest(object, otherVar) {
	return object;
}
var TestClass = (function() {
	function TestClass() {
		window.__IS_INHERITANCE__ = false;
		this.five = 5;
	}
	TestClass.prototype.getThis = function() {
		return this;
	};
	TestClass.prototype.getThis2 = function() {
		return paramTest(this, 'ignored var');
	};
	TestClass.prototype.getThis3 = function() {
		var returnValue;
		returnValue = this;
		return returnValue;
	};
	TestClass.prototype.getClassName = function() {
		var className;
		className = get_class(this);
		return className;
	};
	TestClass.prototype.getValue = function() {
		return this.five;
	};
	return TestClass;
})();
var testClass;
testClass = new TestClass();
assert_(testClass.getThis(), testClass);
assert_(testClass.getThis2(), testClass);
assert_(testClass.getThis3(), testClass);
assert_(testClass.getClassName(), 'TestClass');
assert_(testClass.getValue(), 5);