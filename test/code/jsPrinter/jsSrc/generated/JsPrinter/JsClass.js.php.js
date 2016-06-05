/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 3.6.2016
 * Time: 19:19
 */
/** @var {{testJsClass: {}}} N*/
N._INIT_('testJsClass');
(function() {
	var JsObject = N.jsphp.JsObject;
	var jsClass;
	jsClass = new JsObject({
		0: 1,
		1: 'ggg',
		2: 5,
		3: 2,
		4: 'ff'
	});
	var testIndexes;
	testIndexes = {
		0: 1,
		1: 'ggg',
		2: 5,
		3: 2,
		4: 'ff'
	};
	var index;
	index = 0;
	var _key_;
	for (_key_ in jsClass) {
		var property;
		property = jsClass[_key_];
		assert_(property, testIndexes[index], "test JsClas unindexed index " + index + "");
		index++;
	}
	jsClass = new JsObject();
	jsClass.aaa = 1;
	jsClass.ccc = 2;
	jsClass.bbb = 3;
	jsClass['122'] = 4;
	jsClass['111'] = 5;
	jsClass['011'] = 6;
	jsClass['001'] = 7;
	testIndexes = {
		'111': 5,
		'122': 4,
		'aaa': 1,
		'ccc': 2,
		'bbb': 3,
		'011': 6,
		'001': 7
	};
	var testIndexesKeys;
	testIndexesKeys = {
		0: '111',
		1: '122',
		2: 'aaa',
		3: 'ccc',
		4: 'bbb',
		5: '011',
		6: '001'
	};
	index = 0;
	var key;
	for (key in jsClass) {
		property = jsClass[key];
		assert_(property, testIndexes[testIndexesKeys[index]], "test JsClas indexed values index " + index + "");
		assert_(key, testIndexesKeys[index], "test JsClas indexed keys index " + index + "");
		index++;
	}
	var obj;
	obj = {
		'a': 1
	};
	var copy;
	copy = JsObject.assign({}, obj);
	assert_(copy.a, 1, 'JsArray::assign 1');
	var o1;
	o1 = {
		'a': 1
	};
	var o2;
	o2 = {
		'b': 2
	};
	var o3;
	o3 = {
		'c': 3
	};
	obj = JsObject.assign(o1, o2, o3);
	assert_(obj.a, 1, 'JsArray::assign 2');
	assert_(obj.b, 2, 'JsArray::assign 3');
	assert_(obj.c, 3, 'JsArray::assign 4');
	var v1;
	v1 = 'abc';
	var v2;
	v2 = true;
	var v3;
	v3 = 10;
	var v4;
	v4 = 'fd';
	obj = JsObject.assign({}, v1, null, v2, undefined, v3, v4);
	assert_(obj[0], 'f', 'JsArray::assign 5');
	assert_(obj[1], 'd', 'JsArray::assign 6');
	assert_(obj[2], 'c', 'JsArray::assign 7');
}).call(N.testJsClass);