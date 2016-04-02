<?php
/**
 * GC Staff Taxonomies Base
 *
 * @version NEXT
 * @package GC Staff
 */

abstract class GCST_Taxonomies_Base extends Taxonomy_Core {

	/**
	 * The identifier for this object
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * GCST_Staff object
	 *
	 * @var GCST_Staff
	 * @since  NEXT
	 */
	protected $staff;

	/**
	 * The image meta key for this taxonomy, if applicable
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $image_meta_key = '';

	/**
	 * The default args array for self::get()
	 *
	 * @var array
	 * @since  NEXT
	 */
	protected $term_get_args_defaults = array();

	/**
	 * The default args array for self::get_many()
	 *
	 * @var array
	 * @since  NEXT
	 */
	protected $term_get_many_args_defaults = array(
		'orderby'       => 'name',
		'augment_terms' => true,
	);

	/**
	 * Constructor
	 * Register Taxonomy. See documentation in Taxonomy_Core, and in wp-includes/taxonomy.php
	 *
	 * @since NEXT
	 * @param  object $staff GCST_Staff object.
	 * @return void
	 */
	public function __construct( $staff, $args ) {
		$this->staff = $staff;
		$this->hooks();

		/*
		 * Register this taxonomy
		 * First parameter should be an array with Singular, Plural, and Registered name
		 * Second parameter is the register taxonomy arguments
		 * Third parameter is post types to attach to.
		 */
		parent::__construct(
			$args['labels'],
			$args['args'],
			array( $this->staff->post_type() )
		);

		add_action( 'init', array( $this, 'filter_values' ), 4 );
	}

	public function filter_values( $args ) {
		$args = array(
			'singular'      => $this->singular,
			'plural'        => $this->plural,
			'taxonomy'      => $this->taxonomy,
			'arg_overrides' => $this->arg_overrides,
			'object_types'  => $this->object_types,
		);

		$filtered_args = apply_filters( 'gcst_taxonomies_'. $this->id, $args, $this );
		if ( $filtered_args !== $args ) {
			foreach ( $args as $arg => $val ) {
				if ( isset( $filtered_args[ $arg ] ) ) {
					$this->{$arg} = $filtered_args[ $arg ];
				}
			}
		}
	}

	/**
	 * Initiate our hooks
	 *
	 * @since NEXT
	 * @return void
	 */
	abstract function hooks();

	public function new_cmb2( $args ) {
		$cmb_id = $args['id'];
		return new_cmb2_box( apply_filters( "gcst_cmb2_box_args_{$this->taxonomy}_{$cmb_id}", $args ) );
	}

	/**
	 * Wrapper for get_terms
	 *
	 * @since  NEXT
	 *
	 * @param  array $args Array of arguments.
	 *
	 * @return array|false Array of term objects or false
	 */
	public function get_many( $args = array(), $single_term_args = array() ) {
		$args = wp_parse_args( $args, $this->term_get_many_args_defaults );
		$args = apply_filters( "gcst_get_{$this->id}_args", $args );

		$terms = get_terms( $this->taxonomy(), $args );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return false;
		}

		if (
			isset( $args['augment_terms'] )
			&& $args['augment_terms']
			&& ! empty( $terms )
			// Don't augment for queries w/ greater than 200 terms, for perf. reasons.
			&& 200 < count( $terms )
		) {
			foreach ( $terms as $key => $term ) {
				$terms[ $key ] = $this->get( $term, $single_term_args );
			}
		}

		return $terms;
	}

	/**
	 * Get a single term object
	 *
	 * @since  NEXT
	 *
	 * @param  object|int $term Term id or object
	 * @param  array      $args Array of arguments.
	 *
	 * @return WP_Term|false    Term object or false
	 */
	public function get( $term, $args = array() ) {
		$term = isset( $term->term_id ) ? $term : get_term_by( 'id', $term_id, $this->taxonomy() );
		if ( ! isset( $term->term_id ) ) {
			return false;
		}

		$args = wp_parse_args( $args, $this->term_get_args_defaults );
		$args = apply_filters( "gcst_get_{$this->id}_single_args", $args, $term, $this );

		$term->term_link = get_term_link( $term );
		$term = $this->extra_term_data( $term, $args );

		return $term;
	}

	/**
	 * Sets extra term data on the the term object, including the image, if applicable
	 *
	 * @since  NEXT
	 *
	 * @param  WP_Term $term Term object
	 * @param  array   $args Array of arguments.
	 *
	 * @return WP_Term|false
	 */
	protected function extra_term_data( $term, $args ) {
		if ( $this->image_meta_key ) {
			$term = $this->add_image( $term, $args['image_size'] );
		}

		return $term;
	}

	/**
	 * Add term's image
	 *
	 * @since  NEXT
	 *
	 * @param  WP_Term $term Term object
	 * @param  string  $size Size of the image to retrieve
	 *
	 * @return mixed         URL if successful or set
	 */
	protected function add_image( $term, $size = '' ) {
		if ( ! $this->image_meta_key ) {
			return $term;
		}

		$term->image_id = get_term_meta( $term->term_id, $this->image_meta_key . '_id', 1 );
		if ( ! $term->image_id ) {

			$term->image_url = get_term_meta( $term->term_id, $this->image_meta_key, 1 );

			$term->image = $term->image_url ? '<img src="'. esc_url( $term->image_url ) .'" alt="'. $term->name .'"/>' : '';

			return $term;
		}

		if ( $size ) {
			$size = is_numeric( $size ) ? array( $size, $size ) : $size;
		}

		$term->image = wp_get_attachment_image( $term->image_id, $size ? $size : 'thumbnail' );

		$src = wp_get_attachment_image_src( $term->image_id, $size ? $size : 'thumbnail' );
		$term->image_url = isset( $src[0] ) ? $src[0] : '';

		return $term;
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
			case 'id':
				return $this->id;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}
}
