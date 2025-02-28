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
use Comely\Buffer\BigInteger\BigEndian;
use Comely\Buffer\Bytes32;
use Kaadon\Ethereum\Buffers\RLP_Encoded;
use Kaadon\Ethereum\Ethereum;
use Kaadon\Ethereum\Exception\TxDecodeException;
use Kaadon\Ethereum\Packages\Keccak\Keccak;
use Kaadon\Ethereum\RLP\Mapper;
use Kaadon\Ethereum\RLP\RLP;
use Kaadon\Ethereum\RLP\RLP_Mappable;

/**
 * Class AbstractTransaction
 * @package Kaadon\Ethereum\Transactions
 */
abstract class AbstractTransaction implements RLP_Mappable, TransactionInterface
{
    /**
     * @return \Kaadon\Ethereum\RLP\Mapper
     */
    abstract protected static function Mapper(): Mapper;

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
        $rlpDecode = RLP::Decode($raw);
        if (!is_array($rlpDecode)) {
            throw new TxDecodeException(sprintf('Expected Array from decoded RLP, got "%s"', gettype($rlpDecode)));
        }

        $tx = new static($eth);
        $rlpArray = static::Mapper()->createArray($rlpDecode);
        foreach ($rlpArray as $prop => $value) {
            if (!property_exists($tx, $prop)) {
                throw new TxDecodeException(
                    sprintf('Property "%s" does not exist in %s tx class', $prop, static::class)
                );
            }

            $tx->$prop = $value;
        }

        return $tx;
    }

    /**
     * @param \Kaadon\Ethereum\Ethereum $eth
     */
    public function __construct(public readonly Ethereum $eth)
    {
    }

    /**
     * @return string[]
     */
    public function __debugInfo(): array
    {
        return [static::class];
    }

    /**
     * @return bool
     */
    public function isSigned(): bool
    {
        if ($this->signatureR || $this->signatureS) {
            return true;
        }

        return false;
    }

    /**
     * @param \Kaadon\Ethereum\RLP\Mapper|null $mapper
     * @return \Kaadon\Ethereum\Buffers\RLP_Encoded
     * @throws \Kaadon\Ethereum\Exception\RLP_EncodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     */
    public function encode(?Mapper $mapper = null): RLP_Encoded
    {
        if (!$mapper) {
            $mapper = static::Mapper();
        }

        $encoded = $mapper->encode($this);
        $encodedLen = $encoded->len();

        if ($encodedLen <= 55) {
            $encoded->prependUInt8(0xc0 + $encodedLen);
            return $encoded;
        }

        $encoded->prependUInt8($encodedLen);
        $encoded->prependUInt8(0xf7 + strlen(BigEndian::GMP_Pack($encodedLen)));
        return $encoded;
    }

    /**
     * @return \Comely\Buffer\Bytes32
     * @throws \Kaadon\Ethereum\Exception\RLP_EncodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     */
    public function hash(): Bytes32
    {
        return new Bytes32(Keccak::hash($this->encode()->raw(), 256, true));
    }
}
