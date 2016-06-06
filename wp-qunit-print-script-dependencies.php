<?php
/**
 * Plugin Name: QUnit Print Script Dependencies WP-CLI Command
 * Description: Print dependencies for scripts to be tested and then add into a QUnit HTML test runner file. Uses `wp_print_scripts()`.
 * Author: Weston Ruter, XWP
 * Author URI: https://make.xwp.co/
 * Plugin URI: https://github.com/xwp/wp-qunit-print-script-dependencies
 * Version: 0.1
 * License: GPLv2+
 *
 * Copyright (c) 2016 XWP (https://xwp.co/)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @package QUnit_Print_Script_Dependencies
 */

/**
 * Class QUnit_Print_Script_Dependencies_WP_CLI_Command
 */
class QUnit_Print_Script_Dependencies_WP_CLI_Command {

	/**
	 * Prints dependencies for the supplied script handles.
	 *
	 * ## OPTIONS
	 *
	 * <script_handles>...
	 * : The script handles to print dependencies for.
	 *
	 * [--base_href=<url>]
	 * : Overrides the base URL used for printed scripts. Useful to supply a relative path to ABSPATH from QUnit HTML runner.
	 *
	 * [--boot_customize_controls]
	 * : Whether Customizer controls should be booted. This will automatically append some actions to be done.
	 *
	 * [--do_actions=<actions>]
	 * : List of actions to do before printing scripts, useful to include additional data needed as fixtures.
	 *
	 * ## EXAMPLES
	 *
	 *     wp qunit-print-script-dependencies customize-controls --do_actions=customize_controls_enqueue_scripts
	 *     wp qunit-print-script-dependencies acme-widget acme-menu --base_href=../../../../../
	 *
	 * @todo Add a plugin param to help automate the generation of local paths.
	 * @todo The base_href should only apply to paths that are not inside the plugin; scripts in plugin should be locally-relative.
	 * @param array $script_handles Script handles.
	 * @param array $assoc_args     Associative args.
	 */
	public function __invoke( $script_handles, $assoc_args ) {
		global $wp_customize;

		$wp_scripts = wp_scripts();
		foreach ( $script_handles as $script_handle ) {
			if ( ! $wp_scripts->query( $script_handle, 'registered' ) ) {
				WP_CLI::error( "Script handle not registered: $script_handle" );
			}
		}

		/**
		 * Replace the base URL for script sources.
		 *
		 * @param string $script_tag Script tag.
		 * @return string Rewritten script tag.
		 */
		$rewrite_script_loader_tag_base_href = function ( $script_tag ) use ( $assoc_args ) {
			return preg_replace(
				'#(?<=src=.)(https?:)//[^/]+/#',
				trailingslashit( $assoc_args['base_href'] ),
				$script_tag
			);
		};

		$actions = array();
		if ( isset( $assoc_args['boot_customize_controls'] ) ) {
			require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
			$wp_customize = new \WP_Customize_Manager(); // WPCS: Allow override.
			do_action( 'customize_register', $wp_customize );
			$actions[] = 'customize_controls_init';
			$actions[] = 'customize_controls_enqueue_scripts';
			$actions[] = 'customize_controls_print_scripts';
			$actions[] = 'customize_controls_print_footer_scripts';
		}
		if ( ! empty( $assoc_args['do_actions'] ) ) {
			$actions = explode( ',', $assoc_args['do_actions'] );
		}
		if ( ! empty( $actions ) ) {
			ob_start();
			foreach ( $actions as $action ) {
				do_action( $action );
			}
			$output = ob_get_clean();
			$output = preg_replace( '#<link.*?>#', '', $output );
			echo $output; // WPCS: XSS OK.
		}

		if ( ! empty( $assoc_args['base_href'] ) ) {
			add_filter( 'script_loader_tag', $rewrite_script_loader_tag_base_href );
		}
		echo "<div hidden>\n";
		$dependencies = wp_print_scripts( $script_handles );
		echo "</div>\n";
		if ( ! empty( $assoc_args['base_href'] ) ) {
			remove_filter( 'script_loader_tag', $rewrite_script_loader_tag_base_href );
		}

		// @todo If customize-controls was printed, also print mock data for _wpCustomizeSettings.
		if ( 0 === count( $dependencies ) ) {
			WP_CLI::error( 'No dependencies printed. A script may have a dependency that is not registered.' );
		} else {
			WP_CLI::debug( sprintf( 'Printed %d script(s): %s', count( $dependencies ), join( ', ', $dependencies ) ) );
		}
	}
}

if ( defined( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'qunit-print-script-dependencies', new QUnit_Print_Script_Dependencies_WP_CLI_Command() );
}
