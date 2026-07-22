<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monopoly Digital Bank</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="monopolyBank()"
    x-init="init()"
    x-cloak
    :class="darkMode ? 'bg-slate-950 text-slate-100' : 'bg-slate-100 text-slate-900'"
    class="min-h-screen overflow-x-hidden font-sans antialiased transition-colors duration-300"
>
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-[linear-gradient(135deg,#020617_0%,#111827_48%,#172554_100%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_15%,rgba(16,185,129,0.22),transparent_26%),radial-gradient(circle_at_82%_20%,rgba(37,99,235,0.24),transparent_28%),radial-gradient(circle_at_52%_82%,rgba(124,58,237,0.18),transparent_32%)]"></div>
        <div class="absolute inset-0 bg-slate-950/60" x-show="darkMode"></div>
    </div>

    <div class="mx-auto flex min-h-screen w-full max-w-[1800px] flex-col px-4 pb-4 sm:px-5 lg:px-6">
        <header class="sticky top-0 z-30 mb-4 -mx-4 border-b border-white/10 bg-slate-950/55 px-4 py-3 shadow-2xl shadow-slate-950/20 backdrop-blur-2xl sm:-mx-5 sm:px-5 lg:-mx-6 lg:px-6">
            <div class="mx-auto flex max-w-[1800px] items-center justify-between gap-4">
            <button @click="setView('home')" class="flex items-center gap-3 rounded-2xl px-2 py-2 text-left transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <span class="relative grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-emerald-300 via-blue-400 to-purple-500 text-slate-950 shadow-lg shadow-emerald-500/20">
                    <i data-lucide="landmark" class="h-6 w-6"></i>
                    <span class="absolute -bottom-1 -right-1 grid h-6 w-6 place-items-center rounded-full bg-amber-300 text-[10px] font-black text-slate-950">6</span>
                </span>
                <span>
                    <span class="block text-lg font-black uppercase leading-tight tracking-wide">MONOPOLY DIGITAL BANK</span>
                    <span class="block text-xs font-medium text-slate-400">Smart Cashless & Hybrid Banking System for Monopoly</span>
                </span>
            </button>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <button @click="setView('new-game')" class="nav-chip"><i data-lucide="plus-circle" class="h-4 w-4"></i><span>Baru</span></button>
                <button @click="setView('continue')" class="nav-chip"><i data-lucide="play-circle" class="h-4 w-4"></i><span>Lanjut</span></button>
                <button @click="setView('history')" class="nav-chip"><i data-lucide="history" class="h-4 w-4"></i><span>Riwayat</span></button>
                <button @click="setView('settings')" class="nav-chip"><i data-lucide="settings" class="h-4 w-4"></i><span>Atur</span></button>
                <button @click="toggleTheme()" class="nav-chip"><i data-lucide="moon-star" class="h-4 w-4"></i><span x-text="darkMode ? 'Light' : 'Dark'"></span></button>
            </div>
            </div>
        </header>

        <main class="flex-1">
            <section x-show="loadingInitial" class="grid min-h-[70vh] place-items-center">
                <div class="glass-card px-8 py-7 text-center">
                    <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-emerald-300 border-t-transparent"></div>
                    <p class="text-sm font-semibold text-slate-300">Menyiapkan bank...</p>
                </div>
            </section>

            <section x-show="!loadingInitial && view === 'home'" x-transition.opacity class="grid min-h-[72vh] place-items-center">
                <div class="w-full max-w-6xl">
                    <div class="mb-8 max-w-3xl">
                        <p class="mb-3 text-sm font-bold uppercase tracking-[0.28em] text-emerald-300">Smarter Banking, Better Monopoly.</p>
                        <h1 class="text-5xl font-black leading-tight sm:text-6xl">🏛 Monopoly Banker</h1>
                        <p class="mt-4 max-w-2xl text-base text-slate-300">Aplikasi ini membantu satu orang menjadi bank saat bermain Monopoly. Uang pemain, pembelian tanah, bayar sewa, pajak, kartu hadiah, dan hasil akhir permainan bisa dicatat dari satu layar. Jadi permainan lebih cepat, rapi, dan semua pemain mudah melihat siapa yang sedang unggul.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <button @click="setView('new-game')" class="glass-card p-6 text-left transition hover:-translate-y-1 hover:border-emerald-300/50">
                            <span class="mb-8 grid h-12 w-12 place-items-center rounded-2xl bg-grey-400 text-xl font-black text-slate-950">🎮</span>
                            <span class="block text-xl font-black">Permainan Baru</span>
                            <span class="mt-2 block text-sm text-slate-400">Mulai permainan dari awal tanpa menghapus catatan permainan sebelumnya.</span>
                        </button>
                        <button @click="setView('continue')" class="glass-card p-6 text-left transition hover:-translate-y-1 hover:border-blue-300/50">
                            <span class="mb-8 grid h-12 w-12 place-items-center rounded-2xl bg-grey-400 text-xl font-black text-slate-950">▶️</span>
                            <span class="block text-xl font-black">Lanjutkan Permainan</span>
                            <span class="mt-2 block text-sm text-slate-400">Buka kembali permainan yang belum selesai.</span>
                        </button>
                        <button @click="setView('history')" class="glass-card p-6 text-left transition hover:-translate-y-1 hover:border-orange-300/50">
                            <span class="mb-8 grid h-12 w-12 place-items-center rounded-2xl bg-grey-400 text-xl font-black text-slate-950">📜</span>
                            <span class="block text-xl font-black">Riwayat Permainan</span>
                            <span class="mt-2 block text-sm text-slate-400">Lihat hasil permainan yang sudah selesai.</span>
                        </button>
                        <button @click="setView('settings')" class="glass-card p-6 text-left transition hover:-translate-y-1 hover:border-purple-300/50">
                            <span class="mb-8 grid h-12 w-12 place-items-center rounded-2xl bg-grey-400 text-xl font-black text-slate-950">⚙️</span>
                            <span class="block text-xl font-black">Pengaturan</span>
                            <span class="mt-2 block text-sm text-slate-400">Ubah aturan uang, pajak, dan harga kartu tanah.</span>
                        </button>
                    </div>
                </div>
            </section>

            <section x-show="view === 'new-game'" x-transition.opacity class="mx-auto w-full max-w-5xl">
                <div class="glass-card p-6">
                    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-emerald-300">Mulai Main</p>
                            <h2 class="mt-2 text-3xl font-black">Permainan Baru</h2>
                        </div>
                        <div class="flex rounded-2xl border border-white/10 bg-white/10 p-1">
                            <template x-for="count in [2, 3, 4, 5, 6, 7, 8]" :key="count">
                                <button
                                    @click="setPlayerCount(count)"
                                    :class="newGame.player_count === count ? 'bg-emerald-400 text-slate-950' : 'text-slate-300 hover:bg-white/10'"
                                    class="rounded-xl px-4 py-2 text-sm font-black transition"
                                >
                                    <span x-text="count"></span> Pemain
                                </button>
                            </template>
                        </div>
                    </div>

                    <form @submit.prevent="startGame" class="space-y-5">
                        <div class="grid gap-4 md:grid-cols-3">
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-slate-300">Saldo awal</span>
                                <input x-model.number="newGame.starting_balance" type="number" min="1" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 font-semibold outline-none transition focus:border-emerald-300">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-slate-300">Durasi permainan</span>
                                <select x-model="newGame.duration_minutes" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 font-semibold outline-none transition focus:border-blue-300">
                                    <option :value="null">Tanpa timer</option>
                                    <option :value="60">1 jam</option>
                                    <option :value="120">2 jam</option>
                                    <option :value="180">3 jam</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-slate-300">Uang tunai awal</span>
                                <input x-model.number="newGame.starting_cash" type="number" min="0" class="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 font-semibold outline-none transition focus:border-orange-300">
                            </label>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-sm font-bold text-slate-300">Catatan permainan</p>
                                <p class="mt-2 text-sm text-slate-400">Setiap permainan disimpan sendiri, jadi catatan permainan lama tetap aman.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <p class="text-sm font-bold text-slate-300">Waktu & uang pemain</p>
                                <p class="mt-2 text-sm text-slate-400">Setiap pemain punya uang di bank dan uang tunai yang sedang dipegang. Setor berarti uang tunai masuk ke bank, tarik berarti uang bank jadi tunai.</p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <template x-for="(player, index) in newGame.players" :key="index">
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div class="mb-4 flex items-center gap-3">
                                        <span class="grid h-10 w-10 place-items-center rounded-2xl text-sm font-black text-slate-950" :style="`background:${['#10b981','#2563eb','#f97316','#8b5cf6','#ec4899','#eab308','#14b8a6','#ef4444'][index]}`" x-text="index + 1"></span>
                                        <p class="font-black">Pemain <span x-text="index + 1"></span></p>
                                    </div>
                                    <label class="mb-3 block">
                                        <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Nama</span>
                                        <input x-model="player.name" required type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none transition focus:border-blue-300" placeholder="Nama pemain">
                                    </label>
                                    <label class="block">
                                        <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">RFID UID <span class="normal-case tracking-normal text-slate-500">(opsional)</span></span>
                                        <input x-model="player.rfid_uid" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 font-mono text-sm uppercase outline-none transition focus:border-emerald-300" placeholder="Kosongkan jika belum punya kartu">
                                    </label>
                                </div>
                            </template>
                        </div>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button type="button" @click="setView('home')" class="rounded-xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-bold transition hover:bg-white/15">Cancel</button>
                            <button type="submit" class="rounded-xl bg-emerald-400 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-300">Start Game</button>
                        </div>
                    </form>
                </div>
            </section>

            <section x-show="view === 'continue'" x-transition.opacity class="mx-auto w-full max-w-6xl">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-blue-300">Resume</p>
                        <h2 class="mt-2 text-3xl font-black">Lanjutkan Permainan</h2>
                    </div>
                    <button @click="fetchMeta()" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold transition hover:bg-white/15">Refresh</button>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <template x-for="game in activeGames" :key="game.id">
                        <article class="glass-card p-5">
                            <div class="mb-4 flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-bold text-slate-400" x-text="game.date"></p>
                                    <h3 class="mt-1 text-2xl font-black" x-text="game.code"></h3>
                                </div>
                                <span class="rounded-full border border-emerald-300/30 bg-emerald-400/10 px-3 py-1 text-xs font-black uppercase text-emerald-200" x-text="game.status"></span>
                            </div>
                            <p class="text-sm text-slate-300" x-text="game.players.join(', ')"></p>
                            <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl bg-white/5 p-3">
                                    <p class="text-slate-400">Durasi</p>
                                    <p class="font-black" x-text="duration(game.duration_seconds)"></p>
                                </div>
                                <div class="rounded-xl bg-white/5 p-3">
                                    <p class="text-slate-400">Transaksi</p>
                                    <p class="font-black" x-text="game.transaction_count"></p>
                                </div>
                            </div>
                            <div class="mt-5 grid grid-cols-[1fr_auto] gap-2">
                                <button @click="loadGame(game.id)" class="rounded-xl bg-blue-400 px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-blue-300">Continue</button>
                                <button @click="deleteContinueGame(game)" class="rounded-xl bg-rose-500 px-4 py-3 text-sm font-black text-white transition hover:bg-rose-400">Hapus</button>
                            </div>
                        </article>
                    </template>
                </div>

                <div x-show="activeGames.length === 0" class="glass-card p-8 text-center text-slate-400">
                    Belum ada game yang sedang berlangsung.
                </div>
            </section>

            <section x-show="view === 'history'" x-transition.opacity class="mx-auto w-full max-w-7xl">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-orange-300">Archive</p>
                        <h2 class="mt-2 text-3xl font-black">Riwayat Permainan</h2>
                    </div>
                    <button @click="fetchMeta()" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold transition hover:bg-white/15">Refresh</button>
                </div>

                <div class="glass-card overflow-hidden p-0">
                    <div class="grid grid-cols-[1.1fr_1.4fr_1fr_1fr_1fr_1fr_auto] gap-3 border-b border-white/10 px-5 py-3 text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                        <span>Tanggal</span>
                        <span>Pemain</span>
                        <span>Pemenang</span>
                        <span>Total aset</span>
                        <span>Durasi</span>
                        <span>Transaksi</span>
                        <span></span>
                    </div>
                    <template x-for="game in historyGames" :key="game.id">
                        <div class="grid grid-cols-[1.1fr_1.4fr_1fr_1fr_1fr_1fr_auto] items-center gap-3 border-b border-white/5 px-5 py-4 text-sm">
                            <span x-text="game.date"></span>
                            <span class="text-slate-300" x-text="game.players.join(', ')"></span>
                            <span class="font-black text-emerald-300" x-text="game.winner || '-'"></span>
                            <span x-text="money(game.total_assets)"></span>
                            <span x-text="duration(game.duration_seconds)"></span>
                            <span x-text="game.transaction_count"></span>
                            <div class="flex gap-2">
                                <button @click="loadGame(game.id)" class="rounded-lg bg-white/10 px-3 py-2 text-xs font-bold transition hover:bg-white/15">Detail</button>
                                <button @click="deleteHistoryGame(game)" class="rounded-lg bg-rose-500 px-3 py-2 text-xs font-black text-white transition hover:bg-rose-400">Hapus</button>
                            </div>
                        </div>
                    </template>
                    <div x-show="historyGames.length === 0" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada game yang selesai.</div>
                </div>
            </section>

            <section x-show="view === 'settings'" x-transition.opacity class="mx-auto w-full max-w-[1500px] space-y-5">
                <div class="glass-card p-5">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-purple-300">Configuration</p>
                            <h2 class="mt-2 text-3xl font-black">Pengaturan</h2>
                        </div>
                        <div class="flex gap-2">
                            <button @click="exportProperties()" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold transition hover:bg-white/15">Export CSV</button>
                            <label class="cursor-pointer rounded-xl bg-purple-400 px-4 py-2 text-sm font-black text-slate-950 transition hover:bg-purple-300">
                                Import CSV
                                <input type="file" accept=".csv,text/csv" class="hidden" @change="importProperties">
                            </label>
                        </div>
                    </div>

                    <form @submit.prevent="saveSettings" class="grid gap-4 md:grid-cols-5">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Saldo awal</span>
                            <input x-model.number="settingsForm.starting_balance" type="number" min="1" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-emerald-300">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Gaji</span>
                            <input x-model.number="settingsForm.go_bonus" type="number" min="0" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-blue-300">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Pajak</span>
                            <input x-model.number="settingsForm.tax_amount" type="number" min="0" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-orange-300">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Pajak Istimewa</span>
                            <input x-model.number="settingsForm.fine_amount" type="number" min="0" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-rose-300">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Cash fisik awal</span>
                            <input x-model.number="settingsForm.starting_cash" type="number" min="0" class="w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-blue-300">
                        </label>
                        <div class="md:col-span-5">
                            <button type="submit" class="rounded-xl bg-emerald-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-emerald-300">Simpan Pengaturan</button>
                        </div>
                    </form>
                </div>

                <div class="glass-card p-5">
                    <h3 class="mb-4 text-xl font-black">Tambah Properti</h3>
                    <form @submit.prevent="createProperty" class="grid gap-3 lg:grid-cols-6">
                        <input x-model="propertyForm.name" required placeholder="Nama" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-emerald-300 lg:col-span-2">
                        <input x-model.number="propertyForm.price" required type="number" min="0" placeholder="Harga" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-emerald-300">
                        <input x-model.number="propertyForm.house_price" required type="number" min="0" placeholder="Harga rumah" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-emerald-300">
                        <input x-model.number="propertyForm.hotel_price" required type="number" min="0" placeholder="Harga hotel" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-emerald-300">
                        <input x-model="propertyForm.color" type="color" class="h-[46px] rounded-xl border border-white/10 bg-slate-950/50 px-2 py-2">
                        <template x-for="field in ['rent','rent_1_house','rent_2_houses','rent_3_houses','rent_4_houses','rent_hotel']" :key="field">
                            <input x-model.number="propertyForm[field]" required type="number" min="0" :placeholder="field" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-blue-300">
                        </template>
                        <input x-model.number="propertyForm.sort_order" required type="number" min="0" placeholder="Urutan" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-blue-300">
                        <button type="submit" class="rounded-xl bg-purple-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-purple-300 lg:col-span-2">Tambah</button>
                    </form>
                </div>

                <div class="glass-card overflow-hidden p-0">
                    <div class="soft-scroll overflow-x-auto">
                        <table class="min-w-[1320px] w-full text-left text-sm">
                            <thead class="border-b border-white/10 text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Harga</th>
                                    <th class="px-4 py-3">Rumah</th>
                                    <th class="px-4 py-3">Hotel</th>
                                    <th class="px-4 py-3">Sewa</th>
                                    <th class="px-4 py-3">1R</th>
                                    <th class="px-4 py-3">2R</th>
                                    <th class="px-4 py-3">3R</th>
                                    <th class="px-4 py-3">4R</th>
                                    <th class="px-4 py-3">Hotel</th>
                                    <th class="px-4 py-3">Warna</th>
                                    <th class="px-4 py-3">Urutan</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="property in properties" :key="property.id">
                                    <tr class="border-b border-white/5">
                                        <td class="px-4 py-3"><input x-model="property.name" class="w-36 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none focus:border-emerald-300"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.price" type="number" class="w-24 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.house_price" type="number" class="w-24 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.hotel_price" type="number" class="w-24 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.rent" type="number" class="w-20 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.rent_1_house" type="number" class="w-20 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.rent_2_houses" type="number" class="w-20 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.rent_3_houses" type="number" class="w-20 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.rent_4_houses" type="number" class="w-20 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.rent_hotel" type="number" class="w-20 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3"><input x-model="property.color" type="color" class="h-9 w-14 rounded-lg border border-white/10 bg-slate-950/40 px-1"></td>
                                        <td class="px-4 py-3"><input x-model.number="property.sort_order" type="number" class="w-20 rounded-lg border border-white/10 bg-slate-950/40 px-2 py-1.5 outline-none"></td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-2">
                                                <button @click="saveProperty(property)" class="rounded-lg bg-emerald-400 px-3 py-2 text-xs font-black text-slate-950">Save</button>
                                                <button @click="deleteProperty(property)" class="rounded-lg bg-rose-500 px-3 py-2 text-xs font-black text-white">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <template x-if="current && view === 'dashboard'">
                <section class="space-y-4">
                    <div class="glass-card p-4">
                        <div class="mb-4 grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                            <template x-for="stat in dashboardStats()" :key="stat.label">
                                <article class="stat-card group">
                                    <span class="stat-icon" :class="stat.iconClass">
                                        <i :data-lucide="stat.icon" class="h-5 w-5"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-[11px] font-black uppercase tracking-[0.18em] text-slate-400" x-text="stat.label"></p>
                                        <p class="mt-1 truncate text-lg font-black" :class="stat.valueClass" x-text="stat.value"></p>
                                    </div>
                                </article>
                            </template>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-4">
                            <div class="grid gap-3 text-sm sm:grid-cols-4">
                                <div class="rounded-2xl bg-white/5 px-3 py-2">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Nomor Game</p>
                                    <p class="font-black" x-text="current.game.code"></p>
                                </div>
                                <div class="rounded-2xl bg-white/5 px-3 py-2">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Jam Mulai</p>
                                    <p class="font-black" x-text="current.game.started_at_label"></p>
                                </div>
                                <div class="rounded-2xl bg-white/5 px-3 py-2">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Durasi</p>
                                    <p class="font-black" x-text="duration(current.game.duration_seconds)"></p>
                                </div>
                                <div class="rounded-2xl bg-white/5 px-3 py-2">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Giliran</p>
                                    <p class="font-black text-emerald-300" x-text="current.turn?.current_player?.name || '-'"></p>
                                    <p class="text-xs text-slate-400" x-text="diceStatusText()"></p>
                                    <div x-show="turnCountdownSeconds() !== null" class="mt-2">
                                        <div class="mb-1 flex justify-between text-[10px] font-bold text-slate-400">
                                            <span>Diam jika habis</span><span x-text="`${turnCountdownSeconds()} dtk`"></span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-950/50">
                                            <div class="h-full rounded-full bg-purple-400 transition-all duration-1000" :style="`width:${turnCountdownPercent()}%`"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button @click="toggleMusic()" class="nav-chip"><i data-lucide="music-2" class="h-4 w-4"></i><span x-text="music.playing ? 'Pause Music' : 'Play Music'"></span></button>
                                <input x-model.number="music.volume" @input="setMusicVolume(music.volume)" type="range" min="0" max="1" step="0.05" class="w-20 accent-emerald-400">
                                <button x-show="current.game.first_player" @click="openSpinWheel()" class="rounded-xl border border-purple-300/30 bg-purple-400/10 px-3 py-2 text-sm font-bold text-purple-200 transition hover:bg-purple-400/15">
                                    Pertama: <span x-text="current.game.first_player?.name"></span>
                                </button>
                                <button x-show="current.game.status === 'active'" @click="gameControl('pause')" class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-bold transition hover:bg-white/15">Pause</button>
                                <button x-show="current.game.status === 'paused'" @click="gameControl('resume')" class="rounded-xl bg-blue-400 px-3 py-2 text-sm font-black text-slate-950 transition hover:bg-blue-300">Resume</button>
                                <button @click="openLiveView()" class="rounded-xl bg-purple-400 px-3 py-2 text-sm font-black text-white transition hover:bg-purple-300">Buka Live View</button>
                                <button x-show="current.game.status !== 'finished'" @click="gameControl('finish')" class="rounded-xl bg-emerald-400 px-3 py-2 text-sm font-black text-slate-950 transition hover:bg-emerald-300">Finish</button>
                                <button x-show="current.game.status !== 'finished'" @click="confirm('Reset current game?') && gameControl('reset')" class="rounded-xl bg-orange-400 px-3 py-2 text-sm font-black text-slate-950 transition hover:bg-orange-300">Reset</button>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-5">
                        <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-blue-300">HP Pemain</p>
                                <h2 class="mt-1 text-2xl font-black">Hubungkan Pemain ke Browser HP</h2>
                                <p class="mt-1 max-w-2xl text-sm text-slate-400">Buka halaman Bank memakai IP PC Bank, lalu pemain scan QR masing-masing. Pemain yang tidak pakai HP tetap bisa main lewat Bank/RFID.</p>
                            </div>
                            <button @click="copyBankUrl()" class="rounded-xl bg-blue-400 px-4 py-2 text-sm font-black text-slate-950 transition hover:bg-blue-300">Salin alamat Bank</button>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <template x-for="portal in current.player_portal.players" :key="portal.player_id">
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                                    <div class="mb-3 flex items-center justify-between gap-2">
                                        <p class="font-black" x-text="portal.player_name"></p>
                                        <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase" :class="portal.last_seen_at ? 'bg-emerald-300/15 text-emerald-200' : 'bg-slate-300/15 text-slate-300'" x-text="portal.status"></span>
                                    </div>
                                    <img :src="qrUrl(playerPortalUrl(portal.token))" alt="QR pemain" class="mx-auto h-32 w-32 rounded-xl border border-white/10 bg-white p-2">
                                    <p class="mt-2 break-all rounded-xl bg-slate-950/50 p-2 text-[11px] text-slate-300" x-text="playerPortalUrl(portal.token)"></p>
                                    <button @click="copyText(playerPortalUrl(portal.token))" class="mt-2 w-full rounded-xl bg-white/10 px-3 py-2 text-xs font-black transition hover:bg-white/15">Salin link HP</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)_360px]">
                        <aside class="space-y-3">
                            <template x-for="player in (current?.players || [])" :key="player.id">
                                <article
                                    @click="selectActivePlayer(player)"
                                    class="glass-card player-card relative overflow-hidden p-4 transition"
                                    :class="[
                                        isManualSelected(player) ? 'ring-2 ring-blue-300 shadow-blue-500/20' : '',
                                        Number(current.game.last_scanned_player_id) === Number(player.id) && !manualSelection.playerId ? 'ring-2 ring-emerald-300 shadow-emerald-500/20' : '',
                                        isRichest(player) ? 'ring-2 ring-amber-300 shadow-amber-500/20' : '',
                                        player.is_bankrupt ? 'opacity-60 grayscale' : 'cursor-pointer hover:-translate-y-0.5 hover:border-blue-300/50'
                                    ]"
                                >
                                    <div class="absolute inset-x-0 top-0 h-1.5" :style="`background:${player.avatar_color}`"></div>
                                    <div x-show="player.is_bankrupt" class="pointer-events-none absolute inset-0 grid place-items-center bg-slate-950/25">
                                        <i data-lucide="skull" class="h-16 w-16 text-rose-300/70"></i>
                                    </div>
                                    <div class="mb-4 flex items-center gap-3">
                                        <span class="grid h-12 w-12 place-items-center rounded-2xl text-base font-black text-slate-950 shadow-lg" :class="isRichest(player) ? 'ring-4 ring-amber-300/70' : ''" :style="`background:${player.avatar_color}`" x-text="player.name.slice(0, 1).toUpperCase()"></span>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h3 class="truncate text-lg font-black" x-text="player.name"></h3>
                                                <span x-show="player.is_bankrupt" class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black uppercase text-white">Bangkrut</span>
                                            </div>
                                            <p class="font-mono text-xs text-slate-400" x-text="player.rfid_uid"></p>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap gap-1.5 text-[10px] font-black uppercase">
                                        <span x-show="isRichest(player)" class="badge bg-amber-300/15 text-amber-200">Crown Terkaya</span>
                                        <span x-show="isPoorest(player)" class="badge bg-orange-300/15 text-orange-200">Termiskin</span>
                                        <span x-show="Number(current.game.first_player_id) === Number(player.id)" class="badge bg-purple-300/15 text-purple-200">Dadu Pertama</span>
                                        <span x-show="isManualSelected(player)" class="badge bg-blue-300/15 text-blue-200">Dipilih Bank <span x-text="manualSelectionRemaining()"></span>s</span>
                                        <span x-show="Number(current.game.last_scanned_player_id) === Number(player.id) && !manualSelection.playerId" class="badge bg-emerald-300/15 text-emerald-200">RFID Scan</span>
                                    </div>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <div class="rounded-xl bg-emerald-400/10 p-3">
                                            <p class="text-xs font-bold text-emerald-200">Uang di Bank</p>
                                            <p class="text-xl font-black text-emerald-300" x-text="money(player.balance)"></p>
                                        </div>
                                        <div class="rounded-xl bg-blue-400/10 p-3">
                                            <p class="text-xs font-bold text-blue-200">Uang Tunai</p>
                                            <p class="text-xl font-black text-blue-300" x-text="money(player.cash_balance)"></p>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                        <div class="rounded-xl bg-white/5 p-2">
                                            <p class="text-slate-400">Properti</p>
                                            <p class="font-black" x-text="player.property_count"></p>
                                        </div>
                                        <div class="rounded-xl bg-white/5 p-2">
                                            <p class="text-slate-400">Rumah</p>
                                            <p class="font-black" x-text="player.house_count"></p>
                                        </div>
                                        <div class="rounded-xl bg-white/5 p-2">
                                            <p class="text-slate-400">Hotel</p>
                                            <p class="font-black" x-text="player.hotel_count"></p>
                                        </div>
                                        <div class="rounded-xl bg-white/5 p-2">
                                            <p class="text-slate-400">Aset</p>
                                            <p class="font-black" x-text="player.is_bankrupt ? 'Bangkrut' : money(player.total_asset)"></p>
                                        </div>
                                        <div class="col-span-2 rounded-xl bg-blue-300/10 p-2">
                                            <p class="text-blue-100">Posisi</p>
                                            <p class="font-black text-blue-200" x-text="`${player.board_position}. ${player.current_space?.name || '-'}`"></p>
                                            <p class="text-[11px] text-slate-400" x-text="player.rules_unlocked ? `Putaran ${player.lap_count}` : 'Putaran awal - aturan belum aktif'"></p>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="mb-1 flex items-center justify-between text-[11px] font-bold text-slate-400">
                                            <span>Progress kepemilikan</span>
                                            <span x-text="`${propertyProgress(player)}%`"></span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-950/60">
                                            <div class="h-2 rounded-full bg-gradient-to-r from-emerald-300 via-blue-400 to-purple-400 transition-all duration-700" :style="`width:${propertyProgress(player)}%`"></div>
                                        </div>
                                    </div>
                                    <button
                                        x-show="current.game.status === 'active' && !player.is_bankrupt"
                                        @click.stop="bankruptPlayer(player)"
                                        class="mt-3 w-full rounded-xl bg-rose-500 px-3 py-2 text-xs font-black text-white transition hover:bg-rose-400"
                                    >
                                        Bangkrut
                                    </button>
                                </article>
                            </template>
                        </aside>

                        <main class="space-y-4">
                            <div class="glass-card p-5">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-300">Kartu Pemain</p>
                                    <h2 class="mt-1 text-2xl font-black">Panel Transaksi</h2>
                                    </div>
                                    <div class="flex w-full flex-wrap gap-2 sm:w-auto">
                                        <select
                                            :value="lastScannedPlayer()?.id || ''"
                                            @change="selectActivePlayer(playerById($event.target.value))"
                                            class="min-w-0 flex-1 rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 text-sm font-bold outline-none focus:border-blue-300 sm:w-52"
                                        >
                                            <option value="">Pilih pemain</option>
                                            <template x-for="player in activePlayers()" :key="player.id">
                                                <option :value="player.id" x-text="player.name"></option>
                                            </template>
                                        </select>
                                        <input x-model="rfid.uid" @keydown.enter.prevent="scanUid()" placeholder="Scan UID RFID" class="min-w-0 flex-1 rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 font-mono text-sm uppercase outline-none focus:border-emerald-300 sm:w-56">
                                        <button @click="scanUid()" class="rounded-xl bg-emerald-400 px-4 py-2.5 text-sm font-black text-slate-950 transition hover:bg-emerald-300">Pilih</button>
                                    </div>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-[1fr_1.2fr]">
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <template x-if="lastScannedPlayer()">
                                            <div>
                                                <div class="flex items-center justify-between gap-3">
                                                    <p class="text-sm text-slate-400" x-text="manualSelection.playerId ? 'Pemain dipilih Bank' : 'Pemain terakhir'"></p>
                                                    <span x-show="manualSelection.playerId" class="rounded-full bg-blue-400/15 px-2 py-1 text-[10px] font-black uppercase text-blue-200">
                                                        <span x-text="manualSelectionRemaining()"></span>s
                                                    </span>
                                                </div>
                                                <div class="mt-3 flex items-center gap-3">
                                                    <span class="grid h-12 w-12 place-items-center rounded-2xl text-base font-black text-slate-950" :style="`background:${lastScannedPlayer().avatar_color}`" x-text="lastScannedPlayer().name.slice(0, 1).toUpperCase()"></span>
                                                    <div>
                                                        <h3 class="text-xl font-black" x-text="lastScannedPlayer().name"></h3>
                                                        <p class="font-mono text-xs text-slate-400" x-text="lastScannedPlayer().rfid_uid"></p>
                                                    </div>
                                                </div>
                                                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                                    <div class="rounded-xl bg-emerald-400/10 p-3">
                                                        <p class="text-xs font-bold text-emerald-200">Uang di Bank</p>
                                                        <p class="text-2xl font-black text-emerald-300" x-text="money(lastScannedPlayer().balance)"></p>
                                                    </div>
                                                    <div class="rounded-xl bg-blue-400/10 p-3">
                                                        <p class="text-xs font-bold text-blue-200">Uang Tunai</p>
                                                        <p class="text-2xl font-black text-blue-300" x-text="money(lastScannedPlayer().cash_balance)"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!lastScannedPlayer()">
                                            <div class="py-8 text-center text-sm text-slate-400">Klik kartu pemain di kiri atau scan RFID dulu.</div>
                                        </template>
                                    </div>

                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <p class="mb-3 text-sm font-bold text-slate-300">Properti pemain</p>
                                        <div class="soft-scroll max-h-44 space-y-2 overflow-y-auto pr-1">
                                            <template x-if="lastScannedPlayer() && lastScannedPlayer().properties.length === 0">
                                                <p class="text-sm text-slate-400">Belum memiliki properti.</p>
                                            </template>
                                            <template x-for="property in (lastScannedPlayer()?.properties || [])" :key="property.id">
                                                <div class="flex items-center justify-between rounded-xl bg-slate-950/35 px-3 py-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="h-3 w-3 rounded-full" :style="`background:${property.color}`"></span>
                                                        <span class="font-semibold" x-text="property.name"></span>
                                                    </div>
                                                    <span class="text-xs text-slate-400">
                                                        R<span x-text="property.house_count"></span>
                                                        <span x-show="property.has_hotel">Hotel</span>
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="pendingBoardAction()" class="glass-card border-amber-300/20 bg-amber-300/10 p-5">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.2em] text-amber-200">Aksi Petak Menunggu</p>
                                        <h2 class="mt-1 text-2xl font-black" x-text="pendingBoardAction()?.player?.name"></h2>
                                        <p class="mt-1 text-sm text-slate-300" x-text="pendingBoardAction()?.action?.message"></p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-950/40 px-4 py-3 text-right">
                                        <p class="text-xs text-slate-400" x-text="pendingBoardAction()?.action?.space_name"></p>
                                        <p class="text-2xl font-black text-amber-200" x-text="pendingBoardAction()?.action?.amount ? money(pendingBoardAction()?.action?.amount) : 'Info'"></p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="mb-1 flex items-center justify-between text-xs font-bold">
                                        <span x-text="pendingBoardAction()?.action?.is_expired ? 'Waktu habis - Bank perlu membantu' : 'Waktu mengambil keputusan'"></span>
                                        <span x-text="`${actionCountdownSeconds() ?? 0} detik`"></span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-950/50">
                                        <div class="h-full rounded-full transition-all duration-1000" :class="pendingBoardAction()?.action?.is_expired ? 'bg-rose-400' : 'bg-amber-300'" :style="`width:${actionCountdownPercent()}%`"></div>
                                    </div>
                                </div>
                                <div x-show="pendingBoardAction()?.action?.is_expired" class="mt-4 flex flex-wrap gap-2">
                                    <button
                                        x-show="['pay_tax','pay_special_tax','pay_rent','card_pay_bank','card_repair_assets','card_utility_rent','card_collect_players','card_choice_pay_or_draw'].includes(pendingBoardAction()?.action?.action)"
                                        @click="resolvePendingBoardAction('pay', 'bank')"
                                        class="min-h-11 rounded-xl bg-emerald-400 px-4 py-2 text-sm font-black text-slate-950"
                                    >Selesaikan dari Saldo Bank</button>
                                    <button
                                        x-show="['pay_tax','pay_special_tax','pay_rent','card_pay_bank','card_repair_assets','card_utility_rent','card_choice_pay_or_draw'].includes(pendingBoardAction()?.action?.action)"
                                        @click="resolvePendingBoardAction('pay', 'cash')"
                                        class="min-h-11 rounded-xl bg-blue-400 px-4 py-2 text-sm font-black text-slate-950"
                                    >Selesaikan dari Cash</button>
                                    <button
                                        x-show="pendingBoardAction()?.action?.action === 'card_choice_pay_or_draw'"
                                        @click="resolvePendingBoardAction('draw_chance')"
                                        class="min-h-11 rounded-xl bg-purple-400 px-4 py-2 text-sm font-black text-white"
                                    >Ambil Kesempatan</button>
                                </div>
                            </div>

                            <div class="glass-card p-5">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-300">Aksi Cepat</p>
                                        <h2 class="mt-1 text-2xl font-black">Bayar & Catat Sekali Klik</h2>
                                    </div>
                                    <p class="max-w-md text-sm text-slate-400">Pilih pemain dulu, lalu klik kebutuhan transaksi. Untuk bayar sewa, pilih tanahnya dan uang akan langsung berpindah ke pemilik.</p>
                                </div>

                                <div class="grid gap-4 xl:grid-cols-[1fr_1.1fr]">
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <p class="mb-3 text-sm font-bold text-slate-300">Uang bank & uang tunai</p>
                                        <div class="grid gap-2 sm:grid-cols-4">
                                            <button @click="quickTransaction('bank_to_player', { amount: current.game.go_bonus, reason: 'Gaji', sound: 'go_salary' })" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-emerald-400 px-3 py-3 text-sm font-black text-slate-950 transition hover:bg-emerald-300 disabled:opacity-50">Gaji +<span x-text="money(current.game.go_bonus)"></span></button>
                                            <button @click="quickTransaction('bank_to_player', { amount: Math.floor(current.game.go_bonus / 2), reason: '1/2 Gaji', sound: 'go_salary' })" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-lime-300 px-3 py-3 text-sm font-black text-slate-950 transition hover:bg-lime-200 disabled:opacity-50">1/2 Gaji +<span x-text="money(Math.floor(current.game.go_bonus / 2))"></span></button>
                                            <button @click="quickTransaction('player_to_bank', { amount: current.game.tax_amount, reason: 'Pajak', sound: 'tax' })" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-orange-400 px-3 py-3 text-sm font-black text-slate-950 transition hover:bg-orange-300 disabled:opacity-50">Pajak -<span x-text="money(current.game.tax_amount)"></span></button>
                                            <button @click="quickTransaction('player_to_bank', { amount: current.game.fine_amount, reason: 'Pajak Istimewa', sound: 'fine' })" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-rose-500 px-3 py-3 text-sm font-black text-white transition hover:bg-rose-400 disabled:opacity-50">Pajak Istimewa -<span x-text="money(current.game.fine_amount)"></span></button>
                                        </div>

                                        <div class="mt-4 grid gap-2 sm:grid-cols-[1fr_auto_auto]">
                                            <input x-model.number="quick.amount" type="number" min="1" placeholder="Jumlah uang" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-blue-300">
                                            <button @click="quickTransaction('deposit', { amount: quick.amount })" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-blue-400 px-4 py-2.5 text-sm font-black text-slate-950 transition hover:bg-blue-300 disabled:opacity-50">Setor</button>
                                            <button @click="quickTransaction('withdraw', { amount: quick.amount })" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-white/10 px-4 py-2.5 text-sm font-black transition hover:bg-white/15 disabled:opacity-50">Tarik</button>
                                        </div>

                                        <div class="mt-4 grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                                            <select x-model.number="quick.receiver_id" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-emerald-300">
                                                <option value="">Pilih penerima</option>
                                                <template x-for="player in (current?.players || []).filter((item) => item.id !== lastScannedPlayer()?.id && !item.is_bankrupt)" :key="player.id">
                                                    <option :value="player.id" x-text="player.name"></option>
                                                </template>
                                            </select>
                                            <input x-model.number="quick.amount" type="number" min="1" placeholder="Jumlah uang" class="rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-emerald-300">
                                            <button @click="quickTransaction('transfer')" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-emerald-400 px-4 py-2.5 text-sm font-black text-slate-950 transition hover:bg-emerald-300 disabled:opacity-50">Kirim</button>
                                        </div>
                                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                            <button @click="quick.transfer_source = 'bank'" :class="quick.transfer_source === 'bank' ? 'bg-emerald-400 text-slate-950' : 'bg-white/10'" class="rounded-xl px-3 py-2 text-xs font-black transition">Uang di Bank</button>
                                            <button @click="quick.transfer_source = 'cash'" :class="quick.transfer_source === 'cash' ? 'bg-blue-400 text-slate-950' : 'bg-white/10'" class="rounded-xl px-3 py-2 text-xs font-black transition">Uang Tunai</button>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <p class="mb-3 text-sm font-bold text-slate-300">Tanah & bangunan</p>
                                        <select x-model.number="quick.property_id" class="mb-3 w-full rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2.5 outline-none focus:border-orange-300">
                                            <option value="">Pilih tanah atau tempat</option>
                                            <template x-for="property in current.properties" :key="property.id">
                                                <option :value="property.id" x-text="`${property.name} - ${property.owner_name || 'Bank'} - sewa ${money(currentRent(property))}`"></option>
                                            </template>
                                        </select>

                                        <template x-if="selectedQuickProperty()">
                                            <div class="mb-3 rounded-xl bg-slate-950/35 p-3 text-sm">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="font-black" x-text="selectedQuickProperty().name"></span>
                                                    <span class="rounded-full px-2.5 py-1 text-xs font-black" :style="`background:${selectedQuickProperty().color}; color:#020617`" x-text="selectedQuickProperty().owner_name || 'Bank'"></span>
                                                </div>
                                                <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-slate-400">
                                                    <span>Harga <b class="text-slate-200" x-text="money(selectedQuickProperty().price)"></b></span>
                                                    <span>Jual tanah <b class="text-slate-200" x-text="money(Math.floor(selectedQuickProperty().price / 2))"></b></span>
                                                    <span>1 rumah <b class="text-slate-200" x-text="money(selectedQuickProperty().house_price)"></b></span>
                                                    <span>1 hotel <b class="text-slate-200" x-text="money(selectedQuickProperty().hotel_price)"></b></span>
                                                    <span>Sewa <b class="text-slate-200" x-text="money(currentRent(selectedQuickProperty()))"></b></span>
                                                    <span>R<span x-text="selectedQuickProperty().house_count"></span> <span x-show="selectedQuickProperty().has_hotel">Hotel</span></span>
                                                </div>
                                                <p x-show="selectedQuickProperty().has_complete_group" class="mt-2 rounded-lg bg-emerald-400/10 px-2 py-1 text-xs font-black text-emerald-300">Komplek lengkap: sewa tanah otomatis x2</p>
                                            </div>
                                        </template>

                                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                            <button @click="quickTransaction('buy_property')" :disabled="!lastScannedPlayer() || !selectedQuickProperty() || selectedQuickProperty().owner_id || current.game.status !== 'active'" class="rounded-xl bg-blue-400 px-3 py-3 text-sm font-black text-slate-950 transition hover:bg-blue-300 disabled:opacity-50">Beli</button>
                                            <button @click="quickPayRent(selectedQuickProperty())" :disabled="!lastScannedPlayer() || !selectedQuickProperty() || !selectedQuickProperty().owner_id || current.game.status !== 'active'" class="rounded-xl bg-orange-400 px-3 py-3 text-sm font-black text-slate-950 transition hover:bg-orange-300 disabled:opacity-50">Bayar Sewa</button>
                                            <button @click="quickTransaction('add_house')" :disabled="!lastScannedPlayer() || !selectedQuickProperty() || Number(selectedQuickProperty().owner_id) !== Number(lastScannedPlayer()?.id) || current.game.status !== 'active'" class="rounded-xl bg-emerald-400 px-3 py-3 text-sm font-black text-slate-950 transition hover:bg-emerald-300 disabled:opacity-50">+ Rumah</button>
                                            <button @click="quickTransaction('sell_house')" :disabled="!lastScannedPlayer() || !selectedQuickProperty() || Number(selectedQuickProperty().owner_id) !== Number(lastScannedPlayer()?.id) || selectedQuickProperty().house_count < 1 || selectedQuickProperty().has_hotel || current.game.status !== 'active'" class="rounded-xl bg-white/10 px-3 py-3 text-sm font-black transition hover:bg-white/15 disabled:opacity-50">- Rumah</button>
                                            <button @click="quickTransaction('add_hotel')" :disabled="!lastScannedPlayer() || !selectedQuickProperty() || Number(selectedQuickProperty().owner_id) !== Number(lastScannedPlayer()?.id) || current.game.status !== 'active'" class="rounded-xl bg-purple-400 px-3 py-3 text-sm font-black text-slate-950 transition hover:bg-purple-300 disabled:opacity-50">+ Hotel</button>
                                            <button @click="quickTransaction('sell_hotel')" :disabled="!lastScannedPlayer() || !selectedQuickProperty() || Number(selectedQuickProperty().owner_id) !== Number(lastScannedPlayer()?.id) || !selectedQuickProperty().has_hotel || current.game.status !== 'active'" class="rounded-xl bg-white/10 px-3 py-3 text-sm font-black transition hover:bg-white/15 disabled:opacity-50">- Hotel</button>
                                            <button @click="quickTransaction('player_to_bank', { amount: selectedQuickProperty()?.price || 0, reason: 'Bayar properti manual' })" :disabled="!lastScannedPlayer() || !selectedQuickProperty() || current.game.status !== 'active'" class="rounded-xl bg-white/10 px-3 py-3 text-sm font-black transition hover:bg-white/15 disabled:opacity-50">Potong Harga</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card p-5">
                                <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-purple-300">Kartu Permainan</p>
                                        <h2 class="mt-1 text-2xl font-black">Dana Umum & Kesempatan</h2>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="max-w-md text-sm text-slate-400">Pilih pemain aktif, lalu ambil kartu online. Kartu tidak berulang sampai deck habis; kartu bebas penjara keluar dari deck selama disimpan pemain.</p>
                                        <button @click="drawRandomCard('Dana Umum')" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-emerald-400 px-4 py-2 text-xs font-black text-slate-950 transition hover:bg-emerald-300 disabled:opacity-50">Acak Dana Umum</button>
                                        <button @click="drawRandomCard('Kesempatan')" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-rose-400 px-4 py-2 text-xs font-black text-white transition hover:bg-rose-300 disabled:opacity-50">Acak Kesempatan</button>
                                        <button @click="drawRandomCard()" :disabled="!lastScannedPlayer() || current.game.status !== 'active'" class="rounded-xl bg-blue-400 px-4 py-2 text-xs font-black text-slate-950 transition hover:bg-blue-300 disabled:opacity-50">Acak Semua</button>
                                        <button @click="refreshCards()" :disabled="current.game.status === 'finished'" class="rounded-xl bg-purple-400 px-4 py-2 text-xs font-black text-slate-950 transition hover:bg-purple-300 disabled:opacity-50">Acak Ulang Kartu</button>
                                    </div>
                                </div>
                                <div class="grid gap-3 md:grid-cols-3">
                                    <div class="rounded-2xl border border-emerald-300/20 bg-emerald-300/10 p-4">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-200">Dana Umum</p>
                                        <p class="mt-2 text-3xl font-black text-emerald-300" x-text="`${current?.cards?.decks?.['Dana Umum']?.available ?? 0}/${current?.cards?.decks?.['Dana Umum']?.total ?? 0}`"></p>
                                        <p class="text-xs text-slate-400">Sisa kartu siap ambil</p>
                                    </div>
                                    <div class="rounded-2xl border border-rose-300/20 bg-rose-300/10 p-4">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-200">Kesempatan</p>
                                        <p class="mt-2 text-3xl font-black text-rose-300" x-text="`${current?.cards?.decks?.Kesempatan?.available ?? 0}/${current?.cards?.decks?.Kesempatan?.total ?? 0}`"></p>
                                        <p class="text-xs text-slate-400">Sisa kartu siap ambil</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Kartu Terakhir</p>
                                        <p class="mt-2 text-lg font-black" x-text="current?.cards?.last_draw?.card?.title || '-'"></p>
                                        <p class="text-xs text-slate-400" x-text="current?.cards?.last_draw?.player_name || 'Belum ada kartu'"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                <template x-for="button in transactionButtons" :key="button.type">
                                    <button
                                        @click="openModal(button.type)"
                                        :disabled="current.game.status !== 'active'"
                                        class="action-tile text-left disabled:hover:translate-y-0"
                                    >
                                        <span class="mb-4 grid h-11 w-11 place-items-center rounded-xl" :class="button.color">
                                            <i :data-lucide="button.icon" class="h-5 w-5"></i>
                                        </span>
                                        <span class="block text-sm font-black" x-text="button.label"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-3">
                                <div class="glass-card p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h3 class="font-black">Kekayaan Pemain</h3>
                                        <span class="text-xs font-bold text-emerald-300" x-text="money(current.stats.total_assets)"></span>
                                    </div>
                                    <div class="h-56"><canvas id="wealthChart"></canvas></div>
                                </div>
                                <div class="glass-card p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h3 class="font-black">Grafik Transaksi</h3>
                                        <span class="text-xs font-bold text-blue-300" x-text="`${current.stats.transaction_count} trx`"></span>
                                    </div>
                                    <div class="h-56"><canvas id="transactionChart"></canvas></div>
                                </div>
                                <div class="glass-card p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h3 class="font-black">Kepemilikan</h3>
                                        <span class="text-xs font-bold text-purple-300" x-text="`${current.stats.owned_property_count} properti`"></span>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-[140px_1fr]">
                                        <div class="h-44"><canvas id="ownershipChart"></canvas></div>
                                        <div class="soft-scroll max-h-44 space-y-2 overflow-y-auto pr-1">
                                            <template x-for="owner in current.stats.ownership_summary" :key="owner.name">
                                                <div class="rounded-xl bg-white/5 p-2 text-xs">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="flex min-w-0 items-center gap-2 font-black">
                                                            <span class="h-2.5 w-2.5 rounded-full" :style="`background:${owner.color}`"></span>
                                                            <span class="truncate" x-text="owner.name"></span>
                                                        </span>
                                                        <span class="font-black text-purple-300" x-text="money(owner.value)"></span>
                                                    </div>
                                                    <p class="mt-1 text-slate-400">
                                                        <span x-text="owner.properties"></span> properti,
                                                        <span x-text="owner.houses"></span> rumah,
                                                        <span x-text="owner.hotels"></span> hotel
                                                    </p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <div class="glass-card p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Most Cash</p>
                                    <p class="mt-2 text-xl font-black text-blue-300" x-text="money(statPlayer('cash_balance')?.cash_balance)"></p>
                                    <p class="mt-1 text-sm text-slate-400" x-text="statPlayer('cash_balance')?.name || '-'"></p>
                                </div>
                                <div class="glass-card p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Highest Bank Balance</p>
                                    <p class="mt-2 text-xl font-black text-emerald-300" x-text="money(statPlayer('balance')?.balance)"></p>
                                    <p class="mt-1 text-sm text-slate-400" x-text="statPlayer('balance')?.name || '-'"></p>
                                </div>
                                <div class="glass-card p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Most Transactions</p>
                                    <p class="mt-2 text-xl font-black text-purple-300" x-text="mostTransactions().count"></p>
                                    <p class="mt-1 text-sm text-slate-400" x-text="mostTransactions().name"></p>
                                </div>
                                <div class="glass-card p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Biggest Single Transaction</p>
                                    <p class="mt-2 text-xl font-black text-amber-300" x-text="money(biggestTransaction()?.amount)"></p>
                                    <p class="mt-1 truncate text-sm text-slate-400" x-text="biggestTransaction()?.description || '-'"></p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="glass-card p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Properti terbanyak</p>
                                    <p class="mt-2 text-xl font-black" x-text="current.stats.top_property_owner?.name || '-'"></p>
                                    <p class="mt-1 text-sm text-slate-400" x-text="`${current.stats.top_property_owner?.property_count || 0} properti`"></p>
                                </div>
                                <div class="glass-card p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Pemain terkaya (total aset)</p>
                                    <p class="mt-2 text-xl font-black text-emerald-300" x-text="current.stats.richest_player?.name || '-'"></p>
                                    <p class="mt-1 text-sm text-slate-400" x-text="money(current.stats.richest_player?.total_asset)"></p>
                                </div>
                                <div class="glass-card p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Pemain termiskin (total aset)</p>
                                    <p class="mt-2 text-xl font-black text-orange-300" x-text="current.stats.poorest_player?.name || '-'"></p>
                                    <p class="mt-1 text-sm text-slate-400" x-text="money(current.stats.poorest_player?.total_asset)"></p>
                                </div>
                            </div>
                        </main>

                        <aside class="glass-card max-h-[calc(100vh-150px)] overflow-hidden p-0 xl:sticky xl:top-4">
                            <div class="border-b border-white/10 p-5">
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-300">Permintaan HP</p>
                                <h2 class="mt-1 text-2xl font-black">Butuh Persetujuan</h2>
                                <div class="mt-4 space-y-2">
                                    <template x-for="request in current.player_portal.pending_requests" :key="request.id">
                                        <div class="rounded-2xl border border-amber-300/20 bg-amber-300/10 p-3">
                                            <div class="mb-2 flex items-start justify-between gap-2">
                                                <div>
                                                    <p class="font-black" x-text="request.player_name"></p>
                                                    <p class="text-sm text-slate-300" x-text="request.reason"></p>
                                                    <p class="mt-1 text-xs text-slate-400">
                                                        <span x-text="request.property_name || request.target_player_name || request.type_label"></span>
                                                        <span x-show="request.amount"> - <span x-text="money(request.amount)"></span></span>
                                                    </p>
                                                </div>
                                                <span class="text-xs text-slate-400" x-text="request.created_at_label"></span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button @click="approvePlayerRequest(request)" class="rounded-xl bg-emerald-400 px-3 py-2 text-xs font-black text-slate-950 transition hover:bg-emerald-300">Setujui</button>
                                                <button @click="rejectPlayerRequest(request)" class="rounded-xl bg-rose-500 px-3 py-2 text-xs font-black text-white transition hover:bg-rose-400">Tolak</button>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="current.player_portal.pending_requests.length === 0" class="rounded-2xl bg-white/5 p-3 text-center text-sm text-slate-400">Belum ada permintaan dari HP.</p>
                                </div>
                            </div>
                            <div class="border-b border-white/10 p-5">
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-amber-300">Leaderboard</p>
                                <h2 class="mt-1 text-2xl font-black">Top 3 Richest</h2>
                                <div class="mt-4 space-y-2">
                                    <template x-for="(player, index) in topPlayers()" :key="player.id">
                                        <div class="flex items-center justify-between rounded-2xl bg-white/5 p-3">
                                            <div class="flex min-w-0 items-center gap-3">
                                                <span class="grid h-9 w-9 place-items-center rounded-xl font-black text-slate-950" :class="index === 0 ? 'bg-amber-300' : index === 1 ? 'bg-slate-300' : 'bg-orange-300'" x-text="index + 1"></span>
                                                <div class="min-w-0">
                                                    <p class="truncate font-black" x-text="player.name"></p>
                                                    <p class="text-xs text-emerald-300" x-text="money(player.total_asset)"></p>
                                                </div>
                                            </div>
                                            <span class="text-xs font-black" :class="playerAssetDelta(player) >= 0 ? 'text-emerald-300' : 'text-rose-300'" x-text="assetDeltaLabel(player)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="border-b border-white/10 p-5">
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-blue-300">Realtime</p>
                                <h2 class="mt-1 text-2xl font-black">Timeline Transaksi</h2>
                            </div>
                            <div class="soft-scroll max-h-[calc(100vh-250px)] space-y-3 overflow-y-auto p-4">
                                <template x-for="transaction in current.transactions" :key="transaction.id">
                                    <article class="timeline-item">
                                        <span class="timeline-icon" :class="transactionTone(transaction.type)">
                                            <i :data-lucide="transactionIcon(transaction.type)" class="h-4 w-4"></i>
                                        </span>
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <span class="rounded-full bg-slate-950/60 px-2.5 py-1 text-[11px] font-black uppercase text-slate-300" x-text="transaction.type.replaceAll('_', ' ')"></span>
                                            <span class="text-xs text-slate-400" x-text="transaction.created_at_label"></span>
                                        </div>
                                        <p class="text-sm font-semibold leading-relaxed" x-text="transaction.description"></p>
                                        <p x-show="transaction.amount > 0" class="mt-2 text-sm font-black" :class="transactionAmountClass(transaction)" x-text="money(transaction.amount)"></p>
                                    </article>
                                </template>
                                <p x-show="current.transactions.length === 0" class="py-10 text-center text-sm text-slate-400">Belum ada transaksi.</p>
                            </div>
                        </aside>
                    </div>
                </section>
            </template>
        </main>

        <footer class="mt-8 border-t border-white/10 py-4 text-xs font-semibold text-slate-400">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p>Copyright &copy; 2026 <span class="font-black text-blue-300">IT Dept.</span> All rights reserved.</p>
                <p class="font-black text-slate-300">Version 1.0</p>
            </div>
        </footer>
    </div>

    <div x-show="loading && !loadingInitial" x-transition.opacity class="fixed bottom-5 left-1/2 z-50 -translate-x-1/2 rounded-full border border-white/10 bg-slate-950/90 px-4 py-2 text-sm font-bold text-white shadow-2xl">
        Loading...
    </div>

    <div x-show="spinWheel.open" x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-slate-950/80 p-4 backdrop-blur">
        <div class="glass-card w-full max-w-xl p-6 text-center">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-purple-300">Spin Wheel</p>
            <h2 class="mt-2 text-3xl font-black">Tentukan Pemain Pertama</h2>
            <div class="relative mx-auto mt-6 grid h-72 w-72 place-items-center">
                <div class="absolute -top-2 z-10 h-0 w-0 border-l-[14px] border-r-[14px] border-t-[26px] border-l-transparent border-r-transparent border-t-white"></div>
                <div class="relative h-64 w-64 rounded-full border-8 border-white/20 shadow-2xl transition-transform duration-[3200ms] ease-out" :style="wheelStyle()">
                    <template x-for="(player, index) in current?.players || []" :key="player.id">
                        <span class="absolute left-1/2 top-1/2 origin-[0_0] -translate-x-1/2 -translate-y-1/2 rounded-full bg-slate-950/70 px-2 py-1 text-xs font-black text-white shadow" :style="playerWheelLabelStyle(index)" x-text="player.name"></span>
                    </template>
                </div>
            </div>
            <p class="mt-4 text-lg font-black text-emerald-300" x-show="spinWheel.winner">Pemain pertama: <span x-text="spinWheel.winner?.name"></span></p>
            <div class="mt-6 flex justify-center gap-3">
                <button @click="spinFirstPlayer()" :disabled="spinWheel.spinning || current?.game?.first_player_id" class="rounded-xl bg-purple-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-purple-300 disabled:opacity-50">Spin</button>
                <button @click="closeSpinWheel()" :disabled="!current?.game?.first_player_id" class="rounded-xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-bold transition hover:bg-white/15 disabled:opacity-50">Masuk Game</button>
            </div>
        </div>
    </div>

    <div x-show="finishResult.open" x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-slate-950/90 p-4 backdrop-blur confetti-field">
        <div class="glass-card w-full max-w-5xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="mb-3 grid h-16 w-16 place-items-center rounded-3xl bg-amber-300 text-slate-950 shadow-2xl shadow-amber-500/30 crown-drop">
                        <i data-lucide="crown" class="h-9 w-9"></i>
                    </div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-300">GAME OVER</p>
                    <h2 class="mt-2 text-4xl font-black">Hasil Permainan</h2>
                    <p class="mt-1 text-sm text-slate-400">Peringkat dihitung dari semua kekayaan: uang di bank + uang tunai + tanah + rumah + hotel.</p>
                </div>
                <button @click="closeFinishResult()" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold transition hover:bg-white/15">Tutup</button>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-4">
                <div class="rounded-2xl border border-emerald-300/20 bg-emerald-400/10 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-200">Pemenang Utama</p>
                    <p class="mt-2 text-2xl font-black text-emerald-300" x-text="current?.stats?.richest_player?.name || '-'"></p>
                    <p class="mt-1 text-sm text-slate-300" x-text="money(current?.stats?.richest_player?.total_asset)"></p>
                </div>
                <div class="rounded-2xl border border-orange-300/20 bg-orange-400/10 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-orange-200">Pemain Termiskin</p>
                    <p class="mt-2 text-2xl font-black text-orange-300" x-text="current?.stats?.poorest_player?.name || '-'"></p>
                    <p class="mt-1 text-sm text-slate-300" x-text="money(current?.stats?.poorest_player?.total_asset)"></p>
                </div>
                <div class="rounded-2xl border border-blue-300/20 bg-blue-400/10 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-200">Durasi</p>
                    <p class="mt-2 text-2xl font-black text-blue-300" x-text="duration(current?.game?.duration_seconds)"></p>
                    <p class="mt-1 text-sm text-slate-300">Total waktu main</p>
                </div>
                <div class="rounded-2xl border border-purple-300/20 bg-purple-400/10 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-purple-200">Transaksi</p>
                    <p class="mt-2 text-2xl font-black text-purple-300" x-text="current?.stats?.transaction_count || 0"></p>
                    <p class="mt-1 text-sm text-slate-300">Aktivitas game</p>
                </div>
            </div>

            <div class="mt-5">
                <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Awards</p>
                <div class="grid gap-2 md:grid-cols-4">
                    <template x-for="award in awards()" :key="award.title">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                            <div class="mb-2 grid h-10 w-10 place-items-center rounded-xl" :class="award.color">
                                <i :data-lucide="award.icon" class="h-5 w-5"></i>
                            </div>
                            <p class="font-black" x-text="award.title"></p>
                            <p class="text-xs text-slate-400" x-text="award.description"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="soft-scroll mt-5 max-h-80 space-y-2 overflow-y-auto pr-1">
                <template x-for="(player, index) in [...(current?.players || [])].sort((a, b) => (b.is_bankrupt ? -1 : b.total_asset) - (a.is_bankrupt ? -1 : a.total_asset))" :key="player.id">
                    <div class="grid gap-3 rounded-2xl border border-white/10 bg-white/5 p-3 md:grid-cols-[auto_1fr_auto] md:items-center">
                        <span class="grid h-10 w-10 place-items-center rounded-xl text-sm font-black text-slate-950" :style="`background:${player.avatar_color}`" x-text="index + 1"></span>
                        <div>
                            <p class="font-black">
                                <span x-text="player.name"></span>
                                <span x-show="player.is_bankrupt" class="ml-2 rounded-full bg-rose-500 px-2 py-0.5 text-[10px] uppercase text-white">Bangkrut</span>
                            </p>
                            <p class="text-xs text-slate-400">
                                Bank <span x-text="money(player.balance)"></span> · Cash <span x-text="money(player.cash_balance)"></span> · Properti <span x-text="money(player.property_value)"></span> · Bangunan <span x-text="money(player.house_value + player.hotel_value)"></span>
                            </p>
                        </div>
                        <p class="text-xl font-black" :class="player.is_bankrupt ? 'text-rose-300' : 'text-emerald-300'" x-text="player.is_bankrupt ? 'Kalah' : money(player.total_asset)"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="fixed right-5 top-24 z-50 w-80 space-y-3">
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-transition
                class="flex gap-3 rounded-2xl border p-4 text-sm font-semibold shadow-2xl"
                :class="toast.type === 'error' ? 'border-rose-300/30 bg-rose-950/90 text-rose-100' : 'border-emerald-300/30 bg-emerald-950/90 text-emerald-100'"
            >
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl" :class="toast.type === 'error' ? 'bg-rose-400 text-white' : 'bg-emerald-300 text-slate-950'">
                    <i :data-lucide="toast.type === 'error' ? 'circle-alert' : 'badge-check'" class="h-5 w-5"></i>
                </span>
                <span class="leading-relaxed" x-text="toast.message"></span>
            </div>
        </template>
    </div>

    <div x-show="modal.open" x-transition.opacity class="fixed inset-0 z-40 grid place-items-center bg-slate-950/75 p-4 backdrop-blur-sm">
        <div @click.outside="closeModal()" class="modal-shell w-full max-w-2xl rounded-3xl border border-white/10 bg-slate-900 p-5 text-slate-100 shadow-2xl">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-300">Transaction</p>
                    <h2 class="mt-1 text-2xl font-black" x-text="modal.title"></h2>
                </div>
                <button @click="closeModal()" class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 text-lg font-black transition hover:bg-white/15">x</button>
            </div>

            <form @submit.prevent="submitModal" class="space-y-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="mb-3 flex gap-2">
                        <input x-model="rfid.uid" placeholder="UID RFID" class="min-w-0 flex-1 rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2.5 font-mono uppercase outline-none focus:border-emerald-300">
                        <template x-if="modal.type === 'transfer' || modal.type === 'transfer_property'">
                            <div class="flex gap-2">
                                <button type="button" @click="scanUid('from_player_id')" class="rounded-xl bg-blue-400 px-3 py-2 text-xs font-black text-slate-950">Pengirim</button>
                                <button type="button" @click="scanUid('to_player_id')" class="rounded-xl bg-emerald-400 px-3 py-2 text-xs font-black text-slate-950">Penerima</button>
                            </div>
                        </template>
                        <template x-if="modal.type !== 'transfer' && modal.type !== 'transfer_property'">
                            <button type="button" @click="scanUid('player_id')" class="rounded-xl bg-emerald-400 px-3 py-2 text-xs font-black text-slate-950">Pemain</button>
                        </template>
                    </div>
                            <p class="text-xs text-slate-400">Kode kartu ini membantu memilih pemain yang akan bertransaksi.</p>
                </div>

                <template x-if="modal.type === 'transfer' || modal.type === 'transfer_property'">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Pengirim</span>
                            <select x-model.number="modal.data.from_player_id" required class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2.5 outline-none focus:border-blue-300">
                                <option value="">Pilih pemain</option>
                                <template x-for="player in (current?.players || []).filter((item) => !item.is_bankrupt)" :key="player.id">
                                    <option :value="player.id" x-text="player.name"></option>
                                </template>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Penerima</span>
                            <select x-model.number="modal.data.to_player_id" required class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2.5 outline-none focus:border-emerald-300">
                                <option value="">Pilih pemain</option>
                                <template x-for="player in (current?.players || []).filter((item) => !item.is_bankrupt)" :key="player.id">
                                    <option :value="player.id" x-text="player.name"></option>
                                </template>
                            </select>
                        </label>
                    </div>
                </template>

                <template x-if="modal.type === 'transfer'">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Sumber uang</span>
                        <select x-model="modal.data.source" required class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2.5 outline-none focus:border-blue-300">
                            <option value="bank">Uang di Bank</option>
                            <option value="cash">Uang Tunai</option>
                        </select>
                    </label>
                </template>

                <template x-if="modal.type !== 'transfer' && modal.type !== 'transfer_property'">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Pemain</span>
                        <select x-model.number="modal.data.player_id" required class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2.5 outline-none focus:border-emerald-300">
                            <option value="">Pilih pemain</option>
                            <template x-for="player in (current?.players || []).filter((item) => !item.is_bankrupt)" :key="player.id">
                                <option :value="player.id" x-text="player.name"></option>
                            </template>
                        </select>
                    </label>
                </template>

                <template x-if="['buy_property','sell_property','transfer_property','add_house','sell_house','add_hotel','sell_hotel','auction'].includes(modal.type)">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Properti</span>
                        <select x-model.number="modal.data.game_property_id" required class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2.5 outline-none focus:border-orange-300">
                            <option value="">Pilih properti</option>
                            <template x-for="property in selectableProperties(modal.type, modal.data.player_id)" :key="property.id">
                                <option :value="property.id" x-text="`${property.name} - ${property.owner_name || 'Bank'} - ${money(property.price)}`"></option>
                            </template>
                        </select>
                    </label>
                </template>

                <template x-if="['transfer','deposit','withdraw','bank_to_player','player_to_bank','auction'].includes(modal.type)">
                    <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Jumlah uang</span>
                        <input x-model.number="modal.data.amount" required type="number" min="1" class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2.5 outline-none focus:border-emerald-300">
                    </label>
                </template>

                <template x-if="['deposit','withdraw','bank_to_player','player_to_bank'].includes(modal.type)">
                    <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Catatan</span>
                        <input x-model="modal.data.reason" type="text" class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2.5 outline-none focus:border-blue-300" placeholder="Gaji, hadiah, pajak, pajak istimewa, penjara">
                    </label>
                </template>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="closeModal()" class="rounded-xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-bold transition hover:bg-white/15">Cancel</button>
                    <button type="submit" class="rounded-xl bg-emerald-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-emerald-300">Proses</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
