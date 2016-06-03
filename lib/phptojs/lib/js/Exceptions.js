function Exit(msg) {
	if (typeof msg != "undefined") {
		alert(msg);
	}
}
function Exception(msg) {
	this.msg = msg;
}