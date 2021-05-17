<?php

namespace Tina4;
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4 (Andre van Zuydam)
 * License: MIT https://opensource.org/licenses/MIT
 *
 * Class Api
 * This is supposed to make it easier to consume REST API interfaces with little code
 * @package Tina4
 */
class Api
{
    public ?string $baseURL;
    public ?string $authHeader;

    /**
     * API constructor.
     * @param ?string $baseURL
     * @param string $authHeader Example - Authorization: Bearer AFD-22323-FD
     * @tests tina4
     *   assert ("https://the-one-api.dev/v2", "Authorization: Bearer 123456") === null,"Could not initialize API"
     */
    public function __construct(?string $baseURL, $authHeader = "")
    {
        $this->baseURL = $baseURL;
        $this->authHeader = $authHeader;
    }

    /**
     * Sends a request to the specified API
     * @param string $restService
     * @param string $requestType
     * @param string|null $body
     * @param string $contentType
     * @return array|mixed
     * tests tina4
     *   assert ("/book")['docs'][0]['name'] === "The Fellowship Of The Ring", "API Get request"
     *   assert ("/book")['docs'][1]['name'] !== "The Fellowship Of The Ring", "API Get request"
     *   assert is_array("/book") === true, "This is not an array"
     */
    public function sendRequest(string $restService = "", string $requestType = "GET", ?string $body = null, string $contentType = "*/*"): array
    {
        try {
            $headers = [];
            $headers[] = "Accept: " . $contentType;
            $headers[] = "Accept-Charset: utf-8, *;q=0.8";
            $headers[] = "Accept-Encoding: gzip, deflate,br";
            if (!empty($this->authHeader)) {
                $headers[] = $this->authHeader;
            }

            if (!empty($body)) {
                $headers[] = "Content-Type: " . $contentType;
            }

            $curlRequest = curl_init($this->baseURL . $restService);

            curl_setopt($curlRequest, CURLOPT_CUSTOMREQUEST, $requestType);
            curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlRequest, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curlRequest, CURLOPT_FOLLOWLOCATION, 1);

            if (!empty($body)) {
                curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $body);
            }
            $curlResult = curl_exec($curlRequest); //execute the Curl request
            $curlInfo = curl_getinfo($curlRequest); //Assign the response to a variable
            curl_close($curlRequest);
            //If an error
            if (!($curlInfo['http_code'] === 200 || $curlInfo['http_code'] === 201 || $curlInfo['http_code'] === 202)) {
                return ["error" => $curlInfo, "body" => json_decode($curlResult, false, 512, JSON_THROW_ON_ERROR)];
            } else {
                return json_decode($curlResult, true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\Exception $error) {
            return ["error" => $error->getMessage()];
        }
    }
}