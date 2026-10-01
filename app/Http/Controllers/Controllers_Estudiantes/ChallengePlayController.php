<?php

namespace App\Http\Controllers\Controllers_Estudiantes;

use App\Enums\ChallengeRoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Challenges\SubmitChallengeAnswerRequest;
use App\Models\ChallengeAnswer;
use App\Models\ChallengeRoom;
use App\Models\Question;
use App\Services\Challenges\ChallengeAccessService;
use App\Services\Challenges\RecordChallengeAnswerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ChallengePlayController extends Controller
{
    public function __construct(
        private readonly ChallengeAccessService $access,
        private readonly RecordChallengeAnswerService $answers
    ) {}
    public function enter(): View
    {
        return view('estudiante.desafios.enter');
    }
    
    public function show(Request $request, string $code): View|RedirectResponse
    {
        $room = ChallengeRoom::query()
            ->where('code', strtoupper(trim($code)))
            ->with(['questions' => fn ($q) => $q->with('options', 'topic')])
            ->firstOrFail();

        if ($room->status === ChallengeRoomStatus::Waiting) {
            return view('estudiante.desafios.waiting', compact('room'));
        }

        if ($room->status === ChallengeRoomStatus::Finished) {
            return view('estudiante.desafios.finished', [
                'room' => $room,
                'summary' => $this->studentSummary($request->user(), $room),
            ]);
        }

        try {
            $this->access->assertStudentCanJoin($request->user(), $room);
        } catch (AccessDeniedHttpException $exception) {
            return view('estudiante.desafios.denied', [
                'room' => $room,
                'message' => $exception->getMessage(),
            ]);
        }

        $answeredIds = ChallengeAnswer::query()
            ->where('challenge_room_id', $room->id)
            ->where('student_id', $request->user()->id)
            ->pluck('question_id');

        $pendingQuestions = $room->questions->whereNotIn('id', $answeredIds->all());

        return view('estudiante.desafios.play', [
            'room' => $room,
            'questions' => $pendingQuestions->values(),
            'answeredCount' => $answeredIds->count(),
            'totalCount' => $room->questions->count(),
        ]);
    }

    public function answer(SubmitChallengeAnswerRequest $request, string $code, Question $question): RedirectResponse
    {
        $room = ChallengeRoom::query()
            ->where('code', strtoupper(trim($code)))
            ->firstOrFail();

        try {
            $answer = $this->answers->record(
                $request->user(),
                $room,
                $question,
                $request->option(),
                (int) $request->validated('response_time_seconds')
            );
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['question_option_id' => $exception->getMessage()]);
        } catch (AccessDeniedHttpException $exception) {
            return back()->withErrors(['question_option_id' => $exception->getMessage()]);
        }

        $message = $answer->is_correct
            ? "¡Correcto! +{$answer->xp_earned} XP ({$answer->response_time_seconds}s)."
            : "Incorrecto. Tiempo: {$answer->response_time_seconds}s.";

        return redirect()
            ->route('estudiante.desafio.show', $room->code)
            ->with('gamification_status', $message);
    }

    /**
     * @return array{answered: int, correct: int, xp: int}|null
     */
    private function studentSummary(?\App\Models\User $user, ChallengeRoom $room): ?array
    {
        if (! $user?->isEstudiante()) {
            return null;
        }

        $items = ChallengeAnswer::query()
            ->where('challenge_room_id', $room->id)
            ->where('student_id', $user->id)
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        return [
            'answered' => $items->count(),
            'correct' => $items->where('is_correct', true)->count(),
            'xp' => $items->sum('xp_earned'),
        ];
    }
}
