<?php

    // retrieve the data for the desired page a user wants to access. it is here at the beginning because a successful login will start a new (maybe empty) session
    $get_stash = $_SESSION["plugin_MantisOIDC_get_param_stash"];

    require_once 'assets/lib/OpenID-Connect-PHP/vendor/autoload.php';
    use Jumbojett\OpenIDConnectClient;

    plugin_register('MantisOIDC');

    // we have to so this all over again to access the data in the token
    $OPENID_CLIENT_ID = plugin_config_get('openIDClientID' );

    $oidc = new OpenIDConnectClient(
        plugin_config_get('openIDAuthURL' ),
        $OPENID_CLIENT_ID,
        plugin_config_get('openIDClientSecret' )
    );

    $oidc->setRedirectUrl(substr(config_get('path'), 0, -1).plugin_page( 'redirect'));

    $oidc->authenticate();

    $access_token = $oidc->getAccessToken();

    $data = $oidc->introspectToken($access_token);

    $uid_claim = plugin_config_get('oidc_uid_claim');
    if ( !isset($data->{$uid_claim}) ) {
        layout_login_page_begin();
        echo '<div class="col-md-offset-3 col-md-6 col-sm-10 col-sm-offset-1">
                <div class="login-container">
                  <div class="space-12 hidden-480"></div>'.
                  layout_login_page_logo().
                 '<div class="space-24 hidden-480"></div>
                 <div class="alert alert-danger"><p>' . sprintf(plugin_lang_get( 'user_id_not_transmitted' ), string_html_specialchars( config_get_global( 'webmaster_email' ) ) ) . '</p></div>
               </div>
              </div>';

        layout_login_page_end();

        return false;
    }

    $oidc_uid = $data->{$uid_claim};

    $find_user_by = plugin_config_get('oidc_find_user_by');
    if ( $find_user_by == "email" ) {
        $user_id = user_get_id_by_email($data->email);
    } else {
        $user_id = user_get_id_by_name($data->username);
    }

    $login_success = true;

    # check for disabled account
    if( !user_is_enabled( $user_id ) ) {
        $login_success = false;
    }

    # max. failed login attempts achieved...
    if( !user_is_login_request_allowed( $user_id ) ) {
        $login_success = false;
    }

    # check for anonymous login
    if( user_is_anonymous( $user_id ) ) {
        $login_success = false;
    }

    // user is not able to access MantisBT. Show error message
    if(false === $login_success) {

        layout_login_page_begin();
        echo '<div class="col-md-offset-3 col-md-6 col-sm-10 col-sm-offset-1">
                <div class="login-container">
                  <div class="space-12 hidden-480"></div>'.
                  layout_login_page_logo().
                 '<div class="space-24 hidden-480"></div>    
                 <div class="alert alert-danger"><p>' . sprintf(plugin_lang_get( 'user_not_available' ), string_html_specialchars( config_get_global( 'webmaster_email' ) ) ) . '</p></div>             
               </div>
              </div>';

        layout_login_page_end();

        return false;

    }


    user_increment_login_count( $user_id );

    user_reset_failed_login_count_to_zero( $user_id );
    user_reset_lost_password_in_progress_count_to_zero( $user_id );

    # set the cookies
    auth_set_cookies( $user_id, false );
    auth_set_tokens( $user_id );

    // Obtain the redirect url
    // Example: state=view.php?id=2222
    $redirect_url = '../../..';

    // add get-params if the user was about to access a certain page
    if(!empty($get_stash) && isset($get_stash["return"])) {

        // some redirects (e.g. bug_report_page.php) are missing the leading slash
        // add it if missing
        if(substr($get_stash["return"], 0, 1) != '/') {
            $redirect_url .= "/";
        }

        $redirect_url .= $get_stash["return"];
    } else {
        $redirect_url .= "/index.php";
    }

    // remove forward-slash from the end (could happen when accessing manage overview page) remove it to prevent a broken URL
    // sanitize because Mantis sometimes provides a raw-encoded string (e.g. for manage_overview_page) that needs to be decoded before
    print_header_redirect( string_sanitize_url(rtrim($redirect_url, "/")) );
