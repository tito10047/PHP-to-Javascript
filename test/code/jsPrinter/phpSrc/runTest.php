<?php
ob_start();
define("undefined", PHP_INT_MAX-3);

class console{
	/**
	 * @param mixed ...
	 */
	public static function log(){
		echo join(" ",func_get_args()).PHP_EOL;
	}
}

function sendError($message, $errno = null, $errfile = null, $errline = null) {
	echo json_encode(array('error' => $message, $errno, $errfile, $errline));
	exit(1);
}

if (count($argv) != 2) {
	sendError("bad arguments");
	exit(1);
}
global $asserts;
$asserts = array();
function assert_($what, $to, $message = "no message") {
	global $asserts;
	$asserts[] = (object)array(
		'what' => $what,
		'to' => $to,
		'message' => $message
	);
}

;
include __DIR__ . "/../../../../lib/phptojs/lib/php/HashArray.php";
include __DIR__ . "/../../../../lib/phptojs/lib/php/JsObject.php";
include __DIR__ . "/../../../../lib/phptojs/lib/php/JsArray.php";

try {
	if (file_exists($argv[1])) {
		include $argv[1];
	}else{
		sendError("script not found '{$argv[1]}'");
	}
} catch (Exception $e) {
	sendError($e->getTraceAsString());
}

echo json_encode($asserts);