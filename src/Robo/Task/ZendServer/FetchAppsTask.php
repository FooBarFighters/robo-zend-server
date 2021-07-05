<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Repository\AppList;
use Robo\Result;

class FetchAppsTask extends ZendServerTask
{

    /**
     * Return AppList for the specified environment
     *
     * @return Result
     */
    public function run(): Result
    {
        /** @var AppList[] $apps */
        $apps = [];

        foreach($this->apiConfig as $env => $config){
            if($this->env !== null && $this->env !== $env){
                continue;
            }
            $apps[$env] = $this->getClient($env)->getApps();
        }

        return Result::success($this, "Array of AppLists", ['apps' => $apps]);
    }
}
