<?php

/**
 * Get WP Query paged var
 *
 * @return int
 */
function cptp_get_paged_query_var(){
	if ( get_query_var( 'paged' ) ) {
		$paged = absint( get_query_var( 'paged' ) );
	} else if ( get_query_var( 'page' ) ) {
		$paged = absint( get_query_var( 'page' ) );
	} else {
		$paged = 1;
	}
	return $paged;
}

/**
 * Display custom pagination for WP_Query
 *
 * @param \WP_Query $wp_query
 * @return null
 */
function cptp_render_pagination( $wp_query ) {

	if ( $wp_query->max_num_pages == 1 ) {
		return;
	}

	$big			 = 999999;
	$search_for		 = array( $big, '#038;' );
	$replace_with	 = array( '%#%', '&' );

	$paginationAtts = array(
		'base'		 => str_replace( $search_for, $replace_with, get_pagenum_link( $big ) ),
		'format'	 => '?paged=%#%',
		'current'	 => max( 1, cptp_get_paged_query_var() ),
		'total'		 => $wp_query->max_num_pages
	);
	$paginationAtts = apply_filters( 'cptp_pagination_args', $paginationAtts );

	$pagination = paginate_links( $paginationAtts );
	$pagination = apply_filters( 'cptp_pagination_links', $pagination );

	if ( $pagination ) {

		$screenReaderText = __('Pagination', 'custom-post-type-plus');
		$paginationClass = apply_filters( 'cptp_pagination_class', 'pagination' );

		echo cptp_pagination_markup( $pagination, $paginationClass, $screenReaderText );
	}
}

/**
 * Wraps passed links in pagination markup.
 *
 * @param string $links              Navigational links.
 * @param string $class              Optional. Custom classes string for nav element.
 * @param string $screen_reader_text Optional. Screen reader text for nav element. Default: ''.
 * @return string Pagination template tag.
 */
function cptp_pagination_markup( $links, $class = '', $screen_reader_text = '' ){

	$template =
		'<nav class="navigation %1$s" role="navigation">
			<h2 class="screen-reader-text">%2$s</h2>
			<div class="nav-links">%3$s</div>
		</nav>';

	return sprintf( $template, esc_attr( $class ), esc_html( $screen_reader_text ), $links );
}

function cptp_get_template_part( $slug, $name = null ) {
	/**
	 * Fires before the specified template part file is loaded.
	 */
	do_action( "get_template_part_{$slug}", $slug, $name );

	$templates = array();
	$name = (string) $name;
	if ( '' !== $name )
		$templates[] = "{$slug}-{$name}.php";

	$templates[] = "{$slug}.php";
	$templates[] = Custom_Post_Type_Plus::get_default_template();

	$templates = apply_filters('cptp_get_template_part_templates', $templates, $slug, $name);

	cptp_locate_template($templates, true, false, $slug, $name);
}

function cptp_locate_template($template_names, $load = false, $require_once = true, $slug, $name = null ) {
	$located = '';
	foreach ( (array) $template_names as $template_name ) {
		if ( !$template_name )
			continue;
		if ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
			$located = STYLESHEETPATH . '/' . $template_name;
			break;
		} elseif ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
			$located = TEMPLATEPATH . '/' . $template_name;
			break;
		} elseif ( file_exists( CUSTOM_POST_TYPE_PLUS_PATH . $template_name ) ) {
			$located = CUSTOM_POST_TYPE_PLUS_PATH . $template_name;
			break;
		}
	}

	$located = apply_filters('cptp_locate_template', $located, $slug, $name);

	if ( $load && '' != $located )
		load_template( $located, $require_once );

	return $located;
}