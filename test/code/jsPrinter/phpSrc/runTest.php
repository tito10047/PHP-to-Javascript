<?php
ob_start();


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
include __DIR__ . "/../../../../lib/PhpJs/HashArray.php";

try {
	include $argv[1];
} catch (Exception $e) {
	sendError($e->getTraceAsString());
}

echo json_encode($asserts);