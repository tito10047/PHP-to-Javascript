<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 3.6.2016
 * Time: 19:19
 */

namespace testJsClass {

	use jsphp\JsObject;

	$jsClass = new JsObject([1, "ggg", 5, 2, "ff"]);
	$testIndexes = [1, "ggg", 5, 2, "ff"];

	$index = 0;
	foreach ($jsClass as $property) {
		assert_($property, $testIndexes[$index], "test JsClas unindexed index {$index}");
		$index++;
	}

	$jsClass = new JsObject();
	$jsClass->aaa=1;
	$jsClass->ccc=2;
	$jsClass->bbb=3;
	$jsClass['122'] = 4;
	$jsClass['111'] = 5;
	$jsClass['011'] = 6;
	$jsClass['001'] = 7;

	$testIndexes = [
		"111"=>5,
		'122'=>4,
		"aaa"=>1,
		"ccc"=>2,
		"bbb"=>3,
		'011'=>6,
		'001'=>7
	];
	$testIndexesKeys=["111",'122',"aaa","ccc","bbb",'011','001'];

	$index = 0;
	foreach ($jsClass as $key=>$property) {
		assert_($property, $testIndexes[$testIndexesKeys[$index]], "test JsClas indexed values index {$index}");
		assert_($key, $testIndexesKeys[$index], "test JsClas indexed keys index {$index}");
		$index++;
	}

	$obj = ["a"=>1];
	$copy = JsObject::assign([], $obj);
	assert_($copy->a,1, "JsArray::assign 1");


	$o1 = ["a"=>1];
	$o2 = ["b"=>2];
	$o3 = ["c"=>3];

	$obj = JsObject::assign($o1, $o2, $o3);

	assert_($obj->a,1, "JsArray::assign 2");
	assert_($obj->b,2, "JsArray::assign 3");
	assert_($obj->c,3, "JsArray::assign 4");


	$v1 = 'abc';
	$v2 = true;
	$v3 = 10;
	$v4 = "fd";

	$obj = JsObject::assign([], $v1, null, $v2, undefined, $v3, $v4);
// Primitives will be wrapped, null and undefined will be ignored.
// Note, only string wrappers can have own enumerable properties.
//console.log(obj); // { "0": "a", "1": "b", "2": "c" }
	assert_($obj[0],"f", "JsArray::assign 5");
	assert_($obj[1],"d", "JsArray::assign 6");
	assert_($obj[2],"c", "JsArray::assign 7");
}