<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 24.5.2016
 * Time: 19:26
 */

namespace PhpToJs\Printer;


interface SourceWriterInterface {

    /**
     * @param null $atStart
     * @return SourceWriterInterface
     */
    public function pushDelay($atStart=null);
    /**
     * @param null $id
     * @return SourceWriterInterface
     */
    public function popDelay(&$id=null);
    /**
     * @param $var
     * @return SourceWriterInterface
     */
    public function popDelayToVar(&$var);
    /**
     * @param $id
     * @return SourceWriterInterface
     */
    public function writeDelay($id);
    /**
     * @return SourceWriterInterface
     */
    public function writeLastDelay();
    /**
     * @param $string
     * @param ... $objects
     * @return SourceWriterInterface
     */
    public function println($string='', $objects=null);
    /**
     * @param $string
     * @param ... $objects
     * @return SourceWriterInterface
     */
    public function print_($string, $objects=null);
    /**
     * @return SourceWriterInterface
     */
    public function indent();
    /**
     * @return SourceWriterInterface
     */
    public function outdent();
    /**
     * @param $string
     * @param ... $objects
     * @return SourceWriterInterface
     */
    public function indentln($string, $objects=null);
}