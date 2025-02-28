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
use Kaadon\Ethereum\Buffers\RLP_Encoded;
use Kaadon\Ethereum\Ethereum;

/**
 * Interface TransactionInterface
 * @package Kaadon\Ethereum\Transactions
 */
interface TransactionInterface
{
    /**
     * @param \Kaadon\Ethereum\Ethereum $eth
     * @param \Comely\Buffer\AbstractByteArray $raw
     * @return static
     */
    public static function DecodeRawTransaction(Ethereum $eth, AbstractByteArray $raw): static;

    /**
     * @return $this
     */
    public function getUnsigned(): static;

    /**
     * @return bool
     */
    public function isSigned(): bool;

    /**
     * @return \Comely\Buffer\Bytes32
     */
    public function signPreImage(): Bytes32;

    /**
     * @return \Comely\Buffer\Bytes32
     */
    public function hash(): Bytes32;
}

