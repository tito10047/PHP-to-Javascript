<?php
namespace{
    global $____asserted;
    $____asserted = array();
    function assert_($what,$to,$message='NaN'){
        global $____asserted;
        $____asserted[]=array($what,$to,$message);
    };
    function getAsserts_(){
        global $____asserted;
        $ret = $____asserted;
        $____asserted=array();
        return $ret;
    }
}
namespace PhpTpJs {

    class NodeJsException extends \Exception {
    }


    class PhpToJsTest extends \PHPUnit_Framework_TestCase {

        private $phpGlobalFiles, $PATH_SRC_PHP, $PATH_SRC_JS, $PATH_TO_JS_TEST, $PATH_TO_PHP_TEST;
        private $nodeJsExist = false;
        private $onlyGenerate = false;

        public function __construct($name = null, $data = array(), $dataName = '') {
            parent::__construct($name, $data, $dataName);
            if (!self::checkNodeJs()) {
//                exit;
                //TODO: replace this with warning
                echo "nodejs not found in system path";
                return;
            }
            $this->nodeJsExist = true;
            $s = DIRECTORY_SEPARATOR;
            $this->PATH_SRC_PHP = __DIR__ . "{$s}..{$s}..{$s}code{$s}jsPrinter{$s}phpSrc{$s}";
            $this->PATH_SRC_JS = __DIR__ . "{$s}..{$s}..{$s}code{$s}jsPrinter{$s}jsSrc{$s}generated{$s}";
            $this->PATH_TO_JS_TEST = __DIR__ . "{$s}..{$s}..{$s}code{$s}jsPrinter{$s}jsSrc{$s}runTest.js";
            $this->PATH_TO_PHP_TEST = __DIR__ . "{$s}..{$s}..{$s}code{$s}jsPrinter{$s}phpSrc{$s}runTest.php";
            $this->phpGlobalFiles = self::getFiles($this->PATH_SRC_PHP . 'global', "js.php");
            JsPrinterAbstract::$showWarnings = false;
            JsPrinterAbstract::$throwErrors = false;
        }

        /**
         * @dataProvider provideTestJsNonPrivate
         * @covers       Printer\JsPrinter\NonPrivate<extended>
         */
        public function testJsNonPrivate($class, $fileName, $filePath) {
            $this->doTestJsPrintClass($class, $fileName, $filePath);
        }

        public function provideTestJsNonPrivate() {
            return $this->getTests("NonPrivate");
        }

        protected function doTestJsPrintClass($printerClassName, $fileName, $filePath) {
            $jsFilePath = $this->PATH_SRC_JS . $printerClassName . DIRECTORY_SEPARATOR . $fileName . '.js';

            $className = '\PhpParser\Printer\JsPrinter\\' . $printerClassName;
            /** @var JsPrinterAbstract $jsPrinter */
            $jsPrinter = new $className();
            if (!$jsPrinter->jsPrintFileTo($filePath, $jsFilePath)) {
                $this->throwException(new \Exception("cant write to '{$jsFilePath}'"));
            }

            if ($this->onlyGenerate) {
                return;
            }

            $phpAsserts = $this->runPhpTest($filePath);
            foreach ($phpAsserts as $assert) {
                $this->assertEquals($assert->what, $assert->to, $fileName . ": " . $assert->message);
            }

            $jsAsserts = $this->runJsTest($jsFilePath);
            foreach ($jsAsserts as $assert) {
                if (!isset($assert->what)){//$assert->what=null;
                    var_dump($jsAsserts);exit;
                }
                $this->assertEquals($assert->what, $assert->to, $fileName . ".js: " . $assert->message);
            }

            $this->assertEquals(count($phpAsserts), count($jsAsserts), "php VS js assertions count '{$fileName}'");

            for ($i = 0; $i < count($phpAsserts); $i++) {
                $this->assertEquals($phpAsserts[$i]->to, $jsAsserts[$i]->to, "php VS js '{$fileName}' '{$phpAsserts[$i]->message}'/{$jsAsserts[$i]->message}");
            }
        }

        protected function runPhpTest($filePath) {
            $filePath = realpath($filePath);

            exec("php {$this->PATH_TO_PHP_TEST} '{$filePath}'", $output, $returnCode);
            if ($returnCode != false) {
                echo "================" . PHP_EOL;
                var_dump($output);
                $this->throwException(new NodeJsException('js error'));
                return array();
            }

            if (count($output)!=1){
                var_dump($output);
                $this->throwException(new NodeJsException('bad js result'));
            }
            return json_decode($output[0]);
        }

        protected function runJsTest($filePath) {
            $filePath = realpath($filePath);
            exec("nodejs {$this->PATH_TO_JS_TEST} '{$filePath}'", $output, $returnCode);
            if ($returnCode != false) {
                echo "================" . PHP_EOL;
                var_dump($output);
                $this->throwException(new NodeJsException('js error'));
                return array();
            }
            if (count($output)!=1){
                var_dump($output);
                $this->throwException(new NodeJsException('bad js result'));
            }
            return json_decode($output[0]);
        }


        protected function getTests($printerClassName) {
            if (!$this->nodeJsExist) {
                return array();
            }
            if (file_exists($this->PATH_SRC_JS . $printerClassName)) {
                $this->rrmdir($this->PATH_SRC_JS . $printerClassName);
                mkdir($this->PATH_SRC_JS . $printerClassName, 0777, true);
            }
            $tests = array();

            $phpLocalFiles = self::getFiles($this->PATH_SRC_PHP . $printerClassName, "js.php");
            $phpLocalFiles = array_merge($this->phpGlobalFiles, $phpLocalFiles);
            foreach ($phpLocalFiles as $fileName => $filePath) {
                $tests[] = array($printerClassName, $fileName, $filePath);
            }
            return $tests;
        }

        protected function getFiles($directory, $fileExtension) {
            if (!file_exists($directory)) {
                $this->throw(new \Exception("directory not exist '{$directory}'"));
                return array();
            }
            $it = new \RecursiveDirectoryIterator($directory);
            $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::LEAVES_ONLY);
            $it = new \RegexIterator($it, '(\.' . preg_quote($fileExtension) . '$)');
            $fileNames = array();
            foreach ($it as $file) {
                /** @var \SplFileInfo $file */
                $fileNames[$file->getFilename()] = realpath($file->getPathname());
            }
            return $fileNames;
        }

        protected function checkNodeJs() {
            exec("nodejs -v", $output);
            return count($output) == 1;
        }

        function rrmdir($dir) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (filetype($dir . "/" . $object) == "dir") $this->rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
                    }
                }
                reset($objects);
                rmdir($dir);
            }
        }

    }

}