N._INIT_('test');
(function(){
var Foo = this.Foo = (function (){
	function Foo(/*constructor arguments*/){
		
	}
	Foo.prototype.test = function(){
			return 6;

};
	return Foo;
})();

}).call(N.test);

var Foo = (function (){
	function Foo(/*constructor arguments*/){
		
		
				this.publicVar = 'publicVar';

		
		
	}
	Foo.publicStaticVar = 'publicStaticVar';
	Foo.prototype.publicFunc = function(publicVar){
			this.publicVar = publicVar;
};
	Foo.publicStaticFunc = function(){
			return 5;

};
	Foo.CONSTANT = 'CONSTANT';
	return Foo;
})();

var foo1;
foo1 = new Foo();
var foo2;
foo2 = new Foo();
var testFoo;
testFoo = 'test\\Foo';
var foo3;
foo3 = new (N._GET_(testFoo))();
var foo4;
foo4 = foo3 instanceof N.test.Foo;
assert_(foo4, true, 'foo3 is not instanceof test\\Foo');
if (!foo3 instanceof N._GET_(testFoo)){
		assert_(false, true, 'foo3 is not instanceod testFoo');
}

foo1.publicFunc(5);
assert_(foo1.publicVar, 5, 'foo1 publicvar');
assert_(foo2.publicVar, 'publicVar', 'foo1 publicvar');
assert_(Foo.publicStaticVar, 'publicStaticVar', 'publicStaticVar');
assert_(Foo.publicStaticFunc(), 5, 'publicStaticFunc');

