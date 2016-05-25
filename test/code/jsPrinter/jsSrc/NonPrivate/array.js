// count test
a = [];
assert(count(a),0,"array count 0");

// Hash test
var a = new N.PhpJs.HashArray();
a.set("dKey","item0");
a.push('item1');
a.set("3",'item2');
a.set(5,'item3');
a.push('item4');
a.set('bKey','item5');
a.set('aKey','item6');

items = ['item0','item1','item2','item3','item4','item5',"item6"];
keys = ['dKey',0,3,5,6,"bKey","aKey"];

for(var i=0;a.valid();i++,a.next()){
    var val = a.current();
    var key = a.key();
    assert(val==items[i]);
    assert(key==keys[i]);
}

assert(a.dKey,"item5");
assert(a['dKey'],"item5");
assert(a['dKey'],"item5");

key='dKey';
assert(a[key],"item5");
assert(a[key],"item5");
assert(a[key],"item5");