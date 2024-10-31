<?php
/**
* Plugin Name: SEOBrrr
* Plugin URI: https://seobrrr.com
* Description: This plugin allows you to fetch programmatic SEO campaigns and posts from your SEOBrrr.com account.
* Version: 1.2
* Author: ZeroToDigital
* Author URI: https://zerotodigital.com/
**/

require_once( plugin_dir_path( __FILE__ ) . '/functions.php' );

/**
* Create settings link on installed plugins page
**/

function brrr_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=seobrrr' ) . '">' . __('Settings') . '</a>';
	return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'brrr_settings_link');

/**
* Add settings page to settings menu
**/

function brrr_settings_page() {
    add_options_page( 'SEOBrrr API Settings', 'SEOBrrr', 'manage_options', 'seobrrr', 'brrr_render_settings_page' );
}
add_action( 'admin_menu', 'brrr_settings_page' );

/**
* Render settings page
**/

function brrr_render_settings_page() {
  ?>
  <h2>SEOBrrr API Settings</h2>
  <p>Manage the API key for your SEOBrrr account. For complete management of your programmatic SEO campaigns, please visit your account page at <a href='https://seobrrr.com/dashboard'>seobrr.com</a>.</p>
  <form action="options.php" method="post">
    <?php 
	    settings_fields( 'seobrrr_plugin_options' );
	    do_settings_sections( 'seobrrr_plugin' );
    ?>
    <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
  </form>
  <?php
}

/**
* Register form settings, sections and fields
**/

function brrr_register_settings() {
  register_setting( 'seobrrr_plugin_options', 'seobrrr_plugin_options' );

  // Create section but nullify heading and text since they're unnecessary
  add_settings_section( 'api_settings', null, null, 'seobrrr_plugin' );

  // API key field
  add_settings_field( 'seobrrr_plugin_setting_api_key', 'API Key', 'seobrrr_plugin_setting_api_key', 'seobrrr_plugin', 'api_settings' );
}
add_action( 'admin_init', 'brrr_register_settings' );

/**
* API key input element
**/

function seobrrr_plugin_setting_api_key() {
  $options = get_option( 'seobrrr_plugin_options' );
  echo "<input id='seobrrr_plugin_setting_api_key' name='seobrrr_plugin_options[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' />";
}
