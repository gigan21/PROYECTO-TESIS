<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Docentes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crossword\StoreCrosswordWordRequest;
use App\Http\Requests\Crossword\UpdateCrosswordWordRequest;
use App\Models\CrosswordEvent;
use App\Models\CrosswordWord;
use App\Models\Topic;
use App\Services\Crossword\CrosswordWordService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrosswordWordController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CrosswordWordService $words
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CrosswordWord::class);

        return view('docente.crucigrama.index', [
            'words' => $this->words->getAllForTeacher($request->user(), $request->only([
                'difficulty', 'level', 'topic_id', 'search', 'is_active',
            ])),
            'topics' => Topic::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CrosswordWord::class);

        return view('docente.crucigrama.create', ['topics' => Topic::query()->orderBy('name')->get()]);
    }

    public function store(StoreCrosswordWordRequest $request): RedirectResponse
    {
        $this->authorize('create', CrosswordWord::class);
        $this->words->createWord($request->validated(), $request->user());

        return redirect()->route('docente.crucigrama.index')->with('status', 'Palabra creada correctamente.');
    }

    public function show(CrosswordWord $crosswordWord): View
    {
        $this->authorize('view', $crosswordWord);

        $stats = [
            'success_count' => CrosswordEvent::query()->where('crossword_word_id', $crosswordWord->id)->where('was_correct', true)->count(),
            'attempt_count' => CrosswordEvent::query()->where('crossword_word_id', $crosswordWord->id)->count(),
            'avg_time' => (int) round((float) CrosswordEvent::query()
                ->where('crossword_word_id', $crosswordWord->id)
                ->where('was_correct', true)
                ->whereNotNull('time_spent_seconds')
                ->avg('time_spent_seconds')),
        ];

        return view('docente.crucigrama.show', compact('crosswordWord', 'stats'));
    }

    public function edit(CrosswordWord $crosswordWord): View
    {
        $this->authorize('update', $crosswordWord);

        return view('docente.crucigrama.edit', [
            'word' => $crosswordWord,
            'topics' => Topic::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateCrosswordWordRequest $request, CrosswordWord $crosswordWord): RedirectResponse
    {
        $this->authorize('update', $crosswordWord);
        $this->words->updateWord($crosswordWord, $request->validated());

        return redirect()->route('docente.crucigrama.index')->with('status', 'Palabra actualizada.');
    }

    public function destroy(CrosswordWord $crosswordWord): RedirectResponse
    {
        $this->authorize('delete', $crosswordWord);
        $this->words->deleteWord($crosswordWord);

        return redirect()->route('docente.crucigrama.index')->with('status', 'Palabra eliminada.');
    }
}
