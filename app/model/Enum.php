<?php
class Enum {


	private static $error;


	// get (latest) error message
	public static function error() {
		return self::$error;
	}


	// get multiple enum beans by type
	// get single enum bean by type & key
	public static function get($type, $key=null, $all=false) {
		// filter
		$filter = '`type` = ? ';
		if ( empty($all) ) $filter .= 'AND IFNULL(disabled, 0) = 0 ';
		$filterParam = array($type);
		if ( !empty($key) ) {
			$filter .= "AND `key` = ? ";
			$filterParam[] = $key;
		}
		// order
		$order = 'ORDER BY seq ';
		// get multi records
		if ( empty($key) ) {
			return R::find('enum', $filter.$order, $filterParam);
		// or single value
		} else {
			return R::findOne('enum', $filter.$order, $filterParam);
		}
	}


	// get disabled items as well
	public static function getAll($type, $key=null) {
		return self::get($type, $key, true);
	}


	// get multiple enum records as array
	public static function getArray($type) {
		$beans = self::get($type);
		return self::toArray($beans);
	}


	// get specific enum value
	public static function getValue($type, $key, $returnKeyIfNotFound=true) {
		$result = self::get($type, $key);
		if ( empty($result->id) ) {
			return $returnKeyIfNotFound ? $key : '';
		} else {
			return $result->value;
		}
	}


	// transform multiple enum records to array
	public static function toArray($beans) {
		$result = array();
		foreach ( $beans as $b ) $result[$b->key] = $b->value;
		return $result;
	}


}