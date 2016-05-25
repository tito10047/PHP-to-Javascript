N._INIT_('foo.foo1');
(function() {
    var AInt = this.AInt = (function(){
        function AInt(){__INTERFACE_NEW__();}
        AInt.prototype.funcAInt = __INTERFACE_FUNC__;
        AInt.FOO=1;
        return AInt;
    })();
    var BAbs = this.BAbs = (function(){
        function BAbs(){}
        BAbs.prototype.funcBAbs = function(){};
        BAbs.prototype.funcBAbsA = __ABSTRACT_FUNC__;
        return BAbs;
    })();
    var C = this.C = (function(parent){
        __extends(C,parent,arguments[1]);
        function C(){
            __extends(this,parent);
        }
        C.prototype.funcAInt = function(){};
        C.prototype.funcBAbsA = function(){};
        return C;
    })(BAbs,[AInt]);
}).call(foo.foo1);

document.write(N.foo.foo1.AInt.FOO)