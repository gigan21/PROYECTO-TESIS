"""
entrenar_c45.py
Entrena un árbol de decisión para predecir recomendaciones pedagógicas.

scikit-learn DecisionTreeClassifier (criterio entropy, algoritmo CART/ID3)
es la aproximación disponible a C4.5. No reentrena columnas *_last_10
hasta que el CSV etiquetado las incluya.
"""

import pandas as pd
import numpy as np
from pathlib import Path
import joblib

from sklearn.tree import DecisionTreeClassifier, export_text
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.preprocessing import LabelEncoder
from sklearn.impute import SimpleImputer
from sklearn.metrics import classification_report, accuracy_score


BASE_DIR = Path(__file__).resolve().parent.parent
INPUT_CSV = BASE_DIR / "data" / "student_features_labeled.csv"
MODEL_OUTPUT = BASE_DIR / "models" / "c45_model.joblib"
ENCODER_OUTPUT = BASE_DIR / "models" / "label_encoder.joblib"
TREE_TXT_OUTPUT = BASE_DIR / "models" / "arbol_reglas.txt"
IMPUTER_OUTPUT = BASE_DIR / "models" / "imputer.joblib"

# Excluir features identificadoras Y las de gamificación (no pedagógicas)
NON_FEATURE_COLS = [
    'user_id', 'name', 'classroom', 'snapshot_date', 'recommendation',
    'student_xp_points', 'student_coins',   # No son señales pedagógicas
    # Contadores acumulados de toda la vida: no deben partir el árbol.
    'games_played_count',
    'cure_total_sessions',
    'cure_formulas_mastered_count',
    'cure_formulas_review_count',
    'projectile_total_sessions',
    'projectile_gravity_changes_total',
    'chart_race_total_sessions',
    'crossword_total_words_learned',
    'total_sessions_all_games',
    'questions_attempts_easy',
    'questions_attempts_medium',
    'questions_attempts_hard',
]

ACCURACY_FEATURE_SUFFIXES = ('_accuracy',)
ACCURACY_FEATURE_EXACT = (
    'questions_accuracy_easy',
    'questions_accuracy_medium',
    'questions_accuracy_hard',
)


def norm_pct(value):
    if value is None or (isinstance(value, float) and np.isnan(value)):
        return np.nan
    try:
        v = float(value)
    except (TypeError, ValueError):
        return np.nan
    if v > 1.5:
        return v / 100.0
    return v


def normalize_accuracy_features(X: pd.DataFrame) -> pd.DataFrame:
    out = X.copy()
    for col in out.columns:
        if (
            col in ACCURACY_FEATURE_EXACT
            or col.endswith(ACCURACY_FEATURE_SUFFIXES)
            or col.endswith('_last_10')
        ):
            out[col] = out[col].apply(norm_pct)
    return out


def main():
    print("=" * 70)
    print("SISTEMA GAMIFICADO — Entrenamiento del C4.5")
    print("=" * 70)

    # 1) Cargar CSV
    print(f"\n📂 Leyendo: {INPUT_CSV}")
    if not INPUT_CSV.exists():
        print(f"❌ ERROR: no existe {INPUT_CSV}")
        return

    df = pd.read_csv(INPUT_CSV)
    print(f"✅ Leídos {len(df)} estudiantes con {len(df.columns)} columnas")

    # 2) Separar features de etiqueta
    feature_cols = [c for c in df.columns if c not in NON_FEATURE_COLS]
    X = normalize_accuracy_features(df[feature_cols].copy())
    y = df['recommendation'].copy()

    print(f"\n🔢 Features usadas: {len(feature_cols)}")
    print(f"🎯 Etiquetas únicas: {y.nunique()}")

    # 3) Filtrar etiquetas con < 2 miembros
    print(f"\n🔍 Filtrando etiquetas con menos de 2 miembros...")
    label_counts = y.value_counts()
    valid_labels = label_counts[label_counts >= 2].index.tolist()
    invalid_labels = label_counts[label_counts < 2].index.tolist()

    if invalid_labels:
        print(f"⚠️  Eliminadas: {invalid_labels}")
        mask = y.isin(valid_labels)
        df = df[mask].reset_index(drop=True)
        X = X[mask].reset_index(drop=True)
        y = y[mask].reset_index(drop=True)
        print(f"✅ Dataset: {len(df)} filas")

    print(f"\n📊 Distribución final:")
    for label, count in y.value_counts().items():
        print(f"     {label:50s} {count:3d}")

    # 4) Imputar nulls con mediana
    print(f"\n🩹 Imputando nulls con mediana...")
    imputer = SimpleImputer(strategy='median')
    X_imputed = pd.DataFrame(
        imputer.fit_transform(X),
        columns=feature_cols,
        index=X.index
    )

    # 5) Codificar etiquetas
    le = LabelEncoder()
    y_encoded = le.fit_transform(y)
    print(f"✅ {len(le.classes_)} clases codificadas")

    # 6) Split
    X_train, X_test, y_train, y_test = train_test_split(
        X_imputed, y_encoded,
        test_size=0.25,
        random_state=42,
        stratify=y_encoded
    )
    print(f"\n📊 Split: {len(X_train)} train / {len(X_test)} test")

    # CART/entropy de scikit-learn como aproximación a C4.5.
    print(f"\n🌳 Entrenando árbol (CART/entropy, aproximación a C4.5)...")
    clf = DecisionTreeClassifier(
        criterion='entropy',
        max_depth=10,
        min_samples_leaf=2,
        class_weight='balanced',
        random_state=42,
    )
    clf.fit(X_train, y_train)
    print(f"✅ Modelo entrenado")
    print(f"   Profundidad: {clf.get_depth()}")
    print(f"   Hojas: {clf.get_n_leaves()}")

    # 8) Evaluar en test
    y_pred = clf.predict(X_test)
    acc = accuracy_score(y_test, y_pred)
    print(f"\n📈 Accuracy en test: {acc:.2%}")

    labels_in_test = sorted(set(y_test) | set(y_pred))
    target_names_in_test = [str(le.classes_[i]) for i in labels_in_test]

    print(f"\n📋 Classification Report:")
    print(classification_report(
        y_test, y_pred,
        labels=labels_in_test,
        target_names=target_names_in_test,
        zero_division=0
    ))

    # 9) Cross-validation
    print(f"\n📊 Cross-validation (5-fold):")
    cv_scores = cross_val_score(clf, X_imputed, y_encoded, cv=5)
    print(f"   Scores: {[f'{s:.2%}' for s in cv_scores]}")
    print(f"   Promedio: {cv_scores.mean():.2%} (±{cv_scores.std():.2%})")

    # 10) Árbol con nombres de clase
    tree_rules = export_text(
        clf,
        feature_names=feature_cols,
        class_names=list(le.classes_),
        max_depth=5
    )
    print(f"\n🌳 Reglas del árbol:")
    print(tree_rules)

    # 11) Guardar
    print(f"\n💾 Guardando modelos...")
    joblib.dump(clf, MODEL_OUTPUT)
    joblib.dump(le, ENCODER_OUTPUT)
    joblib.dump(imputer, IMPUTER_OUTPUT)

    with open(TREE_TXT_OUTPUT, 'w', encoding='utf-8') as f:
        f.write("REGLAS DEL ÁRBOL C4.5\n")
        f.write("=" * 70 + "\n\n")
        f.write(f"Features usadas: {len(feature_cols)}\n")
        f.write(f"Clases aprendidas: {len(le.classes_)}\n")
        f.write(f"Accuracy en test: {acc:.2%}\n")
        f.write(f"CV promedio: {cv_scores.mean():.2%}\n")
        f.write(f"Profundidad: {clf.get_depth()}\n")
        f.write(f"Hojas: {clf.get_n_leaves()}\n\n")
        f.write(tree_rules)

    print(f"   ✅ Modelo: {MODEL_OUTPUT}")
    print(f"   ✅ Encoder: {ENCODER_OUTPUT}")
    print(f"   ✅ Imputer: {IMPUTER_OUTPUT}")
    print(f"   ✅ Árbol: {TREE_TXT_OUTPUT}")

    print("\n" + "=" * 70)
    print("✅ ENTRENAMIENTO COMPLETO")
    print("=" * 70)


if __name__ == "__main__":
    main()