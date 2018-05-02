<?php
/**
 * Plugin Name: Custom Post Type Plus
 * Plugin URI:  https://motopress.com
 * Description: This plugin lets you add several custom post types in your WordPress.
 * Version:     0.0.1
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
			
			register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation_hook' ) );

			$this->include_files();

            add_action('plugins_loaded', array($this, 'custom_post_type_plus_plugins_loaded'));
        }

        /**
         * Load plugin textdomain.
         *
         * @access public
         * @return void
         */
        function custom_post_type_plus_plugins_loaded()
        {
            load_plugin_textdomain('custom-post-type-plus', false, basename(dirname(__FILE__)) . '/languages/');

        }

        public function include_files()
        {
            /*
            * Include Custom Post Types
            */
            include_once CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'team.php';
            include_once CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'portfolio.php';
            include_once CUSTOM_POST_TYPE_PLUS_INCLUDES_PATH . 'testimonial.php';
        }
		
		public function activation_hook() {
			flush_rewrite_rules();
		}
		
		public function deactivation_hook() {
			flush_rewrite_rules();
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

endif;