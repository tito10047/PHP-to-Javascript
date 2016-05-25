<?php
// count test
$a = array();
assert_(count($a),0,"array count 0");

// Hash test
//$a = new \PhpJs\HasArray();

$a = array(
    "dKey","item0",
    "item1",
    "3"=>"item2",
    5=>"item3",
    "item4",
    "bKey"=>"item5",
    "aKey"=>"item6",
);

$items = ['item0','item1','item2','item3','item4','item5',"item6"];
$keys = ['dKey',0,3,5,6,"bKey","aKey"];
$i=0;
foreach($a as $key=>$val){
    assert_($key,$keys[$i],'key assert');
    assert_($val,$items[$i],'value assert');
    $i++;
}

assert_($a{'dKey'},"item5");
assert_($a['dKey'],"item5");

$key='dKey';
assert_($a{$key},"item5");
assert_($a[$key],"item5");

