<?php

namespace App\Http\Controllers\Controllers_Docentes;

use App\Enums\ChallengeRoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Challenges\StoreChallengeRoomRequest;
use App\Models\ChallengeAnswer;
use App\Models\ChallengeRoom;
use App\Models\Question;
use App\Services\Challenges\ChallengeCodeGenerator;
use App\Support\Classroom;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChallengeRoomController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ChallengeCodeGenerator $codes
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ChallengeRoom::class);

        $rooms = ChallengeRoom::query()
            ->where('teacher_id', $request->user()->id)
            ->withCount('questions')
            ->latest()
            ->paginate(15);

        return view('docente.desafios.index', compact('rooms'));
    }

    public function create(): View
    {
        $this->authorize('create', ChallengeRoom::class);

        return view('docente.desafios.create', [
            'classrooms' => Classroom::all(),
            'questions' => Question::query()->active()->with('topic')->orderBy('id')->get(),
        ]);
    }

    public function store(StoreChallengeRoomRequest $request): RedirectResponse
    {
        $this->authorize('create', ChallengeRoom::class);

        $room = DB::transaction(function () use ($request) {
            $room = ChallengeRoom::query()->create([
                'teacher_id' => $request->user()->id,
                'classroom' => $request->validated('classroom'),
                'title' => $request->validated('title'),
                'code' => $this->codes->generate(),
                'status' => ChallengeRoomStatus::Waiting,
            ]);

            $sync = [];
            foreach (array_values($request->validated('question_ids')) as $index => $questionId) {
                $sync[$questionId] = ['sort_order' => $index + 1];
            }
            $room->questions()->sync($sync);

            return $room;
        });

        return redirect()
            ->route('docente.desafios.show', $room)
            ->with('status', 'Desafío creado. Comparte el enlace con tu paralelo.');
    }

    public function show(ChallengeRoom $challengeRoom): View
    {
        $this->authorize('view', $challengeRoom);

        $challengeRoom->load(['questions.topic']);

        $joinUrl = route('estudiante.desafio.show', $challengeRoom->code);

        $answers = ChallengeAnswer::query()
            ->where('challenge_room_id', $challengeRoom->id)
            ->with(['student.studentProfile', 'question'])
            ->orderBy('answered_at')
            ->get();

        $participants = $answers
            ->groupBy('student_id')
            ->map(function ($items) {
                $student = $items->first()->student;

                return [
                    'student' => $student,
                    'answered' => $items->count(),
                    'correct' => $items->where('is_correct', true)->count(),
                    'xp' => $items->sum('xp_earned'),
                    'avg_time' => (int) round($items->avg('response_time_seconds')),
                ];
            })
            ->values();

        return view('docente.desafios.show', [
            'room' => $challengeRoom,
            'joinUrl' => $joinUrl,
            'participants' => $participants,
            'answers' => $answers,
        ]);
    }

    public function start(ChallengeRoom $challengeRoom): RedirectResponse
    {
        $this->authorize('update', $challengeRoom);

        if ($challengeRoom->status === ChallengeRoomStatus::Finished) {
            return back()->withErrors(['room' => 'No se puede reactivar un desafío finalizado.']);
        }

        $challengeRoom->update([
            'status' => ChallengeRoomStatus::Active,
            'starts_at' => $challengeRoom->starts_at ?? now(),
        ]);

        return back()->with('status', 'Desafío activado. Los estudiantes ya pueden participar.');
    }

    public function finish(ChallengeRoom $challengeRoom): RedirectResponse
    {
        $this->authorize('update', $challengeRoom);

        $challengeRoom->update([
            'status' => ChallengeRoomStatus::Finished,
            'ends_at' => now(),
        ]);

        return back()->with('status', 'Desafío finalizado.');
    }
}
