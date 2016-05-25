<?php
// count test
$a = array();
assert_(count($a),0,"array count 0");

// Hash test
$a = new \PhpJs\HashArray();
$a->set("dKey","item0");
$a->push('item1');
$a->set("3",'item2');
$a->set(5,'item3');
$a->push('item4');
$a->set('bKey','item5');
$a->set('aKey','item6');

$items = ['item0','item1','item2','item3','item4','item5',"item6"];
$keys = ['dKey',0,3,5,6,"bKey","aKey"];

for($i=0;$a->valid();$i++,$a->next()){
    if ($i>100) throw new Exception('out of range');
    $val = $a->current();
    $key = $a->key();
    assert_($key,$keys[$i],'key assert');
    assert_($val,$items[$i],'value assert');
}

assert_($a->dKey,"item0",1);
assert_($a->{'dKey'},"item0",2);
assert_($a['dKey'],"item0",3);

$key='dKey';
assert_($a->$key,"item0",4);
assert_($a->{$key},"item0",5);
assert_($a[$key],"item0",6);

