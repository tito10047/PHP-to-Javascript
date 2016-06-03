var Foo = (function() {
	function Foo() {
		__IS_INHERITANCE__ = false;
	}
	Foo.prototype.getClassName = function() {
		return 'Foo';
	};
	Foo.prototype.getMethodName = function() {
		return 'Foo::getMethodName';
	};
	return Foo;
})();

function getFunctionName() {
	return 'getFunctionName';
}
var foo;
foo = new Foo();
assert_(foo.getClassName(), 'Foo', '__CLASS__');
assert_(foo.getMethodName(), 'Foo::getMethodName', '__METHOS__');
assert_(getFunctionName(), 'getFunctionName', '__FUNCTION__');
/** @var {{testMagicConstants: {}}} N*/
N._INIT_('testMagicConstants');
(function() {
	var Foo = this.Foo = (function() {
		function Foo() {
			__IS_INHERITANCE__ = false;
		}
		Foo.prototype.getClassName = function() {
			return 'testMagicConstants\\Foo';
		};
		Foo.prototype.getMethodName = function() {
			return 'testMagicConstants\\Foo::getMethodName';
		};
		return Foo;
	})();
	var getFunctionName = this.getFunctionName = function() {
		return 'testMagicConstants\\getFunctionName';
	};
	var getNamespace = this.getNamespace = function() {
		return 'testMagicConstants';
	};
	var foo;
	foo = new Foo();
	assert_(foo.getClassName(), 'testMagicConstants\\Foo', '__CLASS__');
	assert_(foo.getMethodName(), 'testMagicConstants\\Foo::getMethodName', '__METHOS__');
	assert_(getFunctionName(), 'testMagicConstants\\getFunctionName', '__FUNCTION__');
	assert_(getNamespace(), 'testMagicConstants', '__NAMESPACE__');
}).call(N.testMagicConstants);