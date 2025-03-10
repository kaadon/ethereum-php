<?php
/*
 * This file is a part of "kaadon/ethereum-php" package.
 * https://github.com/kaadon/ethereum-php
 *
 * Copyright (c) Furqan A. Siddiqui <kaadon.com@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/kaadon/ethereum-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Kaadon\Ethereum\RPC;

use Kaadon\Ethereum\Ethereum;
use Kaadon\Ethereum\Exception\RPC_ClientException;
use Kaadon\Ethereum\Exception\RPC_CurlException;
use Kaadon\Ethereum\Exception\RPC_RequestException;
use Kaadon\Ethereum\Exception\RPC_ResponseException;

/**
 * Class Abstract_JSON_RPC_2
 * @package Kaadon\Ethereum\RPC
 */
abstract class Abstract_JSON_RPC_2
{
    /** @var int */
    public int $timeout = 3;
    /** @var int */
    public int $connectTimeout = 3;
    /** @var bool */
    public bool $crossCheckReqId = true;
    /** @var bool */
    public bool $ignoreSSL = false;
    /** @var string|null */
    public ?string $httpAuthUser = null;
    /** @var string|null */
    public ?string $httpAuthPass = null;
    /** @var string|null */
    public ?string $requestNoncePrefix = null;

    /**
     * @param string|null $caRootFile
     * @throws \Kaadon\Ethereum\Exception\RPC_ClientException
     */
    public function __construct(
        protected readonly ?string $caRootFile = null
    )
    {
        if ($this->caRootFile && (!is_file($this->caRootFile) || !is_readable($this->caRootFile))) {
            throw new RPC_ClientException('Cannot read CA root file for SSL/TLS support');
        }
    }

    /**
     * @return string
     */
    abstract protected function getServerURL(): string;

    /**
     * @return string
     */
    protected function generateUniqueId(): string
    {
        return sprintf('%s_%s_%d', $this->requestNoncePrefix ?? "", microtime(true), mt_rand(1, 99999));
    }

    /**
     * @param string $method
     * @param array|null $params
     * @return bool|int|float|array|string|null
     * @throws \Kaadon\Ethereum\Exception\RPC_CurlException
     * @throws \Kaadon\Ethereum\Exception\RPC_RequestException
     * @throws \Kaadon\Ethereum\Exception\RPC_ResponseException
     */
    public function apiCall(string $method, array $params = null): null|bool|int|float|array|string
    {
        $ch = curl_init(); // Init cURL handler
        $serverURL = $this->getServerURL();
        curl_setopt($ch, CURLOPT_URL, $serverURL); // Set URL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->ignoreSSL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, !$this->ignoreSSL ? 2 : false);
        if ($this->caRootFile) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->caRootFile);
        }

        // JSON RPC 2.0 spec id
        $reqUniqueId = $this->generateUniqueId();

        // Payload
        $payload = [
            "jsonrpc" => "2.0",
            "method" => $method,
            "id" => $reqUniqueId
        ];

        if ($params) {
            $payload["params"] = $params;
        }

        try {
            $payload = json_encode($payload, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new RPC_RequestException('Failed to JSON encode request body');
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Headers
        $headers[] = "Content-type: application/json; charset=utf-8";
        $headers[] = "Content-length: " . strlen($payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Authentication
        if ($this->httpAuthUser || $this->httpAuthPass) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $this->httpAuthUser ?? "", $this->httpAuthPass ?? ""));
        }

        // Timeouts
        if ($this->timeout > 0) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        }

        if ($this->connectTimeout > 0) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }

        // Execute cURL request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new RPC_CurlException($ch);
        }

        // Close cURL resource
        curl_close($ch);

        // Prepare response
        try {
            $body = json_decode($response, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new RPC_RequestException('Failed to decode JSON response');
        }

        // Error Msg/Code
        if (isset($body["error"])) {
            throw new RPC_ResponseException(
                strval($body["error"]["message"] ?? ""),
                intval($body["error"]["code"] ?? -1),
                method: $method
            );
        }

        // Request IDs
        if ($this->crossCheckReqId) {
            if (!isset($body["id"]) || $body["id"] !== $reqUniqueId) {
                throw new RPC_RequestException('JSON RPC 2.0 request IDs does not match');
            }
        }

        // Result
        if (!isset($body["result"])) {
            throw new RPC_RequestException('No result was received from server');
        }

        return $body["result"];
    }
}
