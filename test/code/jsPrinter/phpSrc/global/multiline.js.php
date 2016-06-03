<?php


$value1 = 5;
$value2 = 10;

$variableString = "$value1
{$value2}";

assert_($variableString, "5
10");

$multiLineString = "This is a string
That spans two lines";

$testInlineString="test JsClas indexed values index {$value2}";

assert_($testInlineString,"test JsClas indexed values index 10");