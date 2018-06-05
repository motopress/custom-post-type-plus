<?php

class Custom_Post_Type_Plus_Portfolio extends Custom_Post_Type_Plus_Base {

	const CUSTOM_POST_TYPE       = 'cptp-portfolio';
	const CUSTOM_TAXONOMY_TYPE   = 'cptp-portfolio-category';
	const OPTION_NAME            = 'cptp_portfolio';

	private static $_instance = null;

	public function __construct() {
		
		parent::__construct();

        if ( ! $this->site_supports_custom_post_type() ) {
			return;
		}

        // Add options
        //add_action( 'admin_init', array( $this, 'settings_api_init' ) );

		$this->register_post_types();

		//add_action( 'customize_register', array( $this, 'customize_register' ) );

		// Register shortcodes
		add_shortcode( 'portfolio', array( $this, 'portfolio_shortcode' ) );

		add_action( sprintf( '%s_shortcode_before', self::OPTION_NAME ), array( $this, 'shortcode_before'), 10, 1);
		add_action( sprintf( '%s_shortcode_after', self::OPTION_NAME ), array( $this, 'shortcode_after'), 10, 1);
		add_action( sprintf( '%s_shortcode_pagination', self::OPTION_NAME ), array( $this, 'shortcode_pagination'), 10, 1);
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Register Portfolio Post Type
	 */
	public function register_post_types() {

		/**
		 * Custom Post Type: Portfolio
		 */
		$labels = array(
			'name' => __( 'Portfolio', 'custom-post-type-plus' ),
			'singular_name' => __( 'Portfolio', 'custom-post-type-plus' ),
			'all_items' => __( 'Portfolio', 'custom-post-type-plus' ),
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_rest' => false,
			'rest_base' => '',
			'has_archive' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-portfolio',
			'show_in_nav_menus' => true,
			'exclude_from_search' => false,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => array(
				'slug' => 'portfolio',
				'with_front' => false,
			),
			'query_var' => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'author', 'page-attributes' ),
		);

		register_post_type(
			self::CUSTOM_POST_TYPE,
			apply_filters( sprintf( '%s_register_post_type', self::OPTION_NAME ), $args)
		);

		/**
		 * Taxonomy: Categories
		 */

		$labels = array(
			'name' => __( 'Portfolio Categories', 'custom-post-type-plus' ),
			'singular_name' => __( 'Category', 'custom-post-type-plus' ),
			'menu_name' => _x( 'Categories', 'Admin menu name', 'custom-post-type-plus' ),
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'portfolio-category',
				'with_front' => false,
				'hierarchical' => true,
			),
			'show_admin_column' => true,
			'show_in_rest' => false,
			'rest_base' => 'portfolio_category',
			'show_in_quick_edit' => true,
		);

		register_taxonomy(
			self::CUSTOM_TAXONOMY_TYPE,
			array( self::CUSTOM_POST_TYPE ),
			apply_filters( sprintf( '%s_register_taxonomy', self::OPTION_NAME ), $args, self::CUSTOM_TAXONOMY_TYPE)
		);
	}

	public function portfolio_shortcode( $atts ) {

		$atts = shortcode_atts( array(
				'ids'		=> '',
				'category'	=> false,
				'columns'	=> 1,
				'order'		=> 'DESC',
				'orderby'	=> 'date',
				'showposts'	=> false,
			), $atts, self::CUSTOM_POST_TYPE
		);

		$atts['columns'] = absint( $atts['columns'] );
		$atts['showposts'] = intval( $atts['showposts'] );

		$default = array(
			'order'          => $atts['order'],
			'orderby'        => $atts['orderby'],
		);
		
		$args = wp_parse_args( $atts, $default );
		$args['post_type'] = self::CUSTOM_POST_TYPE;
		$args['paged'] = cptp_get_paged_query_var();
		$args['posts_per_page'] = $atts['showposts'];

		if ( !empty($atts['ids']) ) {
			$args['post__in'] = array_map( 'trim', explode( ',', $atts['ids'] ) );
		} else if ( false != $atts['category'] ) {
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
				cptp_get_template_part( 'template-parts/content-portfolio', 'shortcode' );

			endwhile;

			do_action( sprintf( '%s_shortcode_pagination', self::OPTION_NAME ), $query );

			do_action( sprintf( '%s_shortcode_after', self::OPTION_NAME ), $atts );
		}

		$html = ob_get_clean();
		wp_reset_postdata();

		return $html;
	}
	
	public function shortcode_before( $atts ) {
		?>
		<div class="portfolio-wrapper columns-<?php echo esc_attr( $atts['columns'] ); ?>">
		<?php
	}
	
	public function shortcode_after( $atts ) {
		?>
		</div>
		<?php
	}
	
	public function shortcode_pagination( $query ) {
		cptp_render_pagination( $query );
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

}

add_action( 'init', array( 'Custom_Post_Type_Plus_Portfolio', 'instance' ) );
register_activation_hook( __FILE__, array( 'Custom_Post_Type_Plus_Portfolio', 'activation_post_type_support' ) );
