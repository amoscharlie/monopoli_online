<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live View - {{ $gameCode }}</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .live-die {
            display: grid;
            width: clamp(4rem, 6vw, 6.5rem);
            aspect-ratio: 1;
            place-items: center;
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, .55);
            background: linear-gradient(145deg, #ffffff, #dbeafe);
            color: #020617;
            font-size: clamp(2rem, 3.2vw, 4rem);
            font-weight: 900;
            box-shadow: 0 18px 55px rgba(59, 130, 246, .22);
        }
        .board-stage {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(15rem, 19rem);
            gap: 1rem;
            align-items: stretch;
            min-height: 0;
        }
        .monopoly-board {
            position: relative;
            display: grid;
            grid-template-columns: repeat(11, minmax(0, 1fr));
            grid-template-rows: repeat(11, minmax(0, 1fr));
            width: min(100%, calc(100vh - 12.5rem), 66rem);
            aspect-ratio: 1;
            place-self: center;
            gap: 2px;
            padding: 3px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 1rem;
            background: #050914;
            box-shadow: 0 28px 80px rgba(0, 0, 0, .38);
        }
        .board-center {
            position: relative;
            grid-column: 2 / 11;
            grid-row: 2 / 11;
            min-width: 0;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .1);
            background: #0b101b;
        }
        .board-center-image {
            position: absolute;
            inset: -2%;
            width: 104%;
            height: 104%;
            object-fit: cover;
            filter: grayscale(1) contrast(1.2) brightness(.48) blur(1.5px);
            opacity: .26;
        }
        .board-center-shade {
            position: absolute;
            inset: 0;
            background: linear-gradient(145deg, rgba(2, 6, 23, .46), rgba(2, 6, 23, .86));
        }
        .board-space {
            position: relative;
            z-index: 2;
            min-width: 0;
            min-height: 0;
            overflow: hidden;
            padding: clamp(.18rem, .35vw, .42rem);
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: .45rem;
            background: rgba(25, 31, 43, .98);
            box-shadow: inset 0 0 0 0 var(--owner-color, transparent);
            transition: border-color .25s ease, background-color .25s ease, transform .25s ease, box-shadow .25s ease;
        }
        .board-space.is-owned {
            border-color: color-mix(in srgb, var(--owner-color) 72%, white 12%);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--owner-color) 62%, transparent);
        }
        .board-space.is-destination {
            z-index: 4;
            border-color: rgba(52, 211, 153, .92);
            background: rgba(17, 54, 49, .98);
            transform: scale(1.045);
            box-shadow: 0 0 0 2px rgba(52, 211, 153, .28), 0 10px 28px rgba(16, 185, 129, .24);
        }
        .board-property-band {
            position: absolute;
            inset: 0 0 auto;
            height: .22rem;
            filter: grayscale(1);
            opacity: .82;
        }
        .board-space-name {
            display: -webkit-box;
            overflow: hidden;
            font-size: clamp(.38rem, .54vw, .67rem);
            font-weight: 900;
            line-height: 1.05;
            color: #f8fafc;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .board-space-meta {
            display: -webkit-box;
            overflow: hidden;
            margin-top: .18rem;
            font-size: clamp(.31rem, .42vw, .52rem);
            font-weight: 800;
            line-height: 1.05;
            color: #94a3b8;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }
        .board-buildings {
            position: absolute;
            right: .2rem;
            bottom: .16rem;
            display: flex;
            align-items: center;
            gap: .12rem;
            color: #e2e8f0;
            font-size: clamp(.34rem, .46vw, .56rem);
            font-weight: 900;
        }
        .board-pins {
            position: absolute;
            left: .16rem;
            bottom: .12rem;
            z-index: 5;
            display: flex;
            align-items: end;
            gap: .05rem;
        }
        .board-token {
            position: relative;
            display: grid;
            width: clamp(.82rem, 1.15vw, 1.38rem);
            aspect-ratio: .78;
            place-items: center;
            filter: drop-shadow(0 2px 2px rgba(0, 0, 0, .65));
            animation: token-arrive .55s ease both;
        }
        .board-token svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            stroke: white;
            stroke-width: 2.4;
        }
        .board-token-initial {
            position: relative;
            z-index: 1;
            margin-top: -.18rem;
            font-size: clamp(.31rem, .42vw, .5rem);
            font-weight: 950;
            color: #020617;
        }
        .card-reveal {
            animation: card-reveal .55s ease both;
        }
        .live-dice-tumble {
            animation: live-dice-tumble .22s ease-in-out infinite;
        }
        @media (min-width: 1101px) {
            .monopoly-board {
                width: auto;
                height: 100%;
                max-width: 100%;
            }
        }
        @media (min-width: 1536px) {
            .board-stage {
                grid-template-columns: minmax(13rem, 16rem) minmax(0, 1fr) minmax(16rem, 20rem);
            }
        }
        @media (min-width: 1280px) {
            .live-analytics {
                display: none !important;
            }
        }
        @media (max-width: 1100px) {
            .board-stage {
                grid-template-columns: 1fr;
            }
            .monopoly-board {
                width: min(100%, calc(100vh - 15rem), 58rem);
            }
        }
        @media (max-width: 640px) {
            .monopoly-board {
                gap: 1px;
                padding: 2px;
                border-radius: .65rem;
            }
            .board-space {
                padding: .12rem;
                border-radius: .25rem;
            }
            .board-space-meta,
            .board-buildings {
                display: none;
            }
        }
        @keyframes token-arrive {
            from { opacity: 0; transform: translateY(8px) scale(.82); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes card-reveal {
            from { opacity: 0; transform: translateY(10px) rotateX(-12deg); }
            to { opacity: 1; transform: translateY(0) rotateX(0); }
        }
        @keyframes live-dice-tumble {
            0% { transform: rotate(0deg) scale(1); }
            35% { transform: rotate(7deg) scale(.94); }
            70% { transform: rotate(-7deg) scale(1.04); }
            100% { transform: rotate(0deg) scale(1); }
        }
    </style>
</head>
<body
    x-data="liveView({{ $gameId }})"
    x-init="init()"
    x-cloak
    class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-100 antialiased xl:h-screen xl:overflow-hidden"
>
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-[linear-gradient(135deg,#020617_0%,#0f172a_45%,#172554_100%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(115deg,rgba(16,185,129,.08),transparent_35%,rgba(124,58,237,.09))]"></div>
    </div>

    <main class="mx-auto flex min-h-screen w-full max-w-[1920px] flex-col gap-3 p-3 sm:gap-4 sm:p-4 xl:h-screen xl:min-h-0 xl:overflow-hidden">
        <header class="glass-card grid items-center gap-4 p-4 xl:shrink-0 xl:grid-cols-[minmax(320px,1fr)_minmax(560px,1.25fr)] xl:py-3">
            <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-emerald-300 via-blue-400 to-purple-500 text-slate-950 shadow-2xl shadow-emerald-500/20 sm:h-16 sm:w-16 sm:rounded-3xl">
                    <i data-lucide="landmark" class="h-7 w-7 sm:h-9 sm:w-9"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-300 sm:text-sm">Layar Penonton</p>
                    <h1 class="text-[clamp(1.9rem,2.8vw,3.3rem)] font-black leading-none">MONOPOLY DIGITAL BANK</h1>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-right md:grid-cols-4 xl:gap-3">
                <div class="min-w-0 rounded-2xl bg-white/5 px-3 py-3">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Nomor Game</p>
                    <p class="break-words text-[clamp(1rem,1.35vw,1.5rem)] font-black leading-tight" x-text="state?.game?.code || '{{ $gameCode }}'"></p>
                </div>
                <div class="min-w-0 rounded-2xl bg-white/5 px-3 py-3">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Sisa Waktu</p>
                    <p class="truncate text-[clamp(1rem,1.35vw,1.5rem)] font-black text-emerald-300" x-text="countdownLabel()"></p>
                </div>
                <div class="min-w-0 rounded-2xl bg-white/5 px-3 py-3">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Transaksi</p>
                    <p class="text-[clamp(1rem,1.35vw,1.5rem)] font-black text-purple-300" x-text="state?.stats?.transaction_count || 0"></p>
                </div>
                <div class="min-w-0 rounded-2xl bg-white/5 px-3 py-3">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Status Main</p>
                    <p class="truncate text-[clamp(1rem,1.35vw,1.5rem)] font-black" :class="state?.game?.status === 'active' ? 'text-emerald-300' : state?.game?.status === 'finished' ? 'text-amber-300' : 'text-blue-300'" x-text="state?.game?.status_label || '-'"></p>
                </div>
            </div>
        </header>

        <section x-show="state?.game?.status !== 'finished'" class="glass-card p-3 sm:p-4 xl:flex xl:min-h-0 xl:flex-1 xl:flex-col xl:overflow-hidden">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-300">Papan Digital</p>
                    <h2 class="text-xl font-black sm:text-2xl">Posisi Pemain Saat Ini</h2>
                    <p class="text-xs text-slate-400">Bidak fisik tetap digerakkan pemain; layar ini membantu semua orang mengikuti posisi.</p>
                </div>
                <div class="min-w-[220px] rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-right">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400" x-text="pendingAction() ? 'Aksi sedang diselesaikan' : 'Waktu kocok dadu'"></p>
                    <p class="font-black" :class="pendingAction()?.is_expired ? 'text-rose-300' : 'text-emerald-300'" x-text="pendingAction() ? (pendingAction().is_expired ? 'Perlu bantuan Bank' : `${actionCountdownSeconds()} detik`) : `${turnCountdownSeconds() ?? 0} detik`"></p>
                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-950/60">
                        <div class="h-full rounded-full transition-all duration-1000" :class="pendingAction()?.is_expired ? 'bg-rose-400' : pendingAction() ? 'bg-amber-300' : 'bg-purple-400'" :style="`width:${pendingAction() ? actionCountdownPercent() : turnCountdownPercent()}%`"></div>
                    </div>
                    <p x-show="!pendingAction()" class="mt-1 text-[9px] font-bold text-slate-500">Habis = pemain diam, giliran lanjut</p>
                </div>
            </div>
            <div class="board-stage xl:min-h-0 xl:flex-1">
                <aside class="hidden min-h-0 flex-col gap-2 overflow-hidden 2xl:flex">
                    <div class="flex items-center justify-between border-b border-white/10 pb-2">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-300">Peringkat</p>
                            <h3 class="text-lg font-black">Kekayaan Pemain</h3>
                        </div>
                        <i data-lucide="trophy" class="h-6 w-6 text-amber-300"></i>
                    </div>
                    <div class="min-h-0 space-y-1.5 overflow-hidden">
                        <template x-for="(player, index) in rankedPlayers().slice(0, 8)" :key="player.id">
                            <div class="rounded-xl border border-white/10 bg-white/5 p-2" :class="index === 0 ? 'border-amber-300/30 bg-amber-300/10' : player.is_bankrupt ? 'opacity-50 grayscale' : ''">
                                <div class="flex items-center gap-2">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg text-xs font-black text-slate-950" :class="index === 0 ? 'bg-amber-300' : 'bg-slate-300'" x-text="index + 1"></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="truncate text-sm font-black" x-text="player.name"></p>
                                            <p class="shrink-0 text-sm font-black" :class="player.is_bankrupt ? 'text-rose-300' : 'text-emerald-300'" x-text="player.is_bankrupt ? 'Kalah' : money(player.total_asset)"></p>
                                        </div>
                                        <p class="truncate text-[9px] text-slate-400" x-text="`Bank ${money(player.balance)} · Tunai ${money(player.cash_balance)}`"></p>
                                    </div>
                                </div>
                                <p class="mt-1 truncate text-[9px] font-bold text-slate-300" x-text="player.properties?.length ? player.properties.map((property) => `${property.name}${property.has_hotel ? ' H' : property.house_count ? ` R${property.house_count}` : ''}`).join(' · ') : 'Belum punya properti'"></p>
                            </div>
                        </template>
                    </div>
                    <div class="mt-auto grid grid-cols-3 gap-1.5 border-t border-white/10 pt-2 text-center">
                        <div class="rounded-lg bg-white/5 p-1.5"><p class="text-[8px] font-black uppercase text-slate-400">Tanah</p><p class="font-black text-amber-300" x-text="`${state?.stats?.owned_property_count || 0}/${state?.properties?.length || 0}`"></p></div>
                        <div class="rounded-lg bg-white/5 p-1.5"><p class="text-[8px] font-black uppercase text-slate-400">Rumah</p><p class="font-black text-emerald-300" x-text="`${state?.stats?.sold_house_count || 0}/32`"></p></div>
                        <div class="rounded-lg bg-white/5 p-1.5"><p class="text-[8px] font-black uppercase text-slate-400">Hotel</p><p class="font-black text-purple-300" x-text="`${state?.stats?.sold_hotel_count || 0}/12`"></p></div>
                    </div>
                </aside>

                <div class="monopoly-board" aria-label="Papan Monopoly digital dari atas">
                    <div class="board-center">
                        <img src="{{ asset('images/monopoly-board-top-view.webp') }}" alt="" class="board-center-image">
                        <div class="board-center-shade"></div>
                        <div class="relative z-10 flex h-full flex-col items-center justify-center p-4 text-center sm:p-7">
                            <div class="grid h-10 w-10 place-items-center rounded-xl border border-white/15 bg-white/10 text-slate-100 sm:h-14 sm:w-14 sm:rounded-2xl">
                                <i data-lucide="landmark" class="h-6 w-6 sm:h-8 sm:w-8"></i>
                            </div>
                            <p class="mt-3 text-[9px] font-black uppercase tracking-[0.24em] text-emerald-300 sm:text-xs">Giliran Sekarang</p>
                            <h3 class="mt-1 max-w-full truncate text-[clamp(1.15rem,2.7vw,3rem)] font-black" x-text="state?.turn?.current_player?.name || 'Menunggu pemain'"></h3>
                            <p class="mt-1 text-[10px] font-bold text-slate-300 sm:text-sm" x-text="currentTurnPlayerState()?.current_space?.name || '-'"></p>

                            <div class="mt-3 flex items-center justify-center gap-2 sm:mt-5 sm:gap-3">
                                <span class="grid h-9 w-9 place-items-center rounded-xl border border-white/20 bg-white/90 text-lg font-black text-slate-950 sm:h-14 sm:w-14 sm:rounded-2xl sm:text-3xl" :class="diceAnimating() ? 'live-dice-tumble' : ''" x-text="displayedDice('dice_one')"></span>
                                <span class="grid h-9 w-9 place-items-center rounded-xl border border-white/20 bg-white/90 text-lg font-black text-slate-950 sm:h-14 sm:w-14 sm:rounded-2xl sm:text-3xl" :class="diceAnimating() ? 'live-dice-tumble' : ''" x-text="displayedDice('dice_two')"></span>
                                <span class="grid h-9 min-w-9 place-items-center rounded-xl border border-emerald-300/30 bg-emerald-300/15 px-2 text-lg font-black text-emerald-200 sm:h-14 sm:min-w-14 sm:rounded-2xl sm:text-3xl" x-text="displayedDice('total')"></span>
                            </div>

                            <p class="mt-3 hidden max-w-2xl text-xs font-bold leading-relaxed text-slate-300 sm:block" x-text="boardMoveText()"></p>
                            <div class="mt-3 hidden max-w-full flex-wrap justify-center gap-2 md:flex">
                                <template x-for="player in (state?.players || []).filter((item) => !item.is_bankrupt)" :key="player.id">
                                    <span class="flex min-w-0 items-center gap-1.5 rounded-full border border-white/10 bg-slate-950/55 px-2 py-1 text-[10px] font-black text-slate-200">
                                        <i data-lucide="map-pin" class="h-3.5 w-3.5 shrink-0" :style="`color:${player.avatar_color};fill:${player.avatar_color}`"></i>
                                        <span class="max-w-28 truncate" x-text="`${player.name}: ${player.current_space?.name || '-'}`"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <template x-for="space in state?.board?.spaces || []" :key="space.index">
                        <div
                            class="board-space"
                            :class="[isLatestDestination(space.index) ? 'is-destination' : '', propertyAtSpace(space)?.owner_id ? 'is-owned' : '']"
                            :style="boardSpaceStyle(space)"
                        >
                            <span x-show="propertyAtSpace(space)" class="board-property-band" :style="`background:${propertyAtSpace(space)?.color || '#cbd5e1'}`"></span>
                            <div class="mb-1 flex items-start justify-between gap-1">
                                <span class="text-[7px] font-black text-slate-500 sm:text-[8px]" x-text="space.index"></span>
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full" :class="spaceTypeColor(space.type)"></span>
                            </div>
                            <p class="board-space-name" x-text="space.name"></p>
                            <p x-show="propertyAtSpace(space)" class="board-space-meta" :style="propertyAtSpace(space)?.owner_id ? `color:${ownerColor(propertyAtSpace(space))}` : ''" x-text="propertyAtSpace(space)?.owner_name || money(propertyAtSpace(space)?.price)"></p>
                            <p x-show="!propertyAtSpace(space)" class="board-space-meta" x-text="spaceTypeLabel(space.type)"></p>

                            <div x-show="propertyAtSpace(space)?.house_count || propertyAtSpace(space)?.has_hotel" class="board-buildings">
                                <i :data-lucide="propertyAtSpace(space)?.has_hotel ? 'building-2' : 'house'" class="h-2.5 w-2.5"></i>
                                <span x-text="propertyAtSpace(space)?.has_hotel ? '1' : propertyAtSpace(space)?.house_count"></span>
                            </div>

                            <div class="board-pins">
                                <template x-for="player in playersAtSpace(space.index)" :key="player.id">
                                    <span class="board-token" :title="`${player.name} berada di ${space.name}`">
                                        <i data-lucide="map-pin" :style="`fill:${player.avatar_color};color:${player.avatar_color}`"></i>
                                        <span class="board-token-initial" x-text="player.name.slice(0,1).toUpperCase()"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <aside class="flex min-h-0 flex-col gap-2 overflow-hidden">
                    <div class="rounded-xl border p-3" :class="pendingAction() ? 'border-amber-300/25 bg-amber-300/10' : 'border-white/10 bg-white/5'">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.16em]" :class="pendingAction() ? 'text-amber-200' : 'text-emerald-300'" x-text="pendingAction() ? `${pendingActionPlayer()?.name} · Aksi Petak` : 'Status Petak'"></p>
                                <p class="mt-0.5 truncate text-sm font-black" x-text="pendingAction()?.label || 'Tidak ada pembayaran tertunda'"></p>
                            </div>
                            <i :data-lucide="pendingAction() ? 'circle-alert' : 'circle-check'" class="h-5 w-5 shrink-0" :class="pendingAction() ? 'text-amber-300' : 'text-emerald-300'"></i>
                        </div>
                        <p class="mt-1 line-clamp-3 text-[11px] leading-snug text-slate-300" x-text="pendingAction()?.message || 'Permainan siap dilanjutkan ke kocokan dadu berikutnya.'"></p>
                        <p x-show="pendingAction()?.amount" class="mt-1 text-xl font-black text-amber-200" x-text="money(pendingAction()?.amount)"></p>
                        <p x-show="pendingAction()?.rule_label" class="mt-1 text-[9px] font-bold text-amber-100" x-text="pendingAction()?.rule_label"></p>
                    </div>

                    <div class="min-h-0 rounded-xl border border-white/10 bg-white/5 p-2.5">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-[0.16em] text-purple-300">Pemain & Posisi</p>
                                <p class="text-xs font-black">Saldo dan petak saat ini</p>
                            </div>
                            <i data-lucide="map-pinned" class="h-5 w-5 text-purple-300"></i>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <template x-for="player in (state?.players || []).slice(0, 8)" :key="player.id">
                                <div class="min-w-0 rounded-lg bg-slate-950/40 p-1.5" :class="player.is_bankrupt ? 'opacity-50 grayscale' : ''">
                                    <div class="flex items-center gap-1.5">
                                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-md text-[8px] font-black text-slate-950" :style="`background:${player.avatar_color}`" x-text="player.name.slice(0,1).toUpperCase()"></span>
                                        <p class="truncate text-[10px] font-black" x-text="player.name"></p>
                                    </div>
                                    <p class="mt-1 truncate text-[8px] text-slate-400" x-text="`${player.current_space?.index ?? 0}. ${player.current_space?.name || 'Start'}`"></p>
                                    <p class="truncate text-[9px] font-black text-emerald-300" x-text="player.is_bankrupt ? 'Bangkrut' : `Bank ${money(player.balance)} · Cash ${money(player.cash_balance)}`"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="min-h-0 rounded-xl border border-white/10 bg-white/5 p-2.5">
                        <div class="mb-1.5 flex items-center justify-between">
                            <p class="text-[9px] font-black uppercase tracking-[0.16em] text-amber-300">Properti Belum Dibeli</p>
                            <span class="text-xs font-black text-amber-200" x-text="unownedProperties().length"></span>
                        </div>
                        <div class="space-y-1 overflow-hidden">
                            <template x-for="group in unownedGroups().slice(0, 8)" :key="group.name">
                                <div class="flex min-w-0 items-start gap-1.5 text-[9px] leading-tight">
                                    <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full" :style="`background:${group.color}`"></span>
                                    <p class="min-w-0"><b x-text="group.name"></b>: <span class="text-slate-400" x-text="group.properties.map((property) => property.name).join(', ')"></span></p>
                                </div>
                            </template>
                            <p x-show="unownedProperties().length === 0" class="text-[10px] font-bold text-emerald-300">Semua properti sudah dimiliki.</p>
                        </div>
                    </div>

                    <div class="mt-auto rounded-xl border border-white/10 bg-white/5 p-2.5">
                        <div class="mb-1.5 flex items-center justify-between">
                            <p class="text-[9px] font-black uppercase tracking-[0.16em] text-blue-300">Transaksi Terbaru</p>
                            <i data-lucide="activity" class="h-4 w-4 text-blue-300"></i>
                        </div>
                        <div class="space-y-1">
                            <template x-for="transaction in (state?.transactions || []).slice(0, 2)" :key="transaction.id">
                                <div class="rounded-lg bg-slate-950/40 p-1.5">
                                    <p class="line-clamp-2 text-[9px] font-bold leading-tight" x-text="transaction.description"></p>
                                    <p class="mt-0.5 text-[8px] text-slate-500" x-text="transaction.created_at_label"></p>
                                </div>
                            </template>
                            <p x-show="!(state?.transactions || []).length" class="text-[9px] text-slate-500">Belum ada transaksi.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section x-show="state?.game?.status === 'finished'" class="glass-card confetti-field grid min-h-0 flex-1 place-items-center overflow-y-auto p-5 text-center xl:p-8">
            <div class="w-full max-w-6xl">
                <div class="mx-auto mb-4 grid h-20 w-20 place-items-center rounded-[1.75rem] bg-amber-300 text-slate-950 shadow-2xl shadow-amber-500/30 crown-drop xl:mb-6 xl:h-24 xl:w-24 xl:rounded-[2rem]">
                    <i data-lucide="crown" class="h-11 w-11 xl:h-14 xl:w-14"></i>
                </div>
                <p class="text-base font-black uppercase tracking-[0.35em] text-amber-300 xl:text-lg">Permainan Selesai</p>
                <h2 class="mt-3 text-[clamp(3rem,6vw,5.5rem)] font-black" x-text="winner()?.name || 'Pemenang'"></h2>
                <p class="mt-2 text-[clamp(1.75rem,3vw,2.5rem)] font-black text-emerald-300" x-text="money(winner()?.total_asset)"></p>
                <div class="mt-5 grid gap-3 xl:mt-8">
                    <template x-for="(player, index) in rankedPlayers()" :key="player.id">
                        <div class="grid grid-cols-[56px_minmax(0,1fr)] items-center gap-3 rounded-3xl border border-white/10 bg-white/8 p-3 text-left md:grid-cols-[64px_minmax(0,1fr)_180px_160px] xl:p-4">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl text-xl font-black text-slate-950 xl:h-14 xl:w-14 xl:text-2xl" :class="index === 0 ? 'bg-amber-300' : index === 1 ? 'bg-slate-300' : index === 2 ? 'bg-orange-300' : 'bg-white/20 text-white'" x-text="index + 1"></span>
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl text-lg font-black text-slate-950 xl:h-12 xl:w-12 xl:text-xl" :style="`background:${player.avatar_color}`" x-text="player.name.slice(0,1).toUpperCase()"></span>
                                <p class="truncate text-2xl font-black xl:text-3xl" x-text="player.name"></p>
                            </div>
                            <p class="text-lg font-black text-emerald-300 md:text-right xl:text-xl" x-text="player.is_bankrupt ? 'KALAH' : money(player.total_asset)"></p>
                            <p class="text-sm text-slate-300"><span x-text="player.property_count"></span> tanah · <span x-text="player.house_count"></span> rumah · <span x-text="player.hotel_count"></span> hotel</p>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section x-show="state?.game?.status !== 'finished'" class="live-analytics grid flex-1 gap-4 xl:grid-cols-[minmax(300px,0.95fr)_minmax(360px,1fr)_minmax(330px,1fr)]">
            <aside class="glass-card flex min-h-[320px] flex-col p-4 xl:p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.22em] text-amber-300">Peringkat</p>
                        <h2 class="text-[clamp(1.6rem,2.1vw,2.6rem)] font-black">Peringkat Kekayaan</h2>
                    </div>
                    <i data-lucide="trophy" class="h-9 w-9 text-amber-300"></i>
                </div>
                <div class="space-y-3">
                    <template x-for="(player, index) in rankedPlayers().slice(0, 8)" :key="player.id">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-3 xl:p-4" :class="index === 0 ? 'bg-amber-300/12 ring-2 ring-amber-300/40' : ''">
                            <div class="mb-3 flex items-center justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid h-12 w-12 place-items-center rounded-2xl text-lg font-black text-slate-950" :class="index === 0 ? 'bg-amber-300' : index === 1 ? 'bg-slate-300' : index === 2 ? 'bg-orange-300' : 'bg-white/20 text-white'" x-text="index + 1"></span>
                                    <div class="min-w-0">
                                        <p class="truncate text-[clamp(1.25rem,1.65vw,2rem)] font-black" x-text="player.name"></p>
                                        <p class="text-sm text-slate-400">
                                            Bank <span x-text="money(player.balance)"></span> · Tunai <span x-text="money(player.cash_balance)"></span>
                                        </p>
                                    </div>
                                </div>
                                <p class="shrink-0 text-[clamp(1.25rem,1.65vw,2rem)] font-black text-emerald-300" x-text="money(player.total_asset)"></p>
                            </div>
                            <div class="h-2 rounded-full bg-slate-950/60">
                                <div class="h-2 rounded-full bg-gradient-to-r from-emerald-300 via-blue-400 to-purple-400" :style="`width:${assetBar(player)}%`"></div>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <template x-for="property in (player.properties || []).slice(0, 6)" :key="property.id">
                                    <span class="rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1 text-[10px] font-bold text-slate-300">
                                        <span x-text="property.name"></span>
                                        <span x-show="property.house_count" class="text-emerald-200" x-text="` R${property.house_count}`"></span>
                                        <span x-show="property.has_hotel" class="text-purple-200"> Hotel</span>
                                    </span>
                                </template>
                                <span x-show="!player.properties?.length" class="text-[10px] text-slate-500">Belum punya properti</span>
                                <span x-show="(player.properties?.length || 0) > 6" class="text-[10px] font-bold text-slate-400" x-text="`+${player.properties.length - 6} lagi`"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>

            <section class="grid gap-4 xl:grid-rows-[auto_auto_auto]">
                <div class="glass-card overflow-hidden p-4 xl:p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-blue-300">Giliran Sekarang</p>
                            <h2 class="truncate text-[clamp(1.8rem,3vw,3.6rem)] font-black" x-text="state?.turn?.current_player?.name || 'Menunggu pemain'"></h2>
                            <p class="text-sm text-slate-400" x-text="turnStatusText()"></p>
                            <p class="mt-1 text-sm font-bold text-emerald-200" x-text="boardMoveText()"></p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <div class="live-die" :class="diceAnimating() ? 'animate-bounce' : ''" x-text="displayedDice('dice_one')"></div>
                            <div class="live-die" :class="diceAnimating() ? 'animate-bounce' : ''" x-text="displayedDice('dice_two')"></div>
                            <div class="rounded-3xl border border-emerald-300/20 bg-emerald-300/10 px-5 py-4 text-center">
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-200">Total</p>
                                <p class="text-4xl font-black text-emerald-300" x-text="displayedDice('total')"></p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <template x-for="player in state?.turn?.order || []" :key="player.id">
                            <span class="rounded-full px-3 py-1.5 text-xs font-black" :class="Number(player.id) === Number(state?.turn?.current_player_id) ? 'bg-emerald-300 text-slate-950' : player.is_in_jail ? 'bg-rose-300/20 text-rose-200' : 'bg-white/10 text-slate-200'" x-text="`${player.name} - ${player.current_space?.name || '-'}${player.is_in_jail ? ' - Penjara' : ''}`"></span>
                        </template>
                    </div>
                </div>

                <div class="glass-card min-h-[300px] p-4 xl:p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-emerald-300">Grafik Kekayaan</p>
                            <h2 class="text-[clamp(1.7rem,2.4vw,3rem)] font-black">Kekayaan Pemain</h2>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-slate-400">Total aset aktif</p>
                            <p class="text-[clamp(1.7rem,2.2vw,3rem)] font-black text-emerald-300" x-text="money(state?.stats?.total_assets)"></p>
                        </div>
                    </div>
                    <div class="h-[220px] xl:h-[260px] 2xl:h-[300px]"><canvas id="liveWealthChart"></canvas></div>
                </div>

                <div class="glass-card space-y-2 p-3 xl:p-4">
                    <div class="flex items-center justify-between gap-4 rounded-2xl bg-white/5 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-amber-300">Tanah Terjual</p>
                            <p class="text-xs text-slate-400">Properti yang sudah dimiliki pemain</p>
                        </div>
                        <p class="shrink-0 text-2xl font-black text-amber-300" x-text="`${state?.stats?.owned_property_count || 0}/${state?.properties?.length || 0}`"></p>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-2xl bg-white/5 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-300">Rumah Terjual</p>
                            <p class="text-xs text-slate-400">Stok rumah bank total 32</p>
                        </div>
                        <p class="shrink-0 text-2xl font-black text-emerald-300" x-text="`${state?.stats?.sold_house_count || 0}/${state?.stats?.total_house_count || 32}`"></p>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-2xl bg-white/5 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-purple-300">Hotel Terjual</p>
                            <p class="text-xs text-slate-400">Stok hotel bank total 12</p>
                        </div>
                        <p class="shrink-0 text-2xl font-black text-purple-300" x-text="`${state?.stats?.sold_hotel_count || 0}/${state?.stats?.total_hotel_count || 12}`"></p>
                    </div>
                </div>
            </section>

            <aside class="grid gap-4 xl:grid-rows-[auto_auto]">
                <div class="glass-card p-4 xl:p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-purple-300">Pemilik Tanah</p>
                            <h2 class="text-[clamp(1.7rem,2.2vw,3rem)] font-black">Pembagian Properti</h2>
                        </div>
                        <i data-lucide="pie-chart" class="h-8 w-8 text-purple-300"></i>
                    </div>
                    <div class="h-[170px] xl:h-[190px] 2xl:h-[230px]"><canvas id="liveOwnershipChart"></canvas></div>
                    <div class="mt-3 rounded-2xl bg-white/5 p-3">
                        <p class="mb-2 text-xs font-black uppercase tracking-[0.16em] text-slate-400">Belum Dibeli</p>
                        <div class="soft-scroll max-h-40 space-y-2 overflow-y-auto">
                            <template x-for="group in unownedGroups()" :key="group.name">
                                <div class="rounded-xl border border-white/10 bg-slate-950/35 p-2">
                                    <div class="mb-1 flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" :style="`background:${group.color}`"></span>
                                        <span class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400" x-text="group.name"></span>
                                    </div>
                                    <p class="text-xs font-bold text-slate-200" x-text="group.properties.map((property) => `${property.name} (${money(property.price)})`).join(' · ')"></p>
                                </div>
                            </template>
                            <span x-show="unownedProperties().length === 0" class="text-xs text-emerald-300">Semua properti sudah dibeli</span>
                        </div>
                    </div>
                </div>

                <div class="glass-card flex min-h-[190px] flex-col p-3 xl:p-4">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-blue-300">Baru Terjadi</p>
                            <h2 class="text-xl font-black xl:text-2xl">Transaksi Terbaru</h2>
                        </div>
                        <i data-lucide="activity" class="h-8 w-8 text-blue-300"></i>
                    </div>
                    <div class="soft-scroll min-h-0 flex-1 space-y-3 overflow-y-auto pr-1">
                        <template x-for="transaction in (state?.transactions || []).slice(0, 4)" :key="transaction.id">
                            <div class="rounded-xl border border-white/10 bg-white/5 p-2.5">
                                <div class="mb-1 flex items-center justify-between gap-3">
                                    <span class="rounded-full bg-slate-950/60 px-2 py-0.5 text-[9px] font-black uppercase text-slate-300" x-text="transaction.type.replaceAll('_', ' ')"></span>
                                    <span class="text-xs text-slate-400" x-text="transaction.created_at_label"></span>
                                </div>
                                <p class="text-sm font-black leading-snug" x-text="transaction.description"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="state?.cards?.last_draw" class="glass-card card-reveal p-4 xl:p-5">
                    <div class="flex items-start gap-4">
                        <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl text-2xl font-black" :class="state?.cards?.last_draw?.deck === 'Dana Umum' ? 'bg-emerald-300 text-slate-950' : 'bg-rose-400 text-white'">
                            <span x-text="state?.cards?.last_draw?.deck === 'Dana Umum' ? 'DU' : 'K'"></span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-black uppercase tracking-[0.22em]" :class="state?.cards?.last_draw?.deck === 'Dana Umum' ? 'text-emerald-300' : 'text-rose-300'" x-text="state?.cards?.last_draw?.deck"></p>
                            <h2 class="mt-1 text-[clamp(1.4rem,2vw,2.6rem)] font-black leading-tight" x-text="state?.cards?.last_draw?.card?.title"></h2>
                            <p class="mt-1 text-sm text-slate-300" x-text="state?.cards?.last_draw?.card?.description"></p>
                            <p class="mt-2 text-xs font-bold text-slate-400" x-text="`${state?.cards?.last_draw?.player_name || '-'} mendapatkan kartu ini`"></p>
                        </div>
                    </div>
                </div>
            </aside>
        </section>
    </main>

    <script>
        window.liveView = (gameId) => ({
            gameId,
            state: null,
            boardProperties: {},
            charts: {},
            chartSignature: '',
            stateRequestInFlight: false,
            stateRefreshQueued: false,
            automationInFlight: false,
            animatedRollId: null,
            diceAnimation: {
                active: false,
                first: 1,
                second: 1,
                timer: null,
                timeout: null,
            },
            now: Date.now(),
            realtime: {
                echo: null,
                connected: false,
            },
            init() {
                this.fetchState();
                setInterval(() => {
                    if (!this.realtime.connected) {
                        this.fetchState(true);
                    }
                }, 4000);
                setInterval(() => {
                    this.now = Date.now();
                    this.maybeRunAutomation().catch(() => {});
                }, 1000);
                this.connectRealtime();
            },
            async fetchState(silent = false) {
                if (this.stateRequestInFlight) {
                    this.stateRefreshQueued = true;
                    return;
                }

                this.stateRequestInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/live/${this.gameId}`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        return;
                    }
                    const payload = await response.json();
                    const nextRollId = payload.state?.turn?.last_event?.type === 'dice_timeout_skipped'
                        ? null
                        : (payload.state?.turn?.last_roll?.id || null);
                    if (nextRollId && this.animatedRollId === null) {
                        this.animatedRollId = nextRollId;
                    } else if (nextRollId && nextRollId !== this.animatedRollId) {
                        this.animatedRollId = nextRollId;
                        this.startDiceAnimation(payload.state?.turn?.last_roll);
                    }
                    this.setState(payload.state);
                    this.$nextTick(() => {
                        this.drawCharts();
                        window.lucide?.createIcons();
                    });
                } catch (error) {
                    this.realtime.connected = false;
                } finally {
                    this.stateRequestInFlight = false;
                    if (this.stateRefreshQueued) {
                        this.stateRefreshQueued = false;
                        queueMicrotask(() => this.fetchState(true));
                    }
                }
            },
            startDiceAnimation(roll) {
                clearInterval(this.diceAnimation.timer);
                clearTimeout(this.diceAnimation.timeout);
                this.diceAnimation.active = true;
                this.diceAnimation.timer = setInterval(() => {
                    this.diceAnimation.first = Math.floor(Math.random() * 6) + 1;
                    this.diceAnimation.second = Math.floor(Math.random() * 6) + 1;
                }, 85);
                this.diceAnimation.timeout = setTimeout(() => {
                    clearInterval(this.diceAnimation.timer);
                    this.diceAnimation.first = Number(roll?.dice_one || 1);
                    this.diceAnimation.second = Number(roll?.dice_two || 1);
                    this.diceAnimation.active = false;
                }, 1100);
            },
            connectRealtime() {
                try {
                    if (!window.createMonopolyEcho) {
                        return;
                    }
                    this.realtime.echo = window.createMonopolyEcho();
                    const connection = this.realtime.echo.connector.pusher.connection;
                    connection.bind('connected', () => { this.realtime.connected = true; });
                    connection.bind('disconnected', () => { this.realtime.connected = false; });
                    connection.bind('error', () => { this.realtime.connected = false; });
                    this.realtime.echo.channel(`game.${this.gameId}`).listen('.game.updated', () => this.fetchState(true));
                } catch (error) {
                    this.realtime.connected = false;
                }
            },
            rankedPlayers() {
                return [...(this.state?.players || [])].sort((a, b) => {
                    if (a.is_bankrupt && !b.is_bankrupt) return 1;
                    if (!a.is_bankrupt && b.is_bankrupt) return -1;
                    return Number(b.total_asset || 0) - Number(a.total_asset || 0);
                });
            },
            winner() {
                return this.rankedPlayers().find((player) => !player.is_bankrupt) || null;
            },
            currentTurnPlayerState() {
                return (this.state?.players || []).find((player) => Number(player.id) === Number(this.state?.turn?.current_player_id)) || null;
            },
            setState(nextState) {
                this.state = nextState;
                this.boardProperties = Object.fromEntries(
                    (nextState?.properties || []).map((property) => [this.normalizedSpaceName(property.name), property]),
                );
            },
            normalizedSpaceName(name) {
                const normalized = String(name || '')
                    .toLowerCase()
                    .replace(/[()]/g, '')
                    .replace(/\s+/g, ' ')
                    .trim();
                const aliases = {
                    filipina: 'philipina',
                    'terminal tokyo': 'terminal bus tokyo',
                    amerika: 'amerika serikat',
                };

                return aliases[normalized] || normalized;
            },
            propertyAtSpace(space) {
                return this.boardProperties[this.normalizedSpaceName(space?.name)] || null;
            },
            ownerColor(property) {
                if (!property?.owner_id) {
                    return '#94a3b8';
                }

                return (this.state?.players || []).find((player) => Number(player.id) === Number(property.owner_id))?.avatar_color || '#94a3b8';
            },
            boardSpaceStyle(space) {
                const index = Number(space?.index || 0);
                let column = 11;
                let row = 11;

                if (index > 0 && index <= 10) {
                    column = 11 - index;
                } else if (index > 10 && index <= 20) {
                    column = 1;
                    row = 21 - index;
                } else if (index > 20 && index <= 30) {
                    column = index - 19;
                    row = 1;
                } else if (index > 30) {
                    row = index - 29;
                }

                const property = this.propertyAtSpace(space);
                return `grid-column:${column};grid-row:${row};--owner-color:${this.ownerColor(property)}`;
            },
            spaceTypeLabel(type) {
                return {
                    start: 'Gaji',
                    chance_card: 'Kesempatan',
                    community_card: 'Dana Umum',
                    tax: 'Pajak',
                    special_tax: 'Pajak',
                    jail_visit: 'Hanya lewat',
                    go_to_jail: 'Masuk penjara',
                    free_parking: 'Diam di sini',
                }[type] || '';
            },
            unownedProperties() {
                return (this.state?.properties || []).filter((property) => !property.owner_id);
            },
            unownedGroups() {
                const labels = { transport: 'Transportasi', utility: 'Perusahaan' };
                const groups = this.unownedProperties().reduce((result, property) => {
                    const name = property.group_name || labels[property.property_kind] || 'Properti Lain';
                    result[name] = result[name] || { name, color: property.color || '#64748b', properties: [] };
                    result[name].properties.push(property);
                    return result;
                }, {});

                return Object.values(groups);
            },
            pendingActionPlayer() {
                return (this.state?.players || []).find((player) => player.pending_space_action) || null;
            },
            pendingAction() {
                return this.pendingActionPlayer()?.pending_space_action || null;
            },
            playersAtSpace(position) {
                return (this.state?.players || []).filter((player) => !player.is_bankrupt && Number(player.board_position) === Number(position));
            },
            isLatestDestination(position) {
                if (this.lastTurnWasSkipped()) {
                    return false;
                }

                const movement = this.state?.turn?.last_roll?.meta?.movement;
                return movement && Number(movement.to_position) === Number(position);
            },
            spaceTypeColor(type) {
                return {
                    property: 'bg-amber-300',
                    transport: 'bg-blue-300',
                    utility: 'bg-cyan-300',
                    chance_card: 'bg-rose-300',
                    community_card: 'bg-emerald-300',
                    tax: 'bg-orange-300',
                    special_tax: 'bg-red-400',
                    go_to_jail: 'bg-red-400',
                    jail_visit: 'bg-slate-300',
                    free_parking: 'bg-purple-300',
                    start: 'bg-lime-300',
                }[type] || 'bg-slate-500';
            },
            turnCountdownSeconds() {
                const deadline = this.state?.turn?.deadline_at;
                if (!deadline || this.pendingAction() || this.state?.game?.status !== 'active') {
                    return null;
                }

                return Math.max(0, Math.ceil((new Date(deadline).getTime() - this.now) / 1000));
            },
            turnCountdownPercent() {
                const remaining = this.turnCountdownSeconds();
                const total = Number(this.state?.turn?.timeout_seconds || 20);
                return remaining === null ? 0 : Math.max(0, Math.min(100, (remaining / total) * 100));
            },
            actionCountdownSeconds() {
                const deadline = this.pendingAction()?.action_deadline_at;
                return deadline ? Math.max(0, Math.ceil((new Date(deadline).getTime() - this.now) / 1000)) : 0;
            },
            actionCountdownPercent() {
                const total = Number(this.pendingAction()?.timeout_seconds || 60);
                return Math.max(0, Math.min(100, (this.actionCountdownSeconds() / total) * 100));
            },
            automationDue() {
                if (!this.state?.game?.id || this.state.game.status !== 'active' || this.automationInFlight) {
                    return false;
                }
                const action = this.pendingAction();
                if (action?.action_deadline_at) {
                    return new Date(action.action_deadline_at).getTime() <= this.now && !action.expired_at;
                }
                const deadline = this.state?.turn?.deadline_at;
                return Boolean(deadline && new Date(deadline).getTime() <= this.now);
            },
            async maybeRunAutomation() {
                if (!this.automationDue()) return;
                this.automationInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/games/${this.gameId}/automation/tick`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({}),
                    });
                    if (response.ok) {
                        const payload = await response.json();
                        this.setState(payload.state);
                        this.$nextTick(() => this.drawCharts());
                    }
                } finally {
                    this.automationInFlight = false;
                }
            },
            maxAsset() {
                return Math.max(...this.rankedPlayers().map((player) => Number(player.total_asset || 0)), 1);
            },
            assetBar(player) {
                return Math.max(6, Math.round((Number(player.total_asset || 0) / this.maxAsset()) * 100));
            },
            diceAnimating() {
                return this.diceAnimation.active;
            },
            lastTurnWasSkipped() {
                return this.state?.turn?.last_event?.type === 'dice_timeout_skipped';
            },
            displayedDice(field) {
                if (this.lastTurnWasSkipped()) {
                    return '-';
                }

                if (this.diceAnimation.active) {
                    if (field === 'dice_one') return this.diceAnimation.first;
                    if (field === 'dice_two') return this.diceAnimation.second;
                    return this.diceAnimation.first + this.diceAnimation.second;
                }

                return this.state?.turn?.last_roll?.[field] ?? '-';
            },
            turnStatusText() {
                if (this.lastTurnWasSkipped()) {
                    return this.state.turn.last_event.description;
                }

                const roll = this.state?.turn?.last_roll;
                if (!roll) {
                    return 'Belum ada dadu yang dikocok.';
                }
                if (roll.result === 'go_to_jail') {
                    return `${roll.player_name} double 3 kali dan masuk penjara.`;
                }
                if (roll.result === 'jail_wait') {
                    return `${roll.player_name} belum keluar penjara.`;
                }
                if (roll.result === 'jail_released') {
                    return `${roll.player_name} keluar dari penjara.`;
                }
                if (roll.is_double) {
                    return `${roll.player_name} dapat double dan boleh kocok lagi.`;
                }

                return `${roll.player_name} jalan ${roll.total} langkah.`;
            },
            boardMoveText() {
                if (this.lastTurnWasSkipped()) {
                    return 'Posisi pemain tidak berubah. Giliran dilanjutkan ke pemain berikutnya.';
                }

                const movement = this.state?.turn?.last_roll?.meta?.movement;
                if (!movement?.to_space) {
                    return 'Posisi papan akan tampil setelah dadu dikocok.';
                }

                const action = movement.action;
                if (action?.message) {
                    return `${movement.from_space?.name || '-'} -> ${movement.to_space.name}. ${action.message}`;
                }

                return `${movement.from_space?.name || '-'} -> ${movement.to_space.name}`;
            },
            assetDeltaLabel(player) {
                const starting = Number(this.state?.game?.starting_balance || 0) + Number(this.state?.game?.starting_cash || 0);
                const delta = Number(player?.total_asset || 0) - starting;
                const sign = delta >= 0 ? '+' : '-';

                return `${sign}${this.money(Math.abs(delta))} dari awal`;
            },
            drawCharts() {
                if (!this.state || !window.Chart || window.matchMedia('(min-width: 1280px)').matches) return;
                const signature = JSON.stringify({
                    wealth: this.rankedPlayers().map((player) => [player.id, player.total_asset]),
                    ownership: this.state.charts.ownership,
                });
                if (signature === this.chartSignature) return;
                this.chartSignature = signature;
                Object.values(this.charts).forEach((chart) => chart?.destroy());
                this.charts = {};
                const wealthCanvas = document.getElementById('liveWealthChart');
                const ownershipCanvas = document.getElementById('liveOwnershipChart');
                const players = this.rankedPlayers();
                if (wealthCanvas) {
                    this.charts.wealth = new Chart(wealthCanvas, {
                        type: 'bar',
                        data: {
                            labels: players.map((player) => player.name),
                            datasets: [{
                                data: players.map((player) => player.total_asset),
                                backgroundColor: players.map((player) => player.avatar_color),
                                borderRadius: 16,
                            }],
                        },
                        options: this.chartOptions(false, 'y'),
                    });
                }
                if (ownershipCanvas) {
                    this.charts.ownership = new Chart(ownershipCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: this.state.charts.ownership.labels,
                            datasets: [{
                                data: this.state.charts.ownership.data,
                                backgroundColor: this.state.charts.ownership.colors,
                                borderWidth: 0,
                            }],
                        },
                        options: this.chartOptions(true),
                    });
                }
            },
            chartOptions(legend = false, indexAxis = 'x') {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis,
                    plugins: {
                        legend: {
                            display: legend,
                            labels: { color: '#dbeafe', boxWidth: 12, font: { size: 14, weight: 'bold' } },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.label}: ${this.money(context.parsed.x ?? context.parsed.y ?? context.parsed)}`,
                            },
                        },
                    },
                    scales: legend ? {} : {
                        x: { ticks: { color: '#dbeafe', font: { size: 14, weight: 'bold' } }, grid: { color: 'rgba(148,163,184,.12)' } },
                        y: { ticks: { color: '#dbeafe', font: { size: 16, weight: 'bold' } }, grid: { color: 'rgba(148,163,184,.12)' } },
                    },
                };
            },
            countdownSeconds() {
                if (!this.state?.game?.ends_at || this.state.game.status === 'finished') {
                    return this.state?.game?.remaining_seconds ?? null;
                }

                return Math.max(0, Math.floor((new Date(this.state.game.ends_at).getTime() - this.now) / 1000));
            },
            countdownLabel() {
                const seconds = this.countdownSeconds();
                return seconds === null || seconds === undefined ? 'Tanpa Timer' : this.duration(seconds);
            },
            duration(seconds) {
                const total = Number(seconds || 0);
                const hours = Math.floor(total / 3600);
                const minutes = Math.floor((total % 3600) / 60);
                const secs = total % 60;
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            },
            basePath() {
                const path = window.location.pathname;
                const publicIndex = path.indexOf('/public');
                return publicIndex >= 0 ? path.slice(0, publicIndex + '/public'.length) : '';
            },
            money(value) {
                return new Intl.NumberFormat('id-ID').format(Number(value || 0));
            },
            shortMoney(value) {
                const number = Number(value || 0);
                if (number >= 1000000) {
                    return `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(number / 1000000)} jt`;
                }
                if (number >= 1000) {
                    return `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(number / 1000)} rb`;
                }

                return this.money(number);
            },
        });
    </script>
</body>
</html>
