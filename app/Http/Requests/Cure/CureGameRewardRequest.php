<?php

declare(strict_types=1);

namespace App\Http\Requests\Cure;

use Illuminate\Foundation\Http\FormRequest;

class CureGameRewardRequest extends FormRequest
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
            'telemetry.levelReached' => ['nullable', 'integer', 'min:0', 'max:4'],
            'telemetry.nivelesCompletados' => ['nullable', 'integer', 'min:0', 'max:4'],
            'telemetry.attempts' => ['nullable', 'integer', 'min:0'],
            'telemetry.hits' => ['nullable', 'integer', 'min:0'],
            'telemetry.misses' => ['nullable', 'integer', 'min:0'],
            'telemetry.accuracy' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'telemetry.score' => ['nullable', 'integer', 'min:0'],
            'telemetry.rank' => ['nullable', 'string', 'max:32'],
            'telemetry.maxStreak' => ['nullable', 'integer', 'min:0'],
            'telemetry.won' => ['nullable', 'boolean'],
            'telemetry.finished' => ['nullable', 'boolean'],
            'telemetry.abandoned' => ['nullable', 'boolean'],
             'telemetry.levelCompleted' => ['nullable', 'integer'],
            'telemetry.pistaUsadas' => ['nullable', 'integer', 'min:0'],
            'telemetry.totalTimeSec' => ['nullable', 'integer', 'min:0'],
            'telemetry.formulasMastered' => ['nullable', 'array'],
            'telemetry.formulasMastered.*' => ['string'],
            'telemetry.formulasReview' => ['nullable', 'array'],
            'telemetry.formulasReview.*' => ['string'],
            'telemetry.errorsByType' => ['nullable', 'array'],
            'telemetry.errorsByType.concepto' => ['nullable', 'integer', 'min:0'],
            'telemetry.errorsByType.calculo' => ['nullable', 'integer', 'min:0'],
            'telemetry.errorsByType.decimales' => ['nullable', 'integer', 'min:0'],
            'telemetry.levels' => ['nullable', 'array'],
            'telemetry.levels.*.level' => ['nullable', 'integer'],
            'telemetry.levels.*.timeMs' => ['nullable', 'integer'],
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