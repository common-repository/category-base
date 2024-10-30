<?php
/**
 * Plugin Name: Category Base
 * Plugin URI: https://wordpress.org/plugins/category-base/
 * Description: Simply Remove Category Base from URL and show only category name in WordPress permalink.
 * Version: 1.0
 * Author: Sirius Pro
 * Author URI: https://siriuspro.pl
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
 
 if ( ! defined( 'ABSPATH' ) ) exit;

 function spcategorybase_remove_category_base( $string ) {
   $category_base = get_option( 'category_base' ) ? get_option( 'category_base' ) : 'category';
   $category_base = '/' . trim($category_base, '/') . '/';
   return preg_replace( '/'.preg_quote($category_base, '/').'/i', '/', $string );
 }
 add_filter( 'category_link', 'spcategorybase_remove_category_base', 1000 );

 function spcategorybase_add_category_rewrite_rules( $rules ) {
   global $wp_rewrite;
   $categories = get_categories( array(
     'hide_empty' => false,
   ) );
   if ( is_array( $categories ) && ! empty( $categories ) ) {
     $slugs = array();
     foreach ( $categories as $category ) {
       if ( is_object( $category ) && ! is_wp_error( $category ) ) {
         if ( 0 == $category->category_parent ) {
           $slugs[] = $category->slug;
         } else {
           $slugs[] = trim(get_category_parents( $category->term_id, false, '/', true ), '/');
         }
       }
     }
     if ( ! empty( $slugs ) ) {
       $rules = array();
       foreach ( $slugs as $slug ) {
         $rules[ '(' . $slug . ')/feed/(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?category_name=' . $slug . '&feed=' . $wp_rewrite->preg_index(2);
         $rules[ '(' . $slug . ')/(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?category_name=' . $slug . '&feed=' . $wp_rewrite->preg_index(2);
         $rules[ '(' . $slug . ')/page/?([0-9]{1,})/?$' ] = 'index.php?category_name=' . $slug . '&paged=' . $wp_rewrite->preg_index(2);
         $rules[ '(' . $slug . ')/?$' ] = 'index.php?category_name=' . $slug;
       }
     }
   }
   return $rules;
 }
 add_filter( 'category_rewrite_rules', 'spcategorybase_add_category_rewrite_rules' );
 
 function spcategorybase_flush_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}
add_action( 'init', 'spcategorybase_flush_rules' );