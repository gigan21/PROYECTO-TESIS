<?php

namespace App\Services\Gamification;

use App\Models\StudentProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RankingService
{
    /**
     * Ranking Local (mismo classroom del estudiante)
     */
    public function local(string $classroom, int $limit = 12): Collection
    {
        $key = 'ranking.local.' . md5($classroom) . '.' . $limit;

        $data = Cache::remember($key, now()->addMinutes(3), function () use ($classroom, $limit) {
            return $this->buildRanking(
                StudentProfile::with('user')
                    ->where('classroom', $classroom)
                    ->orderByDesc('xp_points')
                    ->orderBy('id')
                    ->limit($limit)
                    ->get()
            )->all();
        });

        return collect($data);
    }

    /**
     * Ranking Global (todos los estudiantes)
     */
    public function global(int $limit = 12): Collection
    {
        $key = 'ranking.global.' . $limit;

        $data = Cache::remember($key, now()->addMinutes(3), function () use ($limit) {
            return $this->buildRanking(
                StudentProfile::with('user')
                    ->orderByDesc('xp_points')
                    ->orderBy('id')
                    ->limit($limit)
                    ->get()
            )->all();
        });

        return collect($data);
    }

    /**
     * Posición real del estudiante (Local)
     */
    public function positionLocal(StudentProfile $profile): array
    {
        $better = StudentProfile::where('classroom', $profile->classroom)
            ->where('xp_points', '>', $profile->xp_points)
            ->count();

        $same = StudentProfile::where('classroom', $profile->classroom)
            ->where('xp_points', $profile->xp_points)
            ->where('id', '<', $profile->id)
            ->count();

        $total = StudentProfile::where('classroom', $profile->classroom)
            ->count();

        return [
            'position' => $better + $same + 1,
            'total'    => $total,
            'xp'       => $profile->xp_points,
        ];
    }

    /**
     * Posición real del estudiante (Global)
     */
    public function positionGlobal(StudentProfile $profile): array
    {
        $better = StudentProfile::where('xp_points', '>', $profile->xp_points)
            ->count();

        $same = StudentProfile::where('xp_points', $profile->xp_points)
            ->where('id', '<', $profile->id)
            ->count();

        $total = StudentProfile::count();

        return [
            'position' => $better + $same + 1,
            'total'    => $total,
            'xp'       => $profile->xp_points,
        ];
    }

    /**
     * Cuántos XP faltan para alcanzar al que está justo arriba (Local)
     */
    public function xpToNextLocal(StudentProfile $profile): ?int
    {
        $above = StudentProfile::where('classroom', $profile->classroom)
            ->where('xp_points', '>', $profile->xp_points)
            ->orderBy('xp_points')
            ->first();

        if (!$above) {
            return null;
        }

        return $above->xp_points - $profile->xp_points;
    }

    /**
     * Limpia el cache cuando alguien gana XP
     */
    public function clearCache(?string $classroom = null): void
    {
        if ($classroom) {
            for ($limit = 1; $limit <= 12; $limit++) {
                Cache::forget(
                    'ranking.local.' . md5($classroom) . '.' . $limit
                );
            }
        }

        for ($limit = 1; $limit <= 12; $limit++) {
            Cache::forget('ranking.global.' . $limit);
        }
    }

    /**
     * Transforma la colección a un formato limpio para la vista
     */
    private function buildRanking(Collection $profiles): Collection
    {
        return $profiles->values()->map(function ($profile, $index) {
            return [
                'position'   => $index + 1,
                'user_id'    => $profile->user_id,
                'nickname'   => $profile->nickname ?: ($profile->user->name ?? 'Sin nombre'),
                'avatar'     => $profile->avatar_name ?? 'default_avatar.png',
                'classroom'  => $profile->classroom,
                'xp_points'  => $profile->xp_points,
                'level'      => $profile->level,
                'rank_title' => $profile->rank_title,
            ];
        });
    }
}