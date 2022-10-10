<?php
/**
 * Plugin Name: WooCommerce Blocks Incompatibility Check
 * Description: Checks for incompatibilities with WooCommerce Blocks.
 * Version: 1.0.0
 * Author: Automattic
 * Author URI: https://automattic.com/
 * Text Domain: woocommerce-blocks-incompatibility-check
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce\Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce Blocks Incompatibility Check plugin class
 *
 * @class WC_Blocks_Incompatibility_Check
 */
class WC_Blocks_Incompatibility_Check {

	/**
	 * Initialise the plugin.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_submenu_page' ), 80 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'settings_link' ) );
	}

	/**
	 * Check if WooCommerce Blocks is active.
	 *
	 * @return bool Return true if WooCommerce Blocks is active, false otherwise.
	 */
	private static function is_woocommerce_blocks_active() {
		return class_exists( 'Automattic\WooCommerce\Blocks\Package' );
	}

	/**
	 * Add settings link on plugin page
	 *
	 * @param array $links The original array of plugin action links.
	 * @return array The updated array of plugin action links.
	 */
	public static function settings_link( array $links ) {
		if ( ! self::is_woocommerce_blocks_active() ) {
			return;
		}

		$admin_url     = admin_url( 'tools.php?page=incompatibility-check' );
		$settings_link = sprintf( '<a href="%s">%s</a>', $admin_url, __( 'Settings', 'woocommerce-blocks-incompatibility-check' ) );
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Get all extensions.
	 *
	 * @return array All extensions.
	 */
	private static function get_all_extensions():array {
		return get_plugins();
	}

	/**
	 * Get active extensions.
	 *
	 * @return array Active extensions, if available, empty array otherwise.
	 */
	private static function get_active_extensions():array {
		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$result         = array();

		foreach ( $all_plugins as $key => $value ) {
			if ( in_array( $key, $active_plugins, true ) ) {
				$path                         = explode( '/', $key );
				$result[ $key ]               = $value;
				$result[ $key ]['PluginPath'] = $path[0];
			}
		}

		return $result;
	}

	/**
	 * Get relevant extensions.
	 *
	 * @return array Relevant extensions, if available, empty array otherwise.
	 */
	private static function get_relevant_extensions():array {
		$active_plugins = self::get_active_extensions();

		$excluded_plugins = array(
			'woocommerce/woocommerce.php',
			'woocommerce-blocks/woocommerce-gutenberg-products-block.php',
			'woocommerce-blocks-incompatibility-check/woocommerce-blocks-incompatibility-check.php',
			'woocommerce-blocks-incompatibility/woocommerce-blocks-incompatibility.php',
		);

		foreach ( $excluded_plugins as $key ) {
			unset( $active_plugins[ $key ] );
		}

		return $active_plugins;
	}

	/**
	 * Get all payment gateways.
	 *
	 * @return array All payment gateways, if available, empty array otherwise.
	 */
	private static function get_all_payment_gateways():array {
		return WC()->payment_gateways->payment_gateways() ?? array();
	}

	/**
	 * Get all active payment gateways.
	 *
	 * @return array All active payment gateways, if available, empty array otherwise.
	 */
	private static function get_active_payment_gateways():array {
		$all_payment_gateways = self::get_all_payment_gateways();

		if ( empty( $all_payment_gateways ) ) {
			return array();
		}

		return array_filter(
			$all_payment_gateways,
			function( $gateway ) {
				return 'yes' === $gateway->enabled;
			}
		);
	}

	/**
	 * Register the submenu page.
	 *
	 * @return void
	 */
	public static function register_submenu_page() {
		if ( ! self::is_woocommerce_blocks_active() ) {
			return;
		}

		add_submenu_page(
			'tools.php',
			'Extension Check',
			'Extension Check',
			'manage_options',
			'incompatibility-check',
			array( __CLASS__, 'submenu_page_callback' ),
		);
	}

	/**
	 * Submenu page callback.
	 *
	 * @return void
	 */
	public static function submenu_page_callback() {
		$relevant_plugins        = self::get_relevant_extensions();
		$all_payment_gateways    = self::get_all_payment_gateways();
		$active_payment_gateways = self::get_active_payment_gateways();

		foreach ( $all_payment_gateways as $key => $value ) {
			print '<pre>';
			print_r( $key );
			print '</pre>';
		}

		?>
		<div class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h2>WooCommerce Blocks Extension Check</h2>
			<p><?php _e( 'Check if an extension is compatible with WooCommerce Blocks.', 'woocommerce-blocks-incompatibility-check' ); ?></p>
			<form method="post" action="#">
				<select name="plugin_path" id="plugin_path">
				<?php foreach ( $relevant_plugins as $value ) : ?>
					<?php $selected = isset( $_POST['plugin_path'] ) && $_POST['plugin_path'] === $value['PluginPath'] ? 'selected' : ''; ?>
					<?php var_dump( $selected ); ?>
					<option value="<?php echo $value['PluginPath']; ?>" <?php echo $selected; ?>><?php echo $value['Name']; ?></option>
				<?php endforeach; ?>
				</select>
				<?php submit_button( __( 'Check extension', 'woocommerce-blocks-incompatibility-check' ), 'button', 'submit', false ); ?>
				<label for="payment_gateway">
					<input type="checkbox" name="payment_gateway" id="payment_gateway" value="true" checked>
					<?php _e( 'This extension is a payment gatway.', 'woocommerce-blocks-incompatibility-check' ); ?>
				</label>
			</form>
		</div>
		<?php

		if ( isset( $_POST['plugin_path'] ) && isset( $_POST['payment_gateway'] ) ) {
			$string = 'registerPaymentMethod';
			$path   = WP_PLUGIN_DIR . '/' . $_POST['plugin_path'];
			$result = shell_exec( 'grep -Ri "' . $string . '" ' . $path );

			if ( $result ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . __( 'This extension is compatible with WooCommerce Blocks.', 'woocommerce-blocks-incompatibility-check' ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . __( 'This extension is not compatible with WooCommerce Blocks.', 'woocommerce-blocks-incompatibility-check' ) . '</p></div>';
			}
		}
	}
}

WC_Blocks_Incompatibility_Check::init();
