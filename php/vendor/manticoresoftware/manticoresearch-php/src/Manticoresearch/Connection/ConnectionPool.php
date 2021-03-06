<?php

namespace Manticoresearch\Connection;

use Manticoresearch\Connection;
use Manticoresearch\Connection\Strategy\SelectorInterface;
use Manticoresearch\Exceptions\ConnectionException;
use Manticoresearch\Exceptions\NoMoreNodesException;

/**
 * Class ConnectionPool
 * @package Manticoresearch\Connection
 */
class ConnectionPool
{
    /**
     * @var array
     */
    protected $_connections;

    /**
     * @var SelectorInterface
     */
    public $strategy;

    public $retries;

    public $retries_attempts =0;

    public function __construct(array $connections, SelectorInterface $strategy, int $retries)
    {
        $this->_connections = $connections;
        $this->strategy = $strategy;
        $this->retries = $retries;
    }

    /**
     * @return array
     */
    public function getConnections(): array
    {
        return $this->_connections;
    }

    /**
     * @param array $connections
     */
    public function setConnections(array $connections)
    {
        $this->_connections = $connections;
    }
    public function getConnection(): Connection
    {
        $this->retries_attempts++;
        $connection =   $this->strategy->getConnection($this->_connections);
        if($connection->isAlive()) {
            return $connection;
        }
        if ($this->retries_attempts < $this->retries) {

            return $connection;
        }
        throw new NoMoreNodesException('No more retries left');
    }

    public function hasConnections(): bool
    {
        if ($this->retries_attempts < $this->retries) {
            return true;
        }
        return false;
    }

    /**
     * @return SelectorInterface
     */
    public function getStrategy(): SelectorInterface
    {
        return $this->strategy;
    }

    /**
     * @param SelectorInterface $strategy
     */
    public function setStrategy(SelectorInterface $strategy)
    {
        $this->strategy = $strategy;
    }


}