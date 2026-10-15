# Pipeline ML — Sistema Gamificado

Entrenamiento y API de recomendaciones pedagógicas (árbol C4.5).

## Scripts existentes (no modificar desde la API)

| Script | Uso |
|--------|-----|
| `scripts/asignar_etiquetas.py` | Reglas → columna `recommendation` en CSV |
| `scripts/entrenar_c45.py` | Entrena y guarda `models/*.joblib` |

## API REST (Fase 5)

Servicio local para que Laravel (u otro cliente) pida una recomendación a partir de **42 features pedagógicas** (sin `student_xp_points` ni `student_coins`).

### Dependencias

Desde la carpeta `ml/`:

```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements-api.txt
```

### Arrancar

Desde la raíz del proyecto:

```bash
python ml/scripts/api.py
```

Variables opcionales:

- `ML_API_PORT` — puerto (default `5000`).

La API escucha solo en **127.0.0.1** (no expuesta a la red). `debug=False`, `threaded=True`.

### Endpoints

#### `GET /health`

Comprueba que el proceso y los modelos están cargados.

```bash
curl http://127.0.0.1:5000/health
```

Respuesta ejemplo:

```json
{"status":"ok","model_loaded":true,"n_features":42,"n_classes":12}
```

#### `POST /recommend`

Body JSON con objeto `features` (todas las claves requeridas; `null` permitido):

```bash
curl -X POST http://127.0.0.1:5000/recommend ^
  -H "Content-Type: application/json" ^
  -d "{\"features\": {\"crossword_total_words_learned\": 10, ...}}"
```

También se aceptan las 42 claves en la raíz del JSON (sin wrapper `features`).

Respuesta 200:

```json
{
  "recommendation": "reforzar_componentes_vx_vy",
  "confidence": 0.72,
  "class_index": 3
}
```

Errores: siempre JSON con campo `error` (400 validación, 500 interno).

### Features esperadas

El orden y nombres los define el `imputer.joblib` entrenado (`feature_names_in_`). Coinciden con las columnas de `data/student_features_labeled.csv` excluyendo:

`user_id`, `name`, `classroom`, `snapshot_date`, `recommendation`, `student_xp_points`, `student_coins`.

### Seguridad y rendimiento

- Modelos cargados **una vez** al iniciar `api.py`.
- No usar `host=0.0.0.0` en desarrollo local.
- No compartir puerto con Laravel (`8000`).
- Logs: solo etiqueta y confianza por predicción, no el vector de features.

### Probar con una fila del CSV

Puedes exportar una fila desde `data/student_features_labeled.csv` a JSON (solo columnas feature) y enviarla a `/recommend`.
