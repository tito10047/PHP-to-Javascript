var FooException_ = (function(parent) {
	__extends(FooException_, parent);

	function FooException_( /*constructor arguments*/ ) {
		parent.call(this /*constructor arguments*/ );
	}
	return FooException_;
})(Exception);
var Foo2Exception_ = (function(parent) {
	__extends(Foo2Exception_, parent);

	function Foo2Exception_( /*constructor arguments*/ ) {
		parent.call(this /*constructor arguments*/ );
	}
	return Foo2Exception_;
})(FooException_);
var BeeException_ = (function(parent) {
	__extends(BeeException_, parent);

	function BeeException_( /*constructor arguments*/ ) {
		parent.call(this /*constructor arguments*/ );
	}
	return BeeException_;
})(Exception);
var GooException_ = (function(parent) {
	__extends(GooException_, parent);

	function GooException_( /*constructor arguments*/ ) {
		parent.call(this /*constructor arguments*/ );
	}
	return GooException_;
})(Exception);
try {
	throw new GooException_();
} catch (__e__) {
	var e;
	if (__e__ instanceof GooException_) {
		e = __e__;
		assert_(true, true, 'GooException');
	} else if (__e__ instanceof Exception) {
		e = __e__;
		assert_(true, false, 'GooException');
	}
}
var finally_;
finally_ = false;
var Foo2Exception;
Foo2Exception = false;
try {
	throw new Foo2Exception_();
} catch (__e__) {
	var e;
	if (__e__ instanceof Foo2Exception_) {
		e = __e__;
		Foo2Exception = true;
	} else if (__e__ instanceof FooException_) {
		e = __e__;
		assert_(true, false, 'Foo2Exception');
	} else if (__e__ instanceof Exception) {
		e = __e__;
		assert_(true, false, 'Foo2Exception');
		var t;
		t = e.getLine();
	}
} finally {
	finally_ = true;
}
assert_(true, Foo2Exception, 'Foo2Exception');
assert_(true, finally_, 'finally');
var FooException;
FooException = false;
try {
	throw new FooException_();
} catch (__e__) {
	var e;
	if (__e__ instanceof Foo2Exception_) {
		e = __e__;
		assert_(true, false, 'FooException');
	} else if (__e__ instanceof FooException_) {
		e = __e__;
		FooException = true;
	} else if (__e__ instanceof Exception) {
		e = __e__;
		assert_(true, false, 'FooException');
	}
}
assert_(true, FooException, 'FooException');