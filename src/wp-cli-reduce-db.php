<?php
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	class WP_CLI_Reduce_DB extends WP_CLI_Command {
		protected $environment;

		/**
		 * The tables that are commonly big or contained personal data
		 *
		 **/
		private $tables_to_filter = [
			// SearchWP 3.x
			'swp_log',
			'swp_index',
			'swp_terms',

			// SearchWP 4.x
			'searchwp_index',
			'searchwp_log',

			// Redirect LOG and redirection 404
			'redirection_logs',
			'redirection_404',

			// YOP Logs
			'yop2_poll_logs',

			// WSAL
			'wsal_metadata',
			'wsal_occurrences',

			// Relevanssi LOG
			'relevanssi_log',

			// Log HTTP requests
			'lhr_log',

			// WP mail log
			'wml_entries',

			// WP Mail Logging
			'wpml_mails',

			// Broken Link Checkers
			'blc_linkdata',
			'blc_postdata',
			'blc_instances',
			'blc_links',
			'blc_synch',
			'blc_filters',

			// Stream
			'stream',
			'stream_meta',

			// Audit Trail
			'audit_trail',

			// WPcerber
			'cerber_traffic',
			'cerber_log',
			'_cerber_files',

			// ThirstyAffiliates
			'ta_link_clicks_meta',
			'ta_link_clicks',

			// GDPR Cookie Consent
			'cli_visitor_details',
			'cli_cookie_scan_url',

			// FacetWP
			'facetwp_cache',
			'facetwp_index',

			// Gravityforms
			'gf_entry',
			'gf_entry_meta',
			'gf_entry_notes',
			'gf_form_view',

			// FormidableForms
			'frm_items',
			'frm_item_metas',

			// CF7
			'cf7dbplugin_submits',

			// WPforms
			'wpforms_entries',
			'wpforms_entry_fields',

			// WP All Export / Import
			'pmxe_exports',
			'pmxe_google_cat',
			'pmxe_posts',
			'pmxe_templates',
			'pmxi_files',
			'pmxi_history',
			'pmxi_images',
			'pmxi_imports',
			'pmxi_posts',
			'pmxi_templates',

			// Cavalcade
			'cavalcade_jobs',
			'cavalcade_logs',

			// TA links
			'ta_link_clicks',
			'ta_link_clicks_meta',

			// Yoast
			'yoast_seo_links',
			'yoast_seo_meta',

			// Matomo
			'matomo_access',
			'matomo_archive_invalidations',
			'matomo_brute_force_log',
			'matomo_changes',
			'matomo_custom_dimensions',
			'matomo_goal',
			'matomo_locks',
			'matomo_log_action',
			'matomo_log_conversion',
			'matomo_log_conversion_item',
			'matomo_log_link_visit_action',
			'matomo_log_profiling',
			'matomo_log_visit',
			'matomo_logger_message',
			'matomo_option',
			'matomo_plugin_setting',
			'matomo_privacy_logdata_anonymizations',
			'matomo_report',
			'matomo_report_subscriptions',
			'matomo_segment',
			'matomo_sequence',
			'matomo_session',
			'matomo_site',
			'matomo_site_setting',
			'matomo_site_url',
			'matomo_tracking_failure',
			'matomo_twofactor_recovery_code',
			'matomo_user',
			'matomo_user_dashboard',
			'matomo_user_language',
			'matomo_user_token_auth',
			'matomo_tagmanager_container',
			'matomo_tagmanager_container_release',
			'matomo_tagmanager_container_version',
			'matomo_tagmanager_tag',
			'matomo_tagmanager_trigger',
			'matomo_tagmanager_variable',
			'matomo_archive_',

			// WP-Rocket
			'wpr_rocket_cache',

			// ActionScheduler
			'actionscheduler_logs',

			// WooCommerce
			'woocommerce_sessions',
		];

		public function __construct() {
			$this->environment = wp_get_environment_type();
		}

		/**
		 * Significantly reduce the size of the WordPress database by removing non-essential data, revisions, transients, orphaned data, and keeping only the 500 most recent contents for each content type.
		 *
		 * ## OPTIONS
		 *
		 * [--tables-to-truncate]
		 * : List of table names to truncate data separated with commas, this parameter is used to add new tables to be clean, not to redefine the default list.
		 *
		 * ## EXAMPLES
		 *
		 *     wp reduce-db
		 *     wp reduce-db --tables-to-truncate=postmeta,posts
		 *
		 * @synopsis [--tables-to-filter]
		 */
		public function __invoke( $positional_args, $assoc_args = [] ) {
			WP_CLI::log( sprintf( 'The current environment is: %s', $this->environment ) );

			// Get initial size
			$initial_db_size = $this->get_db_size();

			// Cleanup transients
			$this->remove_all_transients();

			// Cleanup revisions
			$this->remove_all_revisions();

			// Keep only fresh content
			$this->remove_oldest_content();

			// Cleanup orphans content
			$this->remove_orphans_content();

			// Yoast purge & reindex
			// $this->reindex_yoast_seo();

			// Remove data from specific tables
			$this->truncate_tables( $assoc_args );

			// Optimize all tables
			$this->db_optimize();

			// Get after size
			$after_db_size = $this->get_db_size();

			WP_CLI::success( sprintf( 'Yeah ! The size has changed from %s to %s (in MB)', $initial_db_size, $after_db_size ) );
		}

		private function get_db_size() {
			$options = [
				'return'       => true,
				// Return 'STDOUT'; use 'all' for full object.
				'launch'       => false,
				// Reuse the current process.
				'exit_error'   => true,
				// Halt script execution on error.
				'command_args' => [ '--skip-themes --skip-plugins' ],
				// Additional arguments to be passed to the $command.
			];

			return WP_CLI::runcommand( 'db size --skip-plugins --skip-themes --orderby=size --size_format=mb', $options );
		}

		private function remove_all_transients() {
			# Transients networks
			WP_CLI::log( 'Clean network transients' );
			$options = [
				'return'       => true,
				// Return 'STDOUT'; use 'all' for full object.
				'launch'       => false,
				// Reuse the current process.
				'exit_error'   => true,
				// Halt script execution on error.
				'command_args' => [ '--skip-themes --skip-plugins' ],
				// Additional arguments to be passed to the $command.
			];
			$output  = WP_CLI::runcommand( 'transient delete --network --all', $options );
			WP_CLI::log( $output );

			# Transients site
			foreach ( $this->get_sites() as $site ) {
				WP_CLI::log( sprintf( 'Clean transient for site %s', $site['name'] ) );
				$options = [
					'return'       => true,
					// Return 'STDOUT'; use 'all' for full object.
					'launch'       => false,
					// Reuse the current process.
					'exit_error'   => true,
					// Halt script execution on error.
					'command_args' => [ '--skip-themes --skip-plugins' ],
					// Additional arguments to be passed to the $command.
				];
				$output  = WP_CLI::runcommand( 'transient delete  --all --url=' . $site['url'], $options );
				WP_CLI::log( $output );
			}
		}

		/**
		 * Prefer SQL query instead WP-ClI command for performance issue...
		 *
		 * @return void
		 */
		private function remove_all_revisions() {
			global $wpdb;

			# Revisions
			foreach ( $this->get_sites() as $site ) {
				$row_deleted_qty = 0;
				WP_CLI::log( sprintf( 'Clean revision for site %s', $site['name'] ) );

				$this->switch_to_blog( $site['id'] );
				$row_deleted_qty = $wpdb->query( "DELETE FROM $wpdb->posts WHERE post_type = 'revision';" );
				$this->restore_current_blog();

				WP_CLI::log( sprintf( '%d revisions deleted', $row_deleted_qty ) );
			}
		}

		private function remove_oldest_content() {
			global $wpdb;

			# Keep only 500 recently items by CPT
			foreach ( $this->get_sites() as $site ) {
				$row_deleted_qty = 0;

				WP_CLI::log( sprintf( 'Removing superfluous contents for site %s', $site['name'] ) );

				$this->switch_to_blog( $site['id'] );

				$post_types = $wpdb->get_col( "SELECT post_type, count(ID) as counter FROM $wpdb->posts WHERE post_type != 'attachment' GROUP BY post_type HAVING count(ID) > 500" );

				foreach ( $post_types as $post_type ) {
					$row_deleted_qty += $wpdb->query(
						"
							DELETE FROM $wpdb->posts WHERE ID NOT IN (
								SELECT ID
								FROM (
									SELECT ID
									FROM $wpdb->posts
									WHERE post_type = '{$post_type}'
									ORDER BY post_date DESC
									LIMIT 500
								) AS recent_posts
							)
							AND post_type = '{$post_type}';
						"
					);
				}

				$this->restore_current_blog();

				WP_CLI::log( sprintf( '%d superfluous contents deleted', $row_deleted_qty ) );
			}
		}

		private function remove_orphans_content() {
			global $wpdb;

			# Orphelins
			foreach ( $this->get_sites() as $site ) {
				$row_deleted_qty = 0;

				WP_CLI::log( sprintf( 'Clean orphelins contents for site %s', $site['name'] ) );

				$this->switch_to_blog( $site['id'] );

				// post meta
				$row_deleted_qty += $wpdb->query( "DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON pm.post_id = wp.ID WHERE wp.ID IS NULL;" );

				// terms
				$row_deleted_qty += $wpdb->query( "DELETE t FROM $wpdb->terms AS t LEFT JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id WHERE tt.term_id IS NULL;" );

				// user meta
				$row_deleted_qty += $wpdb->query( "DELETE um FROM $wpdb->usermeta um LEFT JOIN $wpdb->users u ON um.user_id = u.ID WHERE u.ID IS NULL;" );

				$this->restore_current_blog();

				WP_CLI::log( sprintf( '%d orphelins contents deleted', $row_deleted_qty ) );
			}
		}

		private function reindex_yoast_seo() {
			# Yoast purge && reindex
			$options = [
				'return'       => true,                // Return 'STDOUT'; use 'all' for full object.
				'launch'       => false,               // Reuse the current process.
				'exit_error'   => false,                // Halt script execution on error.
				'command_args' => [ '--skip-themes' ], // Additional arguments to be passed to the $command.
			];

			$args = '';
			if ( is_multisite() ) {
				$args = ' --network';
			}

			$output = WP_CLI::runcommand( 'yoast index --reindex --skip-confirmation' . $args, $options );
		}

		private function truncate_tables( $assoc_args ) {
			global $wpdb;

			$database_name = $wpdb->dbname;

			/**
			 * Get the list of tables with no-data
			 */
			$this->tables_to_filter = $this->get_tables_to_filter( $assoc_args );

			/**
			 * Get all the tables form the database
			 */
			$table_names = WP_CLI\Utils\wp_get_table_names( [], [ 'all-tables' => true ] );

			/**
			 * Get the tables with no-data and normal tables
			 */
			$no_data_tables = array_filter( $table_names, [ $this, 'extract_no_data_tables' ] );

			$tables_truncate_qty = 0;
			foreach ( $no_data_tables as $table_name ) {
				$tables_truncate_qty += $wpdb->query( "TRUNCATE TABLE $table_name;" );
			}

			WP_CLI::log( sprintf( '%d tables truncated.', $tables_truncate_qty ) );
		}

		private function db_optimize() {
			# Optimize silently
			$options = [
				'return'       => true,
				// Return 'STDOUT'; use 'all' for full object.
				'launch'       => false,
				// Reuse the current process.
				'exit_error'   => true,
				// Halt script execution on error.
				'command_args' => [ '--skip-themes --skip-plugins' ],
				// Additional arguments to be passed to the $command.
			];
			$output  = WP_CLI::runcommand( 'db optimize', $options );
		}

		/**
		 * Get all the tables with the assoc_args
		 *
		 * @param $args
		 *
		 * @return array
		 *
		 */
		private function get_tables_to_filter( $args ) {
			if ( isset( $args['tables-to-filter'] ) ) {
				$this->tables_to_filter = array_merge( $this->tables_to_filter, explode( ',', $args['tables-to-filter'] ) );
			}

			return $this->tables_to_filter;
		}

		/**
		 * Extract the tables with no-data from the array
		 *
		 * @param $table
		 *
		 * @return bool
		 *
		 */
		private function extract_no_data_tables( $table ) {
			foreach ( $this->tables_to_filter as $filter ) {
				if ( false !== strpos( $table, $filter ) ) {
					return true;
				}
			}

			return false;
		}

		private function get_sites() {
			$output = [];

			if ( ! is_multisite() ) {
				$output[] = [
					'id'   => get_current_blog_id(),
					'name' => get_bloginfo( 'blogname' ),
					'url'  => get_home_url( '/' ),
				];

				return $output;
			}

			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );

				$output[] = [
					'id'   => $site->blog_id,
					'name' => get_bloginfo( 'blogname' ),
					'url'  => get_home_url( '/' ),
				];

				restore_current_blog();
			}

			return $output;
		}

		private function switch_to_blog( $id ) {
			if ( function_exists( 'switch_to_blog' ) ) {
				switch_to_blog( $id );
			}
		}

		private function restore_current_blog() {
			if ( function_exists( 'restore_current_blog' ) ) {
				restore_current_blog();
			}
		}
	}

	WP_CLI::add_command( 'reduce-db', 'WP_CLI_Reduce_DB' );
}
