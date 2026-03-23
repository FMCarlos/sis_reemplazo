<?php

namespace App\Providers;

use App\Models\FormSubmission;
use App\Models\Request;
use App\Policies\FormSubmissionPolicy;
use App\Policies\RequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Request::class, RequestPolicy::class);
        Gate::policy(FormSubmission::class, FormSubmissionPolicy::class);
    }
}
