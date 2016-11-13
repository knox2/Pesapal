<?php
/**
 * Created by PhpStorm.
 * User: mxgel
 * Date: 11/14/16
 * Time: 2:29 AM
 */

namespace Knox\Pesapal\OAuth;


/**
 * Class OAuthSignatureMethod_PLAINTEXT
 *
 * @package Knox\Pesapal\OAuth
 */
class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod
{
    /**
     * @return string
     */
    public function get_name()
    {
        return "PLAINTEXT";
    }

    /**
     * @param $request
     * @param $consumer
     * @param $token
     *
     * @return mixed
     */
    public function build_signature($request, $consumer, $token)
    {
        $sig = [
            OAuthUtil::urlencode_rfc3986($consumer->secret),
        ];

        if ($token) {
            array_push($sig, OAuthUtil::urlencode_rfc3986($token->secret));
        } else {
            array_push($sig, '');
        }

        $raw = implode("&", $sig);
        // for debug purposes
        $request->base_string = $raw;

        return OAuthUtil::urlencode_rfc3986($raw);
    }
}