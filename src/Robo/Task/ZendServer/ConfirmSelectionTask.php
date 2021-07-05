<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Model\App;
use FooBarFighters\ZendServer\WebApi\Model\Package;
use Robo\Common\IO;
use Robo\Result;
use Robo\State\Consumer;
use Robo\State\Data;
use Robo\Task\BaseTask;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ConfirmSelectionTask extends BaseTask implements Consumer
{
    use IO;

    /**
     * @var App|null
     */
    private $app;

    /**
     * @var Data|null
     */
    private $state;

    /**
     * @var false|mixed|null
     */
    private $env;

    /**
     * @var string
     */
    private $action;

    /**
     * @var Package|null
     */
    private $package;

    /**
     * ConfirmSelectionTask constructor.
     */
    public function __construct(string $action, ?Package $package = null)
    {
        $this->action = $action;
        $this->package = $package;
    }

    /**
     * @param Data $state
     */
    public function receiveState(Data $state):void
    {
        $this->env = $state['env'] ?? null;
        $this->app = $state['app'] ?? null;
        $this->package = $state['package'] ?? null;
    }

    /**
     * Confirm the environment & app selection
     *
     * @return Result
     */
    public function run(): Result
    {
        $io = $this->io();

        $definitionList = [
            ['action' => $this->action]
            , new TableSeparator()
            , ['env' => $this->env]
        ];

        if($this->app){
            $definitionList[] = new TableSeparator();
            $definitionList[] = '[App]:';
            $definitionList[] = ['id' => $this->app->getId()];
            $definitionList[] = ['name' => $this->app->getName()];
            $definitionList[] = ['url' => $this->app->getUrl()];
            $definitionList[] = ['ts' => $this->app->getTimestampAsString()];
            $definitionList[] = ['deployed' => $this->app->getDeployedVersion()];
            $definitionList[] = ['rollback' => $this->app->getRollbackVersion()];
        }

        if($this->package){
            $definitionList[] = new TableSeparator();
            $definitionList[] = '[Package]:';
            $definitionList[] = ['version' => $this->package->getReleaseVersion()];
            $definitionList[] = ['file' => $this->package->getFileName()];
            $definitionList[] = ['path' => $this->package->getFilePath()];
            $definitionList[] = ['size' => round($this->package->getFileSize(), 2) . ' KB'];
        }

        $io->newLine(2);
        $io->title('Confirm your selection');
        $io->definitionList(...$definitionList);
        $io->newLine();

        $question = (new ChoiceQuestion(
            'Do you wish to continue?',
            ['yes', 'no'],
            'yes'
        ))->setErrorMessage('You answer %s is invalid.');

        if($io->askQuestion($question) === 'yes'){
            $io->writeln('Awesome, lets get this show on the road');
        }else{
            $io->newLine();
            $io->writeln("aborting $this->action");
            $io->newLine();
            return Result::error($this, "$this->action declined"); // use action to restart the console app
        }

        return Result::success($this, "$this->action confirmed", [
            'env' => $this->env,
            'app' => $this->app,
            'package' => $this->package,
        ]);
    }
}
