<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 4.6.2016
 * Time: 23:24
 */
namespace testJsArray{

	use jsphp\JsArray;

	$fruits = new JsArray("Apple", "Banana");

	assert_($fruits->length,2,"JsArray test 1");
	assert_($fruits[0],"Apple","JsArray test 2");
	assert_($fruits[$fruits->length - 1],"Banana","JsArray test 3");
	
	$values=["Apple", "Banana"];
	$indexPos=0;

	$fruits->forEach(function($value,$index,$array) use(&$values,&$indexPos){
		assert_($value,$values[$indexPos], "JsArray test 4.1.{$indexPos}");
		assert_($index,$index, "JsArray test 4.2.{$indexPos}");
		$indexPos++;
	});

	$oneTwoThree=new JsArray(1,2,3);
	$a = JsArray::from($oneTwoThree);

	assert_($a->length,3,"JsArray test 5");
	assert_($a[2],3,"JsArray test 6");

	$a = JsArray::from("string");

	assert_($a->length,6,"JsArray test 6");
	assert_($a[2],"r","JsArray test 7");

	// Using an arrow function as the map function to
	// manipulate the elements
	$a = JsArray::from($oneTwoThree, function($v){
		return $v+$v;
	});
	assert_($a->length,3,"JsArray test 8");
	assert_($a[2],6,"JsArray test 9");

	// Generate a sequence of numbers
	$a = JsArray::from(["length"=>5], function($v, $k){
		return $k;
	});
	assert_($a->length,5,"JsArray test 10");
	assert_($a[2],2,"JsArray test 11");

	$a = JsArray::of(1, 2, 3);
	assert_($a->length,3,"JsArray test 11.2");
	assert_($a[2],3,"JsArray test 11.3");
	
	$alpha = new JsArray('a', 'b', 'c');	
    $numeric = new JsArray(1, 2, 3);

	$alphaNumeric = $alpha->concat($numeric);
	assert_($alphaNumeric->length,6,"JsArray test  12");
	assert_($alphaNumeric[5],3,"JsArray test  13");

	$oneTwo = new JsArray(2,3);
	$alphaNumeric = $alpha->concat(1, $oneTwo);
	assert_($alphaNumeric->length,6,"JsArray test  14");
	assert_($alphaNumeric[5],3,"JsArray test  15");

	$arr = new JsArray('a', 'b', 'c');
	$eArr = $arr->entries();

	assert_($eArr->next()->value[1],'a',"JsArray test  16");
	assert_($eArr->next()->value[1],'b',"JsArray test  17");
	assert_($eArr->next()->value->length,2,"JsArray test  18");

//	Not working in nodejs
//	$eArr = $arr->entries();
//	foreach($eArr as $key=>$value){
//		assert_($value,$arr[$key],"JsArray test  19.".$key);
//	}

	$isBigEnough = function($element, $index, $array) {
		return $element >= 10;
	};
	$check = (new JsArray(12, 5, 8, 130, 44))->every($isBigEnough);
	assert_($check,false,"JsArray test  20");
	$check = (new JsArray(12, 54, 18, 130, 44))->every($isBigEnough);
	assert_($check,true,"JsArray test  21");

	assert_(json_encode((new JsArray(1, 2, 3))->fill(4)),"[4,4,4]","JsArray test  22");
	assert_(json_encode((new JsArray(1, 2, 3))->fill(4,1)),"[1,4,4]","JsArray test  23");
	assert_(json_encode((new JsArray(1, 2, 3))->fill(4,1,2)),"[1,4,3]","JsArray test  24");
	assert_(json_encode((new JsArray(1, 2, 3))->fill(4,1,1)),"[1,2,3]","JsArray test  25");
	assert_(json_encode((new JsArray(1, 2, 3))->fill(4,-3,-2)),"[4,2,3]","JsArray test  26");
	assert_(json_encode((new JsArray(1, 2, 3))->fill(4,"dsad","dasdas")),"[1,2,3]","JsArray test  27");

	$isBigEnough = function($value) {
		return $value >= 10;
	};
	$filtered = (new JsArray(12, 5, 8, 130, 44))->filter($isBigEnough);
	assert_(json_encode($filtered),"[12,130,44]","JsArray test 28");

	$isPrime = function($element, $index, $array){
		$start = 2;
		while ($start <= sqrt($element)) {
			if ($element % $start++ < 1) {
				return false;
			}
		}
		return $element > 1;
	};
	assert_((new JsArray(4, 6, 8, 12))->find($isPrime),undefined,"JsArray test 29");
	assert_((new JsArray(4, 5, 8, 12))->find($isPrime),5,"JsArray test 30");

	assert_((new JsArray(4, 6, 8, 12))->findIndex($isPrime),-1,"JsArray test 31");
	assert_((new JsArray(4, 6, 7, 12))->findIndex($isPrime),2,"JsArray test 32");

	assert_((new JsArray(1, 2, 3))->includes(2),true,"JsArray test 33");
	assert_((new JsArray(1, 2, 3))->includes(4),false,"JsArray test 34");
	assert_((new JsArray(1, 2, 3))->includes(3,3),false,"JsArray test 35");
	assert_((new JsArray(1, 2, 3))->includes(3,-1),true,"JsArray test 36");

	$array = new JsArray(2, 9, 9);
	assert_($array->indexOf(2),0,"JsArray test 37");
	assert_($array->indexOf(7),-1,"JsArray test 38");
	assert_($array->indexOf(9, 2),2,"JsArray test 39");
	assert_($array->indexOf(2, -1),-1,"JsArray test 40");
	assert_($array->indexOf(2, -3),0,"JsArray test 41");

	$arr = new JsArray("a", "b", "c");
	$iterator = $arr->keys();

	assert_($iterator->next()->value,0,"JsArray test 42");
	assert_($iterator->next()->value,1,"JsArray test 43");
	assert_($iterator->next()->value,2,"JsArray test 44");
	assert_($iterator->next()->done,true,"JsArray test 45");

	$array = new JsArray(2, 5, 9, 2);
	assert_($array->lastIndexOf(2),3,"JsArray test 46");
	assert_($array->lastIndexOf(7),-1,"JsArray test 47");
	assert_($array->lastIndexOf(2, 3),3,"JsArray test 48");
	assert_($array->lastIndexOf(2, 2),0,"JsArray test 49");
	assert_($array->lastIndexOf(2, -2),0,"JsArray test 50");
	assert_($array->lastIndexOf(2, -1),3,"JsArray test 51");

	$numbers = new JsArray(1, 4, 9);
	$doubles = $numbers->map(function($num) {
		return $num * 2;
	});
	assert_(json_encode($doubles),"[2,8,18]","JsArray test 52");

	$myFish = new JsArray('angel', 'clown', 'mandarin', 'sturgeon');
	$popped = $myFish->pop();
	assert_(json_encode($myFish),'["angel","clown","mandarin"]',"JsArray test 53");
	assert_($popped,"sturgeon","JsArray test 54");

	$sports = new JsArray('soccer', 'baseball');
	$total = $sports->push('football', 'swimming');
	assert_(json_encode($sports),'["soccer","baseball","football","swimming"]',"JsArray test 54");
	assert_($total,4,"JsArray test 55");

	$testArray=new JsArray(0,1,2,3,4);
	$mapFunc = function($previousValue, $currentValue, $currentIndex, $array) {
		return $previousValue + $currentValue;
	};
	$value = $testArray->reduce($mapFunc);
	assert_($value,10,"JsArray test 56");
	$value = $testArray->reduce($mapFunc,10);
	assert_($value,20,"JsArray test 57");

	$value = $testArray->reduceRight($mapFunc);
	assert_($value,10,"JsArray test 58");
	$value = $testArray->reduceRight($mapFunc,10);
	assert_($value,20,"JsArray test 59");

	$myArray = new JsArray('one', 'two', 'three');
	$myArray->reverse();
	assert_(json_encode($myArray),'["three","two","one"]',"JsArray test 60");

	$myFish = new JsArray('angel', 'clown', 'mandarin', 'surgeon');
	$shifted = $myFish->shift();
	assert_(json_encode($myFish),'["clown","mandarin","surgeon"]',"JsArray test 61");
	assert_($shifted,"angel","JsArray test 62");

	$fruits = new JsArray('Banana', 'Orange', 'Lemon', 'Apple', 'Mango');
	$citrus = $fruits->slice(1, 3);
	assert_(json_encode($citrus),'["Orange","Lemon"]',"JsArray test 62");

	$isBiggerThan10 = function($element, $index, $array) {
		return $element > 10;
	};
	assert_((new JsArray(2, 5, 8, 1, 4))->some($isBiggerThan10),false,"JsArray test 63");
	assert_((new JsArray(12, 5, 8, 1, 4))->some($isBiggerThan10),true,"JsArray test 64");


	$fruit = new JsArray('cherries', 'apples', 'bananas');
	$fruit->sort();
	assert_(json_encode($fruit),'["apples","bananas","cherries"]',"JsArray test 65");

	$scores = new JsArray(1, 10, 2, 21);
	$scores->sort();
	assert_(json_encode($scores),'[1,10,2,21]',"JsArray test 66");

	$things = new JsArray('word', 'Word', '1 Word', '2 Words');
	$things->sort();
	assert_(json_encode($things),'["1 Word","2 Words","Word","word"]',"JsArray test 67");

	$myFish = new JsArray('angel', 'clown', 'mandarin', 'surgeon');
	$removed = $myFish->splice(2, 0, 'drum');
	assert_(json_encode($myFish),'["angel","clown","drum","mandarin","surgeon"]',"JsArray test 68");
	assert_(json_encode($removed),'[]',"JsArray test 69");

	$removed = $myFish->splice(3, 1);
	assert_(json_encode($myFish),'["angel","clown","drum","surgeon"]',"JsArray test 70");
	assert_(json_encode($removed),'["mandarin"]',"JsArray test 71");

	$removed = $myFish->splice(2, 1, 'trumpet');
	assert_(json_encode($myFish),'["angel","clown","trumpet","surgeon"]',"JsArray test 72");
	assert_(json_encode($removed),'["drum"]',"JsArray test 73");

	$removed = $myFish->splice(0, 2, 'parrot', 'anemone', 'blue');
	assert_(json_encode($myFish),'["parrot","anemone","blue","trumpet","surgeon"]',"JsArray test 74");
	assert_(json_encode($removed),'["angel","clown"]',"JsArray test 75");

	$removed = $myFish->splice($myFish->length -3, 2);
	assert_(json_encode($myFish),'["parrot","anemone","surgeon"]',"JsArray test 76");
	assert_(json_encode($removed),'["blue","trumpet"]',"JsArray test 77");

	$arr = new JsArray(1, 2);

	assert_($arr->unshift(0),3,"JsArray test 78");
	assert_(json_encode($arr),"[0,1,2]","JsArray test 79");

	assert_($arr->unshift(-2, -1),"5","JsArray test 80");
	assert_(json_encode($arr),"[-2,-1,0,1,2]","JsArray test 81");

//	 not working in nodejs
//	$arr = new JsArray('w', 'y', 'k', 'o', 'p');
//	$eArr = $arr->values();
//	assert_($eArr->next()->value,"w","JsArray test 82");
//	assert_($eArr->next()->value,"y","JsArray test 83");
//	assert_($eArr->next()->value,"k","JsArray test 84");
//	assert_($eArr->next()->value,"o","JsArray test 85");
//	assert_($eArr->next()->value,"p","JsArray test 86");
}