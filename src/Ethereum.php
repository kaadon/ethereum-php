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

namespace Kaadon\Ethereum;

use Comely\Buffer\AbstractByteArray;
use Comely\Buffer\Bytes32;
use FurqanSiddiqui\BIP32\BIP32;
use FurqanSiddiqui\BIP32\Buffers\BIP32_Provider;
use FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface;
use FurqanSiddiqui\ECDSA\ECC\EllipticCurveInterface;
use FurqanSiddiqui\ECDSA\KeyPair;
use Kaadon\Ethereum\Buffers\EthereumAddress;
use Kaadon\Ethereum\Contracts\ABI_Factory;
use Kaadon\Ethereum\Exception\KeyPairException;
use Kaadon\Ethereum\KeyPair\HDFactory;
use Kaadon\Ethereum\KeyPair\KeyPairFactory;
use Kaadon\Ethereum\KeyPair\PrivateKey;
use Kaadon\Ethereum\KeyPair\PublicKey;
use Kaadon\Ethereum\Networks\AbstractNetworkConfig;
use Kaadon\Ethereum\Transactions\TxFactory;

/**
 * Class Ethereum
 * @package Kaadon\Ethereum
 */
class Ethereum implements BIP32_Provider
{
    /** @var \FurqanSiddiqui\BIP32\BIP32 */
    public readonly BIP32 $bip32;
    /** @var \Kaadon\Ethereum\KeyPair\KeyPairFactory */
    public readonly KeyPairFactory $keyPair;
    /** @var \Kaadon\Ethereum\KeyPair\HDFactory */
    public readonly HDFactory $hdKeyPair;
    /** @var \Kaadon\Ethereum\Contracts\ABI_Factory */
    public readonly ABI_Factory $abi;
    /** @var \Kaadon\Ethereum\Transactions\TxFactory */
    public readonly TxFactory $tx;

    /**
     * Ethereum constructor.
     */
    public function __construct(
        public readonly EllipticCurveInterface $ecc,
        public readonly AbstractNetworkConfig  $network,
    )
    {
        $this->bip32 = new BIP32($this->ecc, $this->network);
        $this->abi = new ABI_Factory();
        $this->keyPair = new KeyPairFactory($this);
        $this->hdKeyPair = new HDFactory($this);
        $this->tx = new TxFactory($this);
    }

    /**
     * @param string $addr
     * @return \Kaadon\Ethereum\Buffers\EthereumAddress
     * @throws \Kaadon\Ethereum\Exception\InvalidAddressException
     */
    public function getAddress(string $addr): EthereumAddress
    {
        return EthereumAddress::fromString($addr, EthereumAddress::hasChecksum($addr));
    }

    /**
     * @param \Comely\Buffer\Bytes32 $entropy
     * @return \FurqanSiddiqui\BIP32\KeyPair\PrivateKeyInterface
     * @throws \FurqanSiddiqui\ECDSA\Exception\KeyPairException
     */
    public function privateKeyFromEntropy(Bytes32 $entropy): PrivateKeyInterface
    {
        return new PrivateKey($this, new KeyPair($this->ecc, $entropy));
    }

    /**
     * @param \Comely\Buffer\AbstractByteArray $compressedPubKey
     * @return \Kaadon\Ethereum\KeyPair\PublicKey
     * @throws \Kaadon\Ethereum\Exception\KeyPairException
     */
    public function publicKeyFromIncomplete(AbstractByteArray $compressedPubKey): PublicKey
    {
        if ($compressedPubKey->len() !== 33) {
            throw new KeyPairException('Compressed public key must be 33 bytes long');
        }

        $compressedPubKey = $compressedPubKey->raw();
        if (!in_array($compressedPubKey[0], ["\x02", "\x03"])) {
            throw new KeyPairException('Invalid compressed public key prefix');
        }

        return new PublicKey(
            $this,
            new \FurqanSiddiqui\ECDSA\ECC\PublicKey(bin2hex(substr($compressedPubKey, 1)), "", bin2hex($compressedPubKey[0]))
        );
    }

    /**
     * @return \FurqanSiddiqui\BIP32\BIP32
     */
    public function bip32(): BIP32
    {
        return $this->bip32;
    }
}
