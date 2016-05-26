var Foo = (function() {
	function Foo( /*constructor arguments*/ ) {
		this.publicVar = 'publicVar';
	}
	Foo.publicStaticVar = 'publicStaticVar';
	Foo.prototype.publicFunc = function(publicVar) {
		this.publicVar = publicVar;
	};
	Foo.publicStaticFunc = function() {
		return 5;
	};
	Foo.CONSTANT = 'CONSTANT';
	return Foo;
})();
var foo1;
foo1 = new Foo();
var foo2;
foo2 = new Foo();
foo1.publicFunc(5);
assert_(foo1.publicVar, 5, 'foo1 publicvar');
assert_(foo2.publicVar, 'publicVar', 'foo1 publicvar');
assert_(Foo.publicStaticVar, 'publicStaticVar', 'publicStaticVar');
assert_(Foo.publicStaticFunc(), 5, 'publicStaticFunc');