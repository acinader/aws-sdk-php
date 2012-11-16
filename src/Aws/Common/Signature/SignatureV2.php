<?php
/**
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Aws\Common\Signature;

use Aws\Common\Credentials\CredentialsInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * Signature V2 signer
 * @link http://docs.amazonwebservices.com/general/latest/gr/signature-version-2.html
 */
class SignatureV2 extends AbstractSignature
{
    /**
     * {@inheritdoc}
     */
    public function signRequest(RequestInterface $request, CredentialsInterface $credentials)
    {
        // Add required fields and sort the POST fields
        $fields = $request->getPostFields()->getAll();
        $fields['Timestamp'] = $this->getDateTime('c');
        $fields['Version'] = $request->getClient()->getDescription()->getApiVersion();
        $fields['SignatureVersion'] = '2';
        $fields['SignatureMethod'] = 'HmacSHA256';
        $fields['AWSAccessKeyId'] = $credentials->getAccessKeyId();
        uksort($fields, 'strcmp');

        // Create the canonicalized query string
        $params = '';
        foreach ($fields as $k => $v) {
            if ($k && $v && $k !== 'Signature') {
                if ($params) {
                    $params .= '&';
                }
                $params .= rawurlencode($k) . '=' . rawurlencode($v);
            }
        }

        $url = $request->getUrl(true);
        $stringToSign = $request->getMethod() . "\n" . $url->getHost() . "\n" . $url->getPath() . "\n" . $params;
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $credentials->getSecretKey(), true));
        $fields['Signature'] = $signature;

        // Update the request with the signature fields
        $request->getPostFields()->replace($fields);
    }
}
