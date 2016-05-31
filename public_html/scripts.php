<?php
/**
 * Created by PhpStorm.
 * User: mostkaj
 * Date: 26.5.2016
 * Time: 22:14
 */
header('Content-Type: application/javascript');
echo "//classManager.js";
echo PHP_EOL;
echo file_get_contents(__DIR__ . "/../lib/PhpJs/classManager.js");
echo PHP_EOL;
echo "//Exceptions.js";
echo PHP_EOL;
echo file_get_contents(__DIR__ . "/../lib/PhpJs/Exceptions.js");
echo PHP_EOL;
echo "//HashArray.js";
echo PHP_EOL;
echo file_get_contents(__DIR__ . "/../lib/PhpJs/HashArray.js");
echo PHP_EOL;
echo "//runTest.js" . PHP_EOL;
echo "
    asserts = assert_ = function(what,to,message){
        if (typeof message=='undefined') message='no message';
        if (typeof what=='undefined') what='no what';
        if (what!=to){
            alert(message);
        }
    };
    count = function(mixed_var, mode){
        var key, cnt = 0;
    
        if( mode == 'COUNT_RECURSIVE' ) mode = 1;
        if( mode != 1 ) mode = 0;
    
        for (key in mixed_var){
            cnt++;
            if( mode==1 && mixed_var[key] && (mixed_var[key].constructor === Array || mixed_var[key].constructor === Object) ){
                cnt += count(mixed_var[key], 1);
            }
        }
    
        return cnt;
    };
    get_class = function(obj){
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
    Exception = function(msg){
        this.msg=msg;
    };
    include = function (fileName) {};
    ";