/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 28.5.2016
 * Time: 9:57
 */
var isNull, isFoo, isFive, __LIST_VALUES__;
__LIST_VALUES__ = {
	0: null,
	1: 'foo',
	2: 5
};
isNull = __LIST_VALUES__[0];
isFoo = __LIST_VALUES__[1];
isFive = __LIST_VALUES__[2];
assert_(isNull, null, 'is null');
assert_(isFoo, 'foo', 'is foo');
assert_(isFive, 5, 'is five');
__LIST_VALUES__ = {
	0: null,
	1: 'foo',
	2: 5
};
isFive = __LIST_VALUES__[2];
assert_(isFive, 5, 'is five again');