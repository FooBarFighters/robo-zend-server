<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Client\ClientFactory;
use FooBarFighters\ZendServer\WebApi\Client\Extended\Client;
use League\Container\ContainerAwareTrait;
use Robo\Common\IO;
use Robo\State\Consumer;
use Robo\State\Data;
use Robo\Task\BaseTask;

abstract class ZendServerTask extends BaseTask implements Consumer
{
    use ContainerAwareTrait;
    use IO;

    /**
     * @var array|null
     */
    protected $apiConfig;

    /**
     * @var string|null
     */
    protected $env;

    /**
     * AppNamesTask constructor.
     */
    public function __construct(?string $env = null)
    {
        $this->env = $env;

        //== check if config is present
        if (($this->apiConfig = \Robo\Robo::Config()->get('ZendServer.api')) === null) {
            throw new \RuntimeException('API config not found');
        }

        //== validate the config
        foreach ($this->apiConfig as $environment => $config){
            foreach(['baseUrl', 'hash', 'username', 'version'] as $key){
                if(!isset($config[$key])){
                    throw new \RuntimeException("Missing config key in [$environment] => $key");
                }
            }
        }
    }

    protected function getClient(?string $env = null): Client
    {
        return ClientFactory::createExtendedClient($this->apiConfig[$env ?? $this->env]);
    }

    public function run()
    {
        //== implement stub in subclass
    }

    public function receiveState(Data $state)
    {
        //== implement stub in subclass
    }
}