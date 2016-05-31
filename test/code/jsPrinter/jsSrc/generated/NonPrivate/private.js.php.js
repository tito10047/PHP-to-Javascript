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

		function ParentClass() {
			__IS_INHERITANCE__ = false;
			__private(this).privateParent = 'privateParent';
			this.publicParent = 'publicParent';
			__private(this).overridePrivateParent = 'overridePrivateParent';
			var __self = this;
			__private(this).getPrivateFunc = function() {
				return __private(__self).privateParent;
			};
		}
		ParentClass.prototype.getPublicFuncGetPrivate = function() {
			return __private(this).getPrivateFunc();
		};
		ParentClass.prototype.getPublicFunc = function() {
			return this.publicParent;
		};
		ParentClass.prototype.getOverridePrivateParent = function() {
			return __private(this).overridePrivateParent;
		};
		return ParentClass;
	})();
	var Children = this.Children = (function(parent) {
		var __private = __PRIVATIZE__();

		function Children() {
			__IS_INHERITANCE__ = true;
			parent.call(this);
			__private(this).privateChildren = 'privateChildren';
			this.publicChildren = 'publicChildren';
			__private(this).overridePrivateParent = 'overridePrivateParent in Children';
			var __self = this;
			__private(this).getPrivateFunc = function() {
				return __self.publicChildren;
			};
		}
		__extends(Children, parent);
		Children.prototype.getPublicFunc = function() {
			return __private(this).privateChildren;
		};
		Children.prototype.testParentPublicFuncGetPrivate = function() {
			return parent.prototype.getPublicFuncGetPrivate.call(this);
		};
		Children.prototype.testParentPublicFunc = function() {
			return parent.prototype.getPublicFunc.call(this);
		};
		Children.prototype.testOverridePrivateParent = function() {
			return parent.prototype.getOverridePrivateParent.call(this);
		};
		return Children;
	})(ParentClass);
	var children;
	children = new Children();
	assert_(children.testParentPublicFunc(), 'publicParent', 'testParentPublicFunc');
	assert_(children.testParentPublicFuncGetPrivate(), 'privateParent', 'testParentPublicFunc');
	assert_(children.getPublicFunc(), 'privateChildren', 'getPublicFunc');
	assert_(children.testOverridePrivateParent(), 'overridePrivateParent', 'getPublicFunc');
}).call(N.privateTest);