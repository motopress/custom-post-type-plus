<?php
/**
 * Plugin Name: Custom Post Type Plus
 * Plugin URI:  https://motopress.com
 * Description: This plugin lets you add several custom post types in your WordPress.
 * Version:     1.1.2
 * Author:      MotoPress
 * Author URI:  https://motopress.com
 * Text Domain: custom-post-type-plus
 * Domain Path: /languages
 */

if ( !class_exists('Custom_Post_Type_Plus') ) :

    class Custom_Post_Type_Plus
    {

        /**
         * The single instance of the class.
         */
        private static $_instance = null;

        /**
         * Main Custom_Post_Type_Plus Instance.
         *
         * @see custom_post_type_plus_instance()
         * @return Custom_Post_Type_Plus - Main instance.
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public function __construct()
        {
			/*
			 *  Path to classes folder in Plugin
			 */

			define('CUSTOM_POST_TYPE_PLUS_PATH', plugin_dir_path(__FILE__) );
			define('CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH', plugin_dir_path(__FILE__) . 'includes/');
			define('CUSTOM_POST_TYPE_PLUS_PLUGIN_URL', plugin_dir_url(__FILE__));

			$this->include_files();

            add_action('plugins_loaded', array($this, 'plugins_loaded'));
			add_action('init', array($this, 'maybe_flush_rewrite_rules'));
        }

        /**
         * Load plugin textdomain.
         *
         * @access public
         * @return void
         */
        public function plugins_loaded()
        {
            load_plugin_textdomain('custom-post-type-plus', false, basename(dirname(__FILE__)) . '/languages/');
        }

        public function include_files()
        {

			include CUSTOM_POST_TYPE_PLUS_PATH . 'functions.php';
            /*
            * Include Custom Post Types
            */
			include CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'base.php';

            include CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'team.php';
            include CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'portfolio.php';
            include CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'testimonial.php';
            include CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'activity.php';
            include CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'amenity.php';

        }

		public static function activation() {
			update_option( 'custom_post_type_plus_flush_rewrite_rules', '1' );
		}

		public function maybe_flush_rewrite_rules() {
			if ( get_option( 'custom_post_type_plus_flush_rewrite_rules', '0' ) === '1' ) {
				flush_rewrite_rules();
				delete_option('custom_post_type_plus_flush_rewrite_rules');
			}
		}

		public static function get_default_template() {
			return 'templates/default.php';
		}
    }

	/**
	 * Main instance of Custom_Post_Type_Plus_Instance.
	 *
	 * @since
	 * @return Custom_Post_Type_Plus
	 */
	function custom_post_type_plus_instance()
	{
		return Custom_Post_Type_Plus::instance();
	}

	/*
	 * Global for backwards compatibility.
	 */
	$GLOBALS['custom_post_type_plus_instance'] = custom_post_type_plus_instance();

	register_activation_hook( __FILE__, array( 'Custom_Post_Type_Plus', 'activation' ) );

endif;