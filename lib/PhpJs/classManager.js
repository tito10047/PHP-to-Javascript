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

__INTERFACE_NEW__=function(){
    throw new Error('cant create interface');
};
__ABSTRACT_FUNC__=function(){
    throw new Error('abstract function must be implemented');
};
