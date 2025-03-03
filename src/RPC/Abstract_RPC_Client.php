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

use Comely\Buffer\BigInteger\BigEndian;
use Kaadon\Ethereum\Buffers\EthereumAddress;
use Kaadon\Ethereum\Buffers\WEIAmount;
use Kaadon\Ethereum\Exception\BadWEIAmountException;
use Kaadon\Ethereum\Exception\RPC_RequestException;
use Kaadon\Ethereum\Exception\RPC_ResponseException;

/**
 * Class AbstractRPCClient
 * @package Kaadon\Ethereum\RPC
 */
abstract class Abstract_RPC_Client extends Abstract_JSON_RPC_2
{
    /**
     * @return int
     * @throws \Kaadon\Ethereum\Exception\RPC_CurlException
     * @throws \Kaadon\Ethereum\Exception\RPC_RequestException
     * @throws \Kaadon\Ethereum\Exception\RPC_ResponseException
     */
    public function eth_blockNumber(): int
    {
        $blockNum = $this->getCleanHexadecimal($this->apiCall("eth_blockNumber"));
        if (!$blockNum) {
            throw RPC_ResponseException::InvalidResultDataType("eth_blockNumber", "Base16", gettype($blockNum));
        }

        return gmp_intval(gmp_init($blockNum, 16));
    }

    /**
     * @param \Kaadon\Ethereum\Buffers\EthereumAddress|string $accountId
     * @param int $height
     * @return int
     * @throws \Kaadon\Ethereum\Exception\RPC_CurlException
     * @throws \Kaadon\Ethereum\Exception\RPC_RequestException
     * @throws \Kaadon\Ethereum\Exception\RPC_ResponseException
     */
    public function eth_getTransactionCount(EthereumAddress|string $accountId, int $height = 0): int
    {
        $height = $height > 0 ? $this->int2hex($height) : "latest";
        $txCount = $this->getCleanHexadecimal($this->apiCall("eth_getTransactionCount", [$accountId, $height]));
        if (!is_string($txCount)) {
            throw RPC_ResponseException::InvalidResultDataType("eth_getTransactionCount", "Base16", gettype($txCount));
        }

        return gmp_intval(gmp_init($txCount, 16));
    }

    /**
     * @param \Kaadon\Ethereum\Buffers\EthereumAddress|string $accountId
     * @param string $scope
     * @return \Kaadon\Ethereum\Buffers\WEIAmount
     * @throws \Kaadon\Ethereum\Exception\RPC_CurlException
     * @throws \Kaadon\Ethereum\Exception\RPC_RequestException
     * @throws \Kaadon\Ethereum\Exception\RPC_ResponseException
     */
    public function eth_getBalance(EthereumAddress|string $accountId, string $scope = "latest"): WEIAmount
    {
        if (!in_array($scope, ["latest", "earliest", "pending"])) {
            throw new RPC_RequestException('Invalid block scope; Valid values are "latest", "earliest" and "pending"');
        }

        $balance = $this->getCleanHexadecimal($this->apiCall("eth_getBalance", [$accountId, $scope]));
        if (!is_string($balance)) {
            throw RPC_ResponseException::InvalidResultDataType("eth_getBalance", "Base16", gettype($balance));
        }

        try {
            return new WEIAmount($balance);
        } catch (BadWEIAmountException) {
            throw new RPC_ResponseException('Cannot decode wei amount', method: "eth_getBalance");
        }
    }

    /**
     * @param mixed $in
     * @return string|null
     */
    public function getCleanHexadecimal(mixed $in): ?string
    {
        if (!is_string($in) || !preg_match('/(0x)?[a-f0-9]+/i', $in)) {
            return null;
        }

        if (str_starts_with($in, "0x")) {
            $in = substr($in, 2);
        }

        if (strlen($in) % 2 !== 0) {
            $in = "0" . $in;
        }

        return $in;
    }

    /**
     * @param int|string $num
     * @return string
     */
    public function int2hex(int|string $num): string
    {
        $hex = bin2hex(BigEndian::GMP_Pack($num));
        if (strlen($hex) % 2 !== 0) {
            $hex = "0" . $hex;
        }

        return "0x" . $hex;
    }
}
