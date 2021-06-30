<?php

namespace FooBarFighters\Robo\Plugin\Commands;

use FooBarFighters\ZendServer\WebApi\Model\App;
use FooBarFighters\ZendServer\WebApi\Model\Package;
use FooBarFighters\ZendServer\WebApi\Repository\AppList;
use FooBarFighters\ZendServer\WebApi\Util\PackageBuilder;
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
    public function deployApp(ConsoleIO $io, ?string $env = null, ?string $appRef = null, ?string $zipPath = null): void
    {
        try {
            //== determine environment
            /** @var string $env */
            $env =  $this->taskSelectEnv($env)->run()->getData()['env'];

            //== determine app
            /** @var App $app */
            $app = $this->taskSelectApp($env, $appRef)->run()->getData()['app'];

            //== create a dummy package if no path to a real package is supplied
            $zipPath = $zipPath ?: PackageBuilder::createDummy($app->getName(), getcwd(), 'test_')->getPath();

            $package = Package::createFromArchive($zipPath);

            //== deploy app
            $io->title("Let's deploy this puppy! - " . filesize($package->getPath())/1000 . 'kb');
            $data = $this->taskDeploy($zipPath, $env, $app->getId())->run()->getData();
            $app = $data['app'];
            $io->writeln("visit the result at {$app->getUrl()}");
            $io->newLine();
            if($app){
                $time = round($data['time'],3);
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
            $task = $this->taskApps($env);

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
     *
     *
     * @command zs:rollback
     */
    public function rollbackApp(ConsoleIO $io, ?string $env = null, ?string $appRef = null)
    {
        try{
            //== determine environment
            $env = $this->taskSelectEnv($env)->run()->getData()['env'];

            //== determine app
            $app = $this->taskSelectApp($env, $appRef)->run()->getData()['app']; //var_dump($app); die;

            //== rollback the app to the previous version
            $result = $this->taskRollback($env, $app->getId())->run();
            $time = round($result->getExecutionTime(),3);
            $io->success("{$app->getName()} ({$app->getId()}) has been rolled back to version \"{$app->getRollbackVersion()}\" in {$time} seconds"); //
        }
        catch (\Exception $e){
            $io->error($e->getMessage());
        }
    }

    /**
     * @command zs:test
     */
    public function test(ConsoleIO $io, ?string $env = null)
    {

    }
}