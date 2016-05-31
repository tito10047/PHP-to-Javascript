<?php

namespace PhpJs;

/**
 * @codeCoverageIgnore
 */
class Autoloader {
	/**
	 * Registers PhpJs\Autoloader as an SPL autoloader.
	 *
	 * @param bool $prepend Whether to prepend the autoloader instead of appending
	 */
	static public function register($prepend = false) {
		spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
	}

	/**
	 * Handles autoloading of classes.
	 *
	 * @param string $class A class name.
	 */
	static public function autoload($class) {
		$fileName = dirname(__DIR__) . '/' . strtr($class, '\\', '/') . '.php';
		if (file_exists($fileName)) {
			require $fileName;
		}
	}

}
