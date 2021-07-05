<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Exception\NoRollbackAvailableException;
use FooBarFighters\ZendServer\WebApi\Model\App;
use Robo\Result;
use Robo\State\Data;

class RollbackAppTask extends UpdateAppTask
{
    /**
     * @var App|null
     */
    private $app;

    /**
     * DeployTask constructor.
     */
    public function __construct(?string $env, ?int $appId)
    {
        parent::__construct($env);
        $this->appId = $appId;
    }

    /**
     * @param Data $state
     */
    public function receiveState(Data $state):void
    {
        $this->env = $state['env'] ?? null;
        $this->app = $state['app'] ?? null;
    }

    public function run(): Result
    {
        $this->hideProgressIndicator();

        $io = $this->io();
        $io->title("Let's roll this puppy back!");
        try {
            //== ZS API client
            $client = $this->getClient();

            //== init progress bar
            $progressBar = $this->getProgressBar();
            $progressBar->setMessage('start rollback');
            $progressBar->start(4);

            //== roll back the app
            $app = $client->rollbackApp($this->app->getId());
            $progressBar->advance();

            //== show progress on deploying
            $this->pollApplicationStatus($app->getId(), $progressBar);

            return Result::success($this, "Rollback app", [
                'app' => $this->app,
            ]);
        }

        catch (NoRollbackAvailableException $e){
            return Result::error($this, "No rollback available for app id {$this->app->getId()}");
        }

        catch (\Exception $e) {
            return Result::error($this, $e->getMessage());
        }
    }
}
