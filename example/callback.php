<?php

/**
 * Copyright (c) 2016, 2017 François Kooman <fkooman@tuxed.net>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
require_once sprintf('%s/vendor/autoload.php', dirname(__DIR__));

use fkooman\OAuth\Client\Http\CurlHttpClient;
use fkooman\OAuth\Client\OAuthClient;
use fkooman\OAuth\Client\Provider;
use fkooman\OAuth\Client\SessionTokenStorage;

// absolute link to index.php in this directory
// after handling the callback, we redirect back to this URL...
$indexUri = 'http://localhost:8081/index.php';

// the user ID to bind to, typically the currently logged in user on the
// _CLIENT_ service...
$userId = 'foo';

// start a session to store the OAuth authorization request, to among other
// things avoid CSRF, this is **NOT** used for storing access_tokens...
if ('' === session_id()) {
    session_start();
}

try {
    $client = new OAuthClient(
        // the OAuth provider configuration
        new Provider(
            'demo_client',
            'demo_secret',
            'http://localhost:8080/authorize.php',
            'http://localhost:8080/token.php'
        ),
        // for DEMO purposes we store the AccessToken in the user session
        // data...
        new SessionTokenStorage(),

        // for DEMO purposes we also allow connecting to HTTP URLs, do **NOT**
        // do this in production
        new CurlHttpClient(['httpsOnly' => false])
    );
    $client->setUserId($userId);

    // handle the callback from the OAuth server
    $client->handleCallback(
        $_SESSION['_oauth2_session'], // URI from session
        $_GET['code'],                // the authorization_code
        $_GET['state']                // the state
    );

    // unset session field as to not allow additional redirects to the same
    // URI to attempt to get another access token with this code
    unset($_SESSION['_oauth2_session']);

    // redirect the browser back to the index
    http_response_code(302);
    header(sprintf('Location: %s', $indexUri));
    exit(0);
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage());
    exit(1);
}
