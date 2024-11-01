<?php
/*
Plugin Name: Wordpress Zendesk
Plugin URI: http://www.patrickgarman.com/wordpress-plugins/wordpress-zendesk/
Description: Allows you to setup single sign on capabilities between your site and Zendesk. Create's user accounts on the fly, automatically logs in users.
Version: 1.0.4
Author: Patrick Garman
Author URI: http://www.patrickgarman.com/
License: GPLv2
*/

/*  Copyright 2011  Patrick Garman  (email : patrickmgarman@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

register_activation_hook(__FILE__, 'zendesk_activation');
register_deactivation_hook(__FILE__, 'zendesk_deactivation');

    if (is_admin()) {
	add_action('admin_menu', 'zendesk_admin_menu');
        add_action( 'admin_init', 'zendesk_register_settings' );
    }

function zendesk_admin_menu() {
	add_options_page('Zendesk', 'Zendesk', 'administrator', __FILE__, 'zendesk_options_page');
}

function zendesk_options_page() {
	echo'
	<div class="wrap">
		<h2>Zendesk Options</h2>
                <p>To setup your Zendesk Remote Authentication you need to login to your Zendesk site and then go to Account -> Security. Make sure the Remote Authentication box is CHECKED and use the values below to fill out the form. If you do not know what to put in the IP range box just put *.*.*.*</p>
		<form method="post" action="options.php">';
			wp_nonce_field('update-options');
			 echo '
			<table class="form-table">
				<tr valign="top">';
				$settings = zendesk_settings_list();
				foreach ($settings as $setting) {
					echo '<th scope="row">'.$setting['display'].'</th>
					<td>';
                                        if ($setting['type']=='radio') {
                                            echo 'Yes <input type="'.$setting['type'].'" name="'.$setting['name'].'" value="1" ';
                                            if (get_option($setting['name'])==1) { echo 'checked="checked" />'; } else { echo ' />'; }
                                            echo 'No <input type="'.$setting['type'].'" name="'.$setting['name'].'" value="0" ';
                                            if (get_option($setting['name'])==0) { echo 'checked="checked" />'; } else { echo ' />'; }
                                        } else { echo '<input type="'.$setting['type'].'" name="'.$setting['name'].'" value="'.get_option($setting['name']).'" />'; }
                                        echo ' (<em>'.$setting['hint'].'</em>)</td></tr>';
				}
			echo '
                                <tr><th scope="row" colspan="2">
                                    <h3>Values for your Zendesk</h3>
                                    <p>If you update the logout redirect URL be sure to update your Zendesk logout URL too!!</p>
                                </th></td>
                                <tr><th scope="row">Remote Login URL</th><td>'.WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'zendesk-redirect.php</td></tr>
                                <tr><th scope="row">Remote Logout URL</th><td>'.wp_logout_url( get_option('zendesk_logout_url') ).'</td></tr>
			</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="';
			foreach ($settings as $setting) {
				echo $setting['name'].',';
			}
			echo '" /><p class="submit"><input type="submit" class="button-primary" value="Save Changes" /></p>
		</form>';
	echo '</div>';
}

function zendesk_settings_list() {
	$settings = array(
		array(
			'display' => 'Zendesk Custom URL',
			'name' => 'zendesk_custom_url',
			'value' => '',
			'type' => 'textbox',
                        'hint' => 'if you have a custom URL set -- ex. support.example.com, otherwise leave it blank'
		),
                array(
			'display' => 'Zendesk URL Prefix',
			'name' => 'zendesk_url_prefix',
			'value' => '',
			'type' => 'textbox',
                        'hint' => 'if your Zendesk URL is example.zendesk.com then use example'
		),
                array(
			'display' => 'Authentication Token',
			'name' => 'zendesk_auth_token',
			'value' => '',
			'type' => 'textbox',
                        'hint' => 'get this from your Account -> Security page within Zendesk'
		),
                array(
			'display' => 'Redirect on Logout',
			'name' => 'zendesk_logout_url',
			'value' => '',
			'type' => 'textbox',
                        'hint' => 'where do you want to redirect users on logout? -- include the http://'
		),
                array(
			'display' => 'Use HTTPS?',
			'name' => 'zendesk_https',
			'value' => 0,
			'type' => 'radio',
                        'hint' => 'if checked you will redirect to your SSL secured Zendesk'
		),
                array(
			'display' => 'Send External ID',
			'name' => 'zendesk_external_id',
			'value' => 0,
			'type' => 'radio',
                        'hint' => 'if checked this will send the Wordpress users ID as their External ID'
		),
	);
	return $settings;
}


function zendesk_register_settings() {
	$settings = zendesk_settings_list();
	foreach ($settings as $setting) {
		register_setting($setting['name'], $setting['value']);
	}
}

function zendesk_activation() {
	$settings = zendesk_settings_list();
	foreach ($settings as $setting) {
		add_option($setting['name'], $setting['value']);
	}
}

function zendesk_deactivation() {
	$settings = zendesk_settings_list();
	foreach ($settings as $setting) {
		delete_option($setting['name']);
	}
}

add_filter( 'page_template', 'zendesk_redirect_template' );
function zendesk_redirect_template($template) {
	//$templates = array('zendesk-redirect.php');
	//$template = locate_plugin_template($templates);
	return $template;
}

function zendesk_settings_link($links) {
$settings_link = '<a href="options-general.php?page=wordpress-zendesk/wp-zendesk.php">Settings</a>';
array_unshift($links, $settings_link);
return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'zendesk_settings_link' );