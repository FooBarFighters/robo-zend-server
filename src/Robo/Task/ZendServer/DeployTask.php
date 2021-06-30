<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use Robo\Result;

class DeployTask extends ZendServerTask
{
    /**
     * @var string
     */
    private $zipPath;

    /**
     * @var int
     */
    private $appId;

    /**
     * @var string
     */
    private $releaseVersion;

    /**
     * DeployTask constructor.
     */
    public function __construct(string $zipPath, string $env, int $appId)
    {
        parent::__construct($env);
        $this->zipPath = $zipPath;
        $this->appId = $appId;
    }

    public function run()
    {
        $client = $this->getClient();

        $progressBar = $this->getProgressBar();
        $progressBar->setMessage('uploading');
        $progressBar->start(4);

        //== upload the zip
        $app = $client->updateApp($this->appId, $this->zipPath);
        $progressBar->advance();

        //== show progress on deploying
        $this->pollApplicationStatus($this->appId, $progressBar);

        return Result::success($this, "Deploy app", [
            'app' => $app,
        ]);
    }
}