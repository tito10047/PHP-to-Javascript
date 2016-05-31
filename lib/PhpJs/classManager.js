if (typeof N=='undefined') N={};
if (typeof N.PhpJs=='undefined') N.PhpJs={};
if (typeof window=='undefined') window=global;
N._INIT_ = function(namespace){
    namespace = namespace.split('.');
    var nam = N;
    for(var i=0;i<namespace.length;i++){
        if (typeof nam[namespace[i]]=='undefined'){
            nam[namespace[i]]={};
        }
        nam = nam[namespace[i]];
    }
};
N._GET_ = function(namespace){
    namespace = namespace.split('\\');
    if (namespace.length==1){
        return window[namespace];
    }
    var nam = N;
    for(var i=0;i<namespace.length;i++){
        if (typeof nam[namespace[i]]=='undefined'){
            return null;
        }
        nam = nam[namespace[i]];
    }
    return nam;
};

__extends =  function (to, from, interfaces) {
    var __= function() { this.constructor = to; };
    var isAbstract=false;
    if (typeof from != 'undefined' && from!==null) {
        if (typeof from.__isAbstract__ !='undefined'){
            isAbstract=true;
        }
        __.prototype = from.prototype;
        for (func in from.prototype) {
            if (!from.prototype.hasOwnProperty(func) || (isAbstract && func=='__isAbstract__')) continue;
            __.prototype[func] = from.prototype[func];
        }
    }
    to.prototype = new __();

    var func;
    if (typeof interfaces != 'undefined') {
        for (var i = 0; i < interfaces.length; i++) {
            var ___ = function(){ this.constructor = to };
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
__IS_INHERITANCE__=false;
__INTERFACE_NEW__=function(){
    throw new Error('cant create interface');
};
__ABSTRACT_FUNC__=function(){
    throw new Error('abstract function must be implemented');
};
__PROXY_HANDLER = {
    get: function (target, name) {
        if (name in target) {
            return target[name];
        }
        // console.log(target,name);
        if (target.__get != undefined) {
            var ret = target.__get(name);
            if (ret === undefined) {
                if (target.__call != undefined) {
                    return function () {
                        return target.__call(name, arguments);
                    }
                }
            }else{
                return ret;
            }
        }else if (target.__call != undefined) {
            return function () {
                return target.__call(name, arguments);
            }
        }
    },
    set:function(target, name, value){
        if (name in target && typeof target[name] !== "function") {
            return target[name]=value;
        }else{
            if (target.__set != undefined) {
                target.__set(name,value);
            }
        }
    }
};
__PRIVATIZE__ = function() {
    var map = new WeakMap();
    return function (obj) {
        var data = map.get(obj);
        if (!data) {
            map.set(obj, data = {});
        }
        return data;
    };
};