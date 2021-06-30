<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use Robo\Result;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectAppTask extends ZendServerTask
{
    /**
     * @var int|null
     */
    private $appId;

    /**
     * @var string|null
     */
    private $appName;

    /**
     * @var string|null
     */
    private $appRef;

    /**
     * SelectAppTask constructor.
     *
     * @param string|null $env
     * @param string|null $appRef
     */
    public function __construct(?string $env = null, ?string $appRef = null)
    {
        parent::__construct($env);

        $this->appId = $this->getAppId($appRef);
        if($this->appId === null){
            $this->appName = $appRef;
        }
        $this->appRef = $appRef;
    }

    private function getAppId(?string $appRef): ?int
    {
        $id = (int)$appRef;
        return $id === 0 ? null : $id;
    }

    /**
     * Select an app through a menu.
     *
     * @return Result
     */
    public function run(): Result
    {
        $io = $this->io();
        $appList = $this->getClient()->getApps();

        //== resolve the application by id or name
        if($this->appId){
            $app = $appList->filterById($this->appId);
        }else{
            $app = $appList->filterByName((string)$this->appName);
        }

        //== user supplied app id or name didn't validate,
        if($app === null){
            $io->warning('Could not resolve an application with the supplied reference: ' . $this->appRef);

            $apps = $appList->getNames();
            $default = array_keys($apps)[0];
            $question = new ChoiceQuestion(
                'Select an app',
                $apps,
                $default
            );
            $question->setErrorMessage('App id %s is invalid.');
            $appName = $io->askQuestion($question);
            $app = $appList->filterByName($appName);

            //== shouldn't be possible
            if($app === null){
                throw new \RuntimeException('No valid app was selected');
            }
        }

        $io->success("You have selected: [{$app->getId()}] {$app->getName()}");

        return Result::success($this, "select app", [
            'app' => $app,
            'env' => $this->env,
        ]);
    }
}
