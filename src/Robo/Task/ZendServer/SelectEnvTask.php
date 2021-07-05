<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use Robo\Result;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectEnvTask extends ZendServerTask
{
    public function run(): Result
    {
        $io = $this->io();
        if(!isset($this->apiConfig[$this->env])){
            $io->warning("Invalid environment: {$this->env}");
            $io->newLine();
            $question = new ChoiceQuestion(
                'Select Zend Server environment',
                array_keys($this->apiConfig),
                0
            );
            $question->setErrorMessage('Env %s is invalid.');
            $this->env = $io->askQuestion($question);
            $io->newLine();
        }

        return Result::success($this, "Select env", [
            'env' => $this->env,
        ]);
    }
}