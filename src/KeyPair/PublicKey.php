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

use Comely\Buffer\Buffer;
use Kaadon\Ethereum\Buffers\EthereumAddress;
use Kaadon\Ethereum\Ethereum;
use Kaadon\Ethereum\Exception\KeyPairException;
use Kaadon\Ethereum\Packages\Keccak\Keccak;

/**
 * Class PublicKey
 * @package Kaadon\Ethereum\KeyPair
 */
class PublicKey extends \FurqanSiddiqui\BIP32\KeyPair\PublicKey
{
    /** @var \Kaadon\Ethereum\Buffers\EthereumAddress|null */
    private ?EthereumAddress $address = null;

    /**
     * @param \Kaadon\Ethereum\Ethereum $eth
     * @param \FurqanSiddiqui\ECDSA\ECC\PublicKey $eccPublicKey
     * @throws \Kaadon\Ethereum\Exception\KeyPairException
     */
    public function __construct(
        public readonly Ethereum            $eth,
        \FurqanSiddiqui\ECDSA\ECC\PublicKey $eccPublicKey
    )
    {
        parent::__construct($this->eth->bip32, $eccPublicKey);
        if (!$eccPublicKey->y) {
            throw new KeyPairException('Cannot instantiate public key with Y coordinate');
        }
    }

    /**
     * @return \Kaadon\Ethereum\Buffers\EthereumAddress
     */
    public function address(): EthereumAddress
    {
        if ($this->address) {
            return $this->address;
        }

        $bn = Buffer::fromBase16($this->eccPublicKey->x . $this->eccPublicKey->y);
        $this->address = new EthereumAddress(substr(Keccak::hash($bn->raw(), 256, true), -20));
        return $this->address;
    }
}
