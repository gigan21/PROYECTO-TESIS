<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Docentes;

use App\Enums\LearningGameType;
use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\Analytics\StudentCardPresenter;
use App\Support\Classroom;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DocenteStudentsController extends Controller
{
    public function index(Request $request, StudentCardPresenter $presenter): View
    {
        $classroomOptions = $this->classroomSelectOptions();
        $selected = $this->resolveSelectedClassroom($request->query('classroom'), $classroomOptions);

        $students = User::query()
            ->where('role', 'estudiante')
            ->whereHas('studentProfile', fn ($q) => $q->whereIn('classroom', Classroom::storedValuesFor($selected)))
            ->with([
                'studentProfile',
                'learningLogs' => fn ($q) => $q->where('game_type', LearningGameType::Challenge->value),
                'problemQuestionAnswers' => fn ($q) => $q
                    ->where(fn ($w) => $w->where('is_correct', false)->orWhere('is_skipped', true))
                    ->with(['question:id,question_text,difficulty,topic_id']),
            ])
            ->orderBy('name')
            ->get();

        return view('docente.estudiantes.index', [
            'classroomOptions' => $classroomOptions,
            'selectedClassroom' => $selected,
            'cards' => $presenter->forStudents($students),
        ]);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function classroomSelectOptions(): array
    {
        $stored = StudentProfile::query()
            ->whereNotNull('classroom')
            ->distinct()
            ->pluck('classroom');

        $canonical = [];
        foreach ($stored as $value) {
            try {
                $normalized = Classroom::normalize((string) $value);
            } catch (InvalidArgumentException) {
                continue;
            }
            $canonical[$normalized] = $normalized;
        }

        if ($canonical === []) {
            foreach (Classroom::all() as $item) {
                $canonical[$item] = $item;
            }
        }

        ksort($canonical);

        return array_map(
            fn (string $label) => ['value' => $label, 'label' => $label],
            array_values($canonical)
        );
    }

    /**
     * @param  list<array{value: string, label: string}>  $options
     */
    private function resolveSelectedClassroom(?string $requested, array $options): string
    {
        $allowed = array_column($options, 'value');

        if ($requested !== null) {
            try {
                $normalized = Classroom::normalize($requested);
                if (in_array($normalized, $allowed, true)) {
                    return $normalized;
                }
            } catch (InvalidArgumentException) {
                // fallback below
            }
        }

        return $allowed[0] ?? Classroom::all()[0];
    }
}
