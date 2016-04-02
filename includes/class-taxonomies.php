<?php
/**
 * GC Staff Taxonomies
 * @version NEXT
 * @package GC Staff
 */

class GCST_Taxonomies {

	/**
	 * Instance of GCST_Position
	 *
	 * @var GCST_Position
	 */
	protected $position;

	/**
	 * Constructor
	 *
	 * @since  NEXT
	 * @param  object $staff GCST_Staff object.
	 * @return void
	 */
	public function __construct( $staff ) {
		$this->position = new GCST_Position( $staff );
	}

	/**
	 * Magic getter for our object. Allows getting but not setting.
	 *
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'position':
				return $this->{$field};
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}
}
