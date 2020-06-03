<?php
class ORM {


	// library
	public static $libPath = array(
		'connectDB' => __DIR__.'/../../lib/redbeanphp/5.3.1/rb.php'
	);




	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			setup redbean and connect to database
		</description>
		<io>
			<in>
				<structure name="config" scope="$fusebox">
					<structure name="db">
						<string name="host" />
						<string name="name" />
						<string name="username" />
						<string name="password" />
					</structure>
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	private static function connectDB() {

	}




	/**
	<fusedoc>
		<description>
			get all records (sort by id)
		</description>
		<io>
			<in>
				<string name="$beanType" />
			</in>
			<out>
				<structure name="~return~">
					<object name="~id~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function all($beanType) {

	}




	/**
	<fusedoc>
		<description>
			count number of records accorrding to criteria (if any)
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string name="$sql" />
				<string name="$param" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	private static function count($beanType, $sql='', $param=array()) {

	}




	/**
	<fusedoc>
		<description>
			obtain specific record according to ID, or...
			obtain multiple records according to criteria
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string_or_number name="$sqlOrID" />
				<array name="$param" />
			</in>
			<out>
				<!-- single record -->
				<object name="~return~" optional="yes" />
				<!-- multiple records -->
				<structure name="~return~" optional="yes">
					<object name="~id~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function get($beanType, $sqlOrID='', $param=array()) {

	}




	/**
	<fusedoc>
		<description>
			obtain first record according to the criteria
		</description>
		<io>
			<in>
				<string name="$beanType" />
				<string name="$sql" />
				<array name="$param" />
			</in>
			<out>
				<object name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function first($beanType, $sql='', $param=array()) {

	}




	/**
	<fusedoc>
		<description>
			create empty new container (preload data when specified)
		</description>
		<io>
			<in>
				<string name="$beanType" />
			</in>
			<out>
				<object name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function new($beanType, $data=array()) {

	}




	/**
	<fusedoc>
		<description>
			delete specific record
		</description>
		<io>
			<in>
				<object_or_number name="$beanOrID" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function delete($beanOrID) {

	}




	/**
	<fusedoc>
		<description>
			save object into database
		</description>
		<io>
			<in>
				<object name="$bean" />
			</in>
			<out>
				<number name="~return~" comments="id of record inserted/updated" />
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
				<string name="$sql" />
				<array name="$param" optional="yes" />
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