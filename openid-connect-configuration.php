<?php

const CONFIGURATION_PATH = '.well-known/openid-configuration';
$configurationPathLength = strlen(CONFIGURATION_PATH);

function usage() {
    echo "Usage: $argv[0] <provider url>\n";
    exit;
}

if (sizeof($argv) < 2) {
    usage();
}

if ($argv[1] === '-h' || $argv[1] === '--help') {
    usage();
}

$url = $argv[1];
if (substr($url, -$configurationPathLength) !== CONFIGURATION_PATH) {
    if (substr($url, -1) !== '/') {
        $url .= '/';
    }
    $url .= CONFIGURATION_PATH;
}

$data = file_get_contents($url);
if (!$data) {
    echo "Failed to get configuration data from $url\n";
    exit;
}
$conf = json_decode($data);
if (!$conf) {
    echo "Failed to json decode configuration data: " . $data;
    exit;
}

echo <<<SNIP1
    //OpenID Connect provider $conf->issuer
    '$conf->issuer' => array(
        'authoauth2:OpenIDConnect',

        // Scopes to request, should include openid
        'scopes' => ['openid', 'profile'],

        // Configured client id and secret
        'clientId' => '<client id>',
        'clientSecret' => '<client secret>',

        'scopeSeparator' => ' ',
        'issuer' => '$conf->issuer',
        'urlAuthorize' => '$conf->authorization_endpoint',
        'urlAccessToken' => '$conf->token_endpoint',
        'urlResourceOwnerDetails' => '$conf->userinfo_endpoint',

SNIP1;
if (isset($conf->end_session_endpoint)) {
    echo "        'urlEndSession' => '$conf->end_session_endpoint',\n";
}

echo <<<SNIP2

    ),

SNIP2;
