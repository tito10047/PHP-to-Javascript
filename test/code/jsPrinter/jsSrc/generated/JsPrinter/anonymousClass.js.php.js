N._INIT_('anonymusClass');
(function() {
	var AAA = this.AAA = (function() {
		function AAA() {
			__IS_INHERITANCE__ = false;
		}
		return AAA;
	})();
	var BBB = this.BBB = (function() {
		function BBB() {
			__IS_INHERITANCE__ = false;
			__INTERFACE_NEW__();
		}
		return BBB;
	})();
	var CCC = this.CCC = (function() {
		function CCC() {
			__IS_INHERITANCE__ = false;
			__INTERFACE_NEW__();
		}
		return CCC;
	})();
	var a;
	a = new(function() {
		function __anonymous__() {
			__IS_INHERITANCE__ = false;
		}
		return __anonymous__;
	})();
	var b;
	b = new(function(parent) {
		function __anonymous__() {
			__IS_INHERITANCE__ = true;
			parent.call(this);
		}
		__extends(__anonymous__, parent, arguments[1]);
		return __anonymous__;
	})(AAA, [BBB, CCC]);
	var c;
	c = new(function(parent, a) {
		var __private = __PRIVATIZE__();

		function __anonymous__(a) {
			var __isInheritance = __IS_INHERITANCE__;
			__IS_INHERITANCE__ = true;
			parent.call(this);
			__private(this).a = null;
			if (__isInheritance == false) {
				this.__construct(a);
			}
		}
		__extends(__anonymous__, parent);
		__anonymous__.prototype.__construct = function(a) {
			__private(this).a = a;
		};
		return __anonymous__;
	})(AAA, a);
}).call(N.anonymusClass);