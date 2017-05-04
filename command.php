<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

class DFM_Term_Rollup extends WP_CLI {

	/**
	 * Attaches an object to all of a terms ancestors
	 *
	 * ## OPTIONS
	 *
	 * <taxonomy>
	 * : The name of the taxonomy you want to perform the rollup on
	 *
	 * <terms>...
	 * : Term ID's you want to perform the rollup on, or "all" if it should be for all terms within the taxonomy
	 *
	 * [--post_type]
	 * : The name of the post_type
	 *
	 * ## EXAMPLES
	 *
	 * 		wp term rollup category all
	 * 		# Performs a rollup for all posts connected to any term in the category taxonomy
	 *
	 * 		wp term rollup location 10 11 12 --post_type=restaurant
	 * 		# Performs a rollup for all posts within the restaurant post type that are attached to location terms with the ID 10, 11, or 12.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function rollup( $args, $assoc_args ) {

		$taxonomy = ( isset( $args[0] ) ) ? $args[0] : '';

		// If the taxonomy doesn't exist, bail
		if ( ! taxonomy_exists( $taxonomy ) ) {
			parent::error( sprintf( 'The taxonomy %s does not exist', $taxonomy ) );
		}

		// If the taxonomy isn't hierarchical, bail.
		if ( ! is_taxonomy_hierarchical( $taxonomy ) ) {
			parent::error( sprintf( 'The %s taxonomy is not hierarchical, so this command should not be used', $taxonomy ) );
		}

		$post_type = \WP_CLI\Utils\get_flag_value( $assoc_args, 'post-type', 'post' );

		// If the post type doesn't exist, bail.
		if ( ! post_type_exists( $post_type ) ) {
			parent::error( sprintf( 'The post type %s does not exist', $post_type ) );
		}

		// If "all" is passed, get all of the terms within the taxonomy.
		if ( 'all' === $args[1] ) {
			$terms = get_terms( [ 'taxonomy' => $taxonomy, 'fields' => 'ids' ] );
		} else {
			$term_args = $args;
			unset( $term_args[0] );
			$terms = array_map( 'absint' , array_values( $term_args ) );
		}

		// Setup the default query args
		$query_args = [
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => 100,
			'tax_query'      => [
				[
					'taxonomy'         => $taxonomy,
					'field'            => 'post_id',
					'include_children' => false,
					'terms'            => $terms,
				]
			]
		];

		// Do a query to figure out the total amount of affected posts
		$total_post_query = new WP_Query( $query_args );

		if ( $total_post_query->have_posts() ) {
			parent::success( sprintf( '%d affected posts found', $total_post_query->found_posts ) );
		} else {
			parent::error( 'No affected posts found' );
		}

		// Create the progress bar
		$progress_bar = \WP_CLI\Utils\make_progress_bar( 'Rolling up terms', $total_post_query->found_posts );

		$page = 1;

		// Shut off defer term counting for now, and just update it once when we're done
		wp_defer_term_counting( true );

		// Kick off the do while loop
		do {

			// Set the query page
			$query_args['paged'] = $page;

			$post_query = new WP_Query( $query_args );

			// Extract the post_ids from the object
			$posts = $post_query->posts;

			if ( ! empty( $posts ) && is_array( $posts ) ) {

				foreach ( $posts as $post_id ) {

					// Get all of the terms within the specified taxonomy attached to the post
					$post_terms = get_the_terms( $post_id, $taxonomy );

					// Pluck the term ID's so we can track which ones are already attached to the object
					$term_ids = wp_list_pluck( $post_terms, 'term_id' );

					if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) && is_array( $post_terms ) ) {

						$ancestors = [];

						foreach ( $post_terms as $term_obj ) {

							// If some id's were passed to specifically passed to do the rollup for, filter
							// the list of objects down to only include objects with that ID.
							if ( 'all' !== $args[1] && ! in_array( $term_obj->term_id, $terms, true ) ) {
								continue;
							}

							if ( ! empty( $term_obj->parent ) ) {

								// Get all of the ancestors for the term object
								$raw_ancestors = get_ancestors( $term_obj->term_id, $taxonomy );

								// Find which of the ancestors are not already attached to the post
								$ancestors = array_merge( array_diff( $raw_ancestors, $term_ids ), $ancestors );

							}
						}

						if ( ! empty( $ancestors ) ) {
							wp_set_object_terms( $post_id, array_unique( $ancestors ), $taxonomy, true );
						}

					}
				}
			}

			$progress_bar->tick( count( $posts ) );
			$page++;

			$this->stop_the_insanity();
			sleep(1 );

		} while( ! empty( $posts ) );

		parent::success( 'Updating term counts...' );
		wp_defer_term_counting( false );
		parent::success( 'Terms rolled up' );

	}

	private function stop_the_insanity() {

		global $wpdb, $wp_object_cache;

		$wpdb->queries = array(); // or define( 'WP_IMPORTING', true );

		if ( !is_object( $wp_object_cache ) )
			return;

		$wp_object_cache->group_ops = array();
		$wp_object_cache->stats = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache = array();

		if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset(); // important
		}

	}
}

WP_CLI::add_command( 'term rollup', [ 'DFM_Term_Rollup', 'rollup' ] );
