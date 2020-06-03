<?php
class ORM {


	// library
	public static $libPath = array();


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function all($type, $sql='', $param=array()) {

	}


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function get($type, $id) {

	}


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getOne($type, $sql='', $param=array()) {

	}


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function new($type, $data=array()) {

	}


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function delete($bean) {

	}


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function save($bean) {

	}


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function runSQL($sql, $param=array()) {

	}


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getCell() {

	}


	/**
	<fusedoc>
		<description>
		</description>
		<io>
			<in>
			</in>
			<out>
			</out>
		</io>
	</fusedoc>
	*/
	public static function getRow() {

	}


} // class