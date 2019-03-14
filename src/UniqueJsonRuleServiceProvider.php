<?php

namespace TopviewDigital\UniqueJsonRule;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use TopviewDigital\UniqueJsonRule\UniqueJsonValidator;

class UniqueJsonRuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('unique_json', UniqueJsonValidator::class . '@validate', trans('validation.unique'));
    }
}
