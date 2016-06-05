var beautify = require('/usr/local/lib/node_modules/js-beautify/js/index.js').js_beautify,
	fs = require('fs'),
	path = require('path');

function sendError(message) {
	console.log(JSON.stringify({error: message}));
	process.exit(1);
}
if (process.argv.length != 3) {
	sendError("bad arguments");
}

var asserts = [];
global.assert_ = function (what, to, message) {
	if (typeof message == 'undefined') message = 'no message';
	asserts.push({
		'what': what,
		to: to,
		message: message
	});
};
global.count = function (mixed_var, mode) {
	var key, cnt = 0;

	if (mode == 'COUNT_RECURSIVE') mode = 1;
	if (mode != 1) mode = 0;

	for (key in mixed_var) {
		cnt++;
		if (mode == 1 && mixed_var[key] && (mixed_var[key].constructor === Array || mixed_var[key].constructor === Object)) {
			cnt += count(mixed_var[key], 1);
		}
	}

	return cnt;
};
global.get_class = function (obj) {
	if (obj && typeof obj === 'object' &&
		Object.prototype.toString.call(obj) !== '[object Array]' &&
		obj.constructor && obj !== this.window) {
		var arr = obj.constructor.toString().match(/function\s*(\w+)/);

		if (arr && arr.length === 2) {
			return arr[1];
		}
	}
	return false;
};
global.Exception = function (msg) {
	this.msg = msg;
};
global.json_encode=function(value){
	return JSON.stringify(value);
};
global.sqrt=function(value){
	return Math.sqrt(value);
};
var include_ = function (fileName) {
	var ev = require(fileName);
	for (var prop in ev) {
		//console.log(prop);
		global[prop] = ev[prop];
	}
};
var __ROOT__ = path.dirname(process.argv[2]) + path.sep;
global.include = function (path) {
	return fs.readFileSync(__ROOT__ + path) + '';
};
global.FALSE = false;


include_("../../../../lib/phptojs/lib/js/classManager.js");
include_("../../../../lib/phptojs/lib/js/HashArray.js");
include_("../../../../lib/phptojs/lib/js/JsObject.js");
include_("../../../../lib/phptojs/lib/js/JsArray.js");

var content = fs.readFileSync(process.argv[2]);
content = beautify(content + '', {
	indent_with_tabs: true,
	preserve_newlines: false
});
fs.writeFileSync(process.argv[2], content);


include_(process.argv[2]);

console.log(JSON.stringify(asserts));