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

namespace Kaadon\Ethereum\Transactions;

use Comely\Buffer\Bytes32;
use Kaadon\Ethereum\Buffers\EthereumAddress;
use Kaadon\Ethereum\Buffers\WEIAmount;
use Kaadon\Ethereum\Ethereum;
use Kaadon\Ethereum\Packages\Keccak\Keccak;
use Kaadon\Ethereum\RLP\Mapper;

/**
 * Class LegacyTx
 * @package Kaadon\Ethereum\Transactions
 */
class LegacyTx extends AbstractTransaction
{
    public ?int $nonce = null;
    public ?WEIAmount $gasPrice = null;
    public ?int $gasLimit = null;
    public ?EthereumAddress $to = null;
    public ?WEIAmount $value = null;
    public ?string $data = null;
    public ?int $signatureV = 1;
    public ?string $signatureR = null;
    public ?string $signatureS = null;

    /**
     * @return \Kaadon\Ethereum\RLP\Mapper
     */
    protected static function Mapper(): Mapper
    {
        return TxRLPMapper::LegacyTx();
    }

    /**
     * @param \Kaadon\Ethereum\Ethereum $eth
     */
    public function __construct(Ethereum $eth)
    {
        parent::__construct($eth);
        $this->signatureV = $this->eth->network->chainId;
    }

    /**
     * @return \Comely\Buffer\Bytes32
     * @throws \Kaadon\Ethereum\Exception\RLP_EncodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     */
    public function signPreImage(): Bytes32
    {
        $unSignedTx = $this->isSigned() ? $this->getUnsigned() : $this;
        return new Bytes32(Keccak::hash($unSignedTx->encode()->raw(), 256, true));
    }

    /**
     * @return $this
     */
    public function getUnsigned(): static
    {
        $unSigned = clone $this;
        $unSigned->signatureV = $this->eth->network->chainId;
        $unSigned->signatureR = null;
        $unSigned->signatureS = null;
        return $unSigned;
    }
}
