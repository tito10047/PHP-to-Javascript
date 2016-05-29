N._INIT_('anonymusClass');
(function() {
	var AAA = this.AAA = (function() {
		function AAA() {}
		return AAA;
	})();
	var BBB = this.BBB = (function() {
		function BBB() {
			__INTERFACE_NEW__();
		}
		return BBB;
	})();
	var CCC = this.CCC = (function() {
		function CCC() {
			__INTERFACE_NEW__();
		}
		return CCC;
	})();
	var a;
	a = new(function() {
		function __anonymous__() {}
		return __anonymous__;
	})();
	var b;
	b = new(function(parent) {
		function __anonymous__() {
			var __OLD_IS_INHERITANCE__ = __IS_INHERITANCE__;
			__IS_INHERITANCE__ = true;
			parent.call(this);
			__IS_INHERITANCE__ = __OLD_IS_INHERITANCE__;
		}
		__extends(__anonymous__, parent, arguments[1]);
		return __anonymous__;
	})(AAA, [BBB, CCC]);
	var c;
	c = new(function(parent, a) {
		function __anonymous__(a) {
			var __OLD_IS_INHERITANCE__ = __IS_INHERITANCE__;
			__IS_INHERITANCE__ = true;
			parent.call(this);
			__IS_INHERITANCE__ = __OLD_IS_INHERITANCE__;
			this.a = null;
			if (__IS_INHERITANCE__ == false) {
				this.__construct(a);
			}
		}
		__extends(__anonymous__, parent);
		__anonymous__.prototype.__construct = function(a) {
			this.a = a;
		};
		return __anonymous__;
	})(AAA, a);
}).call(N.anonymusClass);