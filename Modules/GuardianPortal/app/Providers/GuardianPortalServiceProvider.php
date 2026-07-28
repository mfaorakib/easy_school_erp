<?php

namespace Modules\GuardianPortal\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class GuardianPortalServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'GuardianPortal';

    protected string $nameLower = 'guardianportal';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
