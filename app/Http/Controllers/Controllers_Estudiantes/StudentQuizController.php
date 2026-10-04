<?php

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Http\Requests\Questions\SubmitHardQuestionRequest;
use App\Http\Requests\Questions\SubmitPuzzleAnswerRequest;
use App\Http\Requests\Questions\SubmitQuestionAnswerRequest;
use App\Services\Questions\HardQuestionEvaluationService;
use App\Services\Questions\RecordStudentAnswerService;
use App\Services\Questions\StepPuzzleService;
use App\Services\Analytics\LearningLogSyncService;
use App\Models\StudentQuestionAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentQuizController extends Controller
{
    public function __construct(
        private readonly RecordStudentAnswerService $answers,
        private readonly StepPuzzleService $puzzleService,
        private readonly HardQuestionEvaluationService $hardEvaluation
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Obtener las preguntas que ya respondió correctamente
        $questionIdsCorrectas = \DB::table('student_question_answers')
            ->where('student_id', $user->id)
            ->where('is_correct', true)
            ->pluck('question_id');

        // 2. Obtener el ID de la última pregunta que vio el estudiante (guardado en sesión)
        $lastQuestionId = $request->session()->get('last_seen_question_id');

        // 3. Preparar la consulta principal
        $query = Question::where('is_active', true)
            ->whereNotIn('id', $questionIdsCorrectas);

        // 4. Si hay una pregunta anterior, la excluimos temporalmente para no repetirla
        if ($lastQuestionId) {
            $query->where('id', '!=', $lastQuestionId);
        }

        // 5. Buscar una pregunta al azar
        $question = $query->with(['topic', 'options'])->inRandomOrder()->first();

        // 6. Si no encontró ninguna (porque la que excluimos era la ÚNICA que quedaba libre), la mostramos igual
        if (!$question && $lastQuestionId) {
            $question = Question::where('is_active', true)
                ->whereNotIn('id', $questionIdsCorrectas)
                ->with(['topic', 'options'])
                ->inRandomOrder()
                ->first();
        }

        // 7. Guardar la nueva pregunta en sesión para la próxima recarga
        if ($question) {
            $request->session()->put('last_seen_question_id', $question->id);
        } else {
            $request->session()->forget('last_seen_question_id');
        }

        return view('estudiante.preguntas.index', compact('question'));
    }

    // Usamos Request estándar aquí para decidir la validación dinámicamente
    public function answer(Request $baseRequest, Question $question): RedirectResponse
    {
        $question->load('topic');

        try {
            if ($question->difficulty->value === 'Medio') {
                $request = app(SubmitPuzzleAnswerRequest::class);

                $answer = $this->puzzleService->evaluate(
                    $request->user(),
                    $question,
                    $request->validated('ordered_option_ids'),
                    $request->integer('time_taken')
                );
            } elseif ($question->difficulty->value === 'Difícil') {
                $request = app(SubmitHardQuestionRequest::class);

                $answer = $this->hardEvaluation->evaluate(
                    $request->user(),
                    $question,
                    $request->validated('step_answers'),
                    $request->integer('time_taken')
                );
            } else {
                $request = app(SubmitQuestionAnswerRequest::class);

                $answer = $this->answers->record(
                    $request->user(),
                    $question,
                    $request->option(),
                    $request->integer('time_taken')
                );
            }
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        // Generar mensaje dinámico
        if ($answer->is_correct) {
            $message = "¡Respuesta correcta! Ganaste +{$answer->xp_earned} XP.";
            if ($answer->xp_earned > $question->xp_reward) {
                $message = "⚡ ¡IMPRESIONANTE! Ganaste +{$answer->xp_earned} XP (Bono de velocidad incluido).";
            }
        } else {
            $message = 'Respuesta incorrecta. Has perdido la oportunidad de ganar XP con esta pregunta.';
        }

        return redirect()
            ->route('estudiante.preguntas.index')
            ->with('gamification_status', $message);
    }
    public function skip(Request $request, Question $question, \App\Services\Analytics\LearningLogSyncService $logSync): RedirectResponse
    {
        // Capturamos el tiempo exacto en segundos que el estudiante dudó antes de saltar
        $timeTaken = $request->integer('time_taken');

        // 1. Guardamos el intento explícitamente como "saltado"
        \App\Models\StudentQuestionAnswer::create([
            'student_id' => $request->user()->id,
            'question_id' => $question->id,
            // Asignamos la primera opción de la pregunta por defecto para que la BD no falle por campo nulo
            'question_option_id' => $question->options()->first()?->id, 
            'is_correct' => false,
            'is_skipped' => true,
            'xp_earned' => 0,
            'answered_at' => now(),
        ]);

        // 2. Sincronizamos la estadística para el algoritmo C4.5
        // AHORA ENVIAMOS EL TIEMPO REAL ($timeTaken) en lugar de 0
        $logSync->syncForQuestion($request->user(), $question, $timeTaken);

        // 3. Forzamos a que la próxima pregunta aleatoria no sea esta misma
        $request->session()->put('last_seen_question_id', $question->id);

        // 4. Redirigimos a la siguiente pregunta con un mensaje de feedback
        return redirect()
            ->route('estudiante.preguntas.index')
            ->with('gamification_status', 'Misión omitida. No has ganado XP, ¡pero inténtalo en la siguiente!');
    }
}