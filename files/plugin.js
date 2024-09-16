$(document).ready(function () {

    var oidcStart = $("meta[name='oidcStart']").attr('content');

    // make to sure to pass through deep link GET params
    var get_stash = window.location.search.substr(1);

    var html = '';

    if(true == hide_credentials_login) {

        $("#login-form").hide();

    } else {
        html += '<div id="plugin_mantisoidc_separator">' + plugin_MantisOIDC_seperator_text + '</div>';
    }

    html += '<a id="plugin_mantisoidc_login_button" href="' + oidcStart + "&" + get_stash + '"></a>';

    $(html).insertAfter('#login-form');



});