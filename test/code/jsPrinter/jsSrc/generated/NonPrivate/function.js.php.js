N._INIT_('fooFunctions');
(function() {
	var B = this.B = (function() {
		function B( /*constructor arguments*/ ) {}
		return B;
	})();
}).call(N.fooFunctions);
N._INIT_('functions');
(function() {
	var A = this.A = (function() {
		function A( /*constructor arguments*/ ) {}
		A.prototype.f1 = function() {};
		A.prototype.f2 = function(a, b) {};
		A.prototype.f4 = function(a) {
			if (!a instanceof N.fooFunctions.B) throw new Error('bad param type');
		};
		A.prototype.f5 = function(a) {
			if (!isCallable(a)) throw new Error('bad param type');
		};
		A.prototype.f7 = function(...a) {};
		A.prototype.f10 = function(...qq) {
			for (var __paraPos = 0; __paraPos < arguments.length; __paraPos++) {
				if (!arguments[__paraPos] instanceof A) throw new Error('bad param type');
			}
		};
		A.prototype.f11 = function(a, b, ...qq) {
			if (!a instanceof A) throw new Error('bad param type');
			for (var __paraPos = 2; __paraPos < arguments.length; __paraPos++) {
				if (!arguments[__paraPos] instanceof A) throw new Error('bad param type');
			}
		};
		A.prototype.f13 = function(a) {};
		A.prototype.f14 = function(a) {};
		A.prototype.f15 = function(a) {};
		A.prototype.f16 = function(a) {
			if (!isArray(a)) throw new Error('bad param type');
		};
		return A;
	})();
}).call(N.functions);