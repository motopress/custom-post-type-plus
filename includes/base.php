<?php

class Custom_Post_Type_Plus_Base {

	const CUSTOM_POST_TYPE = '';
	const OPTION_NAME = '';

	public function __construct() {

		add_action( 'import_start', array( $this, 'register_post_types' ) );
		
		// Check on theme switch if theme supports CPT and setting is disabled
		add_action( 'after_switch_theme', array( $this, 'activation_post_type_support' ) );

		if ( ! $this->site_supports_custom_post_type() ) {
			return;
		}

		add_action( 'after_switch_theme', array( $this, 'flush_rules_on_switch' ) );
		add_filter( 'get_the_archive_title', array( $this, 'get_the_archive_title' ) );
		add_action( sprintf( 'add_option_%s', static::OPTION_NAME ), array( $this, 'flush_rules_on_enable' ), 10 );
		add_action( sprintf( 'update_option_%s', static::OPTION_NAME ), array( $this, 'flush_rules_on_enable' ), 10 );
		
		// If CPT was enabled programatically and no CPT items exist when user switches away, disable
		if ( $this->site_supports_custom_post_type() ) {
			add_action( 'switch_theme', array( $this, 'deactivation_post_type_support' ) );
		}
	}

	/*
	 * Filter Archive title
	 */
	public function get_the_archive_title( $title ) {

		if ( is_post_type_archive( static::CUSTOM_POST_TYPE ) )
			$title = post_type_archive_title( '', false );

		return $title;
	}

	/*
	 * Flush permalinks when supported theme is activated
	 */
	public function flush_rules_on_switch() {
		if ( $this->site_supports_custom_post_type() ) {
			flush_rewrite_rules();
		}
	}
	
	/*
	 * Flush permalinks when CPT option is turned on/off
	 */
	function flush_rules_on_enable() {
		flush_rewrite_rules();
	}
	
	/**
	 * On plugin/theme activation, check if current theme supports CPT
	 */
	static function activation_post_type_support() {
		if ( current_theme_supports( static::CUSTOM_POST_TYPE ) ) {
			update_option( static::OPTION_NAME, '1' );
		}
	}
	
	/**
	* Should this Custom Post Type be made available?
	*/
	function site_supports_custom_post_type() {
		// If the current theme requests it.
		if ( current_theme_supports( static::CUSTOM_POST_TYPE ) || get_option( static::OPTION_NAME, '0' ) ) {
			return true;
		}

		// Otherwise, say no unless something wants to filter us to say yes.
		return (bool) apply_filters( 'custom_post_type_plus_enable_cpt', false, static::CUSTOM_POST_TYPE );
	}
	
	/**
	 * On theme switch, check if CPT item exists and disable if not
	 */
	function deactivation_post_type_support() {
		$posts = get_posts( array(
			'fields'           => 'ids',
			'posts_per_page'   => 1,
			'post_type'        => static::CUSTOM_POST_TYPE,
			'suppress_filters' => false
		) );

		if ( empty( $posts ) ) {
			update_option( static::OPTION_NAME, '0' );
		}
	}
	
}