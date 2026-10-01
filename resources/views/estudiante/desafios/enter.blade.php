<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ingresar a desafío</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">

    <main class="mx-auto max-w-lg px-6 py-12">

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">

            <div class="mb-6 text-center">
                <div class="text-5xl">🎯</div>

                <h1 class="mt-4 text-2xl font-bold text-indigo-700">
                    Entrar a un desafío
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Introduce el código proporcionado por tu docente.
                </p>
            </div>

            <form method="GET"
                  action="{{ route('estudiante.desafio.show', ['code' => 'CODIGO']) }}"
                  onsubmit="this.action = this.action.replace('CODIGO', this.code.value.trim())">

                <label for="code"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Código del desafío
                </label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    required
                    maxlength="20"
                    placeholder="Ejemplo: ZEWBR5WB"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 uppercase tracking-widest outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                >

                <button
                    type="submit"
                    class="mt-5 w-full rounded-lg bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700">
                    Entrar al desafío
                </button>

            </form>

            <a href="{{ route('estudiante.inicio') }}"
               class="mt-4 block text-center text-sm font-medium text-slate-500 hover:underline">
                Volver al inicio
            </a>

        </div>

    </main>

</body>

</html>