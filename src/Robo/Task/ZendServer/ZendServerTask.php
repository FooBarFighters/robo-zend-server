<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Client\ClientFactory;
use FooBarFighters\ZendServer\WebApi\Client\Extended\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use League\Container\ContainerAwareTrait;
use Robo\Common\IO;
use Robo\Common\TaskIO;
use Robo\Task\BaseTask;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class ZendServerTask extends BaseTask
{
    use ContainerAwareTrait;
    use IO;

    /**
     * @var array|null
     */
    protected $apiConfig;

    /**
     * @var string|null
     */
    protected $env;

    protected $io;

    /**
     * AppNamesTask constructor.
     */
    public function __construct(?string $env = null)
    {
        $this->env = $env;

        //== check if config is present
        if (($this->apiConfig = \Robo\Robo::Config()->get('ZendServer.api')) === null) {
            throw new \RuntimeException('API config not found');
        }

//        if ($env !== null && !isset($this->apiConfig[$env])) {
//            throw new \RuntimeException("Invalid API environment: $env");
//        }

        //== validate the config
        foreach ($this->apiConfig as $environment => $config){
            foreach(['baseUrl', 'hash', 'username', 'version'] as $key){
                if(!isset($config[$key])){
                    throw new \RuntimeException("Missing config key in [$environment] => $key");
                }
            }
        }
    }

    //https://docs.guzzlephp.org/en/stable/request-options.html#on-stats
    public function getClient(?string $env = null): Client
    {
        return ClientFactory::createExtendedClient($this->apiConfig[$env ?? $this->env]);
    }

    private function getGuzzleClient()
    {
        $guzzle = new \GuzzleHttp\Client([
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

    public function run()
    {
    }
}