<?php
/**
* These functions programmatically create alphabetical "glossary" taxonomy
* based on the first letter of the title of each Touring Arts (TAR) post
* and generate the necessary html for the glossary pagination on
* https://www.4culture.org/touring-arts/all-artists/
*
* This happens on post create or post update operations in the WordPress
* admin area
*/

/**
* Add new `glossary` taxonomy, NOT hierarchical (like tags)
* for all TAR posts
*
* @throws
* @author Laud Tetteh (laud@studiotenfour.com)
* @return
*/
function stf_create_glossary_taxonomy(){
    // Register `glossary` taxonomy, if none exists
    if(!taxonomy_exists('glossary')){
        register_taxonomy('glossary', array('touring_arts'), array(
            'show_ui' => false
        ));
    }
}
add_action('init','stf_create_glossary_taxonomy');

/**
* Force update all TAR posts with glossary taxonomy
* Save first letter of post title as its glossary taxonomy value
*
* @throws
* @author Laud Tetteh (laud@studiotenfour.com)
* @return
*/
function stf_save_first_letter() {
    $taxonomy = 'glossary';
    $my_posts = get_posts( array('post_type' => 'touring_arts', 'numberposts' => -1 ) );

    foreach ( $my_posts as $my_post ):
    //If sort_by_keyword field exists for a profile, use the first
    //letter of that value for glossary taxonomy, if not, use title
    $sort_by = !empty(get_post_meta($my_post->ID, 'sort_by_keyword', true) ) ? get_post_meta($my_post->ID, 'sort_by_keyword', true) : $my_post->post_title;
    //set term as first letter of post title, lower case
    wp_set_post_terms( $my_post->ID, strtolower(substr($sort_by, 0, 1)), $taxonomy );

    endforeach;
}
add_action( 'init', 'stf_save_first_letter' );

/**
* Create and display the `glossary` pagination html
*
* @param string $post_type
* @throws
* @author Laud Tetteh (laud@studiotenfour.com)
* @return string $html
*/
function glossary($post_type) {
    $taxonomy = 'glossary';
    $terms = get_terms($taxonomy); // Get all the terms of the `glossary` taxonomy
    $search_term = get_search_query() ? get_search_query() : ''; // Get current search term from query string
    $tag = is_tag() ? 'tag/' . strtolower(single_tag_title('', false)) : ''; // Incorporate tag search
    $concentration = is_tax('concentration') ? 'touring-arts/' . get_queried_object()->slug . '/': '';

    // Generate the actual pagination html
    $html = '';
    $html .= '<div class="alphabetical-menu-bar">';
    $html .= '<ul class="glossary-menu">';

    // Iterate through array of letters a-z
    foreach(range('a', 'z') as $i) :

        // String together the query string based on requested parameters
        $link = home_url('/') . $concentration . $tag . '?'. 's=' . $search_term . '&search-type=' . $post_type . '&glossary=' . $i . '&sentence=1';
        $current = ($i == get_query_var($taxonomy)) ? "current-menu-item menu-item" : "menu-item";
        // If the current glossary page matches the selected glossary item
        // then it is the `current` glossary page
        // Remove the hyperlink from the item and style it differently
        if ( 'current-menu-item' == $current ){
            $html .= sprintf( __('<li class="az-char %s"><strong>'. strtoupper($i) .'</strong></li>'), $current );
        } else {
            $html .= sprintf( __('<li class="az-char %s"><a href="'.$link.'">'. strtoupper($i) .'</a></li>'), $current );
        }

    endforeach;

    $html .= '</ul>';
    $html .= '<div class="paddles">';
    $html .= '<button class="left-paddle paddle hidden">';
    $html .= '<';
    $html .= '</button>';
    $html .= '<button class="right-paddle paddle">';
    $html .= '>';
    $html .= '</button>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}
