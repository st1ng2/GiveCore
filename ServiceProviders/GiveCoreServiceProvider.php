<?php

namespace Flute\Modules\GiveCore\ServiceProviders;

use Flute\Core\Support\ModuleServiceProvider;

class GiveCoreServiceProvider extends ModuleServiceProvider
{
    public array $extensions = [];

    public function boot(\DI\Container $container): void
    {
        $this->loadTranslations();
    }

    public function register(\DI\Container $container): void
    {
    }
}