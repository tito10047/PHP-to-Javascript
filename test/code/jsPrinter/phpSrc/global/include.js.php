<?php

$foo = 1;

include "includeIt.php";

assert_($foo, 2, 'foo=2');