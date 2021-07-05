<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Client\ClientFactory;
use FooBarFighters\ZendServer\WebApi\Client\Extended\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class UpdateAppTask extends ZendServerTask
{
    /**
     * @url https://docs.guzzlephp.org/en/stable/request-options.html#on-stats
     */
    protected function getGuzzleClient(): \GuzzleHttp\Client
    {
        return new \GuzzleHttp\Client([
            RequestOptions::PROGRESS => static function(
                $downloadTotal,
                $downloadedBytes,
                $uploadTotal,
                $uploadedBytes
            ) {
                echo $uploadTotal . '/' . $uploadedBytes . PHP_EOL;
            },
        ]);
    }

    /**
     * @param string|null $env
     *
     * @return Client
     */
    protected function getClient(?string $env = null): Client
    {
        return ClientFactory::createExtendedClient($this->apiConfig[$env ?? $this->env]);
    }

    /**
     * @return ProgressBar
     */
    protected function getProgressBar(): ProgressBar
    {
        $io = $this->io();
        $progressBar = $io->createProgressBar();
        $progressBar->setBarCharacter('<fg=magenta>=</>');
        $progressBar->setProgressCharacter("\xF0\x9F\x8D\xBA");
        $progressBar->setFormat(' %current%/%max% [%bar%] %message% %elapsed:6s%');
        return $progressBar;
    }

    /**
     * @param int              $appId
     * @param ProgressBar|null $progressBar
     */
    protected function pollApplicationStatus(int $appId, ?ProgressBar $progressBar = null): void
    {
        $io = $this->io();

        //== instantiate progress bar
        if($progressBar === null){
            $progressBar = $this->getProgressBar();
            $progressBar->start();
        }

        //== instantiate ZS client
        $client = ClientFactory::createClient($this->apiConfig[$this->env]);

        //== poll status
        $status = null; $prevStatus = null;
        while($status !== 'deployed'){
            //== fetch application status
            $status = $client->applicationGetDetails($appId)['responseData']['applicationDetails']['applicationInfo']['status']; //$io->writeln($status);

            //== status changed, update the progress bar
            if($prevStatus !== $status){
                $prevStatus = $status;
                $progressBar->setMessage($status);
                $progressBar->advance();
            }

            //== sleep 0.2 seconds
            usleep(200000);
        }
        $progressBar->finish();
        $io->newLine(3);
    }
}