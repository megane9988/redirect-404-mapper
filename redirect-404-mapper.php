<?php
/**
 * Plugin Name: Redirect 404 Mapper
 * Description: Map 404 accessed URLs and redirect to specified destinations using JavaScript.
 * Version: 1.0.0
 * Author: mgn
 * License: GPL-2.0-or-later
 * Text Domain: redirect-404-mapper
 * Domain Path: /languages
 *
 * @package redirect-404-mapper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redirect_404_Mapper' ) ) {
	class Redirect_404_Mapper {
		const OPTION_KEY = 'redirect_404_mapper_rules';

		/**
		 * Constructor.
		 *
		 * Initialize plugin hooks and filters.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'wp_head', array( $this, 'print_redirect_script' ), 1 );
		}

		/**
		 * Add settings page to admin menu.
		 *
		 * @return void
		 */
		public function add_settings_page() {
			add_options_page(
				__( '404 Redirect Mapper', 'redirect-404-mapper' ),
				__( '404 Redirect Mapper', 'redirect-404-mapper' ),
				'manage_options',
				'redirect-404-mapper',
				array( $this, 'render_settings_page' )
			);
		}

		/**
		 * Register settings and sanitization.
		 *
		 * @return void
		 */
		public function register_settings() {
			register_setting(
				'redirect_404_mapper_group',
				self::OPTION_KEY,
				array(
					'type'              => 'array',
					'sanitize_callback' => array( $this, 'sanitize_rules' ),
					'default'           => array(),
					'show_in_rest'      => false,
				)
			);
		}

		/**
		 * Sanitize rules.
		 *
		 * @param mixed $value Raw value.
		 * @return array
		 */
		public function sanitize_rules( $value ) {
			$sanitized_rules = array();

			if ( ! is_array( $value ) ) {
				return $sanitized_rules;
			}

			foreach ( $value as $rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}

				$from = isset( $rule['from'] ) ? sanitize_text_field( wp_unslash( $rule['from'] ) ) : '';
				$to   = isset( $rule['to'] ) ? esc_url_raw( wp_unslash( $rule['to'] ) ) : '';

				if ( '' === $from || '' === $to ) {
					continue;
				}

				$normalized_from = $this->normalize_request_key( $from );

				if ( '' === $normalized_from ) {
					continue;
				}

				$sanitized_rules[] = array(
					'from' => $normalized_from,
					'to'   => $to,
				);
			}

			return $sanitized_rules;
		}

		/**
		 * Render the settings page in admin.
		 *
		 * @return void
		 */
		public function render_settings_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Insufficient permissions to access this page.', 'redirect-404-mapper' ) );
			}

			$rules = get_option( self::OPTION_KEY, array() );
			if ( ! is_array( $rules ) ) {
				$rules = array();
			}

			if ( array() === $rules ) {
				$rules[] = array(
					'from' => '',
					'to'   => '',
				);
			}
			?>
			<div class="wrap">
				<h1><?php echo esc_html__( '404 Redirect Mapper', 'redirect-404-mapper' ); ?></h1>
			<p><?php echo esc_html__( 'Register 404 accessed URLs and their redirect destinations.', 'redirect-404-mapper' ); ?></p>
				<form method="post" action="options.php">
					<?php settings_fields( 'redirect_404_mapper_group' ); ?>
					<table class="widefat striped" id="redirect-404-mapper-table">
						<thead>
							<tr>
								<th><?php echo esc_html__( '404 URL (from)', 'redirect-404-mapper' ); ?></th>
								<th><?php echo esc_html__( 'Redirect URL (to)', 'redirect-404-mapper' ); ?></th>
								<th><?php echo esc_html__( 'Action', 'redirect-404-mapper' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rules as $index => $rule ) : ?>
								<tr>
									<td>
										<input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[<?php echo esc_attr( (string) $index ); ?>][from]" value="<?php echo esc_attr( $rule['from'] ); ?>" placeholder="/old-page/" />
									</td>
									<td>
										<input type="url" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[<?php echo esc_attr( (string) $index ); ?>][to]" value="<?php echo esc_attr( $rule['to'] ); ?>" placeholder="https://example.com/new-page/" />
									</td>
									<td>
										<button type="button" class="button remove-rule"><?php echo esc_html__( 'Remove', 'redirect-404-mapper' ); ?></button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<p>
						<button type="button" class="button" id="add-redirect-rule"><?php echo esc_html__( 'Add Rule', 'redirect-404-mapper' ); ?></button>
					</p>
					<?php submit_button(); ?>
				</form>
			</div>
			<script>
			/**
			 * Admin panel script for managing redirect rules.
			 *
			 * Handles adding new redirect rule rows and removing existing rules
			 * from the settings table.
			 *
			 * @package redirect-404-mapper
			 */
			(function() {
				const tableBody = document.querySelector('#redirect-404-mapper-table tbody');
				const addButton = document.querySelector('#add-redirect-rule');
				if (!tableBody || !addButton) {
					return;
				}

				const optionKey = <?php echo wp_json_encode( self::OPTION_KEY ); ?>;

				/**
				 * Create a new row element for the redirect rules table.
				 *
				 * @param {number} index The index for the new rule row.
				 * @return {HTMLTableRowElement} The new table row.
				 */
				const createRow = function(index) {
					const tr = document.createElement('tr');
					tr.innerHTML = '' +
						'<td><input type="text" class="regular-text" name="' + optionKey + '[' + index + '][from]" placeholder="/old-page/" /></td>' +
						'<td><input type="url" class="regular-text" name="' + optionKey + '[' + index + '][to]" placeholder="https://example.com/new-page/" /></td>' +
						'<td><button type="button" class="button remove-rule"><?php echo esc_js( __( 'Remove', 'redirect-404-mapper' ) ); ?></button></td>';
					return tr;
				};

				addButton.addEventListener('click', function() {
					const index = tableBody.querySelectorAll('tr').length;
					tableBody.appendChild(createRow(index));
				});

				tableBody.addEventListener('click', function(event) {
					if (!event.target.classList.contains('remove-rule')) {
						return;
					}
					event.target.closest('tr').remove();
				});
			}());
			</script>
			<?php
		}

		/**
		 * Print JavaScript redirect script on 404 pages.
		 *
		 * Outputs a script tag in the head that checks the current URL
		 * against registered redirect rules and performs client-side redirects.
		 *
		 * @return void
		 */
		public function print_redirect_script() {
			if ( ! is_404() ) {
				return;
			}

			$rules = get_option( self::OPTION_KEY, array() );
			if ( ! is_array( $rules ) || array() === $rules ) {
				return;
			}
			?>
			<script>
			/**
			 * Frontend script for 404 page redirects.
			 *
			 * Compares the current URL against registered redirect rules
			 * and performs client-side redirects using window.location.replace.
			 *
			 * @package redirect-404-mapper
			 */
			(function() {
				const rules = <?php echo wp_json_encode( $rules ); ?>;
				const currentUrl = window.location.pathname + window.location.search;

				for ( let i = 0; i < rules.length; i++ ) {
					if ( rules[i].from === currentUrl ) {
						window.location.replace( rules[i].to );
						return;
					}
				}
			}());
			</script>
			<?php
		}

		/**
		 * Normalize URL path and query string.
		 *
		 * Processes the raw path to ensure consistency in matching.
		 * Adds leading slash if missing and extracts query strings.
		 *
		 * @param string $raw_path Raw path or URL to normalize.
		 * @return string Normalized path with leading slash and optional query string.
		 */
		private function normalize_request_key( $raw_path ) {
			$raw_path = trim( (string) $raw_path );
			if ( '' === $raw_path ) {
				return '';
			}

			$decoded_path  = wp_parse_url( $raw_path, PHP_URL_PATH );
			$decoded_query = wp_parse_url( $raw_path, PHP_URL_QUERY );
			if ( null === $decoded_path ) {
				$decoded_path = '';
			}

			if ( '' === $decoded_path ) {
				$decoded_path = '/';
			}

			if ( '/' !== substr( $decoded_path, 0, 1 ) ) {
				$decoded_path = '/' . $decoded_path;
			}

			$normalized_key = $decoded_path;
			if ( '' !== (string) $decoded_query ) {
				$normalized_key .= '?' . (string) $decoded_query;
			}

			return $normalized_key;
		}
	}

	new Redirect_404_Mapper();
}
