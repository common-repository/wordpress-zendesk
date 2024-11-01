<?php
	include '../../../wp-blog-header.php'; // Yes I know this is dumb but for now this is how it works. Better fix in place version 1.1 :)
	if ( is_user_logged_in() ) {
		global $current_user;
		get_currentuserinfo();
		if ($current_user->user_firstname != '' && $current_user->user_lastname != '') {
			$sFullName = $current_user->user_firstname.' '.$current_user->user_lastname; 
		} else { $sFullName = $current_user->display_name; }
		$sEmail = $current_user->user_email;
                if (get_option('zendesk_external_id')==1) {
                    $sExternalID = $current_user->ID;
                } else {
                    $sExternalID = '';
                }
		$sOrganization = ''; 
		$sToken = get_option('zendesk_auth_token');
		$sUrlPrefix = get_option('zendesk_url_prefix');
		$custom_url = get_option('zendesk_custom_url');
		$https = get_option('zendesk_https');

		$sTimestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : time(); 
		$sMessage = $sFullName.$sEmail.$sExternalID.$sOrganization.$sToken.$sTimestamp; 
		$sHash = MD5($sMessage); 
                if ($https == 1) { $sso_url = 'https://'; } else { $sso_url = 'http://'; }
                if ($custom_url == '') { $sso_url .= $sUrlPrefix.'.zendesk.com'; } else { $sso_url .= $custom_url; }
		$sso_url .= "/access/remote/?name=".$sFullName."&email=".$sEmail."&external_id=".$sExternalID."&organization=".$sOrganization."&timestamp=".$sTimestamp."&hash=".$sHash;

		header("Location: ".$sso_url);
	} else { header('Location: '.wp_login_url( plugins_url('/zendesk-redirect.php', __FILE__) ) ); }

?>