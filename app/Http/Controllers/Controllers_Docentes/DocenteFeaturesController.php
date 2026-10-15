<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Docentes;

use App\Http\Controllers\Controller;
use App\Models\StudentFeatureSnapshot;
use App\Models\User;
use App\Services\ML\FeatureExtractorService;
use App\Support\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocenteFeaturesController extends Controller
{
    public function index(Request $request): View
    {
        $query = StudentFeatureSnapshot::query()
            ->latestFirst()
            ->with(['user.studentProfile']);

        if ($request->filled('classroom')) {
            $canonical = Classroom::fromSlug($request->string('classroom')->toString());
            $query->forClassroom($canonical);
        }

        $snapshots = $query->paginate(25)->withQueryString();

        return view('docente.features.index', [
            'snapshots' => $snapshots,
            'classroomFilter' => $request->string('classroom')->toString(),
            'classrooms' => Classroom::all(),
        ]);
    }

    public function export(): StreamedResponse
    {
        $latestIds = StudentFeatureSnapshot::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');

        $rows = StudentFeatureSnapshot::query()
            ->whereIn('id', $latestIds)
            ->with('user')
            ->orderBy('user_id')
            ->get();

        $featureKeys = FeatureExtractorService::featureKeysV1();
        $filename = 'student_features_'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows, $featureKeys) {
            $out = fopen('php://output', 'w');
            $header = array_merge(['user_id', 'name', 'classroom', 'snapshot_date'], $featureKeys);
            fputcsv($out, $header);

            foreach ($rows as $snapshot) {
                $features = $snapshot->features_json ?? [];
                $line = [
                    $snapshot->user_id,
                    $snapshot->user?->name ?? '',
                    $snapshot->classroom ?? '',
                    $snapshot->snapshot_date?->format('Y-m-d') ?? '',
                ];
                foreach ($featureKeys as $key) {
                    $val = $features[$key] ?? '';
                    $line[] = $val === null ? '' : $val;
                }
                fputcsv($out, $line);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function recalculate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);
        abort_unless($user->isStudent(), 422);

        Artisan::call('features:extract', ['--user' => $user->id]);

        return back()->with('success', "Features recalculadas para {$user->name}.");
    }
}
