/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 4.6.2016
 * Time: 11:38
 */
if (typeof N == 'undefined') N = {};
if (typeof N.jsphp == 'undefined') N.jsphp = {};
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
N.jsphp.JsObject.freeze = function(target){
	return Object.freeze(target);
};
N.jsphp.JsObject.getOwnPropertyNames = function(target){
	return Object.getOwnPropertyNames(target);
};
N.jsphp.JsObject.isExtensible = function(target){
	return Object.isExtensible(target);
};