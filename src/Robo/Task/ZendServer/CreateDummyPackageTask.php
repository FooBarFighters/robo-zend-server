<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Model\App;
use FooBarFighters\ZendServer\WebApi\Model\Package;
use FooBarFighters\ZendServer\WebApi\Util\PackageBuilder;
use Robo\Result;
use Robo\State\Data;

class CreateDummyPackageTask extends ZendServerTask
{
    /**
     * @var App|null
     */
    private $app;

    /**
     * @var Package|null
     */
    private $package;

    /**
     * @var string|null
     */
    private $zipPath;

    public function __construct(?string $zipPath)
    {
        parent::__construct(null);
        $this->zipPath = $zipPath;
    }

    public function receiveState(Data $state):void
    {
        $this->app = $state['app'] ?? null;
        $this->env = $state['env'] ?? null;
        $this->package = $state['package'] ?? null;
    }

    /**
     * Return AppList for the specified environment
     *
     * @return Result
     */
    public function run(): Result
    {
        $package = $this->zipPath
            ? Package::createFromArchive($this->zipPath)
            : PackageBuilder::createDummy($this->app->getName(), getcwd(), 'test_');

        return Result::success($this, "testy", [
            'app' => $this->app,
            'env' => $this->env,
            'package' => $package,
        ]);
    }
}
