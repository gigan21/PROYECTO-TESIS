"""
api.py — API REST Flask para recomendaciones C4.5 (Fase 5).

Carga modelos joblib una sola vez al arranque. Solo escucha en 127.0.0.1.
"""

from __future__ import annotations

import logging
import os
import sys
from pathlib import Path
from typing import Any

import joblib
import numpy as np
import pandas as pd
from flask import Flask, jsonify, request
from flask_cors import CORS

BASE_DIR = Path(__file__).resolve().parent.parent
MODEL_PATH = BASE_DIR / "models" / "c45_model.joblib"
ENCODER_PATH = BASE_DIR / "models" / "label_encoder.joblib"
IMPUTER_PATH = BASE_DIR / "models" / "imputer.joblib"
LABELED_CSV = BASE_DIR / "data" / "student_features_labeled.csv"

NON_FEATURE_COLS = [
    "user_id",
    "name",
    "classroom",
    "snapshot_date",
    "recommendation",
    "student_xp_points",
    "student_coins",
]

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger("ml_api")

app = Flask(__name__)
CORS(app)

model = None
label_encoder = None
imputer = None
FEATURE_NAMES: list[str] = []


def _feature_names_fallback() -> list[str]:
    if not LABELED_CSV.exists():
        raise FileNotFoundError(f"No CSV fallback: {LABELED_CSV}")
    df = pd.read_csv(LABELED_CSV, nrows=0)
    return [c for c in df.columns if c not in NON_FEATURE_COLS]


def load_artifacts() -> None:
    global model, label_encoder, imputer, FEATURE_NAMES

    for path in (MODEL_PATH, ENCODER_PATH, IMPUTER_PATH):
        if not path.exists():
            logger.error("Modelo no encontrado: %s", path)
            sys.exit(1)

    model = joblib.load(MODEL_PATH)
    label_encoder = joblib.load(ENCODER_PATH)
    imputer = joblib.load(IMPUTER_PATH)

    names_in = getattr(imputer, "feature_names_in_", None)
    if names_in is not None and len(names_in) > 0:
        FEATURE_NAMES = list(names_in)
    else:
        logger.warning("imputer sin feature_names_in_; usando CSV labeled.")
        FEATURE_NAMES = _feature_names_fallback()

    logger.info(
        "Modelos cargados | features=%d clases=%d | %s",
        len(FEATURE_NAMES),
        len(label_encoder.classes_),
        BASE_DIR,
    )


def _error_response(message: str, status: int = 400, **extra: Any):
    body = {"error": message, **extra}
    return jsonify(body), status


def _extract_features_payload(data: dict[str, Any]) -> dict[str, Any] | None:
    if not isinstance(data, dict):
        return None
    if "features" in data and isinstance(data["features"], dict):
        return data["features"]
    reserved = {"features"}
    if any(k in FEATURE_NAMES for k in data.keys()):
        return {k: v for k, v in data.items() if k not in reserved}
    return None


def _norm_pct(value: float | np.floating) -> float | np.floating:
    if np.isnan(value):
        return value
    if value > 1.5:
        return value / 100.0
    return value


def _normalize_row_for_model(row: dict[str, Any]) -> dict[str, Any]:
    """Alinea escala 0-100 → 0-1 en accuracies (igual que entrenar_c45.py)."""
    out = dict(row)
    for key in FEATURE_NAMES:
        if key not in out:
            continue
        val = out[key]
        if isinstance(val, (int, float, np.integer, np.floating)) and not np.isnan(val):
            if (
                key.startswith("questions_accuracy_")
                or key.endswith("_accuracy")
                or key.endswith("_last_10")
            ):
                out[key] = _norm_pct(float(val))
    return out


def _coerce_value(key: str, value: Any) -> float | np.floating:
    if value is None:
        return np.nan
    if isinstance(value, bool):
        return float(int(value))
    if isinstance(value, (int, float, np.integer, np.floating)):
        return float(value)
    if isinstance(value, str):
        stripped = value.strip()
        if stripped == "" or stripped.lower() in ("null", "none"):
            return np.nan
        try:
            return float(stripped)
        except ValueError as exc:
            raise ValueError(f"Valor no numérico en '{key}'") from exc
    raise ValueError(f"Tipo no permitido en '{key}'")


@app.route("/health", methods=["GET"])
def health():
    return jsonify(
        {
            "status": "ok",
            "model_loaded": model is not None,
            "n_features": len(FEATURE_NAMES),
            "n_classes": len(label_encoder.classes_) if label_encoder is not None else 0,
        }
    )


@app.route("/recommend", methods=["POST"])
def recommend():
    if model is None or imputer is None or label_encoder is None:
        return _error_response("Modelos no cargados", 503)

    try:
        if not request.is_json:
            return _error_response("Content-Type debe ser application/json", 400)

        data = request.get_json(silent=False)
        if data is None:
            return _error_response("JSON inválido o vacío", 400)

        features_in = _extract_features_payload(data)
        if features_in is None:
            return _error_response(
                "Se espera objeto 'features' o las claves de features en la raíz",
                400,
            )

        missing = [k for k in FEATURE_NAMES if k not in features_in]
        if missing:
            return _error_response(
                "Faltan features requeridas",
                400,
                missing=missing,
            )

        row: dict[str, Any] = {}
        for key in FEATURE_NAMES:
            try:
                row[key] = _coerce_value(key, features_in[key])
            except ValueError as exc:
                return _error_response(str(exc), 400)

        row = _normalize_row_for_model(row)
        df = pd.DataFrame([row], columns=FEATURE_NAMES)
        X_imputed = pd.DataFrame(
            imputer.transform(df),
            columns=FEATURE_NAMES,
        )
        y_idx = int(model.predict(X_imputed)[0])
        proba = model.predict_proba(X_imputed)[0]
        confidence = float(np.max(proba))
        recommendation = label_encoder.inverse_transform([y_idx])[0]
        label = str(recommendation)

        summary_keys = (
            "has_failed_5_consecutive",
            "has_failed_5_of_10",
            "accuracy_last_10",
            "cure_accuracy_last_10",
            "projectile_accuracy_last_10",
            "chart_race_accuracy_last_10",
            "crossword_accuracy_last_10",
        )
        summary = {k: features_in.get(k) for k in summary_keys}
        logger.info(
            "recommendation=%s confidence=%.4f summary=%s",
            label,
            confidence,
            summary,
        )

        return jsonify(
            {
                "recommendation": label,
                "confidence": confidence,
                "class_index": y_idx,
            }
        )

    except Exception:
        logger.exception("Error en /recommend")
        return _error_response("Error interno al generar recomendación", 500)


def main() -> None:
    load_artifacts()
    port = int(os.environ.get("ML_API_PORT", "5000"))
    logger.info("Iniciando API en http://127.0.0.1:%s", port)
    app.run(host="127.0.0.1", port=port, debug=False, threaded=True)


if __name__ == "__main__":
    main()
