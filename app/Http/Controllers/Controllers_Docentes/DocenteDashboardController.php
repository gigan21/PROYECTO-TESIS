<?php

declare(strict_types=1);

namespace App\Http\Controllers\Controllers_Docentes;

use App\Http\Controllers\Controller;
use App\Services\Analytics\LearningLogAnalyticsService;
use Illuminate\View\View;

class DocenteDashboardController extends Controller
{
    public function index(LearningLogAnalyticsService $analytics): View
    {
        $data = $analytics->summarize();

        return view('docente.dashboard', [
            'summary' => $data['global'],
            'charts' => $analytics->chartPayload($data),
            'topicGameRows' => $data['by_topic_game'],
        ]);
    }
}
