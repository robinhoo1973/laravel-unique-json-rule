<?php

namespace TopviewDigital\UniqueJsonRule;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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
        Validator::extend('unique_json', UniqueJsonValidator::class.'@validate', trans('validation.unique'));
    }
}
