/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 4.6.2016
 * Time: 22:41
 */
if (typeof N == 'undefined') N = {};
if (typeof N.jsphp == 'undefined') N.jsphp = {};
(function () {
	var functions = ["indexOf", "concat", "copyWithin", "entries", "every",
		"filter", "find", "findIndex", "forEach", "includes", "indexOf",
		"join", "keys", "lastIndexOf", "map", "pop", "push", "reduce",
		"reduceRight", "reverse", "shift", "slice", "some", "sort",
		"splice", "toLocaleString", "toSource", "toString", "unshift",
		"values", "fill"
	];

	var JsArray = this.JsArray = function () {
		var arr = [];
		arr.push.apply(arr, arguments);
		arr.__proto__ = JsArray.prototype;
		if (arr.forEach === undefined) {
			// FIXED: for nodejs
			for (var i = 0; i < functions.length; i++) {
				arr.__proto__[functions[i]] = Array.prototype[functions[i]];
			}
		}
		return arr;
	};
	__extends(JsArray,Array);
	var arrayStaticMethods = ["from", "isArray", "of"];
	for (var i = 0; i < arrayStaticMethods.length; i++) {
		JsArray[arrayStaticMethods[i]] = Array[arrayStaticMethods[i]];
	}
}).call(N.jsphp);