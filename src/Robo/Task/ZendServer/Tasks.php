<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use FooBarFighters\ZendServer\WebApi\Model\Package;
use Robo\Collection\CollectionBuilder;

trait Tasks
{
    protected function taskFetchApps(?string $env = null): CollectionBuilder
    {
        return $this->task(FetchAppsTask::class, $env);
    }

    protected function taskConfig(?string $env = null): CollectionBuilder
    {
        return $this->task(ConfigTask::class, $env);
    }

    protected function taskConfirmSelection(string $action, ?Package $package = null): CollectionBuilder
    {
        return $this->task(ConfirmSelectionTask::class, $action, $package);
    }

    protected function taskCreateDummyPackage(?string $zipPath = null): CollectionBuilder
    {
        return $this->task(CreateDummyPackageTask::class, $zipPath);
    }

//    protected function taskDeployApp(string $zipPath, string $env, int $appId): CollectionBuilder
//    {
//        return $this->task(DeployAppTask::class, $zipPath, $env, $appId);
//    }

    protected function taskDeployApp(): CollectionBuilder
    {
        return $this->task(DeployAppTask::class);
    }

    protected function taskRollbackApp(?string $env = null, ?int $appId = null): CollectionBuilder
    {
        return $this->task(RollbackAppTask::class, $env, $appId);
    }

    protected function taskSelectApp(?string $appRef = null, ?string $env = null): CollectionBuilder
    {
        return $this->task(SelectAppTask::class, $appRef, $env);
    }

    protected function taskSelectEnv(?string $env = null): CollectionBuilder
    {
        return $this->task(SelectEnvTask::class, $env);
    }

    protected function taskTest(?string $env = null): CollectionBuilder
    {
        return $this->task(TestTask::class, $env);
    }
}