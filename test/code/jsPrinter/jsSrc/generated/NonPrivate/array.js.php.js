// count test
var a;
a = {};
assert_(count(a), 0, 'array count 0');
// Hash test
a = new N.PhpJs.HashArray();
a.set('dKey', 'item0');
a.push('item1');
a.set('3', 'item2');
a.set(5, 'item3');
a.push('item4');
a.set('bKey', 'item5');
a.set('aKey', 'item6');
var items;
items = {
	0: 'item0',
	1: 'item1',
	2: 'item2',
	3: 'item3',
	4: 'item4',
	5: 'item5',
	6: 'item6'
};
var keys;
keys = {
	0: 'dKey',
	1: 0,
	2: 3,
	3: 5,
	4: 6,
	5: 'bKey',
	6: 'aKey'
};
for (i = 0; a.valid(); i++, a.next()) {
	if (i > 100) {
		var i;
		throw new Exception('out of range');
	}
	var val;
	val = a.current();
	var key;
	key = a.key();
	assert_(key, keys[i], 'key assert');
	assert_(val, items[i], 'value assert');
}
assert_(a.dKey, 'item0', 1);
assert_(a['dKey'], 'item0', 2);
assert_(a['dKey'], 'item0', 3);
key = 'dKey';
assert_(a[key], 'item0', 4);
assert_(a[key], 'item0', 5);
assert_(a[key], 'item0', 6);