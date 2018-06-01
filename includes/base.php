<?php

class Custom_Post_Type_Plus_Base {

	const CUSTOM_POST_TYPE = '';

	public function __construct() {

		add_action( 'import_start', array( $this, 'register_post_types' ) );

		if ( ! $this->theme_supports_custom_post_type() ) {
			return;
		}

		add_action( 'after_switch_theme', array( $this, 'flush_rules_on_switch' ) );
		add_filter( 'get_the_archive_title', array( $this, 'get_the_archive_title' ) );
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
	 * If theme supports CPT
	 */
	function theme_supports_custom_post_type() {
		if ( current_theme_supports( static::CUSTOM_POST_TYPE ) ) {
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