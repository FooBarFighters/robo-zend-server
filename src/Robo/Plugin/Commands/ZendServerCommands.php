<?php

namespace FooBarFighters\Robo\Plugin\Commands;

use FooBarFighters\ZendServer\WebApi\Model\App;
use FooBarFighters\ZendServer\WebApi\Repository\AppList;
use Robo\Symfony\ConsoleIO;
use Robo\Tasks;

class ZendServerCommands extends Tasks
{
    use \FooBarFighters\Robo\Task\ZendServer\Tasks;

    /**
     * Deploy a pre-made package to a Zend Server environment
     *
     * @command zs:deploy
     */
    public function deployApp(ConsoleIO $io, ?string $env = null, ?string $appRef = null): void
    {
        try {
            $cb = $this->collectionBuilder();
            $result = $cb
                ->taskSelectEnv($env)
                ->taskSelectApp($appRef)
                ->taskCreateDummyPackage()
                ->taskConfirmSelection(__FUNCTION__)
                ->taskDeployApp()
            ->run();

            /** @var App $app */
            $app = $cb->getState()['app'];

            $io->newLine();
            if($app){
                $time = round($result->getExecutionTime(),3);
                $io->success("{$app->getName()} ({$app->getId()}) has updated to version \"{$app->getDeployedVersion()}\" in {$time} seconds"); //
            }

        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }

    /**
     * Display the names of the apps found in the specified environments.
     *
     * @command zs:display-apps
     */
    public function displayApps(ConsoleIO $io, string $env = null)
    {
        try {
            $task = $this->taskFetchApps($env);

            /** @var AppList[] $apps */
            $apps = $task->run()['apps'];

            $r = [];
            foreach($apps as $environment => $appList){
                $rows = array_map(
                    static function (App $app) use($environment): array {
                        return [
                            $environment,
                            $app->getId(),
                            $app->getName(),
                            $app->getDeployedVersion(),
                            $app->getRollbackVersion(),
                            $app->getTimestampAsString()
                        ];
                    },
                    $appList->getArrayCopy()
                );
                $rows[] = ['', '', '', '', '', ''];
                $r = array_merge($r, $rows);
            }
            $io->table(['env', 'id', 'name', 'version', 'rollback version', 'updated'], $r);

        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }

    /**
     * Display the Zend Server Web API config
     *
     * @command zs:display-config
     */
    public function displayConfig(ConsoleIO $io): void
    {
        try {
            $task = $this->taskConfig();
            $config = $task->run()['config'];
            $io->success('API config is valid');
            $config = array_map(static function (string $key, array $values): array {
                if(strlen($values['hash'])> 20){
                    $values['hash'] = substr($values['hash'], 0, 20) . '...';
                }
                return array_merge(['env' => $key], $values);
            }, array_keys($config), $config
            );
            $io->newLine(1);
            $io->table(array_keys(current($config)), $config);
            $io->newLine(1);
        } catch (\Exception $e) {
            $io->error('API config is invalid: ' . $e->getMessage());
        }
    }

    /**
     * Rollback an app to its previous version.
     *
     * @command zs:rollback
     */
    public function rollbackApp(ConsoleIO $io, ?string $env = null, ?string $appRef = null)
    {
        try{
            $cb = $this->collectionBuilder();
            $result = $cb
                ->taskSelectEnv($env)
                ->taskSelectApp($appRef)
                ->taskConfirmSelection(__FUNCTION__)
                ->taskRollbackApp()
            ->run();

            /** @var App $app */
            $app = $cb->getState()['app'];

            $time = round($result->getExecutionTime(),3);
            $io->success("{$app->getName()} ({$app->getId()}) has been rolled back to version \"{$app->getRollbackVersion()}\" in {$time} seconds"); //
        }
        catch (\Exception $e){
            $io->error($e->getMessage());
        }
    }
}