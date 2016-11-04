<?php
// generic bean helper
class Bean {


	// compare two objects and return string showing the differences
	public static function diff($bean1, $bean2) {
		$result = '';
		// compare each properties of beans
		$bean1_columns = self::getColumns($bean1);
		$bean2_columns = self::getColumns($bean2);
		$columns = array_merge($bean1_columns, $bean2_columns);
		$columns = array_unique($columns);
		foreach ( $columns as $col ) {
			if ( $bean1[$col] != $bean2[$col] ) {
				$result .= "[{$col}] ";
				$result .= strlen($bean1[$col]) ? $bean1[$col] : '(empty)';
				$result .= ' ===> ';
				$result .= strlen($bean2[$col]) ? $bean2[$col] : '(empty)';
				$result .= "\n";
			}
		}
		// result
		return trim($result);
	}


	public static function getColumns($bean) {
		$result = array();
		// simple value properties only
		if ( !is_array($bean) ) {
			$bean = $bean->export();
		}
		foreach ( $bean as $key => $val ) {
			if ( !is_array($val) ) $result[] = $key;
		}
		// return result
		return $result;
	}


	// transform records into multi-level array
	public static function groupBy($groupColumn, $beans) {
		// empty result container
		$result = array();
		// go through each item and check group
		foreach ( $beans as $bean ) {
			// create empty container for this group
			if ( !isset($result[$bean[$groupColumn]]) ) {
				$result[$bean[$groupColumn]] = array();
			}
			// put item into group
			$result[$bean[$groupColumn]][$bean['id']] = $bean;
		}
		// result
		return $result;
	}


	public static function toString($bean) {
		$result = '';
		$columns = self::getColumns($bean);
		foreach ( $columns as $col ) {
			$result .= "[{$col}] ";
			$result .= strlen($bean[$col]) ? $bean[$col] : '(empty)';
			$result .= "\n";
		}
		return trim($result);
	}



}