var AAA = (function() {
	function AAA( /*constructor arguments*/ ) {}
	return AAA;
})();
var BBB = (function() {
	function BBB( /*constructor arguments*/ ) {
		__INTERFACE_NEW__();
	}
	return BBB;
})();
var CCC = (function() {
	function CCC( /*constructor arguments*/ ) {
		__INTERFACE_NEW__();
	}
	return CCC;
})();
var a;
a = new(function() {
	function __anonymous__( /*constructor arguments*/ ) {}
	return __anonymous__;
})();
var b;
b = new(function(parent) {
	function __anonymous__( /*constructor arguments*/ ) {
		parent.call(this /*constructor arguments*/ );
	}
	__extends(__anonymous__, parent, arguments[1]);
	return __anonymous__;
})(AAA, [BBB, CCC]);
var c;
c = new(function(parent, a) {
	function __anonymous__( /*constructor arguments*/ ) {
		parent.call(this /*constructor arguments*/ );
		this.a = null;
	}
	__extends(__anonymous__, parent);
	__anonymous__.prototype.__construct = function(a) {
		this.a = a;
	};
	return __anonymous__;
})(AAA, a);