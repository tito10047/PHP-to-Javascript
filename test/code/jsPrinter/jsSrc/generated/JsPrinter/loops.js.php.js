/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 28.5.2016
 * Time: 9:26
 */
var e;
e = 5;
var k;
k = 0;
var i;
for (i = 0; i < e; i++) {
	k++;
	if (i < e - 2) {
		continue;
	} else {
		break;
	}
}
assert_(k, 4, 'for loop');
var y;
y = {
	0: 1,
	1: 2,
	2: 3,
	3: 4,
	4: 5
};
k = 0;
var key;
for (key in y) {
	var val;
	val = y[key];
	k += parseInt(key) + val;
}
assert_(k, 25, 'foreach loop');
k = 0;
var _key_;
for (_key_ in y) {
	var val2;
	val2 = y[_key_];
	k += val2;
}
assert_(k, 15, 'foreach loop');
i = 0;
e = 5;
while (i < e) {
	i++;
	if (i < e - 1) {
		continue;
	} else {
		break;
	}
}
assert_(i, 4, 'while loop');
i = 0;
e = 5;
do {
	i++;
	if (i < e - 1) {
		continue;
	} else {
		break;
	}
} while (i < e);
assert_(i, 4, 'do while loop');