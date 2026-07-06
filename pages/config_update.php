<?php

    form_security_validate( 'plugin_MantisOIDC_config_update' );
    auth_reauthenticate( );
    access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

    plugin_config_set('openIDAuthURL', strip_tags(gpc_get_string('oidaurl')));
    plugin_config_set('openIDClientID', strip_tags(gpc_get_string('oidclientid')));
    plugin_config_set('openIDClientSecret', strip_tags(gpc_get_string('oidsecret')));
    plugin_config_set('oidc_scopes', explode(" ", strip_tags(gpc_get_string('oidc_scopes'))));
    plugin_config_set('oidc_uid_claim', strip_tags(gpc_get_string('oidc_uid_claim')));
    plugin_config_set('oidc_find_user_by', strip_tags(gpc_get_string('oidc_find_user_by')));
    plugin_config_set('oidc_role', strip_tags(gpc_get_string('oidc_role')));

    plugin_config_set('login_button_text', strip_tags(gpc_get_string('login_button_text')));
    plugin_config_set('hide_credentials_login', strip_tags(gpc_get_string('hide_credentials_login', 'false')) == "hide_credentials_login" ? "true" : "false");
    plugin_config_set('auto_login', strip_tags(gpc_get_string('auto_login', 'false')) == "auto_login" ? "true" : "false");

    form_security_purge( 'plugin_MantisOIDC_config_update' );
    print_successful_redirect( plugin_page( 'config', true ) );
