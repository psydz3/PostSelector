<?php
/* common functions */
/**
 * Walker to output an unordered list of category option elements.
 * based on Walker_Category_Checklist in wp-admin/includes/meta-boxes.php
 */
class Walker_Category_Options extends Walker {
	public $tree_type = 'category';
	public $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' ); // TODO: decouple this

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker:start_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		// $indent = str_repeat("&mdash; ", $depth);
		// $output .= "$indent<ul class='children'>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		// $output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		if ( empty( $args['taxonomy'] ) ) {
			$taxonomy = 'category';
		} else {
			$taxonomy = $args['taxonomy'];
		}
		$selected = ! empty( $args['current_value'] ) ? ( $args['current_value'] == $category->term_id ? 'selected' : '') : '';
		/** This filter is documented in wp-includes/category-template.php */
		$output .= "\n<option value='{$category->term_id}' $selected>" .
			esc_html( apply_filters( 'the_category', $category->name ) );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 2.5.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 */
	public function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</option>\n";
	}
}
function wototo_category_options_html( $current_value ) {
	// see wp_terms_checklist
	$walker = new Walker_Category_Options;
	$taxonomy = 'category';
	$tax = get_taxonomy( $taxonomy );
	$categories = (array) get_terms( $taxonomy, array( 'get' => 'all' ) );
	$args = array( 'taxonomy' => $taxonomy, 'current_value' => $current_value );
	echo call_user_func_array( array( $walker, 'walk' ), array( $categories, 0, $args ) );
}
function filter_content ( $content ) {
	$content = apply_filters( 'the_content', $content );
	$content = str_replace( ']]>', ']]&gt;', $content );
	// audio may need fixing - player defaults to hidden in WordPress 4.1 when I test...
	// <audio class="wp-audio-shortcode" id="audio-0-1" preload="none" style="width: 100%; /* visibility: hidden; */" controls="controls"><source type="audio/mpeg" src="http://172.17.0.6/wp-content/uploads/2015/01/campus.mp3?_=1"><a href="http://172.17.0.6/wp-content/uploads/2015/01/campus.mp3">http://172.17.0.6/wp-content/uploads/2015/01/campus.mp3</a></audio>
	$content = preg_replace( '/(<audio\s[^\/>]*)visibility\s*:\s*hidden\s*[;]?/', '$1', $content );
	return $content;
}

