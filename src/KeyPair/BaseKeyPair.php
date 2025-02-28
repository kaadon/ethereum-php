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

use FurqanSiddiqui\BIP32\KeyPair\AbstractKeyPair;
use FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface;
use Kaadon\Ethereum\Ethereum;

/**
 * Class BaseKeyPair
 * @package Kaadon\Ethereum\KeyPair
 */
class BaseKeyPair extends AbstractKeyPair
{
    /**
     * @param \Kaadon\Ethereum\Ethereum $eth
     * @param \Kaadon\Ethereum\KeyPair\PrivateKey|\Kaadon\Ethereum\KeyPair\PublicKey $key
     */
    public function __construct(
        public readonly Ethereum $eth,
        PrivateKey|PublicKey     $key
    )
    {
        parent::__construct($this->eth->bip32, $key);
    }

    /**
     * @return \Kaadon\Ethereum\KeyPair\PublicKey
     * @throws \Kaadon\Ethereum\Exception\KeyPairException
     */
    public function publicKey(): PublicKey
    {
        if (!$this->pub) {
            $this->pub = new PublicKey($this->eth, $this->prv->eccPrivateKey->public());
        }

        return $this->pub;
    }

    /**
     * @return bool
     */
    public function hasPrivateKey(): bool
    {
        return isset($this->prv);
    }

    /**
     * @return \Kaadon\Ethereum\KeyPair\PrivateKey|\FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface
     * @throws \FurqanSiddiqui\BIP32\Exception\KeyPairException
     */
    public function privateKey(): PrivateKey|PrivateKeyInterface
    {
        return parent::privateKey();
    }
}
