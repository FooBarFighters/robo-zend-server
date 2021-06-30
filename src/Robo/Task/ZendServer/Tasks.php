<?php

namespace FooBarFighters\Robo\Task\ZendServer;

use Robo\Collection\CollectionBuilder;

trait Tasks
{
    protected function taskApps(?string $env = null): CollectionBuilder
    {
        return $this->task(AppsTask::class, $env);
    }

    protected function taskConfig(?string $env = null): CollectionBuilder
    {
        return $this->task(ConfigTask::class, $env);
    }

    protected function taskDeploy(string $zipPath, string $env, int $appId): CollectionBuilder
    {
        return $this->task(DeployTask::class, $zipPath, $env, $appId);
    }

    protected function taskRollback(string $env, int $appId): CollectionBuilder
    {
        return $this->task(RollbackTask::class, $env, $appId);
    }

    protected function taskSelectApp(string $env, ?string $appRef = null): CollectionBuilder
    {
        return $this->task(SelectAppTask::class, $env, $appRef);
    }

    protected function taskSelectEnv(?string $env = null): CollectionBuilder
    {
        return $this->task(SelectEnvTask::class, $env);
    }
}