<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use Robo\Result;

class ConfigTask extends ZendServerTask
{
    public function run()
    {
        return Result::success($this, "ZS config", ['config' => $this->apiConfig]);
    }
}