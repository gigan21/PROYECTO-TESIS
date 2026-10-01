<?php

namespace App\Providers;

use App\Models\AttendanceSession;
use App\Models\ChallengeRoom;
use App\Models\Question;
use App\Policies\AttendanceSessionPolicy;
use App\Policies\ChallengeRoomPolicy;
use App\Policies\QuestionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(AttendanceSession::class, AttendanceSessionPolicy::class);
        Gate::policy(Question::class, QuestionPolicy::class);
        Gate::policy(ChallengeRoom::class, ChallengeRoomPolicy::class);

        Route::bind('challenge_room', function (string $value) {
            $user = auth()->user();

            abort_unless($user?->isDocente(), 403);

            return ChallengeRoom::query()
                ->where('teacher_id', $user->id)
                ->findOrFail($value);
        });

        Route::bind('attendance_session', function (string $value) {
            $user = auth()->user();

            abort_unless($user?->isDocente(), 403);

            return AttendanceSession::query()
                ->where('teacher_id', $user->id)
                ->findOrFail($value);
        });
    }
}
