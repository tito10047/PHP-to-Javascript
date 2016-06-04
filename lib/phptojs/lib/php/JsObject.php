<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 3.6.2016
 * Time: 18:50
 */
namespace jsphp\util;

use jsphp;

/**
 * Class JsObject
 * @package jsphp
 */
class JsObject implements \ArrayAccess, \Iterator, \Countable {

	private $data=[];
	private $isFreeze=false;
	private $isExtensible=false;

	/**
	 * JsObject constructor.
	 * @param mixed ...$arg
	 * @throws \Exception
	 */
	public function __construct($arg=null) {
		if ($arg!=null){
			if (is_array($arg)){
				$this->data=$arg;
				$this->sortData();
			}else if ($arg instanceof JsObject) {
				$this->data=$arg->data;
				$this->sortData();
			}else{
				throw new \Exception("Constructor on JsObject can get only array as parameter");
			}
		}
	}

	public static function freeze(JsObject $target){
		$target->isFreeze=true;
		return $target;
	}

	public static function isExtensible(JsObject $target){
		return $target->isExtensible;
	}

	/**
	 * @param array|JsObject $target
	 * @return array
	 * @throws \Exception
	 */
	public static function getOwnPropertyNames($target){
		if ($target instanceof JsObject){
			return array_keys($target->data);
		}
		if (is_array($target)){
			return array_keys($target);
		}
		if (is_string($target)){
			$chars = str_split($target);
			$chars[]="length";
			return $chars;
		}
		throw new \Exception("Cannot convert target or null to object");
	}

	/**
	 * @param ...$target
	 * @return JsObject
	 * @throws \Exception
	 */
	public static function assign($target){
		$args = func_get_args();
		if (gettype($target)=="array"){
			$target = new JsObject($target);
		}elseif ($target instanceof JsObject){
			if ($target->isFreeze || !$target->isExtensible){
				throw new \Exception("Can't add property, object is not extensible");
			}
		}else if(gettype($target)=="object"){
		}else if(get_class($target)=="Closure"){
			return $target;
		}else{
			throw new \Exception("This type is not implemented yet ".gettype($target));
		}
		if (count($args)>1){
			for($i=1;$i<count($args);$i++){
				if (gettype($args[$i])=="string"){
					foreach(str_split($args[$i]) as $pos=>$char){
						$target->data[$pos]=$char;
					}
				}elseif(is_array($args[$i]) || $args[$i] instanceof JsObject) {
					foreach ($args[$i] as $key => $val) {
						$target->data[$key] = $val;
					}
				}elseif (is_null($args[$i]) || $args[$i]===undefined || is_bool($args[$i]) || is_numeric($args[$i])) {
					continue;
				}else{
					throw new \Exception("This type is not implemented yet ".gettype($args[$i]));
				}
			}
		}
		$target->sortData();
		return $target;
	}

	private function sortData(){
		uksort($this->data,function($a, $b){
			if (gettype($a)=="string" && gettype($b)=="string"){
				return 0;
			}
			if (gettype($a)=="integer" && gettype($b)=="string"){
				return 0;
			}
			if (gettype($a)=="string" && gettype($b)=="integer"){
				return 1;
			}
			if (gettype($a)=="integer" && gettype($b)=="integer"){
				return $a>$b;
			}
			throw new \Exception("Unimplemented sort types ".gettype($a)."-".gettype($b));
		});
	}

	/**
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current() {
		return current($this->data);
	}

	/**
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next() {
		next($this->data);
	}

	/**
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid() {
		return key($this->data)!==null;
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind() {
		reset($this->data);
	}

	/**
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	/**
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet($offset) {
		return $this->data[$offset];
	}

	/**
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return mixed|void
	 * @throws \Exception
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value) {
		if ($this->isFreeze){
			throw new \Exception("Can't set property {$offset}, object is not extensible");
		}
		if (!isset($this->data[$offset]) && !$this->isExtensible){
			return $value;
		}
		$this->data[$offset]=$value;
		$this->sortData();
		return $value;
	}

	/**
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @throws \Exception
	 * @since 5.0.0
	 */
	public function offsetUnset($offset) {
		if ($this->isFreeze){
			throw new \Exception("Can't delete property {$offset}, object is not extensible");
		}
		unset($this->data[$offset]);
	}

	/**
	 * Count elements of an object
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count() {
		return count($this->data);
	}

	public function __get($name) {
		if (!isset($this->data)){
			return undefined;
		};
		return $this->data[$name];
	}

	public function __set($name, $value) {
		if ($this->isFreeze){
			throw new \Exception("Can't set property {$name}, object is not extensible");
		}
		$this->offsetSet($name,$value);
	}
}
