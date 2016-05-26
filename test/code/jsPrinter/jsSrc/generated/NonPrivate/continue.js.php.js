var total;
total = 0;
for (i = 0; i < 10; i++) {
	if (i % 2 == 0) {
		var i;
		continue;
	}
	total++;
}
assert_(total, 5);