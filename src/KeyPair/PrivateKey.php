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

namespace Kaadon\Ethereum\KeyPair;

use FurqanSiddiqui\ECDSA\KeyPair;
use Kaadon\Ethereum\Buffers\Signature;
use Kaadon\Ethereum\Ethereum;
use Kaadon\Ethereum\Transactions\AbstractTransaction;

/**
 * Class PrivateKey
 * @package Kaadon\Ethereum\KeyPair
 */
class PrivateKey extends \FurqanSiddiqui\BIP32\KeyPair\PrivateKey
{
    /**
     * @param \Kaadon\Ethereum\Ethereum $eth
     * @param \FurqanSiddiqui\ECDSA\KeyPair $eccPrivateKey
     */
    public function __construct(
        public readonly Ethereum $eth,
        KeyPair                  $eccPrivateKey
    )
    {
        parent::__construct($this->eth->bip32, $eccPrivateKey);
    }

    /**
     * @param \Kaadon\Ethereum\Transactions\AbstractTransaction $tx
     * @return \Kaadon\Ethereum\Buffers\Signature
     * @throws \FurqanSiddiqui\ECDSA\Exception\SignatureException
     */
    public function signTransaction(AbstractTransaction $tx): Signature
    {
        return new Signature($this->eccPrivateKey->signRecoverable($tx->signPreImage()), $this->eth);
    }
}
