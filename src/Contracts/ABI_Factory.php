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

namespace Kaadon\Ethereum\Contracts;

use Kaadon\Ethereum\Buffers\EthereumAddress;
use Kaadon\Ethereum\Exception\ContractsException;
use Kaadon\Ethereum\RPC\Abstract_RPC_Client;

/**
 * Class ABI_Factory
 * @package Kaadon\Ethereum\Contracts
 */
class ABI_Factory
{
    /**
     * @param string $filePath
     * @param bool $validate
     * @param array $errors
     * @return \Kaadon\Ethereum\Contracts\Contract
     * @throws \Kaadon\Ethereum\Exception\Contract_ABIException
     * @throws \Kaadon\Ethereum\Exception\ContractsException
     * @throws \Throwable
     */
    public function fromJSONFile(string $filePath, bool $validate, array &$errors): Contract
    {
        $fileBasename = basename($filePath);
        if (!file_exists($filePath)) {
            throw new ContractsException(sprintf('Contract ABI JSON file "%s" not found', $fileBasename));
        } elseif (!is_readable($filePath)) {
            throw new ContractsException(sprintf('Contract ABI JSON file "%s" is not readable', $fileBasename));
        }

        $source = file_get_contents($filePath);
        if (!$source) {
            throw new ContractsException(sprintf('Failed to read contract ABI file "%s"', $fileBasename));
        }

        try {
            $decoded = json_decode($source, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ContractsException(sprintf('Failed to JSON decode contract ABI file "%s"', $fileBasename));
        }

        return $this->fromArray($decoded, $validate, $errors);
    }

    /**
     * @param array $abi
     * @param bool $validate
     * @param array $errors
     * @return \Kaadon\Ethereum\Contracts\Contract
     * @throws \Kaadon\Ethereum\Exception\Contract_ABIException
     * @throws \Throwable
     */
    public function fromArray(array $abi, bool $validate, array &$errors): Contract
    {
        return Contract::fromArray($abi, $validate, $errors);
    }

    /**
     * @param \Kaadon\Ethereum\RPC\Abstract_RPC_Client $rpc
     * @param \Kaadon\Ethereum\Contracts\Contract $contract
     * @param \Kaadon\Ethereum\Buffers\EthereumAddress|string $address
     * @return \Kaadon\Ethereum\Contracts\DeployedContract
     */
    public function deployedAt(Abstract_RPC_Client $rpc, Contract $contract, EthereumAddress|string $address): DeployedContract
    {
        return new DeployedContract($contract, $address, $rpc);
    }
}
