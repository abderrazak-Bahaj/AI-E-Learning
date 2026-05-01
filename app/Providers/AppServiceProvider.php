<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\CertificateServiceInterface;
use App\Contracts\PaypalServiceInterface;
use App\Events\EnrollmentCompleted;
use App\Listeners\IssueCertificateOnCompletion;
use App\Services\CertificateService;
use App\Services\PaypalService;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaypalServiceInterface::class, PaypalService::class);
        $this->app->bind(CertificateServiceInterface::class, CertificateService::class);
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureEvents();
        $this->configureApiDocs();

        // Catch N+1 queries in non-production, non-testing environments
        if ($this->app->isLocal()) {
            Model::preventLazyLoading();
        }
    }

    private function configureEvents(): void
    {
        Event::listen(EnrollmentCompleted::class, IssueCertificateOnCompletion::class);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('authenticated', fn (Request $request) => $request->user()
            ? Limit::perMinute(120)->by($request->user()->id)
            : Limit::perMinute(60)->by($request->ip()));
    }

    private function configureApiDocs(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });
    }
}
