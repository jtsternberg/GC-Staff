<?php
/**
 * GC Staff Member
 * @version 0.1.0
 * @package GC Staff
 */

class GCST_Staff_Member {

	/**
	 * Post object to wrap
	 *
	 * @var   WP_Post
	 * @since 0.0.0
	 */
	protected $post;

	/**
	 * Post ID
	 *
	 * @var   int
	 * @since 0.0.0
	 */
	protected $ID;

	/**
	 * Image data for the staff member.
	 *
	 * @var array
	 */
	protected $images = array();

	/**
	 * Constructor
	 *
	 * @since  0.0.0
	 * @param  WP_Post $post Post object to wrap
	 * @return void
	 */
	public function __construct( WP_Post $post ) {
		$post_type = gc_staff()->staff->post_type();
		if ( $post->post_type !== $post_type ) {
			wp_die( 'Sorry, '. __CLASS__ .' expects a '. $post_type .' object.' );
		}

		$this->post = $post;
		$this->ID = $this->post->ID;
	}

	/**
	 * Wrapper for get_permalink
	 *
	 * @since  0.0.0
	 *
	 * @return string Staff post permalink
	 */
	public function permalink() {
		return get_permalink( $this->ID );
	}

	/**
	 * Wrapper for get_the_title
	 *
	 * @since  0.0.0
	 *
	 * @return string Staff post title
	 */
	public function title() {
		return get_the_title( $this->ID );
	}

	/**
	 * Wrapper for get_the_post_thumbnail which stores the results to the object
	 *
	 * @since  0.0.0
	 *
	 * @param  string|array $size  Optional. Image size to use. Accepts any valid image size, or
	 *	                            an array of width and height values in pixels (in that order).
	 *	                            Default 'full'.
	 * @param  string|array $attr Optional. Query string or array of attributes. Default empty.
	 * @return string             The post thumbnail image tag.
	 */
	public function featured_image( $size = 'full', $attr = '' ) {
		// Unique id for the passed-in attributes.
		$id = md5( $attr );

		if ( isset( $this->images[ $size ] ) ) {
			// If we got it already, then send it back
			if ( isset( $this->images[ $size ][ $id ] ) ) {
				return $this->images[ $size ][ $id ];
			} else {
				$this->images[ $size ][ $id ] = array();
			}
		} else {
			$this->images[ $size ][ $id ] = array();
		}

		$img = get_the_post_thumbnail( $this->ID, $size, $attr );
		$this->images[ $size ][ $id ] = $img;

		return $this->images[ $size ][ $id ];
	}

	/**
	 * Wrapper for get_post_thumbnail_id
	 *
	 * @since  0.0.0
	 *
	 * @return string|int Post thumbnail ID or empty string.
	 */
	public function image_id() {
		return get_post_thumbnail_id( $this->ID );
	}

	/**
	 * Wrapper for get_post_meta
	 *
	 * @since  0.0.0
	 *
	 * @param  string  $key Meta key
	 *
	 * @return mixed        Value of post meta
	 */
	public function get_meta( $key ) {
		return get_post_meta( $this->ID, $key, 1 );
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $property
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $property ) {
		// Automate
		switch ( $property ) {
			case 'post':
			case 'images':
				return $this->{$property};
			default:
				// Check post object for property
				// In general, we'll avoid using same-named properties,
				// so the post object properties are always available.
				if ( isset( $this->post->{$property} ) ) {
					return $this->post->{$property};
				}
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $property );
		}
	}

	/**
	 * Magic isset checker for our object.
	 *
	 * @param string $property
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __isset( $property ) {
		$property = $this->translate_property( $property );

		// Automate
		switch ( $property ) {
			// case '':
			// 	$terms = $this->{$property}();
			// 	return ! empty( $terms );
			default:
				// Check post object for property
				// In general, we'll avoid using same-named properties,
				// so the post object properties are always available.
				return isset( $this->post->{$property} );
		}
	}

}
