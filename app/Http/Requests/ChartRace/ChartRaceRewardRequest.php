<?php

declare(strict_types=1);

namespace App\Http\Requests\ChartRace;

use Illuminate\Foundation\Http\FormRequest;

class ChartRaceRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() === true;
    }

    public function rules(): array
    {
        return [
            'telemetry' => ['required', 'array'],
            'telemetry.sessionId' => ['required', 'string', 'max:64'],
            'telemetry.hits' => ['nullable', 'integer', 'min:0', 'max:6'],
            'telemetry.misses' => ['nullable', 'integer', 'min:0', 'max:6'],
            'telemetry.accuracy' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'telemetry.exploded' => ['nullable', 'boolean'],
            'telemetry.completed' => ['nullable', 'boolean'],
            'telemetry.won' => ['nullable', 'boolean'],
            'telemetry.totalTimeSec' => ['nullable', 'integer', 'min:0'],
            'telemetry.weakAreas' => ['nullable', 'array'],
            'telemetry.weakAreas.*' => ['string'],
            'telemetry.strongAreas' => ['nullable', 'array'],
            'telemetry.strongAreas.*' => ['string'],
            'telemetry.byTypeAccuracy' => ['nullable', 'array'],
            'telemetry.byGraphKindAccuracy' => ['nullable', 'array'],
            'telemetry.events' => ['nullable', 'array'],
            'telemetry.events.*.round_number' => ['nullable', 'integer', 'min:1', 'max:6'],
            'telemetry.events.*.question_type' => ['nullable', 'string', 'max:20'],
            'telemetry.events.*.graph_kind' => ['nullable', 'string', 'max:10'],
            'telemetry.events.*.graph_type' => ['nullable', 'string', 'max:32'],
            'telemetry.events.*.topic' => ['nullable', 'string', 'max:120'],
            'telemetry.events.*.correct' => ['nullable', 'boolean'],
            'telemetry.events.*.answered_in_seconds' => ['nullable', 'numeric', 'min:0'],
            'telemetry.events.*.timed_out' => ['nullable', 'boolean'],
            'telemetry.events.*.wrong_answer' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();
        if (isset($validated['telemetry']) && is_array($validated['telemetry'])) {
            $validated['telemetry'] = $this->input('telemetry', []);
        }
        if ($key !== null) {
            return data_get($validated, $key, $default);
        }

        return $validated;
    }
}
