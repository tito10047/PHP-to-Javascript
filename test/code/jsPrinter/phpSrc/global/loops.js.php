<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 28.5.2016
 * Time: 9:26
 */
$e=5;
$k=0;
for($i=0;$i<$e;$i++){
	$k++;
	if ($i<$e-2){
		continue;
	}else{
		break;
	}
}
assert_($k,4,"for loop");

$y=[1,2,3,4,5];
$k=0;
foreach ($y as $key=>$val){
	$k+=(int)$key+$val;
}
assert_($k,25,"foreach loop");

$k=0;
foreach ($y as $val2){
	$k+=$val2;
}
assert_($k,15,"foreach loop");


$i=0;
$e=5;

while($i<$e){
	$i++;
	if ($i<$e-1){
		continue;
	}else{
		break;
	}
}
assert_($i,4,"while loop");

$i=0;
$e=5;
do{
	$i++;
	if ($i<$e-1){
		continue;
	}else{
		break;
	}
}while($i<$e);
assert_($i,4,"do while loop");