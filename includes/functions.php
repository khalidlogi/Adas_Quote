<?php
// functions.php

class PluginToolbox {
	/**
	 * Get all the categories
	 *
	 * @return array
	 */
	public static function getAllProductCategories() {
		$args           = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		);
		$all_categories = get_terms( $args );

		return $all_categories;
	}

	/**
	 * Get the selected categories
	 *
	 * @return array
	 */
	public static function getAdasQuoteSelectedCategories() {
		$selected_categories = get_option( 'adas_quote_selected_categories', array() );

		if ( is_string( $selected_categories ) ) {
			return unserialize( $selected_categories );
		} else {
			return $selected_categories;
		}
	}

	/**
	 * Get all category IDs
	 *
	 * @return array
	 */
	public static function getAllProductCategoryIds() {
		$all_categories = self::getAllProductCategories();
		$category_ids   = wp_list_pluck( $all_categories, 'term_id' );

		return $category_ids;
	}

	/**
	 * Display a list of all categories
	 */
	public static function displayAllProductCategories() {
		$all_categories = self::getAllProductCategories();

		if ( ! empty( $all_categories ) ) {
			echo '<ul>';
			foreach ( $all_categories as $category ) {
				echo '<li>Category ID: ' . $category->term_id . ', Category Name: ' . $category->name . '</li>';
			}
			echo '</ul>';
		} else {
			echo 'No product categories found.';
		}
	}


	/**
	 * Custom function to do something else
	 */
	public static function doSomething( $param1, $param2 ) {
		// Implement your custom functionality here
		return $result;
	}
}