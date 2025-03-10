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
use Kaadon\Ethereum\Ethereum;
use Kaadon\Ethereum\Exception\TxDecodeException;

/**
 * Class TxFactory
 * @package Kaadon\Ethereum\Transactions
 */
class TxFactory
{
    /**
     * @param \Kaadon\Ethereum\Ethereum $eth
     */
    public function __construct(public readonly Ethereum $eth)
    {
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $rawTx
     * @return \Kaadon\Ethereum\Transactions\LegacyTx|\Kaadon\Ethereum\Transactions\Type1Tx|\Kaadon\Ethereum\Transactions\Type2Tx|\Kaadon\Ethereum\Transactions\TransactionInterface
     * @throws \Kaadon\Ethereum\Exception\RLP_DecodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     * @throws \Kaadon\Ethereum\Exception\TxDecodeException
     */
    public function decode(AbstractByteArray $rawTx): LegacyTx|Type1Tx|Type2Tx|TransactionInterface
    {
        $prefix = substr($rawTx->raw(), 0, 1);
        if (ord($prefix) < 127) {
            return match ($prefix) {
                "\x01" => Type1Tx::DecodeRawTransaction($this->eth, $rawTx),
                "\x02" => Type2Tx::DecodeRawTransaction($this->eth, $rawTx),
                default => throw new TxDecodeException(
                    sprintf('Unsupported transaction envelope prefix "%s"', bin2hex($prefix))
                )
            };
        }

        return LegacyTx::DecodeRawTransaction($this->eth, $rawTx);
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $rawTx
     * @return \Kaadon\Ethereum\Transactions\LegacyTx
     * @throws \Kaadon\Ethereum\Exception\RLP_DecodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     * @throws \Kaadon\Ethereum\Exception\TxDecodeException
     */
    public function decodeLegacy(AbstractByteArray $rawTx): LegacyTx
    {
        return LegacyTx::DecodeRawTransaction($this->eth, $rawTx);
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $rawTx
     * @return \Kaadon\Ethereum\Transactions\Type1Tx
     * @throws \Kaadon\Ethereum\Exception\RLP_DecodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     * @throws \Kaadon\Ethereum\Exception\TxDecodeException
     */
    public function decodeType1(AbstractByteArray $rawTx): Type1Tx
    {
        return Type1Tx::DecodeRawTransaction($this->eth, $rawTx);
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $rawTx
     * @return \Kaadon\Ethereum\Transactions\Type2Tx
     * @throws \Kaadon\Ethereum\Exception\RLP_DecodeException
     * @throws \Kaadon\Ethereum\Exception\RLP_MapperException
     * @throws \Kaadon\Ethereum\Exception\TxDecodeException
     */
    public function decodeType2(AbstractByteArray $rawTx): Type2Tx
    {
        return Type2Tx::DecodeRawTransaction($this->eth, $rawTx);
    }

    /**
     * @return \Kaadon\Ethereum\Transactions\LegacyTx
     */
    public function legacyTx(): LegacyTx
    {
        return new LegacyTx($this->eth);
    }

    /**
     * @return \Kaadon\Ethereum\Transactions\Type1Tx
     */
    public function type1(): Type1Tx
    {
        return new Type1Tx($this->eth);
    }

    /**
     * @return \Kaadon\Ethereum\Transactions\Type2Tx
     */
    public function type2(): Type2Tx
    {
        return new Type2Tx($this->eth);
    }
}
