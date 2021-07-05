<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use Robo\Common\BuilderAwareTrait;
use Robo\Common\TaskIO;
use Robo\Result;
use Robo\State\Data;
use Robo\State\StateAwareTrait;

class TestTask extends DeployAppTask
{
//    use StateAwareTrait;
//    use BuilderAwareTrait;
//    use TaskIO;

    public static $foo = 0;

    public function receiveState(Data $state):void
    {
       // var_dump($state);
    }


    /**
     * Return AppList for the specified environment
     *
     * @return Result
     */
    public function run(): Result
    {
//
//        $this->writeln('ja toch');



        $progressBar = $this->io()->createProgressBar();
        $progressBar->setMessage('testing');
        $progressBar->start(10);
        $progressBar->advance();
        $i = 0;
        while($i < 10){
            $i++;
            //== sleep 0.2 seconds
            usleep(200000);
            $progressBar->advance();
        }
        $progressBar->finish();
        //$progressBar->clear();
        $this->io()->writeln('xx');
        $this->io()->newLine(2);

        $this->hideProgressIndicator();

 self::$foo++;

        return Result::success($this, "testy", [
            'TEST2' => '_________dus' .  self::$foo,
        ]);
    }

}
