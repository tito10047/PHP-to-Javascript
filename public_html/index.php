<?php
/**
 * Created by PhpStorm.
 * User: mostkaj
 * Date: 26.5.2016
 * Time: 20:17
 */
setcookie("converter",true);

?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Php to Js converter</title>
    <script src="/scripts.php" type="application/javascript"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.4.0/styles/default.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.4.0/highlight.min.js"></script>
    <script src="beautify.js"></script>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
</head>
<body>
    <style>
        html, body{
        }
        .center {
            margin: auto;
            width: 1200px;
            padding: 10px;
        }
        table td{
            padding: 20px;
        }
        .error{
            background-color: #F74E4E;
        }
        .hidden{
            display: none;
        }
        pre{
            margin: 0;
        }
        code{
            background-color: #f0f0f0;
        }
    </style>
    <div class="center">
        <div style="text-align: center;">
            <h1>PHP to JavaScript converter</h1>
            <h3><a href="https://github.com/tito10047/phptojs" target="_blank">More info</a></h3>
        </div>
        <table style="width: 1200px;">
            <tr>
                <td>
                    <div style="text-align: center;">
                        <span>Write some PHP code</span><br>
                        <select id="templates">
                            <option value="">select from template</option>
                            <?php foreach (glob(__DIR__.'/../test/code/jsPrinter/phpSrc/*/*.js.php') as $file):$filename=basename($file, ".js.php");?>
                            <option value="<?=$filename?>"><?=$filename?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div style="height: 600px;">
                        <pre style="height: 600px;width: 554px;border: 1px solid #a9a9a9;" id="phpCodeColoredPre"><code id="phpCodeColored" style="width: 100%;height: 100%;border: 1px solid #a9a9a9;display: block;">&lt;?php

</code></pre>
                        <textarea id="phpCode" style="width: 554px;height: 600px;border: 1px solid #a9a9a9;white-space: pre;" class="hidden">&lt;?php

</textarea>
                    </div>
                </td>
                <td style="width: 57px;">
                    <button id="convertButton">Convert</button><br><br>
                    <button id="runButton">Execute</button>
                </td>
                <td>
                    <div style="text-align: center;">Converted JavaScript code<br></div>
                    <div style="height: 600px;">
                        <pre style="height: 600px;">
                            <code id="jsCode" style="width: 554px;height: 600px;border: 1px solid #a9a9a9;display: block;"></code>
                        </pre>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <form enctype="application/x-www-form-urlencoded"></form>
    <script type="text/javascript">
        var jsCode="";
        window.addEventListener("load", function load(event){
            var phpCode=document.getElementById("phpCode").value;
            document.getElementById("phpCodeColoredPre").innerHTML=phpCode.replaceAll("<","&lt;");
            hljs.highlightBlock(document.getElementById("phpCodeColoredPre"));
        },false);
        function loadTemplate(template) {
            var xmlhttp;

            if (window.XMLHttpRequest) {
                // code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            } else {
                // code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }

            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
                    if(xmlhttp.status == 200){
                        if (xmlhttp.responseText=="1"){
                            alert("Template not find");
                            return;
                        }
                        document.getElementById("phpCode").value = xmlhttp.responseText;
                        document.getElementById("phpCodeColoredPre").className="";
                        document.getElementById("phpCode").className="hidden";
                        document.getElementById("phpCodeColoredPre").innerHTML=xmlhttp.responseText.replaceAll("<","&lt;");
                        hljs.highlightBlock(document.getElementById("phpCodeColoredPre"));
                    }
                    else if(xmlhttp.status == 400) {
                        alert('There was an error 400')
                    }
                    else {
                        alert('something else other than 200 was returned')
                    }
                }
            };

            xmlhttp.open("GET", "/getTemplate.php?template="+template, true);
            xmlhttp.send();
        }
        String.prototype.replaceAll = function(search, replacement) {
            var target = this;
            return target.replace(new RegExp(search, 'g'), replacement);
        };
        function convert() {
            var xmlhttp;

            if (window.XMLHttpRequest) {
                // code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            } else {
                // code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }

            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
                    if(xmlhttp.status == 200){
                        if (xmlhttp.responseText=="1"){
                            alert("Template not find");
                            return;
                        }
                        var code = xmlhttp.responseText;
                        if (code.indexOf("ERROR:")===0){
                            code = "//"+code.substring(6,code.length);
                            document.getElementById("jsCode").className="error";
                        }else{
                            document.getElementById("jsCode").className="";
                        }
                        code = code.replaceAll("__ART__","alert");
                        jsCode=code;
                        document.getElementById("jsCode").innerHTML=js_beautify(code,{
                            "indent-char":"\\t",
                            "preserve-newlines":true,
                            "keep-array-indentation":false
                        }).replaceAll("<","&lt;");
                        hljs.highlightBlock(document.getElementById("jsCode"));
                    }
                    else if(xmlhttp.status == 400) {
                        alert('There was an error 400')
                    }
                    else {
                        alert('something else other than 200 was returned')
                    }
                }
            };

            var code = document.getElementById("phpCode").value;
            if (!code){
                return;
            }

            xmlhttp.open("POST", "/convert.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("code="+encodeURI(code).replaceAll("&","__AND__").replaceAll("\\+","__PLUS__").replaceAll("alert","__ART__"));
        }
        document.getElementById("templates").addEventListener("change",function (event) {
            if (!event.target.value){
                return;
            }
            loadTemplate(event.target.value);
        });
        document.getElementById("convertButton").addEventListener("click",function (event) {
            convert();
        });
        document.getElementById("runButton").addEventListener("click",function (event) {
            eval(jsCode);
        });
        document.getElementById("phpCodeColoredPre").addEventListener("click",function (event) {
            document.getElementById("phpCodeColoredPre").className="hidden";
            document.getElementById("phpCode").className="";
            document.getElementById("phpCode").focus();
        });
        document.getElementById("phpCode").addEventListener("blur",function (event) {
            document.getElementById("phpCodeColoredPre").className="";
            document.getElementById("phpCode").className="hidden";
            var phpCode=document.getElementById("phpCode").value;
            document.getElementById("phpCodeColoredPre").innerHTML=phpCode.replaceAll("<","&lt;");
            hljs.highlightBlock(document.getElementById("phpCodeColoredPre"));
        });
    </script>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-22668293-2', 'auto');
        ga('send', 'pageview');

    </script>
</body>
</html>