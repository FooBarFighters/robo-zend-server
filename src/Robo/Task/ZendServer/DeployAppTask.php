<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Model\App;
use FooBarFighters\ZendServer\WebApi\Model\Package;
use Robo\Result;
use Robo\State\Data;
use Robo\State\StateAwareTrait;

class DeployAppTask extends UpdateAppTask
{
    use StateAwareTrait;

    /**
     * @var App|null
     */
    private $app;

    /**
     * @var Package|null
     */
    private $package;

    /**
     * @param Data $state
     */
    public function receiveState(Data $state):void
    {
        $this->env = $state['env'] ?? null;
        $this->app = $state['app'] ?? null;
        $this->package = $state['package'] ?? null;
    }

    public function run()
    {
        $this->hideProgressIndicator();

        //== ZS API client
        $client = $this->getClient();

        //== init progress bar
        $progressBar = $this->getProgressBar();
        $progressBar->clear();
        $progressBar->setMessage('uploading');
        $progressBar->start(4);

        //== upload the zip
        $app = $client->updateApp($this->app->getId(), $this->package->getFilePath());
        $progressBar->advance();

        //== show progress on deploying
        $this->pollApplicationStatus($app->getId(), $progressBar);

        $this->setStateValue('app', $app);

        return Result::success($this, "Deploy app", [
            'app' => $app,
        ]);
    }
}