{{-- resources/views/estudiante/partials/lore-player.blade.php --}}

@props(['videos'])

@if($videos->isEmpty())
    <div class="flex h-[420px] items-center justify-center rounded-2xl border border-white/20 bg-black/50">
        <p class="text-slate-400">No hay videos de lore todavía</p>
    </div>
@else

<div
    x-data="{
        videos: {{ $videos->pluck('youtube_id')->values()->toJson() }},
        current: 0,
        muted: true,               // empieza silenciado
        next() {
            this.muted = false;    // a partir de la primera interacción ya hay sonido
            this.current = (this.current + 1) % this.videos.length;
        },
        prev() {
            this.muted = false;
            this.current = (this.current - 1 + this.videos.length) % this.videos.length;
        }
    }"
    class="relative h-[420px] w-full overflow-hidden rounded-2xl border border-white/20 bg-black shadow-xl"
>
    {{-- Iframe --}}
    <iframe
        :key="videos[current] + '-' + muted"
        :src="`https://www.youtube-nocookie.com/embed/${videos[current]}?autoplay=1&mute=${muted ? 1 : 0}&controls=0&disablekb=1&rel=0&modestbranding=1&playsinline=1&fs=0&iv_load_policy=3`"
        class="absolute inset-0 h-full w-full"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        referrerpolicy="strict-origin-when-cross-origin"
    ></iframe>

    {{-- Capas para tapar logo/título --}}
    <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-16 bg-gradient-to-b from-black/80 to-transparent"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 h-14 bg-gradient-to-t from-black/70 to-transparent"></div>

    {{-- Botones --}}
    <div class="absolute inset-y-0 right-3 z-20 flex flex-col items-center justify-center gap-4">
        <button @click="prev()" class="flex h-11 w-11 items-center justify-center rounded-full bg-black/60 text-white backdrop-blur transition hover:bg-black/80">
            ▲
        </button>

        <span class="rounded-full bg-black/60 px-2.5 py-1 text-xs font-bold text-white backdrop-blur"
              x-text="`${current + 1} / ${videos.length}`"></span>

        <button @click="next()" class="flex h-11 w-11 items-center justify-center rounded-full bg-black/60 text-white backdrop-blur transition hover:bg-black/80">
            ▼
        </button>
    </div>
</div>
@endif