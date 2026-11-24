<?php

namespace App\Providers;

use App\Contracts\ChallengeRunnerInterface;
use App\Services\Sandbox\DockerPhpChallengeRunner;
use App\Services\Sandbox\FakeChallengeRunner;
use App\Services\Sandbox\PistonChallengeRunner;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChallengeRunnerInterface::class, function (): ChallengeRunnerInterface {
            return match (config('sandbox.driver')) {
                'fake' => new FakeChallengeRunner,
                'piston' => new PistonChallengeRunner,
                default => new DockerPhpChallengeRunner,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
