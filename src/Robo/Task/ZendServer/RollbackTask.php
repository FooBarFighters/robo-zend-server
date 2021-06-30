<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Exception\NoRollbackAvailableException;
use Robo\Result;

class RollbackTask extends ZendServerTask
{
    /**
     * @var int
     */
    private $appId;

    /**
     * DeployTask constructor.
     */
    public function __construct(string $env, int $appId)
    {
        parent::__construct($env);
        $this->appId = $appId;
    }

    public function run(): Result
    {
        $io = $this->io();
        $io->title("Let's roll this puppy back!");
        try {
            $client = $this->getClient();

            $progressBar = $this->getProgressBar();
            $progressBar->setMessage('start rollback');
            $progressBar->start(4);

            //== roll back the app
            $app = $client->rollbackApp($this->appId);
            $progressBar->advance();

            //== show progress on deploying
            $this->pollApplicationStatus($this->appId, $progressBar);

            return Result::success($this, "Rollback app", [
                'app' => $app,
                'env' => $this->env,
            ]);
        }

        catch (NoRollbackAvailableException $e){
            return Result::error($this, "No rollback available for app id {$this->appId}");
        }

        catch (\Exception $e) {
            return Result::error($this, $e->getMessage());
        }
    }
}
