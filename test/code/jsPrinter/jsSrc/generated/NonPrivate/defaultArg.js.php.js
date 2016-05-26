function getTotal(value1, value2) {
	if (typeof value2 == 'undefined') value2 = 5;
	return value1 + value2;
}
var mathTotal;
mathTotal = getTotal(5);
assert_(mathTotal, 10);