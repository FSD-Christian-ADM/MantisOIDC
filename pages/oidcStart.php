<?php

    require_once 'assets/lib/OpenID-Connect-PHP/vendor/autoload.php';
    use Jumbojett\OpenIDConnectClient;

    plugin_register('MantisOIDC');

    $oidc = new OpenIDConnectClient(
        plugin_config_get('openIDAuthURL' ),
        plugin_config_get('openIDClientID' ),
        plugin_config_get('openIDClientSecret' )
    );

    $oidc->setRedirectUrl(substr(config_get('path'), 0, -1).plugin_page( 'redirect'));

    $oidc->addScope(plugin_config_get('oidc_scopes'));

    $oidc->authenticate();
