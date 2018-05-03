<?php

class Custom_Post_Type_Plus_Testimonial {

	const CUSTOM_POST_TYPE       = 'cptp-testimonial';
	const CUSTOM_TAXONOMY_TYPE   = 'cptp-testimonial-category';
	const OPTION_NAME            = 'cptp_testimonial';

	private static $_instance = null;

	public function __construct() {

        if ( ! $this->theme_supports_custom_post_type() ) {
			return;
		}

        // Add options
        //add_action( 'admin_init', array( $this, 'settings_api_init' ) );

		$this->register_post_types();

		add_action( 'after_switch_theme', array( $this, 'flush_rules_on_switch' ) );
		//add_action( 'customize_register', array( $this, 'customize_register' ) );

		// Register shortcodes
		add_shortcode( 'testimonials', array( $this, 'testimonials_shortcode' ) );

		add_action( sprintf( '%s_shortcode_before', self::OPTION_NAME ), array( $this, 'shortcode_before'), 10, 1);
		add_action( sprintf( '%s_shortcode_after', self::OPTION_NAME ), array( $this, 'shortcode_after'), 10, 1);
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Register Testimonial Post Type
	 */
	function register_post_types() {

		/**
		 * Custom Post Type: Testimonial
		 */
		$labels = array(
			'name' => __( 'Testimonials', 'custom-post-type-plus' ),
			'singular_name' => __( 'Testimonial', 'custom-post-type-plus' ),
		);

		$args = array(
			'label' => __( 'Testimonials', 'custom-post-type-plus' ),
			'labels' => $labels,
			'description' => __( 'Testimonials post type description.', 'custom-post-type-plus' ),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_rest' => false,
			'rest_base' => '',
			'has_archive' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-testimonial',
			'show_in_nav_menus' => true,
			'exclude_from_search' => false,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => array( 'slug' => 'testimonial', 'with_front' => true ),
			'query_var' => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author', 'page-attributes' ),
		);

		register_post_type(
			self::CUSTOM_POST_TYPE,
			apply_filters( sprintf( '%s_register_post_type', self::OPTION_NAME ), $args)
		);

		/**
		 * Taxonomy: Categories
		 */

		$labels = array(
			'name' => __( 'Categories', 'custom-post-type-plus' ),
			'singular_name' => __( 'Category', 'custom-post-type-plus' ),
		);

		$args = array(
			'label' => __( 'Categories', 'custom-post-type-plus' ),
			'labels' => $labels,
			'public' => true,
			'hierarchical' => true,
			'label' => 'Categories',
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'testimonial-category', 'with_front' => true, ),
			'show_admin_column' => false,
			'show_in_rest' => false,
			'rest_base' => 'testimonial_category',
			'show_in_quick_edit' => false,
		);

		register_taxonomy(
			self::CUSTOM_TAXONOMY_TYPE,
			array( self::CUSTOM_POST_TYPE ),
			apply_filters( sprintf( '%s_register_taxonomy', self::OPTION_NAME ), $args, self::CUSTOM_TAXONOMY_TYPE)
		);
	}

	public function testimonials_shortcode( $atts ) {

		$atts = shortcode_atts( array(
				'category'	=> false,
				'columns'	=> 1,
			), $atts, self::CUSTOM_POST_TYPE
		);

		$atts['columns'] = absint( $atts['columns'] );

		$default = array();
		$exclude = '';
		
		if ( is_singular( self::CUSTOM_POST_TYPE ) ) {
		    $exclude = array( get_the_ID() );
	    }

		$args = wp_parse_args( $atts, $default );
		$args['post_type'] = self::CUSTOM_POST_TYPE;
	    $args['post__not_in'] = $exclude;

		if ( false != $atts['category'] ) {
			$args['tax_query'] = array();
			array_push( $args['tax_query'], array(
				'taxonomy' => self::CUSTOM_TAXONOMY_TYPE,
				'field'    => 'slug',
				'terms'    => $atts['category'],
			) );
		}

		$html = '';
		$query = new WP_Query( $args );

		ob_start();

		if ( $query->have_posts() ) {

			do_action( sprintf( '%s_shortcode_before', self::OPTION_NAME ), $atts );

			while ( $query->have_posts() ) :

				$query->the_post();
				get_template_part( 'template-parts/content-testimonial', 'shortcode' );

			endwhile;

			do_action( sprintf( '%s_shortcode_after', self::OPTION_NAME ), $atts );

		}

		$html = ob_get_clean();
		wp_reset_postdata();

		return $html;
	}
	
	public function shortcode_before( $atts ) {
		?>
		<div class="testimonials-wrapper columns-<?php echo esc_attr( $atts['columns'] ); ?>">
		<?php
	}
	
	public function shortcode_after( $atts ) {
		?>
		</div>
		<?php
	}

	/**
	 * Adds section to the Customizer.
	 */
	function customize_register( $wp_customize ) {
		$options = get_theme_support( self::CUSTOM_POST_TYPE );
	}

	/**
	 * Add fields in 'Settings' > 'Writing'
	 * for enabling CPTP functionality.
	 */
    function settings_api_init() {
    }

	function theme_supports_custom_post_type() {
		if ( current_theme_supports( self::CUSTOM_POST_TYPE ) ) {
			return true;
		}
		return false;
	}

	/*
	 * Flush permalinks when supported theme is activated
	 */
	public function flush_rules_on_switch() {
		if ( $this->theme_supports_custom_post_type() ) {
			flush_rewrite_rules();
		}
	}

}

add_action( 'init', array( 'Custom_Post_Type_Plus_Testimonial', 'instance' ) );
