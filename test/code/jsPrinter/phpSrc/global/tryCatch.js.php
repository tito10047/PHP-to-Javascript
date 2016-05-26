<?php

class FooException_ extends Exception
{
}

;

class Foo2Exception_ extends FooException_
{
}

;

class BeeException_ extends Exception
{
}

;

class GooException_ extends Exception
{
}

;

try {
    throw new GooException_();
} catch (GooException_ $e) {
    assert_(true, true, "GooException");
} catch (Exception $e) {
    assert_(true, false, "GooException");
}
$finally_ = false;
$Foo2Exception = false;
try {
    throw new Foo2Exception_();
} catch (Foo2Exception_ $e) {
    $Foo2Exception = true;
} catch (FooException_ $e) {
    assert_(true, false, "Foo2Exception");
} catch (Exception $e) {
    assert_(true, false, "Foo2Exception");
    $t = $e->getLine();
} finally {
    $finally_ = true;
}
assert_(true, $Foo2Exception, "Foo2Exception");
assert_(true, $finally_, "finally");

$FooException = false;
try {
    throw new FooException_();
} catch (Foo2Exception_ $e) {
    assert_(true, false, "FooException");
} catch (FooException_ $e) {
    $FooException = true;
} catch (Exception $e) {
    assert_(true, false, "FooException");
}
assert_(true, $FooException, "FooException");