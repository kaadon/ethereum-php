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

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\Bytes32;
use Kaadon\Ethereum\Buffers\EthereumAddress;
use Kaadon\Ethereum\Buffers\RLP_Encoded;
use Kaadon\Ethereum\Buffers\WEIAmount;
use Kaadon\Ethereum\Ethereum;
use Kaadon\Ethereum\Exception\TxDecodeException;
use Kaadon\Ethereum\Packages\Keccak\Keccak;
use Kaadon\Ethereum\RLP\Mapper;

/**
 * Class EIP2718Tx
 * @package Kaadon\Ethereum\Transactions
 */
class EIP2718Tx extends AbstractTransaction
{
    public ?int $chainId = null;
    public ?int $nonce = null;
    public ?WEIAmount $gasPrice = null;
    public ?int $gasLimit = null;
    public ?EthereumAddress $to = null;
    public ?WEIAmount $value = null;
    public ?string $data = null;
    public ?array $accessList = null;
    public ?bool $signatureParity = null;
    public ?string $signatureR = null;
    public ?string $signatureS = null;

    /**
     * @param \Kaadon\Ethereum\Ethereum $eth
     * @param \Comely\Buffer\AbstractByteArray $raw
     * @return static
     * @throws \Kaadon\Ethereum\Exception\RLP_DecodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     * @throws \Kaadon\Ethereum\Exception\TxDecodeException
     */
    public static function DecodeRawTransaction(Ethereum $eth, AbstractByteArray $raw): static
    {
        $raw = $raw->copy();
        $prefix = $raw->pop(1);
        if ($prefix !== "\x01") {
            throw new TxDecodeException(sprintf('Bad prefix "%s" for Type1/EIP2718 transaction', bin2hex($prefix)));
        }

        // pop method removed the first prefix byte from buffer.
        return parent::DecodeRawTransaction($eth, $raw);
    }

    /**
     * @return \Kaadon\Ethereum\RLP\Mapper
     */
    protected static function Mapper(): Mapper
    {
        return TxRLPMapper::EIP2718Tx();
    }

    /**
     * @param \Kaadon\Ethereum\RLP\Mapper|null $mapper
     * @return \Kaadon\Ethereum\Buffers\RLP_Encoded
     * @throws \Kaadon\Ethereum\Exception\RLP_EncodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     */
    public function encode(?Mapper $mapper = null): RLP_Encoded
    {
        $buffer = parent::encode($mapper);
        return $buffer->prepend("\x01");
    }

    /**
     * @return \Comely\Buffer\Bytes32
     * @throws \Kaadon\Ethereum\Exception\RLP_EncodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     */
    public function signPreImage(): Bytes32
    {
        $unSignedTx = $this->isSigned() ? $this->getUnsigned() : $this;
        $encoded = $unSignedTx->encode(TxRLPMapper::EIP2718Tx_Unsigned())->raw();
        return new Bytes32(Keccak::hash($encoded, 256, true));
    }

    /**
     * @return $this
     */
    public function getUnsigned(): static
    {
        $unSigned = clone $this;
        $unSigned->signatureParity = false;
        $unSigned->signatureR = null;
        $unSigned->signatureS = null;
        return $unSigned;
    }
}
