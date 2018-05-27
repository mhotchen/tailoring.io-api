<?php

namespace App\Providers;

use App\Model\Company;
use App\Policies\CompanyPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

final class AuthServiceProvider extends ServiceProvider
{
    /** @var array */
    protected $policies = [
        Company::class => CompanyPolicy::class,
    ];

    /**
     * @return void
     * @throws \Exception
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
        Passport::tokensExpireIn(now()->addDays(30));
        Passport::refreshTokensExpireIn(now()->addDays(60));
    }
}
