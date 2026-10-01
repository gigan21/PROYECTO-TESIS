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
            ->when($request->filled('topic_id'), fn ($query) => $query->where('topic_id', $request->integer('topic_id')))
            ->when($request->filled('difficulty'), fn ($query) => $query->where('difficulty', $request->string('difficulty')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('docente.preguntas.index', [
            'questions' => $questions,
            'topics' => Topic::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Question::class);

        return view('docente.preguntas.create', $this->formData());
    }

    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        $this->authorize('create', Question::class);

        $question = $this->persistQuestion(new Question, $request->validated());

        return redirect()
            ->route('docente.preguntas.show', $question)
            ->with('status', 'Pregunta creada correctamente.');
    }

    public function show(Question $question): View
    {
        $this->authorize('view', $question);

        $question->load(['topic', 'options']);

        return view('docente.preguntas.show', compact('question'));
    }

    public function edit(Question $question): View
    {
        $this->authorize('update', $question);

        $question->load('options');

        return view('docente.preguntas.edit', array_merge($this->formData(), [
            'question' => $question,
        ]));
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        $this->authorize('update', $question);

        $this->persistQuestion($question, $request->validated());

        return redirect()
            ->route('docente.preguntas.show', $question)
            ->with('status', 'Pregunta actualizada correctamente.');
    }

    public function toggle(Question $question): RedirectResponse
    {
        $this->authorize('update', $question);

        $question->update(['is_active' => ! $question->is_active]);

        $estado = $question->is_active ? 'activada' : 'desactivada';

        return back()->with('status', "Pregunta {$estado}.");
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persistQuestion(Question $question, array $data): Question
    {
        return DB::transaction(function () use ($question, $data) {
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
                // Si ya había una foto antes (al editar), la borramos para no ocupar espacio
                if ($question->image_path) {
                    Storage::disk('public')->delete($question->image_path);
                }
                // Guardamos la nueva en la carpeta 'preguntas'
                $question->image_path = $data['image']->store('preguntas', 'public');
            }
            $question->save();

            $difficultyValue = $data['difficulty'] instanceof QuestionDifficulty
                ? $data['difficulty']->value
                : (string) $data['difficulty'];

            $this->optionsPersistence->syncForDifficulty(
                $question,
                $difficultyValue,
                $data['options'],
                isset($data['correct_option']) ? (int) $data['correct_option'] : null
            );

            return $question;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'topics' => Topic::query()->orderBy('name')->get(),
            'difficulties' => QuestionDifficulty::cases(),
            'types' => QuestionType::cases(),
        ];
    }
}
