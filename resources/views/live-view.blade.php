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
    </style>
</head>
<body
    x-data="liveView({{ $gameId }})"
    x-init="init()"
    x-cloak
    class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-100 antialiased"
>
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-[linear-gradient(135deg,#020617_0%,#0f172a_45%,#172554_100%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(16,185,129,.22),transparent_26%),radial-gradient(circle_at_82%_22%,rgba(124,58,237,.24),transparent_28%),radial-gradient(circle_at_55%_88%,rgba(245,158,11,.18),transparent_34%)]"></div>
    </div>

    <main class="mx-auto flex min-h-screen w-full max-w-[1920px] flex-col gap-3 p-3 sm:gap-4 sm:p-4">
        <header class="glass-card grid items-center gap-4 p-4 xl:grid-cols-[minmax(320px,1fr)_minmax(560px,1.25fr)]">
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

        <section x-show="state?.game?.status !== 'finished'" class="grid flex-1 gap-4 xl:grid-cols-[minmax(300px,0.95fr)_minmax(360px,1fr)_minmax(330px,1fr)]">
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
                            <div class="live-die" :class="diceAnimating() ? 'animate-bounce' : ''" x-text="state?.turn?.last_roll?.dice_one || '-'"></div>
                            <div class="live-die" :class="diceAnimating() ? 'animate-bounce' : ''" x-text="state?.turn?.last_roll?.dice_two || '-'"></div>
                            <div class="rounded-3xl border border-emerald-300/20 bg-emerald-300/10 px-5 py-4 text-center">
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-200">Total</p>
                                <p class="text-4xl font-black text-emerald-300" x-text="state?.turn?.last_roll?.total || '-'"></p>
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
                        <div class="soft-scroll flex max-h-24 flex-wrap gap-1.5 overflow-y-auto">
                            <template x-for="property in unownedProperties()" :key="property.id">
                                <span class="rounded-full bg-slate-950/50 px-2.5 py-1 text-xs font-bold text-slate-200" x-text="property.name"></span>
                            </template>
                            <span x-show="unownedProperties().length === 0" class="text-xs text-emerald-300">Semua properti sudah dibeli</span>
                        </div>
                    </div>
                </div>

                <div class="glass-card flex min-h-[260px] flex-col p-4 xl:p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-blue-300">Baru Terjadi</p>
                            <h2 class="text-[clamp(1.7rem,2.2vw,3rem)] font-black">Transaksi Terbaru</h2>
                        </div>
                        <i data-lucide="activity" class="h-8 w-8 text-blue-300"></i>
                    </div>
                    <div class="soft-scroll min-h-0 flex-1 space-y-3 overflow-y-auto pr-1">
                        <template x-for="transaction in state?.transactions || []" :key="transaction.id">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <span class="rounded-full bg-slate-950/60 px-2.5 py-1 text-[11px] font-black uppercase text-slate-300" x-text="transaction.type.replaceAll('_', ' ')"></span>
                                    <span class="text-xs text-slate-400" x-text="transaction.created_at_label"></span>
                                </div>
                                <p class="text-lg font-black leading-snug" x-text="transaction.description"></p>
                                <p x-show="transaction.amount > 0" class="mt-1 text-lg font-black text-emerald-300" x-text="money(transaction.amount)"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </aside>
        </section>
    </main>

    <script>
        window.liveView = (gameId) => ({
            gameId,
            state: null,
            charts: {},
            chartSignature: '',
            stateRequestInFlight: false,
            animatedRollId: null,
            diceAnimationUntil: 0,
            now: Date.now(),
            realtime: {
                echo: null,
                connected: false,
            },
            init() {
                this.fetchState();
                setInterval(() => {
                    this.now = Date.now();
                    if (!this.realtime.connected) {
                        this.fetchState(true);
                    }
                }, 2500);
                this.connectRealtime();
            },
            async fetchState(silent = false) {
                if (this.stateRequestInFlight) {
                    return;
                }

                this.stateRequestInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/live/${this.gameId}`, { headers: { Accept: 'application/json' } });
                    const payload = await response.json();
                    const nextRollId = payload.state?.turn?.last_roll?.id || null;
                    if (nextRollId && nextRollId !== this.animatedRollId) {
                        this.animatedRollId = nextRollId;
                        this.diceAnimationUntil = Date.now() + 1200;
                    }
                    this.state = payload.state;
                    this.$nextTick(() => {
                        this.drawCharts();
                        window.lucide?.createIcons();
                    });
                } finally {
                    this.stateRequestInFlight = false;
                }
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
            unownedProperties() {
                return (this.state?.properties || []).filter((property) => !property.owner_id);
            },
            maxAsset() {
                return Math.max(...this.rankedPlayers().map((player) => Number(player.total_asset || 0)), 1);
            },
            assetBar(player) {
                return Math.max(6, Math.round((Number(player.total_asset || 0) / this.maxAsset()) * 100));
            },
            diceAnimating() {
                return Date.now() < this.diceAnimationUntil;
            },
            turnStatusText() {
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
                if (!this.state || !window.Chart) return;
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
