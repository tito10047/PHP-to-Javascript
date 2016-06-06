<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 4.6.2016
 * Time: 22:41
 */
namespace jsphp\util;


use jsphp\JsArray;

class JsArrayIteratorPair{
	public $done=false;
	/**
	 * @var JsArray
	 */
	public $value;
}

class JsArrayIterator implements \Iterator{
	private $data;
	private $onlyKeys=false;

	/**
	 * JsArrayIterator constructor.
	 * @param array|JsArray $data
	 * @param bool $onlyKeys
	 * @throws \Exception
	 * @internal
	 */
	public function __construct($data, $onlyKeys=false) {
		$this->onlyKeys=$onlyKeys;
		if ($data instanceof JsArray) {
			$this->data = [];
			for ($i = 0; $i < $data->length; $i++) {
				if ($onlyKeys) {
					$this->data[] = $data[$i];
				} else {
					$this->data[] = $data[$i];
				}
			}
		} else if (gettype($data) == "array" && array_values($data) === $data) {
			foreach ($data as $key => $value) {
				if ($onlyKeys) {
					$this->data[] = $key;
				} else {
					$this->data[] = $value;
				}
			}
		} else {
			throw new \Exception("Iterator can get only JsArray o indexed array");
		}
	}

	/**
	 * @internal
	 */
	public function current() {
		return current($this->data);
	}

	/**
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return JsArrayIteratorPair Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next() {
		$ret = new JsArrayIteratorPair();
		$current = current($this->data);
		if ($current===false){
			$ret->done=true;
			if ($this->onlyKeys){
				$ret->value=undefined;
			}else {
				$ret->value = new JsArray(
					undefined,
					undefined
				);
			}
		}else {
			$ret->done = false;
			if ($this->onlyKeys) {
				$ret->value = key($this->data);
			} else {
				$ret->value = new JsArray(
					key($this->data),
					current($this->data)
				);
			}
			next($this->data);
		}
		return $ret;
	}

	/**
	 * @internal
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * @internal
	 */
	public function valid() {
		return key($this->data)!==null;
	}

	/**
	 * @internal
	 */
	public function rewind() {
//		reset($this->data);
	}
}

namespace jsphp;
use jsphp\util\JsArrayIterator;

/**
 * Class JSArray
 * @property int length
 * @package jsphp
 */
class JsArray implements \ArrayAccess, \JsonSerializable{

	private $data;

	public function __construct() {
		$args=func_get_args();
		if (count($args)==1 && is_numeric($args[0])){
			while($args[0]-->0){
				$this->data[]=undefined;
			}
		}else if(count($args)>0){
			foreach($args as $arg){
				$this->data[]=$arg;
			}
		}
	}

	/**
	 * Calls a defined callback function on each element of an array, and returns an array that contains the results.
	 * @param callback $callback A function that accepts up to three arguments. The map method calls the callbackfn function one time for each element in the array.
	 */
	public function forEach($callback){

		foreach ($this->data as $index=>$value){
			call_user_func_array($callback,[$value,$index,$this]);
		}
	}

	/**
	 * The Array::from() method creates a new Array instance from an array-like or iterable object.
	 * @param array|JsArray|string|JsObject $arrayLike An array-like or iterable object to convert to an array.
	 * @param callable $mapFunction Optional. Map function to call on every element of the array.
	 * @return JsArray
	 * @throws \Exception
	 */
	public static function from($arrayLike, $mapFunction=null){
		$ret = new JsArray();
		if ($mapFunction!==null){
			if (gettype($arrayLike)=="array" && array_key_exists("length",$arrayLike)) {
				for($i=0;$i<$arrayLike["length"];$i++){
					$ret->data[] = call_user_func_array($mapFunction, [undefined,$i]);
				}
				return $ret;
			}
			if($arrayLike instanceof JsObject && $arrayLike->offsetExists("length")){
				for($i=0;$i<$arrayLike["length"];$i++){
					$ret->data[] = call_user_func_array($mapFunction, [undefined,$i]);
				}
				return $ret;
			}
			if($arrayLike instanceof JsArray){
				foreach ($arrayLike->data as $key=>$val) {
					$ret->data[] = call_user_func_array($mapFunction, [$val,$key]);
				}
				return $ret;
			}
			if(is_string($arrayLike)) {
				$chars = str_split($arrayLike);
				foreach ($chars as $char) {
					$ret->data[] = $char;
				}
				return $ret;
			}
			foreach ($arrayLike as $key=>$val) {
				$ret->data[] = call_user_func_array($mapFunction, [$val,$key]);
			}
			return $ret;
		}else{
			if (gettype($arrayLike)=="array"){
				if (array_values($arrayLike) === $arrayLike){
					foreach ($arrayLike as $val){
						$ret->data[]=$val;
					}
					return $ret;
				}
				if (array_key_exists("length",$arrayLike)) {
					for($i=0;$i<$arrayLike["length"];$i++){
						$ret->data[] = call_user_func_array($mapFunction, [undefined,$i]);
					}
					return $ret;
				}
				return $ret;
			}
			if($arrayLike instanceof JsObject && $arrayLike->offsetExists("length")){
				for($i=0;$i<$arrayLike["length"];$i++){
					$ret->data[] = call_user_func_array($mapFunction, [undefined,$i]);
				}
				return $ret;
			}
			if ($arrayLike instanceof JsArray){
				foreach ($arrayLike->data as $val){
					$ret->data[]=$val;
				}
				return $ret;
			}
			if(is_string($arrayLike)){
				$chars = str_split($arrayLike);
				foreach($chars as $char){
					$ret->data[]=$char;
				}
				return $ret;
			}
			if ($arrayLike===null || $arrayLike===undefined){
				throw new \Exception("Cannot convert undefined or null to object");
			}
			return $ret;
		}
	}

	/**
	 * The Array::isArray() determines whether the passed value is an Array.
	 * @param mixed $object The object to be checked.
	 * @return bool If the object is an Array, true is returned, otherwise false is.
	 */
	public static function isArray($object){
		if (gettype($object)=="array" && array_values($object) === $object){
			return true;
		}
		if ($object instanceof JsArray){
			return true;
		}
		return false;
	}

	/**
	 * The Array.of() method creates a new Array instance with a variable number of arguments, regardless of number or type of the arguments.
	 *
	 * The difference between Array.of() and the Array constructor is in the handling of integer arguments: Array.of(42) creates an array with a single element, 42, whereas Array(42) creates an array with 42 elements, each of which is undefined.
	 * @param mixed ...element Elements of which to create the array.
	 * @return JsArray
	 */
	public static function of($element){
		$ret = new JsArray();
		foreach (func_get_args() as $val){
			$ret->data[]=$val;
		}
		return $ret;
	}

	/**
	 * The concat() method returns a new array comprised of the array on which it is called joined with the array(s) and/or value(s) provided as arguments.
	 * @param mixed|array ...$valueN Arrays and/or values to concatenate into a new array. See the description below for details.
	 * @return JsArray
	 */
	public function concat($valueN){
		$ret = new JsArray();
		foreach ($this->data as $item) {
			$ret->data[]=$item;
		}
		$args=func_get_args();
		foreach($args as $arg){
			if (gettype($arg)=="array" && array_values($arg) === $arg){
				foreach ($arg as $val){
					$ret->data[]=$val;
				}
			}else if ($arg instanceof JsArray){
				foreach ($arg->data as $value) {
					$ret->data[]=$value;
				}
			}else{
				$ret->data[]=$arg;
			}
		}
		return $ret;
	}

	/**
	 * The entries() method returns a new Array Iterator object that contains the key/value pairs for each index in the array.
	 */
	public function entries(){
		return new JsArrayIterator($this);
	}

	/**
	 * The every() method tests whether all elements in the array pass the test implemented by the provided function.
	 * @param callable $callback Function to test for each element.
	 * @return bool
	 * @throws \Exception
	 */
	public function every($callback){
		if (!is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		foreach($this->data as $key=>$value){
			if (!call_user_func_array($callback,[$value,$key,$this])){
				return false;
			}
		}
		return true;
	}

	/**
	 * The fill() method fills all the elements of an array from a start index to an end index with a static value.
	 * @param mixed $value
	 * @param int $start
	 * @param int $end
	 * @return JsArray
	 */
	public function fill($value,$start=null,$end=null){
		$len = count($this->data);
		if ($start==null){
			$start=0;
		}
		if ($end==null){
			$end=$len;
		}
		if (((int)$start)!==$start || ((int)$end)!==$end){
			return $this;
		}
		$start = $start < 0 ?
			max($len + $start, 0) :
			min($start, $len);
		$end = $end < 0 ?
			max($len + $end, 0) :
			min($end, $len);
		for($i=$start;$i<$end;$i++){
			$this->data[$i]=$value;
		}
		return $this;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
	 * The filter() method creates a new array with all elements that pass the test implemented by the provided function.
	 * @param callable $callback Function to test each element of the array. Invoked with arguments (element, index, array). Return true to keep the element, false otherwise.
	 * @return JsArray
	 * @throws \Exception
	 */
	public function filter($callback){
		if (!is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		$ret = new JsArray();
		foreach($this->data as $key=>$value){
			if (call_user_func_array($callback,[$value,$key,$this])){
				$ret->data[]=$value;
			}
		}
		return $ret;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find
	 * The find() method returns a value in the array, if an element in the array satisfies the provided testing function. Otherwise undefined is returned.
	 * @param callable $callback
	 * @throws \Exception
	 */
	public function find($callback){
		if (!is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		foreach($this->data as $key=>$value){
			if (call_user_func_array($callback,[$value,$key,$this])){
				return $value;
			}
		}
		return undefined;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/findIndex
	 * The findIndex() method returns an index in the array, if an element in the array satisfies the provided testing function. Otherwise -1 is returned.
	 * @param callable $callback
	 * @return int
	 * @throws \Exception
	 */
	public function findIndex($callback){
		if (!is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		foreach($this->data as $key=>$value){
			if (call_user_func_array($callback,[$value,$key,$this])){
				return $key;
			}
		}
		return -1;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/includes
	 * The includes() method determines whether an array includes a certain element, returning true or false as appropriate.
	 * @param mixed $searchElement The element to search for.
	 * @param int $fromIndex Optional. The position in this array at which to begin searching for searchElement. A negative value searches from the index of array.length + fromIndex by asc. Defaults to 0.
	 * @return bool
	 */
	public function includes($searchElement,$fromIndex=0){
		$len=count($this->data);
		if ($len==0){
			return false;
		}
		if ($fromIndex < 0) {
			$fromIndex = $len + $fromIndex;
			if ($fromIndex < 0) {
				$fromIndex = 0;
			}
		}
		while ($fromIndex < $len) {
			$currentElement = $this->data[$fromIndex];
			if ($searchElement === $currentElement) {
				return true;
			}
			$fromIndex++;
		}
		return false;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/indexOf
	 * The indexOf() method returns the first index at which a given element can be found in the array, or -1 if it is not present.
	 * @param mixed $searchElement Element to locate in the array.
	 * @param int $fromIndex The index to start the search at. If the index is greater than or equal to the array's length, -1 is returned, which means the array will not be searched. If the provided index value is a negative number, it is taken as the offset from the end of the array. Note: if the provided index is negative, the array is still searched from front to back. If the calculated index is less than 0, then the whole array will be searched. Default: 0 (entire array is searched).
	 * @return int
	 */
	public function indexOf($searchElement,$fromIndex=0){
		$len = count($this->data);
		if ($len==0){
			return -1;
		}
		if ($fromIndex >= $len) {
			return -1;
		}
		$fromIndex = max($fromIndex >= 0 ? $fromIndex : $len - abs($fromIndex), 0);
		while ($fromIndex < $len) {
			if ($this->data[$fromIndex] === $searchElement) {
				return $fromIndex;
			}
			$fromIndex++;
		}
		return -1;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/join
	 * The join() method joins all elements of an array into a string.
	 * @param string $separator
	 * @return string
	 */
	public function join($separator=","){
		return join($separator, $this->data);
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/keys
	 * The keys() method returns a new Array Iterator that contains the keys for each index in the array.
	 * @return JsArrayIterator
	 */
	public function keys(){
		return new JsArrayIterator($this,true);
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/lastIndexOf
	 * The lastIndexOf() method returns the last index at which a given element can be found in the array, or -1 if it is not present. The array is searched backwards, starting at fromIndex.
	 * @param mixed $searchElement Element to locate in the array.
	 * @param int $fromIndex Optional. The index at which to start searching backwards. Defaults to the array's length minus one, i.e. the whole array will be searched. If the index is greater than or equal to the length of the array, the whole array will be searched. If negative, it is taken as the offset from the end of the array. Note that even when the index is negative, the array is still searched from back to front. If the calculated index is less than 0, -1 is returned, i.e. the array will not be searched.
	 * @return int
	 */
	public function lastIndexOf($searchElement,$fromIndex=null){
		$len=count($this->data);
		if ($len === 0) {
			return -1;
		}
		if ($fromIndex===null){
			$fromIndex=$len-1;
		}else{
			if (((int)$fromIndex)!==$fromIndex){
				$fromIndex=0;
			}
		}

		for ($k = $fromIndex >= 0 ? min($fromIndex, $len - 1) : $len - abs($fromIndex); $k >= 0; $k--) {
			if ($this->data[$k] === $searchElement) {
				return $k;
			}
		}
		return -1;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/map
	 * The map() method creates a new array with the results of calling a provided function on every element in this array.
	 * @param callable $callback Function that produces an element of the new Array
	 * @return JsArray
	 * @throws \Exception
	 */
	public function map($callback){
		if (!is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		$ret = new JsArray();
		foreach ($this->data as $index=>$value) {
			$ret->data[$index]=call_user_func_array($callback,[$value,$index,$this]);
		}
		return $ret;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/pop
	 * The pop() method removes the last element from an array and returns that element.
	 */
	public function pop(){
		return array_pop($this->data);
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/push
	 * The push() method adds one or more elements to the end of an array and returns the new length of the array.
	 * @param mixed ...$value
	 * @return int
	 */
	public function push($value){
		foreach (func_get_args() as $arg) {
			array_push($this->data,$arg);
		}
		return count($this->data);
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/Reduce
	 * The reduce() method applies a function against an accumulator and each value of the array (from left-to-right) to reduce it to a single value.
	 * @param callable $callback <div>Function to execute on each value in the array, taking four arguments:
	 * <ul>
	 * <li>previousValue<br>
	 * The value previously returned in the last invocation of the callback, or initialValue, if supplied. (See below.)</li>
	 * <li>currentValue<br>
	 * The current element being processed in the array.</li>
	 * <li>currentIndex<br>
	 * The index of the current element being processed in the array.</li>
	 * <li>array</li>
	 * </ul></div>
	 * The array reduce was called upon.
	 * @param int $initialValue
	 * @return int
	 * @throws \Exception
	 */
	public function reduce($callback, $initialValue=0){
		if (!is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		foreach ($this->data as $index=>$value) {
			$initialValue = call_user_func_array($callback,[$initialValue,$value,$index,$this]);
		}
		return $initialValue;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/ReduceRight
	 * The reduceRight() method applies a function against an accumulator and each value of the array (from right-to-left) has to reduce it to a single value.
	 * @param callable $callback <div>Function to execute on each value in the array, taking four arguments:
	 * <ul>
	 * <li>previousValue<br>
	 * The value previously returned in the last invocation of the callback, or initialValue, if supplied. (See below.)</li>
	 * <li>currentValue<br>
	 * The current element being processed in the array.</li>
	 * <li>currentIndex<br>
	 * The index of the current element being processed in the array.</li>
	 * <li>array<br>The array reduce was called upon.</li>
	 * </ul></div>
	 * @param int $initialValue
	 * @return int
	 * @throws \Exception
	 */
	public function reduceRight($callback, $initialValue=0){
		if (!is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		for ($i=count($this->data)-1;$i>0;$i--) {
			$initialValue = call_user_func_array($callback,[$initialValue, $this->data[$i],$i,$this]);
		}
		return $initialValue;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/reverse
	 * The reverse() method reverses an array in place. The first array element becomes the last and the last becomes the first.
	 * @return JsArray
	 */
	public function reverse(){
		$this->data = array_reverse($this->data,false);
		return $this;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/shift
	 * The shift() method removes the first element from an array and returns that element. This method changes the length of the array.
	 */
	public function shift(){
		return array_shift($this->data);
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/slice
	 * The slice() method returns a shallow copy of a portion of an array into a new array object.
	 * @param int $begin <div>Zero-based index at which to begin extraction.<br>
	 * As a negative index, begin indicates an offset from the end of the sequence. slice(-2) extracts the last two elements in the sequence.<br>
	 * If begin is undefined, slice begins from index 0.</div>
	 * @param int $end <div>Zero-based index at which to end extraction. slice extracts up to but not including end.<br>
	 * slice(1,4) extracts the second element through the fourth element (elements indexed 1, 2, and 3).<br>
	 * As a negative index, end indicates an offset from the end of the sequence. slice(2,-1) extracts the third element through the second-to-last element in the sequence.<br>
	 * If end is omitted, slice extracts through the end of the sequence (arr.length).</div>
	 * @return JsArray
	 */
	public function slice($begin = 0, $end = null){
		if ($end===null){
			$end=count($this->data)-1;
		}
		$ret = new JsArray();
		$ret->data = array_slice($this->data,$begin,$begin-$end,false);
		return $ret;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/some
	 * The some() method tests whether some element in the array passes the test implemented by the provided function.
	 * @param callable $callback <div>Function to test for each element, taking three arguments:
	 * <ul>
	 * <li>currentValue<br>
	 * The current element being processed in the array.</li>
	 * <li>index<br>
	 * The index of the current element being processed in the array.</li>
	 * <li>array<br>The array reduce was called upon.</li>
	 * </ul></div>
	 * @return bool
	 * @throws \Exception
	 */
	public function some($callback){
		if (!is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		foreach ($this->data as $index=>$value) {
			if (call_user_func_array($callback,[$value,$index,$this])==true){
				return true;
			}
		}
		return false;
	}

	/**
	 * The sort() method sorts the elements of an array in place and returns the array. The sort is not necessarily stable. The default sort order is according to string Unicode code points.
	 * @param callable $callback Optional. Specifies a function that defines the sort order. If omitted, the array is sorted according to each character's Unicode code point value, according to the string conversion of each element.
	 * @return $this
	 * @throws \Exception
	 */
	public function sort($callback=null){
		if ($callback!=null && !is_callable($callback)){
			throw new \Exception("this is not a function");
		}
		if ($callback!==null){
			usort($this->data,$callback);
		}else{
			usort($this->data,"strcoll");
		}
		return $this;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/splice
	 * The splice() method changes the content of an array by removing existing elements and/or adding new elements.
	 * @param int $start Index at which to start changing the array (with origin 0). If greater than the length of the array, actual starting index will be set to the length of the array. If negative, will begin that many elements from the end.
	 * @param int $deleteCount <div>An integer indicating the number of old array elements to remove. If deleteCount is 0, no elements are removed. In this case, you should specify at least one new element. If deleteCount is greater than the number of elements left in the array starting at start, then all of the elements through the end of the array will be deleted.<br>
	 * If deleteCount is omitted, deleteCount will be equal to (arr.length - start).</div>
	 * @param mixed [...$replacement] The elements to add to the array, beginning at the start index. If you don't specify any elements, splice() will only remove elements from the array.
	 * @return JsArray
	 */
	public function splice($start,$deleteCount=null){
		if ($deleteCount===null){
			$deleteCount=count($this->data)-1;
		}
		$len = $deleteCount;
		$ret = new JsArray();

		$args = func_get_args();
		if (count($args)==3) {
			$ret->data = array_splice($this->data, $start, $len,$args[2]);
		}else if (count($args)>3){
			$replacement = [];
			for($i=2;$i<count($args);$i++){
				$replacement[]=$args[$i];
			}
			$ret->data = array_splice($this->data, $start, $len,$replacement);
		}else{
			$ret->data = array_splice($this->data, $start, $len);
		}
		return $ret;
	}
	
	/**
	 * @param mixed $offset
	 * @internal
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @internal
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->data[$offset];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @internal
	 * @return mixed
	 */
	public function offsetSet($offset, $value) {
		if (((int)$offset)!=$offset){
			return $value;
		}
		if (count($this->data)>$offset){
			while($offset++<count($this->data)){
				$this->data[]=undefined;
			}
		}
		$this->data[$offset]=$value;
		return $value;
	}

	/**
	 * @param mixed $offset
	 * @internal
	 */
	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}

	/**
	 * @return mixed
	 * @internal
	 */
	function jsonSerialize() {
		return $this->data;
	}

	public function __get($name) {
		switch ($name){
			case "length": return count($this->data);
		}

		return null;
	}
}
