var value1;
value1 = 5;
var value2;
value2 = 10;
var variableString;
variableString = "" + value1 + "\n\
" + value2 + "";
assert_(variableString, '5\n\
10');
var multiLineString;
multiLineString = 'This is a string\n\
That spans two lines';
var testInlineString;
testInlineString = "test JsClas indexed values index " + value2 + "";
assert_(testInlineString, 'test JsClas indexed values index 10');