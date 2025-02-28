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

namespace Kaadon\Ethereum\Buffers;

use Comely\Buffer\AbstractFixedLenBuffer;
use Kaadon\Ethereum\Exception\InvalidAddressException;
use Kaadon\Ethereum\Packages\Keccak\Keccak;

/**
 * Class EthereumAddress
 * @package Kaadon\Ethereum\Buffers
 */
class EthereumAddress extends AbstractFixedLenBuffer
{
    /** @var int */
    protected const SIZE = 20;
    /** @var bool */
    protected bool $readOnly = true;
    /** @var string|null */
    private ?string $address = null;
    /** @var string|null */
    private ?string $checksumAddress = null;

    /**
     * @param string $addr
     * @param bool $validateChecksum
     * @return static
     * @throws \Kaadon\Ethereum\Exception\InvalidAddressException
     */
    public static function fromString(string $addr, bool $validateChecksum = false): static
    {
        if ($validateChecksum) {
            if (static::CalculateChecksum($addr) !== $addr) {
                throw new InvalidAddressException('Checksum error');
            }
        } else {
            if (!static::isValidString($addr)) {
                throw new InvalidAddressException('Bad address string');
            }
        }

        return new static(hex2bin(substr($addr, 2)));
    }

    /**
     * @param bool $withChecksum
     * @return string
     * @throws \Kaadon\Ethereum\Exception\InvalidAddressException
     */
    public function toString(bool $withChecksum): string
    {
        if ($withChecksum) {
            if ($this->checksumAddress) {
                return $this->checksumAddress;
            }

            $this->checksumAddress = static::CalculateChecksum("0x" . $this->toBase16());
            if (!$this->address) {
                $this->address = strtolower($this->checksumAddress);
            }

            return $this->checksumAddress;
        }

        if (!$this->address) {
            $this->address = "0x" . $this->toBase16();
        }

        return $this->address;
    }

    /**
     * @return string[]
     * @throws \Kaadon\Ethereum\Exception\InvalidAddressException
     */
    public function __debugInfo(): array
    {
        return [$this->toString(false)];
    }

    /**
     * @return string
     * @throws \Kaadon\Ethereum\Exception\InvalidAddressException
     */
    public function __toString(): string
    {
        return $this->toString(false);
    }

    /**
     * @param string $addr
     * @return bool
     */
    public static function isValidString(mixed $addr): bool
    {
        return is_string($addr) && preg_match('/^0x[a-fA-F0-9]{40}$/', $addr);
    }

    /**
     * @param string $addr
     * @return bool
     */
    public static function hasChecksum(string $addr): bool
    {
        return (bool)preg_match('/^[A-F]+$/', $addr);
    }

    /**
     * @param string $addr
     * @return string
     * @throws \Kaadon\Ethereum\Exception\InvalidAddressException
     */
    public static function CalculateChecksum(string $addr): string
    {
        if (!static::isValidString($addr)) {
            throw new InvalidAddressException('Bad address string');
        }

        $addrLc = strtolower(substr($addr, 2));
        $addrKeccak = Keccak::hash($addrLc, 256);
        $checksum = "";
        for ($i = 0; $i < strlen($addrLc); $i++) {
            $addrChar = $addrLc[$i];
            $keccakChar = $addrKeccak[$i];
            $checksum .= intval($keccakChar, 16) >= 8 ? strtoupper($addrChar) : $addrChar;
        }

        return "0x" . $checksum;
    }
}
