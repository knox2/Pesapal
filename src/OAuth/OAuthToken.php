<?php
/**
 * Created by PhpStorm.
 * User: mxgel
 * Date: 11/14/16
 * Time: 2:26 AM
 */

namespace Knox\Pesapal\OAuth;


/**
 * Class OAuthToken
 *
 * @package Knox\Pesapal\OAuth
 */
class OAuthToken
{
    // access tokens and request tokens
    /**
     * @var
     */
    public $key;
    /**
     * @var
     */
    public $secret;

    /**
     * @param string $key - the token
     * @param string $secret - the token secret
     */
    function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    function to_string()
    {
        return "oauth_token=" .
        OAuthUtil::urlencode_rfc3986($this->key) .
        "&oauth_token_secret=" .
        OAuthUtil::urlencode_rfc3986($this->secret);
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->to_string();
    }
}