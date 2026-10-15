<?php

declare(strict_types=1);

namespace App\Services\ML;

/**
 * Reglas pedagógicas alineadas con ml/scripts/asignar_etiquetas.py (R0 → F → D → B → C → A → E).
 *
 * @phpstan-type FeatureMap array<string, mixed>
 */
class PedagogicalRulesEvaluator
{
    /**
     * @param  FeatureMap  $features
     */
    public function evaluate(array $features): string
    {
        $crosswordWrongRate = $this->toFloat($this->safeGet($features, 'crossword_wrong_rate'));
        $questionsEasy = $this->toFloat($this->safeGet($features, 'questions_accuracy_easy'));
        $questionsMedium = $this->toFloat($this->safeGet($features, 'questions_accuracy_medium'));
        $questionsHard = $this->toFloat($this->safeGet($features, 'questions_accuracy_hard'));

        $crosswordWords = $this->toFloat($this->safeGet($features, 'crossword_total_words_learned'));
        $cureErrorsConcepto = $this->toFloat($this->safeGet($features, 'cure_errors_concepto'));
        $cureErrorsCalculo = $this->toFloat($this->safeGet($features, 'cure_errors_calculo'));
        $cureErrorsDecimales = $this->toFloat($this->safeGet($features, 'cure_errors_decimales'));
        $cureFormulasMastered = $this->toFloat($this->safeGet($features, 'cure_formulas_mastered_count'));
        $cureFormulasReview = $this->toFloat($this->safeGet($features, 'cure_formulas_review_count'));
        $projectileGravityChanges = $this->toFloat($this->safeGet($features, 'projectile_gravity_changes_total'));
        $gamesPlayed = $this->toFloat($this->safeGet($features, 'games_played_count'));

        $cureAvgAccuracy = $this->normPct($this->safeGet($features, 'cure_avg_accuracy'));
        $projectileAvgAccuracy = $this->normPct($this->safeGet($features, 'projectile_avg_accuracy'));
        $chartIdentify = $this->normPct($this->safeGet($features, 'chart_race_accuracy_identify'));
        $chartPredict = $this->normPct($this->safeGet($features, 'chart_race_accuracy_predict'));
        $chartAvg = $this->normPct($this->safeGet($features, 'chart_race_avg_accuracy'));
        $chartChoose = $this->normPct($this->safeGet($features, 'chart_race_accuracy_choose'));

        if ($gamesPlayed !== null && $gamesPlayed < 3) {
            return 'recomendar_practicar_mas';
        }

        if ($cureAvgAccuracy !== null && $cureAvgAccuracy > 0.80
            && $projectileAvgAccuracy !== null && $projectileAvgAccuracy > 0.70
            && $chartAvg !== null && $chartAvg > 0.70) {
            return 'avanzar_a_movimiento_circular';
        }

        if ($gamesPlayed !== null && $gamesPlayed >= 4
            && $cureAvgAccuracy !== null && $cureAvgAccuracy > 0.75
            && $questionsHard !== null && $questionsHard > 0.60) {
            return 'avanzar_a_leyes_newton';
        }

        if ($projectileAvgAccuracy !== null && $projectileAvgAccuracy < 0.40
            && $projectileGravityChanges !== null && $projectileGravityChanges >= 4) {
            return 'reforzar_relacion_gravedad_alcance';
        }

        if ($projectileAvgAccuracy !== null && $projectileAvgAccuracy < 0.40
            && $projectileGravityChanges !== null && $projectileGravityChanges < 2) {
            return 'reforzar_angulo_tiro_parabolico';
        }

        if ($projectileAvgAccuracy !== null && $projectileAvgAccuracy < 0.40
            && $projectileGravityChanges !== null && $projectileGravityChanges >= 2) {
            return 'reforzar_componentes_vx_vy';
        }

        if ($cureErrorsDecimales !== null && $cureErrorsDecimales > 2) {
            return 'reforzar_precision_decimales';
        }

        if ($cureErrorsCalculo !== null && $cureErrorsCalculo > 2
            && $cureAvgAccuracy !== null && $cureAvgAccuracy < 0.50) {
            return 'reforzar_formula_tiempo_caida_libre';
        }

        if ($cureErrorsConcepto !== null && $cureErrorsConcepto > 2
            && $cureAvgAccuracy !== null && $cureAvgAccuracy <= 0.55) {
            return 'reforzar_formula_distancia_mruv';
        }

        if ($cureFormulasReview !== null && $cureFormulasReview > 0
            && $cureAvgAccuracy !== null && $cureAvgAccuracy < 0.50) {
            return 'reforzar_formula_v_igual_d_entre_t';
        }

        if ($chartChoose !== null && $chartChoose < 0.40) {
            return 'reforzar_elegir_grafica_correcta';
        }

        if ($chartPredict !== null && $chartPredict < 0.40
            && $chartIdentify !== null && $chartIdentify > 0.60) {
            return 'reforzar_predecir_movimiento_grafica';
        }

        if ($chartPredict !== null && $chartPredict < 0.30) {
            return 'reforzar_leer_grafica_velocidad_tiempo';
        }

        if ($chartIdentify !== null && $chartIdentify < 0.40) {
            return 'reforzar_leer_grafica_posicion_tiempo';
        }

        if ($questionsHard !== null && $questionsHard < 0.30
            && $projectileGravityChanges !== null && $projectileGravityChanges < 1) {
            return 'reforzar_concepto_gravedad';
        }

        if ($cureFormulasMastered !== null && $cureFormulasMastered < 3
            && $cureAvgAccuracy !== null && $cureAvgAccuracy < 0.40) {
            return 'reforzar_concepto_aceleracion';
        }

        if (($questionsEasy !== null && $questionsEasy < 0.40)
            || ($chartIdentify !== null && $chartIdentify < 0.30)) {
            return 'reforzar_concepto_velocidad_constante';
        }

        if ($crosswordWrongRate !== null && $crosswordWrongRate > 0.50
            && $crosswordWords !== null && $crosswordWords < 3) {
            return 'reforzar_vocabulario_fisica';
        }

        if ($questionsHard !== null && $questionsHard < 0.30
            && $questionsEasy !== null && $questionsEasy > 0.60) {
            return 'reforzar_procedimiento_multipaso';
        }

        if ($questionsMedium !== null && $questionsMedium < 0.30
            && $questionsHard !== null && $questionsHard < 0.30) {
            return 'reforzar_contextualizacion_problemas';
        }

        return 'sin_recomendacion_especifica';
    }

    public function isAdvanceSlug(string $slug): bool
    {
        return str_starts_with($slug, 'avanzar_');
    }

    /**
     * Slug de refuerzo si hay una racha crítica. Null si el modelo puede decidir.
     *
     * @param  array<string, mixed>  $features
     */
    public function criticalReinforcement(array $features): ?string
    {
        $consecutive = (int) ($features['has_failed_5_consecutive'] ?? 0) === 1;
        $fiveOfTen = (int) ($features['has_failed_5_of_10'] ?? 0) === 1;
        $hard = $this->toFloat($features['questions_accuracy_hard_last_10'] ?? null);
        $hardLow = $hard !== null && $hard < 40;

        if (! $consecutive && ! $fiveOfTen && ! $hardLow) {
            return null;
        }

        $games = [
            'cure' => $this->toFloat($features['cure_accuracy_last_10'] ?? null),
            'projectile' => $this->toFloat($features['projectile_accuracy_last_10'] ?? null),
            'chart_race' => $this->toFloat($features['chart_race_accuracy_last_10'] ?? null),
            'crossword' => $this->toFloat($features['crossword_accuracy_last_10'] ?? null),
        ];

        $worst = null;
        $worstAccuracy = null;
        foreach ($games as $game => $accuracy) {
            if ($accuracy === null) {
                continue;
            }
            if ($worstAccuracy === null || $accuracy < $worstAccuracy) {
                $worstAccuracy = $accuracy;
                $worst = $game;
            }
        }

        return match ($worst) {
            'cure' => 'reforzar_concepto_velocidad_constante',
            'projectile' => 'reforzar_componentes_vx_vy',
            'chart_race' => 'reforzar_leer_grafica_posicion_tiempo',
            'crossword' => 'reforzar_vocabulario_fisica',
            default => $hardLow ? 'reforzar_procedimiento_multipaso' : null,
        };
    }

    /**
     * @param  FeatureMap  $features
     */
    private function safeGet(array $features, string $key): mixed
    {
        if (! array_key_exists($key, $features)) {
            return null;
        }

        $value = $features[$key];
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return $value;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return (float) (int) $value;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function normPct(mixed $value): ?float
    {
        $v = $this->toFloat($value);
        if ($v === null) {
            return null;
        }

        if ($v > 1.5) {
            return $v / 100.0;
        }

        return $v;
    }
}
