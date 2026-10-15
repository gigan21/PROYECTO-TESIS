"""
asignar_etiquetas.py
Asigna una etiqueta de recomendación a cada estudiante basándose en las reglas
pedagógicas del sistema SistemaGamificado.

IMPORTANTE: las features de "accuracy" vienen en escala 0-100, y las de "rate"
en escala 0-1. Este script NORMALIZA todo a 0-1 antes de evaluar.
"""

import pandas as pd
from pathlib import Path

# ============================================================================
# CONFIGURACIÓN
# ============================================================================

BASE_DIR = Path(__file__).resolve().parent.parent
INPUT_CSV = BASE_DIR / "data" / "student_features_2026-10-13.csv"
OUTPUT_CSV = BASE_DIR / "data" / "student_features_labeled.csv"

# ============================================================================
# FUNCIONES AUXILIARES
# ============================================================================

def safe_get(row, key, default=None):
    """Devuelve el valor de una feature, tratando NaN como None."""
    value = row.get(key, default)
    if pd.isna(value):
        return None
    return value


def to_float(value):
    """Convierte a float si se puede, si no devuelve None."""
    if value is None or pd.isna(value):
        return None
    try:
        return float(value)
    except (ValueError, TypeError):
        return None


def norm_pct(value):
    """Normaliza un valor de 0-100 a 0-1. Si ya está en 0-1, lo deja igual.
    Heurística: si el valor es > 1.5, se asume escala 0-100.
    """
    v = to_float(value)
    if v is None:
        return None
    if v > 1.5:
        return v / 100.0
    return v


# ============================================================================
# REGLAS DE ETIQUETADO
# Orden: R0 → F → D → B → C → A → E
# Se aplica la PRIMERA que cumpla.
# Todos los umbrales están en escala 0-1.
# ============================================================================

def evaluar_reglas(row):

    # ------------------------------------------------------------------------
    # Extracción + normalización
    # ------------------------------------------------------------------------
    # Ya en 0-1
    crossword_wrong_rate = to_float(safe_get(row, 'crossword_wrong_rate'))
    questions_easy = to_float(safe_get(row, 'questions_accuracy_easy'))
    questions_medium = to_float(safe_get(row, 'questions_accuracy_medium'))
    questions_hard = to_float(safe_get(row, 'questions_accuracy_hard'))

    # Conteos (int)
    crossword_words = to_float(safe_get(row, 'crossword_total_words_learned'))
    cure_errors_concepto = to_float(safe_get(row, 'cure_errors_concepto'))
    cure_errors_calculo = to_float(safe_get(row, 'cure_errors_calculo'))
    cure_errors_decimales = to_float(safe_get(row, 'cure_errors_decimales'))
    cure_formulas_mastered = to_float(safe_get(row, 'cure_formulas_mastered_count'))
    cure_formulas_review = to_float(safe_get(row, 'cure_formulas_review_count'))
    projectile_gravity_changes = to_float(safe_get(row, 'projectile_gravity_changes_total'))
    games_played = to_float(safe_get(row, 'games_played_count'))

    # Normalizar de 0-100 a 0-1
    cure_avg_accuracy = norm_pct(safe_get(row, 'cure_avg_accuracy'))
    projectile_avg_accuracy = norm_pct(safe_get(row, 'projectile_avg_accuracy'))
    chart_identify = norm_pct(safe_get(row, 'chart_race_accuracy_identify'))
    chart_predict = norm_pct(safe_get(row, 'chart_race_accuracy_predict'))
    chart_avg = norm_pct(safe_get(row, 'chart_race_avg_accuracy'))
    chart_choose = norm_pct(safe_get(row, 'chart_race_accuracy_choose'))

    # ========================================================================
    # GRUPO 0 — Prioridad máxima: practicar más
    # ========================================================================

    # R0: recomendar_practicar_mas
    if games_played is not None and games_played < 3:
        return 'recomendar_practicar_mas'

    # ========================================================================
    # GRUPO F — Avanzadas
    # ========================================================================

    # F1: avanzar_a_movimiento_circular
    if (cure_avg_accuracy is not None and cure_avg_accuracy > 0.80 and
            projectile_avg_accuracy is not None and projectile_avg_accuracy > 0.70 and
            chart_avg is not None and chart_avg > 0.70):
        return 'avanzar_a_movimiento_circular'

    # F2: avanzar_a_leyes_newton
    if (games_played is not None and games_played >= 4 and
            cure_avg_accuracy is not None and cure_avg_accuracy > 0.75 and
            questions_hard is not None and questions_hard > 0.60):
        return 'avanzar_a_leyes_newton'

    # ========================================================================
    # GRUPO D — Tiro parabólico
    # ========================================================================

    # D3: reforzar_relacion_gravedad_alcance
    if (projectile_avg_accuracy is not None and projectile_avg_accuracy < 0.40 and
            projectile_gravity_changes is not None and projectile_gravity_changes >= 4):
        return 'reforzar_relacion_gravedad_alcance'

    # D1: reforzar_angulo_tiro_parabolico
    if (projectile_avg_accuracy is not None and projectile_avg_accuracy < 0.40 and
            projectile_gravity_changes is not None and projectile_gravity_changes < 2):
        return 'reforzar_angulo_tiro_parabolico'

    # D2: reforzar_componentes_vx_vy
    if (projectile_avg_accuracy is not None and projectile_avg_accuracy < 0.40 and
            projectile_gravity_changes is not None and projectile_gravity_changes >= 2):
        return 'reforzar_componentes_vx_vy'

    # ========================================================================
    # GRUPO B — Fórmulas específicas
    # ========================================================================

    # B4: reforzar_precision_decimales
    if cure_errors_decimales is not None and cure_errors_decimales > 2:
        return 'reforzar_precision_decimales'

    # B3: reforzar_formula_tiempo_caida_libre
    if (cure_errors_calculo is not None and cure_errors_calculo > 2 and
            cure_avg_accuracy is not None and cure_avg_accuracy < 0.50):
        return 'reforzar_formula_tiempo_caida_libre'

    # B2: reforzar_formula_distancia_mruv
    if (cure_errors_concepto is not None and cure_errors_concepto > 2 and
            cure_avg_accuracy is not None and cure_avg_accuracy <= 0.55):
        return 'reforzar_formula_distancia_mruv'

    # B1: reforzar_formula_v_igual_d_entre_t
    if (cure_formulas_review is not None and cure_formulas_review > 0 and
            cure_avg_accuracy is not None and cure_avg_accuracy < 0.50):
        return 'reforzar_formula_v_igual_d_entre_t'

    # ========================================================================
    # GRUPO C — Interpretación de gráficas
    # ========================================================================

    # C4: reforzar_elegir_grafica_correcta
    if chart_choose is not None and chart_choose < 0.40:
        return 'reforzar_elegir_grafica_correcta'

    # C3: reforzar_predecir_movimiento_grafica
    if (chart_predict is not None and chart_predict < 0.40 and
            chart_identify is not None and chart_identify > 0.60):
        return 'reforzar_predecir_movimiento_grafica'

    # C2: reforzar_leer_grafica_velocidad_tiempo
    if chart_predict is not None and chart_predict < 0.30:
        return 'reforzar_leer_grafica_velocidad_tiempo'

    # C1: reforzar_leer_grafica_posicion_tiempo
    if chart_identify is not None and chart_identify < 0.40:
        return 'reforzar_leer_grafica_posicion_tiempo'

    # ========================================================================
    # GRUPO A — Conceptos básicos
    # ========================================================================

    # A4: reforzar_concepto_gravedad
    if (questions_hard is not None and questions_hard < 0.30 and
            projectile_gravity_changes is not None and projectile_gravity_changes < 1):
        return 'reforzar_concepto_gravedad'

    # A3: reforzar_concepto_aceleracion
    if (cure_formulas_mastered is not None and cure_formulas_mastered < 3 and
            cure_avg_accuracy is not None and cure_avg_accuracy < 0.40):
        return 'reforzar_concepto_aceleracion'

    # A2: reforzar_concepto_velocidad_constante
    if ((questions_easy is not None and questions_easy < 0.40) or
            (chart_identify is not None and chart_identify < 0.30)):
        return 'reforzar_concepto_velocidad_constante'

    # A1: reforzar_vocabulario_fisica
    if (crossword_wrong_rate is not None and crossword_wrong_rate > 0.50 and
            crossword_words is not None and crossword_words < 3):
        return 'reforzar_vocabulario_fisica'

    # ========================================================================
    # GRUPO E — Resolución de problemas
    # ========================================================================

    # E1: reforzar_procedimiento_multipaso
    if (questions_hard is not None and questions_hard < 0.30 and
            questions_easy is not None and questions_easy > 0.60):
        return 'reforzar_procedimiento_multipaso'

    # E2: reforzar_contextualizacion_problemas
    if (questions_medium is not None and questions_medium < 0.30 and
            questions_hard is not None and questions_hard < 0.30):
        return 'reforzar_contextualizacion_problemas'

    # Si nada aplica
    return 'sin_recomendacion_especifica'


# ============================================================================
# PROCESO PRINCIPAL
# ============================================================================

def main():
    print("=" * 60)
    print("SISTEMA GAMIFICADO — Asignación de etiquetas C4.5")
    print("=" * 60)

    print(f"\n📂 Leyendo: {INPUT_CSV}")
    if not INPUT_CSV.exists():
        print(f"❌ ERROR: no existe el archivo {INPUT_CSV}")
        return

    df = pd.read_csv(INPUT_CSV)
    print(f"✅ Leídos {len(df)} estudiantes con {len(df.columns)} columnas")

    print(f"\n🏷️  Aplicando reglas de etiquetado...")
    df['recommendation'] = df.apply(evaluar_reglas, axis=1)

    print(f"\n📊 Distribución de etiquetas:")
    dist = df['recommendation'].value_counts()
    for label, count in dist.items():
        pct = (count / len(df)) * 100
        print(f"   {label:50s} {count:3d} ({pct:5.1f}%)")

    print(f"\n💾 Guardando: {OUTPUT_CSV}")
    df.to_csv(OUTPUT_CSV, index=False, encoding='utf-8')
    print(f"✅ Guardado correctamente")

    print("\n" + "=" * 60)
    print("✅ PROCESO COMPLETADO")
    print("=" * 60)


if __name__ == "__main__":
    main()