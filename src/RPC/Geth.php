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

namespace Kaadon\Ethereum\RPC;

/**
 * Class Geth
 * @package Kaadon\Ethereum\RPC
 */
class Geth extends Abstract_RPC_Client
{
    /** @var string */
    public readonly string $serverURL;

    /**
     * @param string $hostname
     * @param int|null $port
     * @throws \Kaadon\Ethereum\Exception\RPC_ClientException
     */
    public function __construct(
        public readonly string $hostname,
        public readonly ?int   $port,
    )
    {
        parent::__construct();
        $serverURL = $this->port ? $this->hostname . ":" . $this->port : $this->hostname;
        if (!preg_match('/^(http|https):\/\//i', $serverURL)) {
            $serverURL = "http://" . $serverURL;
        }

        $this->serverURL = $serverURL;
    }

    /**
     * @return string
     */
    protected function getServerURL(): string
    {
        return $this->serverURL;
    }
}
