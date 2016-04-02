<?php
/**
 * GC Staff Position
 *
 * @version NEXT
 * @package GC Staff
 */

class GCST_Position extends GCST_Taxonomies_Base {

	/**
	 * The identifier for this object
	 *
	 * @var string
	 */
	protected $id = 'position';

	/**
	 * Constructor
	 * Register Taxonomy. See documentation in Taxonomy_Core, and in wp-includes/taxonomy.php
	 *
	 * @since NEXT
	 * @param  object $sermons GCST_Staff object.
	 * @return void
	 */
	public function __construct( $sermons ) {
		parent::__construct( $sermons, array(
			'labels' => array( __( 'Staff Position', 'gc-sermons' ), __( 'Staff Positions', 'gc-sermons' ), 'gcs-position' ),
			'args' => array(
				'hierarchical' => true,
			),
		) );
	}

	/**
	 * Initiate our hooks
	 *
	 * @since NEXT
	 * @return void
	 */
	public function hooks() {
	}
}
