var foo;
foo = 1;
eval(include("includeIt.php.js"))
;
assert_(foo, 2, 'foo=2');
