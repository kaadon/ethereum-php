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
use FurqanSiddiqui\BIP32\Buffers\Bits32;
use FurqanSiddiqui\BIP32\Exception\UnserializeBIP32KeyException;
use Kaadon\Ethereum\Ethereum;

/**
 * Class MasterKeyPair
 * @package Kaadon\Ethereum\KeyPair
 */
class MasterHDKey extends HDKey
{
    /**
     * @param \FurqanSiddiqui\BIP32\BIP32 $bip32
     * @param \Kaadon\Ethereum\KeyPair\PublicKey|\Kaadon\Ethereum\KeyPair\PrivateKey $key
     * @param int $depth
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $childNum
     * @param \FurqanSiddiqui\BIP32\Buffers\Bits32 $parentPubFp
     * @param \Comely\Buffer\Bytes32 $chainCode
     * @param \Kaadon\Ethereum\Ethereum|null $eth
     * @throws \FurqanSiddiqui\BIP32\Exception\UnserializeBIP32KeyException
     */
    public function __construct(
        BIP32                $bip32,
        PublicKey|PrivateKey $key,
        int                  $depth,
        Bits32               $childNum,
        Bits32               $parentPubFp,
        Bytes32              $chainCode,
        ?Ethereum            $eth = null
    )
    {
        if (!$childNum->isZeroBytes() || !$parentPubFp->isZeroBytes() || $depth !== 0) {
            throw new UnserializeBIP32KeyException('Cannot unserialize child key as MasterHDKey');
        }

        parent::__construct($bip32, $key, $depth, $childNum, $parentPubFp, $chainCode, $eth);
    }
}
