window.FOO = 1;
/** @var {{AAA: {}}} N*/
N._INIT_('AAA');
(function() {
	var FOO = 2;
	this.FOO = FOO;
	assert_(FOO, 2);
	var FooCls = this.FooCls = (function() {
		function FooCls() {
			__IS_INHERITANCE__ = false;
			this.foo2 = null;
		}
		FooCls.foo = 4;
		FooCls.fooStatic = function(foo) {
			if (typeof foo == 'undefined') foo = 5;
			var aa;
			aa = 5;
			return aa + foo;
		};
		FooCls.prototype.fooFunc = function() {
			return this.foo2;
		};
		return FooCls;
	})();
	assert_(FooCls.foo, 4);
	assert_(FooCls.fooStatic(), 10);
}).call(N.AAA);
/** @var {{AAA: {BBB: {}}}} N*/
N._INIT_('AAA.BBB');
(function() {
	var FOO = 3;
	this.FOO = FOO;
	assert_(FOO, 3);
}).call(N.AAA.BBB);
/** @var {{AAA: {BBB: {CCC: {}}}}} N*/
N._INIT_('AAA.BBB.CCC');
(function() {
	var CCCFOO = 46;
	this.CCCFOO = CCCFOO;
	var testNamespaceFunc = this.testNamespaceFunc = function() {
		return 465;
	};
}).call(N.AAA.BBB.CCC);
var PPP = N.AAA.BBB.CCC;
assert_(FOO, 1);
assert_(N.AAA.FOO, 2);
assert_(N.AAA.BBB.FOO, 3);
assert_(PPP.CCCFOO, 46);
assert_(N.AAA.FooCls.foo, 4);
assert_(N.AAA.FooCls.fooStatic(), 10);
var foo;
foo = new N.AAA.FooCls();
foo.foo2 = 6;
assert_(foo.foo2, 6, 'foo2');
assert_(foo.fooFunc(), 6, 'fooFunc');
assert_(PPP.testNamespaceFunc(), 465, 'testNamespaceFunc');