<?php

namespace AppBundle\Service;

use AppBundle\Entity\Address;
use Ehann\RedisRaw\PredisAdapter;
use Ehann\RedisRaw\Exceptions\UnknownIndexNameException;
use Ehann\RediSearch\Index;

class Search
{
    private $redis;
    private $prefix;
    private $indexes = [];

    public function __construct($host, $port, $prefix)
    {
        $adapter = new PredisAdapter();
        $this->redis = $adapter->connect($host, $port);
        $this->prefix = $prefix;
    }

    public function indexAddress(Address $address)
    {
        if (!isset($this->indexes[Address::class])) {
            $index = new Index($this->redis, sprintf('%s:%s', $this->prefix, 'address'));
            try {
                $index->info();
            } catch (UnknownIndexNameException $e) {
                $index
                    ->addTextField('name')
                    ->create();
            }
            $this->indexes[Address::class] = $index;
        } else {
            $index = $this->indexes[Address::class];
        }
    }
}
