<?php

function testSwitchFunction($name, $value = FALSE)
{

    $result = FALSE;

    switch ($name) {

        case('output'): {
            $result = 'output';
            break;
        }

        case('silent'): {
            $result = 'notloud';
            break;
        }

        case('custom'): {
            $result = $value;
            break;
        }

        default: {
            $result = 'Unknown';
        }
    }

    return $result;
}


assert_(testSwitchFunction('output'), 'output');
assert_(testSwitchFunction('custom', 'bar'), 'bar');
assert_(testSwitchFunction('shamoan'), 'Unknown');