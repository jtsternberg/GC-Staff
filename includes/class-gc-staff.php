<?php
/**
 * GC Staff
 *
 * @version 0.0.0
 * @package GC Staff
 */

class GCST_Staff extends GCST_Post_Types_Base {

	/**
	 * Parent plugin class
	 *
	 * @var class
	 * @since  0.0.0
	 */
	protected $plugin = null;

	/**
	 * The identifier for this object
	 *
	 * @var string
	 */
	protected $id = 'staff';

	/**
	 * Default WP_Query args
	 *
	 * @var   array
	 * @since 0.0.0
	 */
	protected $query_args = array(
		'post_type'      => 'THIS(REPLACE)',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	);

	/**
	 * Constructor
	 * Register Custom Post Types. See documentation in CPT_Core, and in wp-includes/post.php
	 *
	 * @since  NEXT
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		// Register this cpt
		// First parameter should be an array with Singular, Plural, and Registered name.
		parent::__construct( $plugin, array(
			'labels' => array( __( 'Staff', 'gc-staff' ), __( 'Staff Members', 'gc-staff' ), 'gc-staff' ),
			'args' => array(
				'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
				'menu_icon' => 'dashicons-nametag',
				'rewrite' => array( 'slug' => 'staff' ),
			),
		) );
		$this->query_args['post_type'] = $this->post_type();
	}


	/**
	 * Initiate our hooks
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function hooks() {
		add_action( 'cmb2_admin_init', array( $this, 'fields' ) );
		add_action( 'dbx_post_advanced', array( $this, 'remove_excerpt_box' ) );
		add_filter( 'cmb2_override_excerpt_meta_value', array( $this, 'get_excerpt' ), 10, 2 );
		add_filter( 'cmb2_override_excerpt_meta_save', '__return_true' );

		// To remove:
		// remove_filter( 'the_content', array( gc_staff()->staff, 'do_social_links' ) );
		add_filter( 'the_content', array( $this, 'do_social_links' ) );
	}

	public function remove_excerpt_box() {
		remove_meta_box( 'postexcerpt', null, 'normal' );
		remove_meta_box( 'postimagediv', null, 'side' );
	}

	public function get_excerpt( $data, $post_id ) {
		return get_post_field( 'post_excerpt', $post_id );
	}

	/**
	 * Add custom fields to the CPT
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function fields() {
		$fields = array(
			'gc_staff_connected_user' => array(
				'name'  => __( 'Connected WordPress User', 'gc-staff' ),
				'id'    => 'gc_staff_connected_user',
				'desc'  => __( 'Type the name of the WordPress user and select from the suggested options. By associating a staff-member with a WordPress user, that WordPress user account details (first/last name, avatar, bio, etc) will be used as a fallback to the information here.', 'gc-staff' ),
				'type'  => 'user_select_text',
				'options' => array(
					'minimum_user_level' => 0,
				),
			),
			'gc_staff_first' => array(
				'id'   => 'gc_staff_first',
				'name' => __( 'First Name', 'gc-staff' ),
				'type' => 'text',
			),
			'gc_staff_last' => array(
				'id'   => 'gc_staff_last',
				'name' => __( 'Last Name', 'gc-staff' ),
				'type' => 'text',
			),
			'excerpt' => array(
				'id'   => 'excerpt',
				'name' => __( 'Excerpt', 'gc-staff' ),
				'desc' => __( 'Excerpts are optional hand-crafted summaries of your content that can be used in your theme. <a href="https://codex.wordpress.org/Excerpt" target="_blank">Learn more about manual excerpts.</a>' ),
				'type' => 'textarea',
				'escape_cb' => false,
			),
			'_thumbnail' => array(
				'id'   => '_thumbnail',
				'name' => __( 'Image', 'gc-staff' ),
				'type' => 'file',
			),
			'gc_staff_email' => array(
				'id'   => 'gc_staff_email',
				'name' => __( 'Email Address', 'gc-staff' ),
				'type' => 'text_email',
			),
			'gc_staff_social' => array(
				'id'          => 'gc_staff_social',
				'type'        => 'group',
				'options'     => array(
					'group_title'   => __( 'Social Account {#}', 'gc-staff' ), // {#} gets replaced by row number
					'add_button'    => __( 'Add Another Social Account', 'gc-staff' ),
					'remove_button' => __( 'Remove Social Account', 'gc-staff' ),
					'sortable'      => true, // beta
					'closed'     => true, // true to have the groups closed by default
				),
				'fields' => array(
					array(
						'name' => __( 'Social Account Link Title', 'gc-staff' ),
						'id'   => 'title',
						'type' => 'text',
					),
					array(
						'name' => __( 'Social Account Link URL', 'gc-staff' ),
						'id'   => 'url',
						'type' => 'text_url',
					),
					array(
						'name' => __( 'Social Account Link CSS Class', 'gc-staff' ),
						'desc'    => __( 'Enter classes separated by spaces (e.g. "fa fa-facebook")', 'gc-staff' ),
						'id'   => 'classes',
						'type' => 'text',
					),
				),
			),
		);

		$this->new_cmb2( array(
			'id'           => 'gc_staff_metabox',
			'title'        => __( 'Staff Member Details', 'gc-staff' ),
			'object_types' => array( $this->post_type() ),
			'fields'       => $fields,
		) );
	}

	public function do_social_links( $content ) {
		if ( ! is_main_query() ) {
			return $content;
		}

		$social = get_post_meta( get_the_id(), 'gc_staff_social', 1 );

		if ( $social && is_array( $social ) && ! empty( $social ) ) {

			$links = '';

			foreach ( $social as $account ) {

				$classes = isset( $account['classes'] ) ? $account['classes'] : '';
				$url     = isset( $account['text_url'] ) ? $account['text_url'] : '';
				$text    = isset( $account['text'] ) ? $account['text'] : $url;

				$links .= sprintf(
					'<li><a class="%s" href="%s">%s</a></li>',
					esc_attr( $classes ),
					esc_url( $url ),
					wp_kses_post( $text )
				);
			}

			$links = sprintf( '<ul class="gc-staff-social">%s</ul><!-- .gc-staff-social -->', $links );

			$content .= $links;
		}

		return $content;
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  NEXT
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array
	 */
	public function columns( $columns ) {
		$new_column = array();
		return array_merge( $new_column, $columns );
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 *
	 * @since  NEXT
	 * @param array $column  Column currently being rendered.
	 * @param int   $post_id ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {
		}
	}

	/**
	 * Retrieve a specific staff member.
	 *
	 * @since  0.0.0
	 *
	 * @return GCST_Staff_Member|false  GC Staff Member object if successful.
	 */
	public function get( $args ) {
		$args = wp_parse_args( $args, $this->query_args );
		$staff = new WP_Query( apply_filters( 'gcst_get_staffmember_args', $args ) );
		$staffmember = false;
		if ( $staff->have_posts() ) {
			$staffmember = new GCST_Staff_Member( $staff->post );
		}

		return $staffmember;
	}

	/**
	 * Retrieve staff members.
	 *
	 * @since  0.0.0
	 *
	 * @return WP_Query WP_Query object
	 */
	public function get_many( $args ) {
		$defaults = $this->query_args;
		unset( $defaults['posts_per_page'] );
		$args['augment_posts'] = true;

		$args = apply_filters( 'gcst_get_staffmembers_args', wp_parse_args( $args, $defaults ) );
		$staff = new WP_Query( $args );

		if (
			isset( $args['augment_posts'] )
			&& $args['augment_posts']
			&& $staff->have_posts()
			// Don't augment for queries w/ greater than 100 posts, for perf. reasons.
			&& $staff->post_count < 100
		) {
			foreach ( $staff->posts as $key => $post ) {
				$staff->posts[ $key ] = new GCST_Staff_Member( $post );
			}
		}

		return $staff;
	}

}
