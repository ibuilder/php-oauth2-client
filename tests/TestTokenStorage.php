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

namespace fkooman\OAuth\Client\Tests;

use DateTime;
use fkooman\OAuth\Client\AccessToken;
use fkooman\OAuth\Client\TokenStorageInterface;

class TestTokenStorage implements TokenStorageInterface
{
    /** @var array */
    private $data = [];

    public function __construct()
    {
        $this->setAccessToken('fooz', new AccessToken('AT:abc', 'bearer', 'my_scope', null, new DateTime('2016-01-01 01:00:00')));
        $this->setAccessToken('bar', new AccessToken('AT:xyz', 'bearer', 'my_scope', null, new DateTime('2016-01-01 01:00:00')));
        $this->setAccessToken('baz', new AccessToken('AT:expired', 'bearer', 'my_scope', 'RT:abc', new DateTime('2016-01-01 01:00:00')));
        $this->setAccessToken('bazz', new AccessToken('AT:expired', 'bearer', 'my_scope', 'RT:invalid', new DateTime('2016-01-01 01:00:00')));
    }

    /**
     * @return AccessToken|false
     */
    public function getAccessToken($userId, $requestScope)
    {
        if (!array_key_exists($userId, $this->data)) {
            return false;
        }

        if (array_key_exists('access_token', $this->data[$userId])) {
            return AccessToken::fromJson($this->data[$userId]['access_token']);
        }

        return false;
    }

    public function setAccessToken($userId, AccessToken $accessToken)
    {
        $this->data[$userId]['access_token'] = $accessToken->json();
    }

    public function deleteAccessToken($userId, AccessToken $accessToken)
    {
        unset($this->data[$userId]['access_token']);
    }
}
