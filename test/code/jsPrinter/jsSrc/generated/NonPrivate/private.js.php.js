/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 31.5.2016
 * Time: 15:50
 */
N._INIT_('privateTest');
(function() {
	var ParentClass = this.ParentClass = (function() {
		var __private = __PRIVATIZE__();
		var __getPrivateFunc = function() {
			return __private(this).privateParent;
		};

		function ParentClass() {
			__IS_INHERITANCE__ = false;
			__private(this).privateParent = 'privateParent';
			this.publicChildren = 'publicParent';
			__private(this).getPrivateFunc = __getPrivateFunc;
		}
		ParentClass.prototype.getPublicFunc = function() {
			return __private(this).getPrivateFunc();
		};
		return ParentClass;
	})();
	var Children = this.Children = (function(parent) {
		var __private = __PRIVATIZE__();
		var __getPrivateFunc = function() {
			return this.publicChildren;
		};

		function Children() {
			__IS_INHERITANCE__ = true;
			parent.call(this);
			__private(this).privateChildren = 'privateChildren';
			this.publicChildren = 'publicChildren';
			__private(this).getPrivateFunc = __getPrivateFunc;
		}
		__extends(Children, parent);
		Children.prototype.getPublicFunc = function() {
			return __private(this).privateChildren;
		};
		Children.prototype.testParentPublicFunc = function() {
			return parent.prototype.getPublicFunc.call(this);
		};
		return Children;
	})(ParentClass);
	var children;
	children = new Children();
	//assert_($children->testParentPublicFunc(),"privateParent","testParentPublicFunc");
	assert_(children.getPublicFunc(), 'privateChildren', 'getPublicFunc');
}).call(N.privateTest);