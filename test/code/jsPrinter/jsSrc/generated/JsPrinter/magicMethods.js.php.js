/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 29.5.2016
 * Time: 9:54
 */
/** @var {{testMagicMethods: {}}} N*/
N._INIT_('testMagicMethods');
(function() {
	/**
	 * @property string test
	 * @method testCall($test)
	 */
	var Foo = this.Foo = (function() {
		function Foo(test) {
			var __isInheritance = __IS_INHERITANCE__;
			__IS_INHERITANCE__ = false;
			this._test = null;
			if (__isInheritance == false) {
				this.__construct(test);
			}
		}
		Foo.prototype.__construct = function(test) {
			this._test = test;
		};
		Foo.prototype.tetsFunc = function() {
			return 5;
		};
		Foo.prototype.__get = function(name) {
			switch (name) {
				case 'test':
					return this._test;
			}
			return undefined;
		};
		Foo.prototype.__set = function(name, value) {
			switch (name) {
				case 'test':
					this._test = value;
			}
		};
		Foo.prototype.__call = function(name, arguments) {
			switch (name) {
				case 'testCall':
					return arguments[0];
			}
			return null;
		};
		var __handler = {
			construct: function(target, args) {
				var obj = Object.create(Foo.prototype);
				Foo.apply(obj, args);
				return new Proxy(obj, __PROXY_HANDLER);
			}
		};
		return new Proxy(Foo, __handler);
	})();
	var foo;
	foo = new Foo('12345');
	assert_(foo._test, '12345', '12345');
	foo._test = '6789';
	assert_(foo._test, '6789', '6789');
	assert_(foo.tetsFunc(), 5, 'testfunc');
	assert_(foo.test, '6789', 'foo->test 1');
	foo.test = '98765';
	assert_(foo.test, '98765', '98765');
	assert_(foo.testCall(5), 5, 'testCall(5)');
}).call(N.testMagicMethods);