<?php

namespace Modules\Admission\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class AdmissionServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Admission';

    protected string $nameLower = 'admission';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
