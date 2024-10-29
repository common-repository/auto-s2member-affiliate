<?php
/*
Plugin Name: Auto s2Member Affiliate
Plugin URI: https://authorhelp.uk/wordpress-plugin-auto-s2member-affiliate/
Description: Automatically creates a <a href='https://wordpress.org/plugins/affiliates-manager/'>WP Affiliate Manager</a> affiliate account for users when they register using the <a href='https://wordpress.org/plugins/s2member/'>s2Member framework</a>
Version: 1.3
Author: Robin Phillips (Author Help)
Author URI: https://www.authorhelp.uk/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Auto s2Member Affiliate is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Auto s2Member Affiliate is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Auto s2Member Affiliate. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// Add Settings link to plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'autos2aff_pluginpagelinks');
function autos2aff_pluginpagelinks ($links) {
	// Add a "Settings" link
	$links [] = '<a href="'. get_admin_url(null, 'options-general.php?page=autos2aff_config') .'">' . esc_html__('Settings', 'auto_s2member_affiliate') . '</a>';
	return $links;
}

// Create settings page
add_action('admin_menu', 'autos2aff_admin');
function autos2aff_admin() {
	add_submenu_page ('options-general.php', 'Auto s2Member Affiliate', 'Auto s2Member Affiliate', 'manage_options', 'autos2aff_config', 'autos2aff_config_page');
}

// Add init action: create an affiliate
add_action('init', 'autos2aff_create_affiliate');

/*
Add a value to the affiliate data array
$data: array to be populated
$key: array key
$val: value to add
$required: True if value is required
Returns 0 if value created successfully, 1 if there was an error
*/
function autos2aff_add_to_array (&$data, $key, $val, $required = False) {
	// Check the required plugins are installed and activated
	if (autos2aff_check_plugins () != '')
		return 1;

	if($required === True && (string) $val == '') {
		WPAM_Logger::log_debug(__('Auto s2Member Affiliate - Error, ', 'auto_s2member_affiliate') . $key . __(' is missing. Cannot create affiliate record!', 'auto_s2member_affiliate'), 4);
		return 1;
	}
	else {
		$data[$key] = $val;
		return 0;
	}
}

/*
Create an affiliate user
*/
function autos2aff_create_affiliate() {
	// Check the required plugins are installed and activated
	if (autos2aff_check_plugins () != '')
		return;

	// Need database access to check if affiliate already exists
	$db = new WPAM_Data_DataAccess();

	// Only continue if GET parameters are set
	if (isset($_GET['auto-s2member-affiliate']) && intval($_GET['auto-s2member-affiliate']) > 0) {
		// Do not continue if email already exists
		if ($db->getAffiliateRepository()->existsBy(array('email' => $_GET['uem']))) {
			WPAM_Logger::log_debug(__('Auto s2Member Affiliate - affiliate email ', 'auto_s2member_affiliate') . $_GET['uem'] . __(' is already registered', 'auto_s2member_affiliate'));
		}
		else {
			WPAM_Logger::log_debug(__('Auto s2Member Affiliate - creating affiliate record', 'auto_s2member_affiliate'));

			// Initialise error count
			$err = 0;
			// Create empty array to store affiliate data
			$fields = array();

			// Validate the user ID is an integer
			if (filter_var($_GET['uid'], FILTER_VALIDATE_INT)) {
				// Convert the user ID to an integer before saving. Should not be required, but better safe than sorry :)
				$err += autos2aff_add_to_array ($fields, 'userId', intval ($_GET['uid']), True);
			}
			else {
				WPAM_Logger::log_debug(__('Auto s2Member Affiliate - Error, invalid user ID (', 'auto_s2member_affiliate') .
					$_GET['uid'] .
					__('). Cannot create affiliate record!', 'auto_s2member_affiliate'), 4);
				$err++;
			}

			// Add the user's name
			$err += autos2aff_add_to_array ($fields, 'firstName', $_GET['ufn']);
			$err += autos2aff_add_to_array ($fields, 'lastName', $_GET['uln']);

			// Validate the email
			if (filter_var($_GET['uem'], FILTER_VALIDATE_EMAIL)) {
				// Email was validated - add it to the $fields array
				$err += autos2aff_add_to_array ($fields, 'email', $_GET['uem'], True);
				// Set the PayPal email to be the same email. They can change it later if need be
				$err += autos2aff_add_to_array ($fields, 'paypalEmail', $_GET['uem'], True);
			}
			else {
				// Email was not validated. Log an error
				WPAM_Logger::log_debug(__('Auto s2Member Affiliate - Error, invalid email (', 'auto_s2member_affiliate') . $_GET['uem'] . __('). Cannot create affiliate record!', 'auto_s2member_affiliate'), 4);
				$err++;
			}

			if ($err > 0) {
				// Errors were encountered. Add a log entry
				WPAM_Logger::log_debug(__('Auto s2Member Affiliate - Affiliate record not created. ', 'auto_s2member_affiliate') . $err . __(' . errors encountered', 'auto_s2member_affiliate'), 4);
			}
			else {
				// Create the affiliate user
				$userhandler = new WPAM_Util_UserHandler();
				$userhandler->create_wpam_affiliate_record($fields);
				WPAM_Logger::log_debug(__('Auto s2Member Affiliate - affiliate record creation complete.', 'auto_s2member_affiliate'));
			}
		}
	}
}

/*
Create the config page
*/
function autos2aff_config_page() {
	// Check that the user has the required capability
	if (!current_user_can('manage_options'))
		wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'auto_s2member_affiliate'));

	// Display the settings screen
	echo '<div class="wrap">';
	echo '<h2>' . esc_html__('Auto s2Member Affiliate', 'auto_s2member_affiliate') . '</h2>';

	// Check the required plugins are installed
	if (autos2aff_check_plugins () != '')
		echo autos2aff_check_plugins();

	// Display the URL to add to s2Member settings
	echo '<p>' . esc_html__('Enter the following URL in the s2Member settings:', 'auto_s2member_affiliate') . '</p>';
	echo '<p><input style="width:99%;" value="' .
		htmlentities (home_url() . '?auto-s2member-affiliate=1&uid=%%user_id%%&ufn=%%user_first_name%%&uln=%%user_last_name%%&uem=%%user_email%%', ENT_QUOTES) .
		'"></p>';
	echo '<p>' . esc_html__('To have ', 'auto_s2member_affiliate') .
		'<strong>' . esc_html__('all', 'auto_s2member_affiliate') . '</strong>' .
		esc_html__(' new members added to the affiliates, add it under s2Member -> API/Notifications -> Registration Notifications.', 'auto_s2member_affiliate') . '</p>';
	echo '<p>' . esc_html__('To only have ', 'auto_s2member_affiliate') .
		'<strong>' . esc_html__('new', 'auto_s2member_affiliate') . '</strong>' .
		esc_html__(' paid members added to the affiliates, add it under s2Member -> API/Notifications -> Signup Notifications.', 'auto_s2member_affiliate') . '</p>';
	echo '<p>' . esc_html__('More details about which members will be added are given on the s2Member settings page.', 'auto_s2member_affiliate') . '</p>';
	echo '</div>';
}

/*
Check required plugins exist.
Returns an error message if any plugins are missing, or blank string if all required plugins are installed and activated
*/
function autos2aff_check_plugins () {
	// Initialise return message and error messages
	$msg = '';
	$err1 = __('The s2Member plugin is not installed or not activated.');
	$err2 = __('The WordPress Affiliate Manager plugin is not installed or not activated.');

	// Go through plugins
	$plugins = get_option('active_plugins');
	foreach ($plugins as $plugin) {
		// If the plugin exists, set the associated error message to empty string
		if (strtolower(substr ($plugin, 0, 9)) == 's2member/')
			$err1 = '';
		if (strtolower(substr ($plugin, 0, 19)) == 'affiliates-manager/')
			$err2 = '';
	}

	// Return error message or blank string
	if ($err1 . $err2 != '') {
		$msg = '<p style="font-weight:bold;color:red;">' . $err1 . '<br />' . $err2 . '</p>';
		error_log ('Auto s2Member Affiliate error: ' . $err1 . ' ' . $err2);
		return $msg;
	}
}
