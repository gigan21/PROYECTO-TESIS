<?php

namespace App\Http\Controllers\Controllers_Docentes;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Questions\StoreQuestionRequest;
use App\Http\Requests\Questions\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use App\Models\StudentQuestionAnswer;
use App\Services\Questions\QuestionOptionsPersistenceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly QuestionOptionsPersistenceService $optionsPersistence
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Question::class);

        $questions = Question::query()
            ->with('topic')
            ->when(
                $request->filled('topic_id'),
                fn ($query) => $query->where(
                    'topic_id',
                    $request->integer('topic_id')
                )
            )
            ->when(
                $request->filled('difficulty'),
                fn ($query) => $query->where(
                    'difficulty',
                    $request->string('difficulty')
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        /*
         * Estudiantes disponibles para el restablecimiento
         * de una pregunta específica.
         */
        $students = User::query()
            ->where('role', 'estudiante')
            ->with('studentProfile')
            ->orderBy('name')
            ->get();

        return view('docente.preguntas.index', [
            'questions' => $questions,
            'topics' => Topic::query()
                ->orderBy('name')
                ->get(),
            'students' => $students,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Question::class);

        return view(
            'docente.preguntas.create',
            $this->formData()
        );
    }

    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        $this->authorize('create', Question::class);

        $question = $this->persistQuestion(
            new Question,
            $request->validated()
        );

        return redirect()
            ->route('docente.preguntas.show', $question)
            ->with('status', 'Pregunta creada correctamente.');
    }

    public function show(Question $question): View
    {
        $this->authorize('view', $question);

        $question->load([
            'topic',
            'options',
        ]);

        return view(
            'docente.preguntas.show',
            compact('question')
        );
    }

    public function edit(Question $question): View
    {
        $this->authorize('update', $question);

        $question->load('options');

        return view(
            'docente.preguntas.edit',
            array_merge(
                $this->formData(),
                [
                    'question' => $question,
                ]
            )
        );
    }

    public function update(
        UpdateQuestionRequest $request,
        Question $question
    ): RedirectResponse {
        $this->authorize('update', $question);

        $this->persistQuestion(
            $question,
            $request->validated()
        );

        return redirect()
            ->route('docente.preguntas.show', $question)
            ->with('status', 'Pregunta actualizada correctamente.');
    }

    public function toggle(Question $question): RedirectResponse
    {
        $this->authorize('update', $question);

        $question->update([
            'is_active' => ! $question->is_active,
        ]);

        $estado = $question->is_active
            ? 'activada'
            : 'desactivada';

        return back()->with(
            'status',
            "Pregunta {$estado}."
        );
    }

    private function persistQuestion(
        Question $question,
        array $data
    ): Question {
        return DB::transaction(function () use (
            $question,
            $data
        ) {
            $question->fill([
                'topic_id' => $data['topic_id'],
                'difficulty' => $data['difficulty'],
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'xp_reward' => $data['xp_reward'],
                'time_limit_seconds' => $data['time_limit_seconds'] ?? 60,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (isset($data['image'])) {

                // Si ya había una imagen, eliminarla.
                if ($question->image_path) {
                    Storage::disk('public')->delete(
                        $question->image_path
                    );
                }

                // Guardar nueva imagen.
                $question->image_path = $data['image']
                    ->store('preguntas', 'public');
            }

            $question->save();

            $difficultyValue = $data['difficulty'] instanceof QuestionDifficulty
                ? $data['difficulty']->value
                : (string) $data['difficulty'];

            $this->optionsPersistence->syncForDifficulty(
                $question,
                $difficultyValue,
                $data['options'],
                isset($data['correct_option'])
                    ? (int) $data['correct_option']
                    : null
            );

            return $question;
        });
    }

    private function formData(): array
    {
        return [
            'topics' => Topic::query()
                ->orderBy('name')
                ->get(),

            'difficulties' => QuestionDifficulty::cases(),

            'types' => QuestionType::cases(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RESTABLECER PREGUNTAS
    |--------------------------------------------------------------------------
    */

    /**
     * Pantalla de restablecimiento general.
     */
    public function resetIndex(Request $request): View
    {
        $this->authorize(
            'viewAny',
            Question::class
        );

        $classrooms = \App\Models\StudentProfile::query()
            ->select('classroom')
            ->whereNotNull('classroom')
            ->distinct()
            ->orderBy('classroom')
            ->pluck('classroom');

        $students = User::query()
            ->where('role', 'estudiante')
            ->whereHas(
                'studentProfile',
                function ($q) use ($request) {
                    if ($request->filled('classroom')) {
                        $q->where(
                            'classroom',
                            $request->string('classroom')
                        );
                    }
                }
            )
            ->with('studentProfile')
            ->orderBy('name')
            ->get();

        return view(
            'docente.preguntas.ResetQuestions.reset',
            compact(
                'classrooms',
                'students'
            )
        );
    }

    /**
     * Restablece TODAS las preguntas de un estudiante.
     */
    public function resetStudent(
        User $student
    ): RedirectResponse {
        $this->authorize(
            'viewAny',
            Question::class
        );

        StudentQuestionAnswer::where(
            'student_id',
            $student->id
        )->delete();

        return back()->with(
            'status',
            "Se han restablecido todas las preguntas para el estudiante: {$student->name}."
        );
    }

    /**
     * Restablece las preguntas para un paralelo
     * o para todos los estudiantes.
     */
    public function resetBulk(
        Request $request
    ): RedirectResponse {
        $this->authorize(
            'viewAny',
            Question::class
        );

        $classroom = $request->input('classroom');

        if ($classroom) {

            $studentIds = User::query()
                ->where('role', 'estudiante')
                ->whereHas(
                    'studentProfile',
                    fn ($q) => $q->where(
                        'classroom',
                        $classroom
                    )
                )
                ->pluck('id');

            StudentQuestionAnswer::whereIn(
                'student_id',
                $studentIds
            )->delete();

            $msg = "Se han restablecido las preguntas para todos los estudiantes del paralelo {$classroom}.";

        } else {

            StudentQuestionAnswer::query()->delete();

            $msg = "Se han restablecido las preguntas para TODOS los estudiantes del sistema.";
        }

        return back()->with(
            'status',
            $msg
        );
    }

    /**
     * Restablece UNA pregunta:
     *
     * - Si llega student_id:
     *   solamente para ese estudiante.
     *
     * - Si NO llega student_id:
     *   para todos los estudiantes.
     */
    public function resetSingleQuestion(
        Request $request,
        Question $question
    ): RedirectResponse {
        $this->authorize(
            'update',
            $question
        );

        $studentId = $request->input('student_id');

        if ($studentId) {

            StudentQuestionAnswer::where(
                'question_id',
                $question->id
            )
                ->where(
                    'student_id',
                    $studentId
                )
                ->delete();

            $studentName = User::find($studentId)?->name
                ?? 'el estudiante';

            $msg = "Pregunta restablecida correctamente para {$studentName}.";

        } else {

            StudentQuestionAnswer::where(
                'question_id',
                $question->id
            )->delete();

            $msg = "Pregunta restablecida para todos los estudiantes.";
        }

        return back()->with(
            'status',
            $msg
        );
    }
}