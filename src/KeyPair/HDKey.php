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

use Comely\Buffer\Bytes32;
use FurqanSiddiqui\BIP32\BIP32;
use FurqanSiddiqui\BIP32\Buffers\BIP32_Provider;
use FurqanSiddiqui\BIP32\Buffers\Bits32;
use FurqanSiddiqui\BIP32\Buffers\SerializedBIP32Key;
use FurqanSiddiqui\BIP32\KeyPair\ExtendedKeyPair;
use FurqanSiddiqui\BIP32\KeyPair\PublicKeyInterface;
use Kaadon\Ethereum\Ethereum;

/**
 * Class KeyPair
 * @package Kaadon\Ethereum\KeyPair
 */
class HDKey extends ExtendedKeyPair
{
    /** @var \Kaadon\Ethereum\Ethereum */
    public readonly Ethereum $eth;
    /** @var \Kaadon\Ethereum\KeyPair\PublicKey|null */
    protected ?PublicKey $_public = null;

    /**
     * @param \Kaadon\Ethereum\Ethereum|\FurqanSiddiqui\BIP32\Buffers\BIP32_Provider $bip32
     * @param \FurqanSiddiqui\BIP32\Buffers\SerializedBIP32Key $ser
     * @return static
     * @throws \FurqanSiddiqui\BIP32\Exception\UnserializeBIP32KeyException
     */
    public static function Unserialize(Ethereum|BIP32_Provider $bip32, SerializedBIP32Key $ser): static
    {
        if (!$bip32 instanceof Ethereum) {
            throw new \InvalidArgumentException('Expected instance of Ethereum for Unserialize method');
        }

        $hdKey = parent::Unserialize($bip32, $ser);
        $hdKey->eth = $bip32;
        return $hdKey;
    }

    /**
     * @param \FurqanSiddiqui\BIP32\BIP32 $bip32
     * @param \Kaadon\Ethereum\KeyPair\PublicKey|\Kaadon\Ethereum\KeyPair\PrivateKey $key
     * @param int $depth
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $childNum
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $parentPubFp
     * @param \Comely\Buffer\Bytes32 $chainCode
     * @param \Kaadon\Ethereum\Ethereum|null $eth
     */
    public function __construct(
        BIP32                $bip32,
        PublicKey|PrivateKey $key,
        int                  $depth,
        Bits32               $childNum,
        Bits32               $parentPubFp,
        Bytes32              $chainCode,
        ?Ethereum            $eth = null,
    )
    {
        parent::__construct($bip32, $key, $depth, $childNum, $parentPubFp, $chainCode);
        if ($eth) {
            $this->eth = $eth;
        }
    }

    /**
     * @param int $index
     * @param bool $isHardened
     * @return $this
     * @throws \FurqanSiddiqui\BIP32\Exception\ChildKeyDeriveException
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function derive(int $index, bool $isHardened = false): HDKey
    {
        return HDKey::Unserialize($this->eth, $this->_derive($index, $isHardened));
    }

    /**
     * @param $path
     * @return $this
     * @throws \FurqanSiddiqui\BIP32\Exception\ChildKeyDeriveException
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function derivePath($path): HDKey
    {
        return parent::derivePath($path);
    }

    /**
     * @return \Kaadon\Ethereum\KeyPair\PublicKey|\FurqanSiddiqui\BIP32\KeyPair\PublicKeyInterface
     * @throws \FurqanSiddiqui\BIP32\Exception\KeyPairException
     * @throws \Kaadon\Ethereum\Exception\KeyPairException
     */
    public function publicKey(): PublicKey|PublicKeyInterface
    {
        if (!$this->_public) {
            $this->_public = new PublicKey($this->eth, $this->privateKey()->eccPrivateKey->public());
        }

        return $this->_public;
    }
}
