if (typeof N == 'undefined') N = {};
if (typeof N.jsphp == 'undefined') N.jsphp = {};
if (typeof window == 'undefined') window = global;

N._INIT_ = function (namespace) {
	namespace = namespace.split('.');
	var nam = N;
	for (var i = 0; i < namespace.length; i++) {
		if (typeof nam[namespace[i]] == 'undefined') {
			nam[namespace[i]] = {};
		}
		nam = nam[namespace[i]];
	}
};
N._GET_ = function (namespace) {
	namespace = namespace.split('\\');
	if (namespace.length == 1) {
		return window[namespace];
	}
	var nam = N;
	for (var i = 0; i < namespace.length; i++) {
		if (typeof nam[namespace[i]] == 'undefined') {
			return null;
		}
		nam = nam[namespace[i]];
	}
	return nam;
};

N.jsphp.JsObject=function () {
	if (arguments.length>0){
		if (arguments[0] instanceof Object){
			for(var i in arguments[0]){
				if (!arguments[0].hasOwnProperty(i)) continue;
				this[i]=arguments[0][i];
			}
		}
	}
};
N.jsphp.JsObject.prototype = new Object();
N.jsphp.JsObject.assign=function (target) {
	if (target===null || typeof target=="undefined"){
		throw new Exception("Cannot convert undefined or null to object");
	}
	if (!target instanceof N.jsphp.JsObject){
		if (target instanceof Array || target.constructor.name=="Object") {
			target = new N.jsphp.JsObject(target);
		}else if (typeof target=="function"){
			return target;
		}else{
			throw new Exception("This type is not implemented yet "+(typeof target));
		}
	}
	if (arguments.length>1){
		for(var i=1;i<arguments.length;i++){
			if (typeof arguments[i]=="string"){
				var chars=arguments[i].split("");
				for(var g=0;g<chars.length;g++){
					target[g]=chars[g];
				}
			}
			for(var e in arguments[i]){
				if (!arguments[i].hasOwnProperty(e)) continue;
				target[e]=arguments[i][e];
			}
		}
		return target;
	}else{
		if (!target instanceof N.jsphp.JsObject){
			target=new N.jsphp.JsObject(target);
		}
	}
	return target;
};

__extends = function (to, from, interfaces) {
	var __ = function () {
		this.constructor = to;
	};
	var isAbstract = false;
	if (typeof from != 'undefined' && from !== null) {
		if (typeof from.__isAbstract__ != 'undefined') {
			isAbstract = true;
		}
		__.prototype = from.prototype;
		for (func in from.prototype) {
			if (!from.prototype.hasOwnProperty(func) || (isAbstract && func == '__isAbstract__')) continue;
			__.prototype[func] = from.prototype[func];
		}
	}
	to.prototype = new __();

	for(var staticMethod in from){
		if (!from.hasOwnProperty(staticMethod)) continue;
		to[staticMethod]=from[staticMethod];
	}

	var func;
	if (typeof interfaces != 'undefined') {
		for (var i = 0; i < interfaces.length; i++) {
			var ___ = function () {
				this.constructor = to
			};
			___.prototype = interfaces[i].prototype;
			for (func in ___.prototype) {
				if (___.prototype.hasOwnProperty(func) && to.prototype.hasOwnProperty(func)) {
					___.prototype[func] = to.prototype[func];
				}
			}
			to.prototype = new ___();
		}
	}
};
__IS_INHERITANCE__ = false;
__INTERFACE_NEW__ = function () {
	throw new Error('cant create interface');
};
__INTERFACE_FUNC__ = function () {
	throw new Error('cant call interface method');
};
__ABSTRACT_FUNC__ = function () {
	throw new Error('abstract function must be implemented');
};
__PROXY_HANDLER = {
	get: function (target, name) {
		if (name in target) {
			return target[name];
		}
		if (target.__get != undefined) {
			var ret = target.__get(name);
			if (ret === undefined) {
				if (target.__call != undefined) {
					return function () {
						return target.__call(name, arguments);
					}
				}
			} else {
				return ret;
			}
		} else if (target.__call != undefined) {
			return function () {
				return target.__call(name, arguments);
			}
		}
	},
	set: function (target, name, value) {
		if (name in target && typeof target[name] !== "function") {
			return target[name] = value;
		} else {
			if (target.__set != undefined) {
				target.__set(name, value);
			}
		}
	}
};
__PRIVATIZE__ = function () {
	var map = new WeakMap();
	return function (obj) {
		var data = map.get(obj);
		if (typeof data == "undefined") {
			map.set(obj, data = {});
		}
		return data;
	};
};