<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HP Pemain - {{ $playerName }}</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes dice-tumble {
            0% { transform: rotate(0deg) scale(1); }
            35% { transform: rotate(7deg) scale(.94); }
            70% { transform: rotate(-7deg) scale(1.04); }
            100% { transform: rotate(0deg) scale(1); }
        }
        .dice-tumble { animation: dice-tumble .24s ease-in-out infinite; }
    </style>
</head>
<body
    x-data="playerPortal('{{ $token }}')"
    x-init="init()"
    x-cloak
    class="min-h-screen bg-slate-950 text-slate-100 antialiased"
>
    <div class="pointer-events-none fixed inset-0 -z-10 bg-[linear-gradient(135deg,#020617_0%,#111827_48%,#172554_100%)]"></div>

    <main class="mx-auto flex min-h-screen w-full max-w-2xl flex-col gap-4 px-4 py-5">
        <header class="glass-card p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-300">HP Pemain</p>
                    <h1 class="mt-1 text-2xl font-black" x-text="player?.name || '{{ $playerName }}'"></h1>
                    <p class="text-sm text-slate-400" x-text="game?.code || 'Memuat permainan...'"></p>
                </div>
                <span class="grid h-14 w-14 place-items-center rounded-2xl text-xl font-black text-slate-950" :style="`background:${player?.avatar_color || '#10b981'}`" x-text="(player?.name || '?').slice(0,1).toUpperCase()"></span>
            </div>
            <p x-show="connectionMessage" class="mt-3 rounded-xl bg-amber-300/10 p-2 text-xs font-bold text-amber-200" x-text="connectionMessage"></p>
        </header>

        <section class="grid grid-cols-2 gap-3">
            <div class="glass-card p-4">
                <p class="text-xs font-bold text-emerald-200">Uang di Bank</p>
                <p class="mt-1 text-2xl font-black text-emerald-300" x-text="money(player?.balance)"></p>
            </div>
            <div class="glass-card p-4">
                <p class="text-xs font-bold text-blue-200">Uang Tunai</p>
                <p class="mt-1 text-2xl font-black text-blue-300" x-text="money(player?.cash_balance)"></p>
            </div>
            <div class="glass-card p-4">
                <p class="text-xs font-bold text-amber-200">Total Aset</p>
                <p class="mt-1 text-2xl font-black text-amber-300" x-text="player?.is_bankrupt ? 'Kalah' : money(player?.total_asset)"></p>
            </div>
            <div class="glass-card p-4">
                <p class="text-xs font-bold text-purple-200">Status</p>
                <p class="mt-1 text-xl font-black" x-text="player?.is_bankrupt ? 'Bangkrut' : (game?.status_label || '-')"></p>
            </div>
        </section>

        <section x-show="game?.status === 'finished'" class="glass-card p-5">
            <div class="mb-4 text-center">
                <div class="mx-auto mb-3 grid h-16 w-16 place-items-center rounded-3xl bg-amber-300 text-slate-950">
                    <i data-lucide="crown" class="h-9 w-9"></i>
                </div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-amber-300">Game Telah Selesai</p>
                <h2 class="mt-2 text-3xl font-black">Hasil Akhir Permainan</h2>
                <p class="mt-1 text-sm text-slate-400">Transaksi sudah ditutup. Berikut statistik akhir pemain.</p>
            </div>
            <div class="mb-4 grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-emerald-300/20 bg-emerald-300/10 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-200">Pemenang</p>
                    <p class="mt-1 text-2xl font-black text-emerald-300" x-text="topPlayers()[0]?.name || '-'"></p>
                    <p class="text-sm text-slate-300" x-text="money(topPlayers()[0]?.total_asset)"></p>
                </div>
                <div class="rounded-2xl border border-blue-300/20 bg-blue-300/10 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-200">Durasi</p>
                    <p class="mt-1 text-2xl font-black text-blue-300" x-text="duration(game?.duration_seconds)"></p>
                    <p class="text-sm text-slate-300" x-text="`${transactions.length} transaksi kamu`"></p>
                </div>
                <div class="rounded-2xl border border-amber-300/20 bg-amber-300/10 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-amber-200">Aset Kamu</p>
                    <p class="mt-1 text-2xl font-black text-amber-300" x-text="player?.is_bankrupt ? money(player?.bankrupt_summary?.total_liquidation) : money(player?.total_asset)"></p>
                    <p class="text-sm text-slate-300" x-text="`${player?.property_count || 0} tanah`"></p>
                </div>
                <div class="rounded-2xl border border-purple-300/20 bg-purple-300/10 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-purple-200">Ranking Kamu</p>
                    <p class="mt-1 text-2xl font-black text-purple-300" x-text="`#${myRank()}`"></p>
                    <p class="text-sm text-slate-300" x-text="player?.is_bankrupt ? 'Bangkrut' : 'Selesai'"></p>
                </div>
            </div>
            <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Ranking Lengkap</p>
            <div class="space-y-2">
                <template x-for="(item, index) in topPlayers()" :key="item.id">
                    <div class="grid grid-cols-[auto_1fr_auto] items-center gap-3 rounded-2xl bg-white/5 p-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl font-black text-slate-950" :class="index === 0 ? 'bg-amber-300' : index === 1 ? 'bg-slate-300' : 'bg-orange-300'" x-text="index + 1"></span>
                        <div class="min-w-0">
                            <p class="truncate font-black" x-text="item.name"></p>
                            <p class="text-xs text-slate-400">
                                Bank <span x-text="money(item.balance)"></span> · Tunai <span x-text="money(item.cash_balance)"></span> · Tanah <span x-text="item.property_count"></span>
                            </p>
                        </div>
                        <p class="font-black" :class="item.is_bankrupt ? 'text-rose-300' : 'text-emerald-300'" x-text="item.is_bankrupt ? 'Kalah' : money(item.total_asset)"></p>
                    </div>
                </template>
            </div>
            <div class="mt-5">
                <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Rincian Kamu</p>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-xl bg-white/5 p-3">
                        <p class="text-slate-400">Uang di Bank</p>
                        <p class="font-black text-emerald-300" x-text="money(player?.balance)"></p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3">
                        <p class="text-slate-400">Uang Tunai</p>
                        <p class="font-black text-blue-300" x-text="money(player?.cash_balance)"></p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3">
                        <p class="text-slate-400">Nilai Tanah</p>
                        <p class="font-black text-amber-300" x-text="money(player?.property_value)"></p>
                    </div>
                    <div class="rounded-xl bg-white/5 p-3">
                        <p class="text-slate-400">Nilai Bangunan</p>
                        <p class="font-black text-purple-300" x-text="money((player?.house_value || 0) + (player?.hotel_value || 0))"></p>
                    </div>
                </div>
                <div x-show="player?.is_bankrupt" class="mt-3 rounded-2xl border border-rose-300/20 bg-rose-300/10 p-3 text-sm">
                    <p class="font-black text-rose-200">Catatan Bangkrut</p>
                    <p class="mt-1 text-slate-300">
                        Nilai jual aset: <b x-text="money(player?.bankrupt_summary?.sale_total)"></b>,
                        uang terakhir: <b x-text="money(player?.bankrupt_summary?.liquid_balance)"></b>,
                        total tercatat: <b x-text="money(player?.bankrupt_summary?.total_liquidation)"></b>.
                    </p>
                </div>
                <div class="mt-3 rounded-2xl border border-white/10 bg-white/5 p-3 text-sm">
                    <p class="font-black text-slate-200">Properti yang pernah dimiliki</p>
                    <p class="mt-1 text-slate-300" x-text="player?.history?.ever_owned_properties?.length ? player.history.ever_owned_properties.join(', ') : 'Belum ada catatan properti.'"></p>
                </div>
                <div class="mt-3 rounded-2xl border border-white/10 bg-white/5 p-3 text-sm">
                    <p class="mb-2 font-black text-slate-200">Riwayat selama bermain</p>
                    <div class="max-h-56 space-y-2 overflow-y-auto pr-1">
                        <template x-for="item in player?.history?.transactions || []" :key="item.id">
                            <div class="rounded-xl bg-slate-950/40 p-2">
                                <p class="font-bold" x-text="item.description"></p>
                                <p class="text-xs text-slate-400" x-text="item.created_at_label"></p>
                            </div>
                        </template>
                        <p x-show="!player?.history?.transactions?.length" class="text-slate-400">Belum ada transaksi pribadi.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Transaksi manual dipusatkan di layar Bank agar HP pemain fokus pada giliran dan keuangan. --}}
        {{--
        <section x-show="game?.status !== 'finished'" x-data="{ manualOpen: false }" class="glass-card order-[90] p-4">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Cadangan</p>
                    <h2 class="text-lg font-black">Menu Manual / Override</h2>
                    <p class="text-xs text-slate-400">Dipakai kalau aksi otomatis dari petak papan perlu dibantu manual.</p>
                </div>
                <button
                    type="button"
                    @click="manualOpen = !manualOpen"
                    class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-xs font-black transition hover:bg-white/15"
                >
                    <span x-show="!manualOpen">Buka</span>
                    <span x-show="manualOpen">Tutup</span>
                </button>
            </div>

            <div x-show="player?.is_in_jail" class="mb-3 rounded-2xl border border-rose-300/20 bg-rose-300/10 p-3">
                <p class="font-black text-rose-200">Kamu sedang di penjara</p>
                <p class="text-sm text-slate-300">Kamu tetap ikut giliran. Bisa keluar dengan double, bayar 5.000, atau pakai kartu bebas penjara.</p>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button @click="requestJailAction('pay_jail_fee')" :disabled="submitInFlight || hasPendingType('pay_jail_fee')" class="rounded-xl bg-amber-300 px-3 py-2 text-sm font-black text-slate-950 disabled:opacity-50">Bayar 5.000</button>
                    <button @click="requestJailAction('use_jail_card')" :disabled="submitInFlight || hasPendingType('use_jail_card') || Number(player?.jail_free_cards || 0) <= 0" class="rounded-xl bg-purple-400 px-3 py-2 text-sm font-black text-white disabled:opacity-50">Pakai Kartu</button>
                </div>
                <p x-show="player?.pending_jail_release" class="mt-2 text-sm font-black text-emerald-200">Sudah disetujui Bank. Bebas mulai giliran berikutnya.</p>
            </div>

            <div x-show="manualOpen" x-transition class="space-y-3">
                <div class="rounded-2xl border border-amber-300/20 bg-amber-300/10 p-3 text-sm text-amber-100">
                    Transaksi utama sebaiknya lewat dadu dan popup posisi papan. Menu ini hanya untuk cadangan, koreksi, atau transaksi khusus yang belum otomatis.
                </div>

                <div class="flex justify-end">
                    <span class="rounded-full px-3 py-1 text-xs font-black" :class="form.type === 'pay_rent' ? 'bg-emerald-300/15 text-emerald-200' : 'bg-amber-300/15 text-amber-200'" x-text="form.type === 'pay_rent' ? 'Bayar langsung' : 'Butuh persetujuan Bank'"></span>
                </div>

                <label class="block">
                    <span class="mb-1 block text-xs font-black uppercase tracking-[0.16em] text-slate-400">Aksi</span>
                    <select x-model="form.type" class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-3 font-bold outline-none focus:border-emerald-300">
                        <option value="buy_property">Beli tanah/tempat</option>
                        <option value="pay_rent">Bayar sewa</option>
                        <option value="add_house">Beli rumah</option>
                        <option value="add_hotel">Beli hotel</option>
                        <option value="sell_property">Jual tanah</option>
                        <option value="sell_house">Jual rumah</option>
                        <option value="sell_hotel">Jual hotel</option>
                        <option value="transfer">Kirim uang ke pemain</option>
                        <option value="player_to_bank">Bayar ke Bank</option>
                        <option value="bank_to_player">Minta uang dari Bank</option>
                        <option value="deposit">Setor tunai</option>
                        <option value="withdraw">Tarik tunai</option>
                    </select>
                </label>

                <label class="block" x-show="needsProperty()">
                    <span class="mb-1 block text-xs font-black uppercase tracking-[0.16em] text-slate-400">Tanah/tempat</span>
                    <select x-model.number="form.game_property_id" @change="syncRentAmount()" class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-3 font-bold outline-none focus:border-amber-300">
                        <option value="">Pilih dari papan</option>
                        <template x-for="property in filteredProperties()" :key="property.id">
                            <option :value="property.id" x-text="propertyOptionLabel(property)"></option>
                        </template>
                    </select>
                </label>

                <div x-show="selectedProperty()" class="rounded-2xl border border-white/10 bg-white/5 p-3 text-sm">
                    <p class="font-black" x-text="selectedProperty()?.name"></p>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-slate-300">
                        <p>Harga tanah: <b x-text="money(selectedProperty()?.price)"></b></p>
                        <p>Jual tanah: <b x-text="money(Math.floor((selectedProperty()?.price || 0) / 2))"></b></p>
                        <p>Harga 1 rumah: <b x-text="money(selectedProperty()?.house_price)"></b></p>
                        <p>Harga 1 hotel: <b x-text="money(selectedProperty()?.hotel_price)"></b></p>
                        <p>Sewa sekarang: <b x-text="money(selectedProperty()?.current_rent)"></b></p>
                        <p x-show="selectedProperty()?.has_complete_group" class="text-emerald-300">Komplek lengkap: sewa x2</p>
                        <p x-show="selectedProperty()?.property_kind === 'utility'" class="col-span-2 text-blue-300" x-text="utilityRentInfo()"></p>
                    </div>
                </div>

                <label class="block" x-show="form.type === 'transfer'">
                    <span class="mb-1 block text-xs font-black uppercase tracking-[0.16em] text-slate-400">Penerima</span>
                    <select x-model.number="form.target_player_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-3 font-bold outline-none focus:border-blue-300">
                        <option value="">Pilih pemain</option>
                        <template x-for="item in players.filter((item) => item.id !== player?.id && !item.is_bankrupt)" :key="item.id">
                            <option :value="item.id" x-text="item.name"></option>
                        </template>
                    </select>
                </label>

                <label class="block" x-show="needsAmount()">
                    <span class="mb-1 block text-xs font-black uppercase tracking-[0.16em] text-slate-400">Jumlah uang</span>
                    <input x-model.number="form.amount" type="number" min="1" class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-3 font-bold outline-none focus:border-emerald-300">
                </label>

                <div class="grid grid-cols-2 gap-2" x-show="['transfer','pay_rent'].includes(form.type)">
                    <button @click="form.source = 'bank'" :class="form.source === 'bank' ? 'bg-emerald-400 text-slate-950' : 'bg-white/10'" class="rounded-xl px-3 py-3 text-sm font-black">Uang di Bank</button>
                    <button @click="form.source = 'cash'" :class="form.source === 'cash' ? 'bg-blue-400 text-slate-950' : 'bg-white/10'" class="rounded-xl px-3 py-3 text-sm font-black">Uang Tunai</button>
                </div>

                <input x-show="form.type !== 'pay_rent'" x-model="form.reason" placeholder="Catatan singkat untuk Bank" class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-3 font-semibold outline-none focus:border-purple-300">

                <div x-show="needsAmount() && Number(form.amount || 0) > availableMoney()" class="rounded-2xl border border-amber-300/20 bg-amber-300/10 p-3">
                    <p class="text-sm font-black text-amber-200">Saldo kurang. Saran jual aset:</p>
                    <div class="mt-2 space-y-2">
                        <template x-for="(plan, index) in saleRecommendations()" :key="index">
                            <div class="rounded-xl bg-slate-950/40 p-2 text-xs">
                                <p class="font-black text-slate-100" x-text="`Pilihan ${index + 1}: ${money(plan.total)} terkumpul`"></p>
                                <p class="text-slate-300" x-text="plan.items.join(' + ')"></p>
                                <button @click="submitBulkSellPlan(plan)" :disabled="submitInFlight || hasPendingType('bulk_sell_assets')" class="mt-2 rounded-lg bg-amber-300 px-2.5 py-1.5 text-[11px] font-black text-slate-950 disabled:opacity-50">
                                    Ajukan paket ini ke Bank
                                </button>
                            </div>
                        </template>
                        <p x-show="saleRecommendations().length === 0" class="text-xs text-slate-400">Belum ada aset yang cukup untuk menutup kekurangan.</p>
                    </div>
                </div>

                <button @click="submitRequest()" :disabled="loading || submitInFlight || hasPendingSimilarRequest() || player?.is_bankrupt || game?.status !== 'active'" class="w-full rounded-xl bg-emerald-400 px-4 py-3 font-black text-slate-950 transition hover:bg-emerald-300 disabled:opacity-50">
                    <span x-show="!submitInFlight && !hasPendingSimilarRequest()" x-text="form.type === 'pay_rent' ? 'Bayar Sewa Sekarang' : 'Ajukan ke Bank'"></span>
                    <span x-show="submitInFlight">Mengirim...</span>
                    <span x-show="!submitInFlight && hasPendingSimilarRequest()">Menunggu Bank</span>
                </button>
            </div>
        </section>
        --}}

        <section x-show="game?.status !== 'finished' && player?.is_in_jail" class="glass-card border-rose-300/20 bg-rose-300/10 p-4">
            <div class="flex items-start gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-rose-400 text-white"><i data-lucide="lock-keyhole" class="h-6 w-6"></i></span>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-200">Status Penjara</p>
                    <h2 class="mt-1 text-xl font-black">Kamu sedang di penjara</h2>
                    <p class="mt-1 text-sm text-slate-300">Tetap tunggu giliran. Kamu bisa keluar dengan double, bayar 5.000 melalui Bank, atau pakai kartu milikmu sendiri.</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <button @click="requestJailAction('pay_jail_fee')" :disabled="submitInFlight || hasPendingType('pay_jail_fee') || player?.pending_jail_release" class="min-h-11 rounded-xl bg-amber-300 px-3 py-2 text-sm font-black text-slate-950 disabled:opacity-50">Minta Bayar 5.000</button>
                <button @click="useJailCardNow()" :disabled="submitInFlight || Number(player?.jail_free_cards || 0) <= 0 || player?.pending_jail_release" class="min-h-11 rounded-xl bg-purple-400 px-3 py-2 text-sm font-black text-white disabled:opacity-50">Pakai Kartu Sendiri</button>
            </div>
            <p x-show="player?.pending_jail_release" class="mt-3 rounded-xl bg-emerald-300/10 p-3 text-sm font-black text-emerald-200">Sudah siap bebas. Pada giliran berikutnya kamu langsung keluar lalu menjalankan dadu.</p>
        </section>

        <section x-show="game?.status !== 'finished' && player?.current_space" class="glass-card p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-300">Posisi Papan</p>
                    <h2 class="mt-1 text-2xl font-black" x-text="player?.current_space?.name || '-'"></h2>
                    <p class="text-sm text-slate-400">
                        Putaran <span x-text="player?.lap_count || 0"></span>
                        <span x-show="!player?.rules_unlocked">- aturan belum aktif sampai lewat Start</span>
                    </p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-black" :class="player?.rules_unlocked ? 'bg-emerald-300/15 text-emerald-200' : 'bg-amber-300/15 text-amber-200'" x-text="player?.rules_unlocked ? 'Aturan aktif' : 'Putaran awal'"></span>
            </div>

            <div x-show="player?.pending_space_action" class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-300" x-text="spaceAction()?.label"></p>
                <h3 class="mt-1 text-xl font-black" x-text="spaceAction()?.space_name"></h3>
                <p class="mt-1 text-sm text-slate-300" x-text="spaceAction()?.message"></p>
                <div x-show="spaceAction()?.action_deadline_at" class="mt-3">
                    <div class="mb-1 flex items-center justify-between text-xs font-bold" :class="spaceAction()?.is_expired ? 'text-rose-200' : 'text-slate-300'">
                        <span x-text="spaceAction()?.is_expired ? 'Waktu habis - minta bantuan Bank' : 'Waktu untuk menyelesaikan aksi'"></span>
                        <span x-text="`${actionCountdownSeconds()} detik`"></span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-950/60">
                        <div class="h-full rounded-full transition-all duration-1000" :class="spaceAction()?.is_expired ? 'bg-rose-400' : 'bg-blue-400'" :style="`width:${actionCountdownPercent()}%`"></div>
                    </div>
                </div>
                <div x-show="spaceAction()?.action === 'own_property'" class="mt-3 rounded-2xl border border-white/10 bg-slate-950/40 p-3 text-xs text-slate-300">
                    <p class="font-black text-slate-100">Detail properti milikmu</p>
                    <p class="mt-1">Rumah saat ini: <b x-text="spaceAction()?.house_count || 0"></b> / 4 · Hotel: <b x-text="spaceAction()?.has_hotel ? 1 : 0"></b></p>
                    <p class="mt-1">Harga beli rumah: <b x-text="money(spaceAction()?.house_price)"></b> · Harga beli hotel: <b x-text="money(spaceAction()?.hotel_price)"></b></p>
                    <p class="mt-1">Harga jual 1 rumah (1/2): <b x-text="money(spaceAction()?.sell_house_value)"></b> · Harga jual hotel (1/2): <b x-text="money(spaceAction()?.sell_hotel_value)"></b></p>
                    <p class="mt-1">Harga jual tanah (1/2): <b x-text="money(spaceAction()?.sell_property_value)"></b></p>
                </div>
                <div x-show="spaceAction()?.card" class="mt-3 rounded-3xl border p-4" :class="spaceAction()?.card?.deck === 'Dana Umum' ? 'border-emerald-300/30 bg-emerald-300/10' : 'border-rose-300/30 bg-rose-300/10'">
                    <p class="text-xs font-black uppercase tracking-[0.18em]" :class="spaceAction()?.card?.deck === 'Dana Umum' ? 'text-emerald-200' : 'text-rose-200'" x-text="spaceAction()?.card?.deck"></p>
                    <h4 class="mt-1 text-2xl font-black" x-text="spaceAction()?.card?.title"></h4>
                    <p class="mt-2 text-sm text-slate-200" x-text="spaceAction()?.card?.description"></p>
                    <div x-show="spaceAction()?.repair" class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-300">
                        <span>Rumah: <b x-text="`${spaceAction()?.repair?.houses || 0} x ${money(spaceAction()?.repair?.house_amount)}`"></b></span>
                        <span>Hotel: <b x-text="`${spaceAction()?.repair?.hotels || 0} x ${money(spaceAction()?.repair?.hotel_amount)}`"></b></span>
                    </div>
                </div>
                <p x-show="spaceAction()?.amount" class="mt-3 text-3xl font-black" :class="['pay_tax','pay_special_tax','pay_rent','card_pay_bank','card_repair_assets','card_utility_rent','card_choice_pay_or_draw'].includes(spaceAction()?.action) ? 'text-rose-300' : 'text-emerald-300'" x-text="money(spaceAction()?.amount)"></p>

                <div x-show="['pay_tax','pay_special_tax','pay_rent','card_pay_bank','card_repair_assets','card_utility_rent','card_choice_pay_or_draw'].includes(spaceAction()?.action)" class="mt-3 grid grid-cols-2 gap-2">
                    <button @click="form.source = 'bank'" :class="form.source === 'bank' ? 'bg-emerald-400 text-slate-950' : 'bg-white/10'" class="rounded-xl px-3 py-3 text-sm font-black">Uang Bank</button>
                    <button @click="form.source = 'cash'" :class="form.source === 'cash' ? 'bg-blue-400 text-slate-950' : 'bg-white/10'" class="rounded-xl px-3 py-3 text-sm font-black">Uang Tunai</button>
                </div>

                <div x-show="['pay_tax','pay_special_tax','pay_rent','card_pay_bank','card_repair_assets','card_utility_rent','card_choice_pay_or_draw'].includes(spaceAction()?.action) && spaceAction()?.amount > actionAvailableMoney()" class="mt-3 rounded-2xl border border-amber-300/20 bg-amber-300/10 p-3">
                    <p class="text-sm font-black text-amber-200">Uang yang dipilih kurang. Saran jual aset:</p>
                    <div class="mt-2 space-y-2">
                        <template x-for="(plan, index) in actionSaleRecommendations()" :key="index">
                            <div class="rounded-xl bg-slate-950/40 p-2 text-xs">
                                <p class="font-black text-slate-100" x-text="`Pilihan ${index + 1}: ${money(plan.total)} terkumpul`"></p>
                                <p class="text-slate-300" x-text="plan.items.join(' + ')"></p>
                                <button @click="submitBulkSellPlan(plan)" :disabled="submitInFlight || hasPendingType('bulk_sell_assets')" class="mt-2 rounded-lg bg-amber-300 px-2.5 py-1.5 text-[11px] font-black text-slate-950 disabled:opacity-50">
                                    Ajukan paket ini ke Bank
                                </button>
                            </div>
                        </template>
                        <p x-show="actionSaleRecommendations().length === 0" class="text-xs text-slate-400">Kalau semua aset tetap kurang, pemain harus bangkrut.</p>
                    </div>
                    <button @click="openBankruptcyModal()" :disabled="submitInFlight" class="mt-3 min-h-11 w-full rounded-xl border border-rose-300/30 bg-rose-400/15 px-3 py-2 text-sm font-black text-rose-100 disabled:opacity-50">
                        Pilih Bangkrut
                    </button>
                </div>

                <div class="mt-4 grid gap-2" :class="['buy_property','own_property','card_choice_pay_or_draw'].includes(spaceAction()?.action) ? 'grid-cols-3' : 'grid-cols-2'">
                    <button x-show="spaceAction()?.action === 'buy_property'" @click="resolveSpaceAction('buy')" :disabled="submitInFlight || spaceAction()?.is_expired" class="rounded-xl bg-emerald-400 px-3 py-3 text-sm font-black text-slate-950 disabled:opacity-50">Beli</button>
                    <button x-show="spaceAction()?.action === 'own_property'" @click="resolveSpaceAction('add_house')" :disabled="submitInFlight || spaceAction()?.is_expired || !spaceAction()?.can_buy_house" class="rounded-xl bg-emerald-400 px-3 py-3 text-sm font-black text-slate-950 disabled:opacity-50">Beli Rumah</button>
                    <button x-show="spaceAction()?.action === 'own_property'" @click="resolveSpaceAction('add_hotel')" :disabled="submitInFlight || spaceAction()?.is_expired || !spaceAction()?.can_buy_hotel" class="rounded-xl bg-purple-400 px-3 py-3 text-sm font-black text-white disabled:opacity-50">Beli Hotel</button>
                    <button x-show="spaceAction()?.action === 'own_property'" @click="resolveSpaceAction('sell_house')" :disabled="submitInFlight || spaceAction()?.is_expired || !spaceAction()?.can_sell_house" class="rounded-xl bg-amber-300 px-3 py-3 text-sm font-black text-slate-950 disabled:opacity-50">Jual 1 Rumah (1/2)</button>
                    <button x-show="spaceAction()?.action === 'own_property'" @click="resolveSpaceAction('sell_hotel')" :disabled="submitInFlight || spaceAction()?.is_expired || !spaceAction()?.can_sell_hotel" class="rounded-xl bg-amber-300 px-3 py-3 text-sm font-black text-slate-950 disabled:opacity-50">Jual Hotel (1/2)</button>
                    <button x-show="['pay_tax','pay_special_tax','pay_rent','card_pay_bank','card_repair_assets','card_utility_rent','card_choice_pay_or_draw'].includes(spaceAction()?.action)" @click="resolveSpaceAction('pay')" :disabled="submitInFlight || spaceAction()?.is_expired" class="rounded-xl bg-rose-400 px-3 py-3 text-sm font-black text-white disabled:opacity-50">Bayar</button>
                    <button x-show="spaceAction()?.action === 'card_collect_players'" @click="resolveSpaceAction('pay')" :disabled="submitInFlight || spaceAction()?.is_expired" class="rounded-xl bg-emerald-400 px-3 py-3 text-sm font-black text-slate-950 disabled:opacity-50">Terima dari Semua Pemain</button>
                    <button x-show="spaceAction()?.action === 'card_choice_pay_or_draw'" @click="resolveSpaceAction('draw_chance')" :disabled="submitInFlight || spaceAction()?.is_expired" class="rounded-xl bg-rose-400 px-3 py-3 text-sm font-black text-white disabled:opacity-50">Ambil Kesempatan</button>
                    <button x-show="!['pay_tax','pay_special_tax','pay_rent','card_pay_bank','card_repair_assets','card_utility_rent','card_collect_players','card_choice_pay_or_draw'].includes(spaceAction()?.action)" @click="resolveSpaceAction('skip')" :disabled="submitInFlight || spaceAction()?.is_expired" class="rounded-xl bg-white/10 px-3 py-3 text-sm font-black disabled:opacity-50" x-text="spaceAction()?.action === 'buy_property' ? 'Lewati' : 'Selesai'"></button>
                    <button x-show="spaceAction()?.action === 'buy_property'" @click="alert('Datang ke Bank jika semua pemain sepakat memulai lelang manual.')" :disabled="spaceAction()?.is_expired" class="rounded-xl bg-amber-300 px-3 py-3 text-sm font-black text-slate-950 disabled:opacity-50">Lelang</button>
                </div>
            </div>

            <div x-show="!player?.pending_space_action" class="mt-4 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">
                Tidak ada aksi yang harus diselesaikan di petak ini.
            </div>
        </section>

        <section x-show="game?.status !== 'finished'" class="glass-card p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-purple-300">Dadu Digital</p>
                    <h2 class="text-lg font-black" x-text="isMyTurn() ? 'Giliran Kamu' : `Menunggu ${game?.current_turn_player?.name || 'giliran'}`"></h2>
                    <p class="text-sm text-slate-400">Kocok dari HP, hasilnya langsung tampil di Bank dan Live View.</p>
                </div>
                <button @click="rollDice()" :disabled="!isMyTurn() || dice.rolling || loading || player?.pending_space_action" class="min-h-11 rounded-2xl bg-purple-400 px-4 py-3 font-black text-slate-950 transition hover:bg-purple-300 disabled:opacity-50">Kocok</button>
            </div>
            <div x-show="isMyTurn() && turnCountdownSeconds() !== null" class="mt-4">
                <div class="mb-1 flex items-center justify-between text-xs font-bold text-purple-200">
                    <span>Jika waktu habis, kamu diam dan giliran dilewati</span>
                    <span x-text="`${turnCountdownSeconds()} detik`"></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-950/60">
                    <div class="h-full rounded-full bg-purple-400 transition-all duration-1000" :style="`width:${turnCountdownPercent()}%`"></div>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-3 items-center gap-3">
                <div class="grid aspect-square place-items-center rounded-3xl border border-white/10 bg-white/10 text-5xl font-black" :class="dice.rolling ? 'dice-tumble border-purple-300/50 bg-purple-300/10' : ''" x-text="displayedDice('dice_one', dice.first)"></div>
                <div class="grid aspect-square place-items-center rounded-3xl border border-white/10 bg-white/10 text-5xl font-black" :class="dice.rolling ? 'dice-tumble border-blue-300/50 bg-blue-300/10' : ''" x-text="displayedDice('dice_two', dice.second)"></div>
                <div class="rounded-3xl border border-emerald-300/20 bg-emerald-300/10 p-4 text-center">
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-200">Total</p>
                    <p class="text-4xl font-black text-emerald-300" x-text="displayedDice('total', dice.first + dice.second)"></p>
                    <p x-show="lastRoll()?.is_double" class="mt-1 text-xs font-black text-amber-200">Double</p>
                    <p x-show="lastRoll()?.result === 'go_to_jail'" class="mt-1 text-xs font-black text-rose-200">Masuk penjara</p>
                </div>
            </div>
            <p x-show="lastTurnWasSkipped()" x-text="game?.turn?.last_event?.description" class="mt-3 rounded-2xl border border-amber-300/20 bg-amber-300/10 px-3 py-2 text-sm font-bold text-amber-100"></p>
        </section>

        <section class="glass-card p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-black">Tanah & Aset Saya</h2>
                    <p class="text-xs text-slate-400">Harga jual selalu setengah dari harga beli. Penjualan tetap diperiksa Bank.</p>
                </div>
            </div>
            <div class="space-y-2">
                <template x-for="property in player?.properties || []" :key="property.id">
                    <div class="rounded-xl bg-white/5 p-3">
                        <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 font-bold">
                            <span class="h-3 w-3 rounded-full" :style="`background:${property.color}`"></span>
                            <span x-text="property.name"></span>
                        </span>
                        <span class="text-xs text-slate-400">R<span x-text="property.house_count"></span> <span x-show="property.has_hotel">Hotel</span></span>
                        </div>
                        <div class="mt-2 grid grid-cols-2 gap-1 text-[11px] text-slate-400">
                            <span>Rumah: <b x-text="money(property.house_price)"></b></span>
                            <span>Hotel: <b x-text="money(property.hotel_price)"></b></span>
                            <span>Jual tanah: <b x-text="money(Math.floor((property.price || 0) / 2))"></b></span>
                            <span>Sewa: <b x-text="money(property.current_rent)"></b></span>
                        </div>
                        <div x-show="game?.status === 'active' && !player?.is_bankrupt" class="mt-3 grid grid-cols-2 gap-2">
                            <button x-show="Number(property.house_count || 0) > 0 && !property.has_hotel" @click="submitBulkSellPlan(manualSalePlan(property, 'sell_house'))" :disabled="submitInFlight || hasPendingType('bulk_sell_assets')" class="min-h-11 rounded-xl bg-amber-300 px-2 py-2 text-xs font-black text-slate-950 disabled:opacity-50">Jual 1 Rumah</button>
                            <button x-show="property.has_hotel" @click="submitBulkSellPlan(manualSalePlan(property, 'sell_hotel'))" :disabled="submitInFlight || hasPendingType('bulk_sell_assets')" class="min-h-11 rounded-xl bg-purple-400 px-2 py-2 text-xs font-black text-white disabled:opacity-50">Jual Hotel</button>
                            <button x-show="Number(property.house_count || 0) === 0 && !property.has_hotel" @click="submitBulkSellPlan(manualSalePlan(property, 'sell_property'))" :disabled="submitInFlight || hasPendingType('bulk_sell_assets')" class="col-span-2 min-h-11 rounded-xl bg-rose-400 px-2 py-2 text-xs font-black text-white disabled:opacity-50">Jual Tanah ke Bank</button>
                        </div>
                    </div>
                </template>
                <p x-show="!player?.properties?.length" class="py-4 text-center text-sm text-slate-400">Belum punya tanah.</p>
            </div>
        </section>

        <section x-show="game?.status === 'active' && !player?.is_bankrupt" class="glass-card border border-rose-300/20 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-black text-rose-200">Tidak Bisa Melanjutkan?</h2>
                    <p class="mt-1 text-xs text-slate-400">Bangkrut mengembalikan seluruh aset ke Bank dan langsung mengakhiri permainanmu.</p>
                </div>
                <button @click="openBankruptcyModal()" :disabled="submitInFlight" class="min-h-11 shrink-0 rounded-xl bg-rose-500 px-4 py-3 text-sm font-black text-white disabled:opacity-50">Bangkrut</button>
            </div>
        </section>

        <section x-show="jailCardTransfers.length" class="glass-card p-4">
            <h2 class="mb-3 text-lg font-black">Penawaran Kartu Bebas Penjara</h2>
            <div class="space-y-2">
                <template x-for="transfer in jailCardTransfers" :key="transfer.id">
                    <div class="rounded-2xl border border-purple-300/20 bg-purple-300/10 p-3">
                        <p class="font-black" x-text="transfer.status === 'pending' && Number(transfer.to_player_id) === Number(player?.id) ? `${transfer.from_player_name} menawarkan kartu bebas penjara` : `Kartu bebas penjara: ${transfer.status}`"></p>
                        <p class="text-sm text-slate-300">Harga <b x-text="money(transfer.amount)"></b></p>
                        <div x-show="transfer.status === 'pending' && Number(transfer.to_player_id) === Number(player?.id)" class="mt-3 grid grid-cols-2 gap-2">
                            <button @click="decideJailCardTransfer(transfer, true)" :disabled="submitInFlight" class="rounded-xl bg-emerald-400 px-3 py-2 text-sm font-black text-slate-950 disabled:opacity-50">Beli</button>
                            <button @click="decideJailCardTransfer(transfer, false)" :disabled="submitInFlight" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-black disabled:opacity-50">Tolak</button>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <section x-show="Number(player?.jail_free_cards || 0) > 0 && game?.status !== 'finished'" class="glass-card p-4">
            <h2 class="text-lg font-black">Kartu Bebas Penjara</h2>
            <p class="mt-1 text-sm text-slate-400">Kamu punya <b x-text="player?.jail_free_cards || 0"></b> kartu. Bisa disimpan, dipakai saat penjara, atau dijual ke pemain lain seharga 3.000.</p>
            <div class="mt-3 grid grid-cols-[1fr_auto] gap-2">
                <select x-model.number="jailCardTargetId" class="rounded-xl border border-white/10 bg-slate-950/60 px-3 py-3 font-bold outline-none focus:border-purple-300">
                    <option value="">Pilih pembeli</option>
                    <template x-for="item in players.filter((item) => item.id !== player?.id && !item.is_bankrupt)" :key="item.id">
                        <option :value="item.id" x-text="item.name"></option>
                    </template>
                </select>
                <button @click="offerJailCardTransfer()" :disabled="submitInFlight || !jailCardTargetId" class="rounded-xl bg-purple-400 px-4 py-3 text-sm font-black text-white disabled:opacity-50">Jual</button>
            </div>
        </section>

        <section class="glass-card p-4">
            <h2 class="mb-3 text-lg font-black">Permintaan Terakhir</h2>
            <div class="space-y-2">
                <template x-for="request in requests" :key="request.id">
                    <div class="rounded-xl bg-white/5 p-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold" x-text="request.reason"></p>
                            <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase" :class="request.status === 'approved' ? 'bg-emerald-300/15 text-emerald-200' : request.status === 'rejected' ? 'bg-rose-300/15 text-rose-200' : 'bg-amber-300/15 text-amber-200'" x-text="request.status"></span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400" x-text="request.created_at_label"></p>
                    </div>
                </template>
            </div>
        </section>
    </main>

    <div x-show="bankruptcyModal.open" x-transition.opacity class="fixed inset-0 z-50 grid place-items-end bg-slate-950/85 p-3 backdrop-blur-sm sm:place-items-center" @keydown.escape.window="closeBankruptcyModal()">
        <div @click.outside="closeBankruptcyModal()" class="w-full max-w-lg rounded-3xl border border-rose-300/20 bg-slate-900 p-5 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-rose-300">Konfirmasi Terakhir</p>
                    <h2 class="mt-1 text-2xl font-black">Nyatakan Bangkrut?</h2>
                </div>
                <button @click="closeBankruptcyModal()" class="grid h-11 w-11 place-items-center rounded-xl bg-white/10" aria-label="Tutup"><i data-lucide="x" class="h-5 w-5"></i></button>
            </div>
            <p class="mt-3 text-sm text-slate-300">Semua tanah, rumah, dan hotel dijual ke Bank dengan harga setengah. Keputusan ini tidak dapat dibatalkan.</p>

            <div x-show="bankruptcyModal.loading" class="mt-4 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">Menghitung seluruh aset...</div>
            <div x-show="!bankruptcyModal.loading && bankruptcyModal.summary" class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-xl bg-white/5 p-3"><p class="text-slate-400">Uang bank + tunai</p><p class="font-black" x-text="money(bankruptcyModal.summary?.liquid_balance)"></p></div>
                    <div class="rounded-xl bg-white/5 p-3"><p class="text-slate-400">Hasil jual aset</p><p class="font-black text-amber-300" x-text="money(bankruptcyModal.summary?.sale_total)"></p></div>
                    <div class="rounded-xl bg-white/5 p-3"><p class="text-slate-400">Tanah / Rumah / Hotel</p><p class="font-black" x-text="`${bankruptcyModal.summary?.property_count || 0} / ${bankruptcyModal.summary?.house_count || 0} / ${bankruptcyModal.summary?.hotel_count || 0}`"></p></div>
                    <div class="rounded-xl bg-rose-400/10 p-3"><p class="text-rose-200">Total likuidasi</p><p class="font-black text-rose-300" x-text="money(bankruptcyModal.summary?.total_liquidation)"></p></div>
                </div>
                <div x-show="bankruptcyModal.summary?.settlement" class="rounded-2xl border border-rose-300/20 bg-rose-400/10 p-3 text-sm text-rose-100">
                    Seluruh hasil likuidasi <b x-text="money(bankruptcyModal.summary?.settlement?.payable_to_owner)"></b> akan dibayarkan kepada <b x-text="bankruptcyModal.summary?.settlement?.creditor_name"></b> karena sewa <b x-text="bankruptcyModal.summary?.settlement?.property_name"></b>.
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-2">
                <button @click="closeBankruptcyModal()" :disabled="bankruptcyModal.submitting" class="min-h-11 rounded-xl bg-white/10 px-4 py-3 font-black disabled:opacity-50">Batal</button>
                <button @click="confirmBankruptcy()" :disabled="bankruptcyModal.loading || bankruptcyModal.submitting || !bankruptcyModal.summary" class="min-h-11 rounded-xl bg-rose-500 px-4 py-3 font-black text-white disabled:opacity-50" x-text="bankruptcyModal.submitting ? 'Memproses...' : 'Ya, Saya Bangkrut'"></button>
            </div>
        </div>
    </div>

    <script>
        window.playerPortal = (token) => ({
            token,
            loading: false,
            game: null,
            player: null,
            players: [],
            properties: [],
            transactions: [],
            requests: [],
            jailCardTransfers: [],
            jailCardTargetId: '',
            submitInFlight: false,
            stateRequestInFlight: false,
            stateRefreshQueued: false,
            automationInFlight: false,
            now: Date.now(),
            connectionMessage: '',
            rentPreview: null,
            bankruptcyModal: {
                open: false,
                loading: false,
                submitting: false,
                summary: null,
            },
            dice: {
                first: 1,
                second: 1,
                rolling: false,
            },
            realtime: {
                echo: null,
                connected: false,
                gameId: null,
            },
            form: {
                type: 'buy_property',
                game_property_id: '',
                target_player_id: '',
                amount: '',
                source: 'bank',
                reason: '',
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
                this.$nextTick(() => window.lucide?.createIcons());
            },
            async fetchState(silent = false) {
                if (this.stateRequestInFlight) {
                    this.stateRefreshQueued = true;
                    return;
                }

                this.stateRequestInFlight = true;
                this.loading = !silent;
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/state`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        throw new Error('Koneksi Bank sedang lambat.');
                    }
                    const payload = await response.json();
                    this.game = payload.game;
                    this.player = payload.player;
                    this.players = payload.players || [];
                    this.properties = payload.properties || [];
                    this.transactions = payload.transactions || [];
                    this.requests = payload.requests || [];
                    this.jailCardTransfers = payload.jail_card_transfers || [];
                    this.connectionMessage = '';
                    this.connectRealtime(this.game?.id);
                    this.$nextTick(() => window.lucide?.createIcons());
                } catch (error) {
                    this.connectionMessage = 'Koneksi Bank sedang lambat. Data akan dicoba lagi otomatis.';
                } finally {
                    this.loading = false;
                    this.stateRequestInFlight = false;
                    if (this.stateRefreshQueued) {
                        this.stateRefreshQueued = false;
                        queueMicrotask(() => this.fetchState(true));
                    }
                }
            },
            connectRealtime(gameId) {
                if (!gameId || this.realtime.gameId === gameId) {
                    return;
                }

                try {
                    if (!this.realtime.echo && window.createMonopolyEcho) {
                        this.realtime.echo = window.createMonopolyEcho();
                        const connection = this.realtime.echo.connector.pusher.connection;
                        connection.bind('connected', () => {
                            this.realtime.connected = true;
                        });
                        connection.bind('disconnected', () => {
                            this.realtime.connected = false;
                        });
                        connection.bind('error', () => {
                            this.realtime.connected = false;
                        });
                    }

                    if (!this.realtime.echo) {
                        this.realtime.connected = false;
                        return;
                    }

                    if (this.realtime.gameId) {
                        this.realtime.echo.leave(`game.${this.realtime.gameId}`);
                    }

                    this.realtime.gameId = gameId;
                    this.realtime.echo
                        .channel(`game.${gameId}`)
                        .listen('.game.updated', () => this.fetchState(true));
                } catch (error) {
                    this.realtime.connected = false;
                }
            },
            globalPendingAction() {
                return this.players.find((item) => item.pending_space_action)?.pending_space_action || null;
            },
            turnCountdownSeconds() {
                const deadline = this.game?.turn?.deadline_at;
                if (!deadline || this.globalPendingAction() || this.game?.status !== 'active') {
                    return null;
                }

                return Math.max(0, Math.ceil((new Date(deadline).getTime() - this.now) / 1000));
            },
            turnCountdownPercent() {
                const remaining = this.turnCountdownSeconds();
                const total = Number(this.game?.turn?.timeout_seconds || 20);

                return remaining === null ? 0 : Math.max(0, Math.min(100, (remaining / total) * 100));
            },
            actionCountdownSeconds() {
                const deadline = this.spaceAction()?.action_deadline_at;
                if (!deadline) {
                    return 0;
                }

                return Math.max(0, Math.ceil((new Date(deadline).getTime() - this.now) / 1000));
            },
            actionCountdownPercent() {
                const total = Number(this.spaceAction()?.timeout_seconds || 60);

                return Math.max(0, Math.min(100, (this.actionCountdownSeconds() / total) * 100));
            },
            automationDue() {
                if (!this.game?.id || this.game.status !== 'active' || this.automationInFlight) {
                    return false;
                }

                const action = this.globalPendingAction();
                if (action?.action_deadline_at) {
                    return new Date(action.action_deadline_at).getTime() <= this.now && !action.expired_at;
                }

                const deadline = this.game?.turn?.deadline_at;
                return Boolean(deadline && new Date(deadline).getTime() <= this.now);
            },
            async maybeRunAutomation() {
                if (!this.automationDue()) {
                    return;
                }

                this.automationInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/games/${this.game.id}/automation/tick`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({}),
                    });
                    if (response.ok) {
                        await this.fetchState(true);
                    }
                } finally {
                    this.automationInFlight = false;
                }
            },
            async submitRequest() {
                if (this.submitInFlight || this.hasPendingSimilarRequest()) {
                    return;
                }

                if (this.form.type === 'pay_rent') {
                    await this.payRentNow();
                    return;
                }

                this.loading = true;
                this.submitInFlight = true;
                try {
                    const selected = this.selectedProperty();
                    const body = { ...this.form };
                    if (this.form.type === 'pay_rent' && selected) {
                        body.owner_id = selected.owner_id;
                        body.amount = this.form.amount || selected.current_rent;
                        body.reason = body.reason || `Bayar sewa ${selected.name}`;
                    }
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/requests`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify(body),
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        alert(payload.message || 'Permintaan belum bisa dikirim.');
                        return;
                    }
                    this.form.reason = '';
                    alert(payload.message);
                    await this.fetchState(true);
                } finally {
                    this.loading = false;
                    this.submitInFlight = false;
                }
            },
            async payRentNow() {
                const selected = this.selectedProperty();
                if (!selected) {
                    alert('Pilih properti yang kamu injak dulu.');
                    return;
                }

                this.loading = true;
                this.submitInFlight = true;
                try {
                    const previewResponse = await fetch(`${this.basePath()}/api/player/${this.token}/rent-preview`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            game_property_id: selected.id,
                            source: this.form.source,
                        }),
                    });
                    const previewPayload = await previewResponse.json();
                    if (!previewResponse.ok) {
                        alert(previewPayload.message || 'Sewa belum bisa dihitung.');
                        return;
                    }

                    const preview = previewPayload.preview;
                    this.rentPreview = preview;
                    this.form.amount = preview.amount;

                    if (!preview.can_pay_now && !preview.must_bankrupt) {
                        alert(`Uang ${this.form.source === 'cash' ? 'tunai' : 'bank'} kurang ${this.money(preview.shortage)}. Jual aset dulu atau pilih sumber uang lain.`);
                        return;
                    }

                    const message = preview.must_bankrupt
                        ? `Uang dan semua aset kamu tetap kurang untuk membayar sewa ${preview.property_name}.\n\nKamu wajib bangkrut. Total sisa uang + jual semua aset ${this.money(preview.bankruptcy.payable_to_owner)} akan dibayarkan ke ${preview.owner_name}.\n\nLanjutkan?`
                        : `Bayar sewa ${preview.property_name} ke ${preview.owner_name} sebesar ${this.money(preview.amount)} lewat ${this.form.source === 'cash' ? 'uang tunai' : 'uang bank'}?\n${preview.rule_label || ''}`;

                    if (!confirm(message)) {
                        return;
                    }

                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/pay-rent`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            game_property_id: selected.id,
                            source: this.form.source,
                        }),
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        alert(payload.message || 'Sewa belum bisa dibayar.');
                        return;
                    }

                    alert(payload.message);
                    await this.fetchState(true);
                } finally {
                    this.loading = false;
                    this.submitInFlight = false;
                }
            },
            needsProperty() {
                return ['buy_property','sell_property','add_house','sell_house','add_hotel','sell_hotel','pay_rent'].includes(this.form.type);
            },
            needsAmount() {
                return ['transfer','player_to_bank','bank_to_player','deposit','withdraw','pay_rent'].includes(this.form.type);
            },
            selectedProperty() {
                return this.properties.find((property) => Number(property.id) === Number(this.form.game_property_id));
            },
            filteredProperties() {
                const mine = (property) => Number(property.owner_id) === Number(this.player?.id);
                const ownedByOther = (property) => property.owner_id && !mine(property);

                if (this.form.type === 'buy_property') {
                    return this.properties.filter((property) => !property.owner_id);
                }
                if (this.form.type === 'pay_rent') {
                    return this.properties.filter(ownedByOther);
                }
                if (['sell_property','add_house','sell_house','add_hotel','sell_hotel'].includes(this.form.type)) {
                    return this.properties.filter(mine);
                }

                return this.properties;
            },
            propertyOptionLabel(property) {
                const owner = property.owner_name || 'Bank';
                if (this.form.type === 'add_house') {
                    return `${property.name} - rumah ${property.house_count}/4 - harga ${this.money(property.house_price)}`;
                }
                if (this.form.type === 'add_hotel') {
                    const refund = (property.house_count || 0) * Math.floor((property.house_price || 0) / 2);
                    return `${property.name} - hotel ${this.money(property.hotel_price)} - potong rumah ${this.money(refund)}`;
                }
                if (this.form.type === 'pay_rent') {
                    return `${property.name} - owner ${owner} - sewa ${this.money(property.current_rent)}`;
                }
                if (this.form.type === 'sell_property') {
                    return `${property.name} - jual ${this.money(Math.floor((property.price || 0) / 2))}`;
                }

                return `${property.name} - ${owner} - ${this.money(property.price)}`;
            },
            hasPendingSimilarRequest() {
                if (this.form.type === 'pay_rent') {
                    return false;
                }

                return this.requests.some((request) => request.status === 'pending'
                    && request.type === this.form.type
                    && Number(request.game_property_id || 0) === Number(this.form.game_property_id || 0));
            },
            hasPendingType(type) {
                return this.requests.some((request) => request.status === 'pending' && request.type === type);
            },
            async submitBulkSellPlan(plan) {
                if (!plan?.operations?.length || this.submitInFlight || this.hasPendingType('bulk_sell_assets')) {
                    return;
                }

                const confirmed = confirm(`Ajukan paket jual aset ke Bank?\nTotal target ${this.money(plan.total)}\n${plan.items.join('\n')}`);
                if (!confirmed) {
                    return;
                }

                this.submitInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/requests`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            type: 'bulk_sell_assets',
                            bulk_total: plan.total,
                            liquidation_plan: plan.operations,
                            reason: `Paket jual aset ${this.money(plan.total)}`,
                        }),
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        alert(payload.message || 'Paket jual aset belum bisa dikirim.');
                        return;
                    }
                    alert(payload.message);
                    await this.fetchState(true);
                } finally {
                    this.submitInFlight = false;
                }
            },
            manualSalePlan(property, type) {
                const values = {
                    sell_house: Math.floor(Number(property?.house_price || 0) / 2),
                    sell_hotel: Math.floor(Number(property?.hotel_price || 0) / 2),
                    sell_property: Math.floor(Number(property?.price || 0) / 2),
                };
                const labels = {
                    sell_house: `Jual 1 rumah di ${property?.name}`,
                    sell_hotel: `Jual hotel di ${property?.name}`,
                    sell_property: `Jual tanah ${property?.name}`,
                };

                return {
                    items: [labels[type]],
                    total: values[type],
                    operations: [{ type, game_property_id: property?.id, quantity: 1 }],
                };
            },
            async openBankruptcyModal() {
                if (this.player?.is_bankrupt || this.game?.status !== 'active' || this.submitInFlight) {
                    return;
                }

                this.bankruptcyModal.open = true;
                this.bankruptcyModal.loading = true;
                this.bankruptcyModal.summary = null;
                this.$nextTick(() => window.lucide?.createIcons());
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/bankruptcy-preview`, {
                        headers: { Accept: 'application/json' },
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        alert(payload.message || 'Rincian bangkrut belum bisa dihitung.');
                        this.bankruptcyModal.open = false;
                        return;
                    }
                    this.bankruptcyModal.summary = payload.summary;
                } catch (error) {
                    alert('Koneksi ke Bank sedang lambat. Coba buka konfirmasi sekali lagi.');
                    this.bankruptcyModal.open = false;
                } finally {
                    this.bankruptcyModal.loading = false;
                }
            },
            closeBankruptcyModal() {
                if (this.bankruptcyModal.submitting) {
                    return;
                }
                this.bankruptcyModal.open = false;
            },
            async confirmBankruptcy() {
                if (this.bankruptcyModal.submitting || !this.bankruptcyModal.summary) {
                    return;
                }

                this.bankruptcyModal.submitting = true;
                this.submitInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/bankrupt`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({}),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        alert(payload.message || 'Status bangkrut belum bisa diproses.');
                        return;
                    }
                    this.bankruptcyModal.open = false;
                    await this.fetchState(true);
                } catch (error) {
                    alert('Status belum tercatat. Periksa koneksi lalu coba sekali lagi.');
                } finally {
                    this.bankruptcyModal.submitting = false;
                    this.submitInFlight = false;
                }
            },
            async requestJailAction(type) {
                if (this.submitInFlight || this.hasPendingType(type)) {
                    return;
                }

                const label = type === 'pay_jail_fee' ? 'bayar 5.000 ke Bank' : 'pakai kartu bebas penjara';
                if (!confirm(`Ajukan ${label}? Setelah disetujui, kamu bebas mulai giliran berikutnya.`)) {
                    return;
                }

                this.submitInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/requests`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ type }),
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        alert(payload.message || 'Permintaan belum bisa dikirim.');
                        return;
                    }
                    alert(payload.message);
                    await this.fetchState(true);
                } finally {
                    this.submitInFlight = false;
                }
            },
            async useJailCardNow() {
                if (this.submitInFlight || Number(this.player?.jail_free_cards || 0) <= 0) {
                    return;
                }

                if (!confirm('Pakai satu kartu bebas penjara? Pada giliran berikutnya kamu langsung keluar lalu menjalankan dadu.')) {
                    return;
                }

                this.submitInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/use-jail-card`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({}),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        alert(payload.message || 'Kartu belum bisa dipakai. Coba sekali lagi.');
                        return;
                    }
                    alert(payload.message);
                    await this.fetchState(true);
                } catch (error) {
                    alert('Kartu belum tercatat. Periksa koneksi lalu coba sekali lagi.');
                } finally {
                    this.submitInFlight = false;
                }
            },
            spaceAction() {
                return this.player?.pending_space_action || null;
            },
            async resolveSpaceAction(decision) {
                const action = this.spaceAction();
                if (!action || this.submitInFlight) {
                    return;
                }

                if (action.is_expired) {
                    alert('Waktu aksi sudah habis. Datang ke Bank agar pembayaran atau asetmu dibantu sampai selesai.');
                    return;
                }

                const needsConfirm = ['pay_tax','pay_special_tax','pay_rent','buy_property','own_property','card_collect_players'].includes(action.action);
                const confirmText = action.action === 'card_collect_players'
                    ? `${action.label}\nTerima ${this.money(action.amount)} dari setiap pemain?`
                    : `${action.label}: ${action.amount ? this.money(action.amount) : ''}\nLanjutkan?`;
                if (needsConfirm && !confirm(confirmText)) {
                    return;
                }

                this.submitInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/space-action`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            decision,
                            source: this.form.source,
                        }),
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        alert(payload.message || 'Aksi belum tersimpan. Coba tekan sekali lagi atau minta bantuan Bank.');
                        return;
                    }
                    await this.fetchState(true);
                } catch (error) {
                    alert('Aksi belum tersimpan karena koneksi lambat. Coba tekan sekali lagi.');
                } finally {
                    this.submitInFlight = false;
                }
            },
            async decideJailCardTransfer(transfer, approve) {
                if (this.submitInFlight) {
                    return;
                }

                if (approve && !confirm(`Beli kartu bebas penjara dari ${transfer.from_player_name} seharga ${this.money(transfer.amount)}?`)) {
                    return;
                }

                this.submitInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/jail-card-transfers/${transfer.id}`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ approve }),
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        alert(payload.message || 'Penawaran kartu belum bisa diproses.');
                        return;
                    }
                    alert(payload.message);
                    await this.fetchState(true);
                } finally {
                    this.submitInFlight = false;
                }
            },
            async offerJailCardTransfer() {
                if (this.submitInFlight || !this.jailCardTargetId) {
                    return;
                }

                this.submitInFlight = true;
                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/jail-card-transfers`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ to_player_id: this.jailCardTargetId }),
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        alert(payload.message || 'Penawaran belum bisa dikirim.');
                        return;
                    }
                    this.jailCardTargetId = '';
                    alert(payload.message);
                    await this.fetchState(true);
                } finally {
                    this.submitInFlight = false;
                }
            },
            utilityRentInfo() {
                const count = this.fullGroupCount();
                const multiplier = count >= 2 ? 10 : (count === 1 ? 4 : 1);

                return `Air/Listrik: 7.500 x ${multiplier} karena kamu punya ${count} komplek penuh.`;
            },
            fullGroupCount() {
                const landGroups = this.properties
                    .filter((property) => property.property_kind === 'land' && property.group_code)
                    .reduce((groups, property) => {
                        groups[property.group_code] = groups[property.group_code] || { total: 0, mine: 0 };
                        groups[property.group_code].total += 1;
                        if (Number(property.owner_id) === Number(this.player?.id)) {
                            groups[property.group_code].mine += 1;
                        }
                        return groups;
                    }, {});

                return Object.values(landGroups).filter((group) => group.total > 0 && group.total === group.mine).length;
            },
            availableMoney() {
                return this.form.source === 'cash' ? Number(this.player?.cash_balance || 0) : Number(this.player?.balance || 0);
            },
            saleRecommendations() {
                return this.recommendAssetsFor(Number(this.form.amount || 0), this.availableMoney());
            },
            actionAvailableMoney() {
                return this.form.source === 'cash' ? Number(this.player?.cash_balance || 0) : Number(this.player?.balance || 0);
            },
            actionSaleRecommendations() {
                return this.recommendAssetsFor(Number(this.spaceAction()?.amount || 0), this.actionAvailableMoney());
            },
            recommendAssetsFor(requiredAmount, availableAmount) {
                const shortage = Math.max(0, Number(requiredAmount || 0) - Number(availableAmount || 0));
                if (shortage <= 0) {
                    return [];
                }

                const assets = (this.player?.properties || []).flatMap((property) => {
                    const items = [];
                    if (property.house_count > 0 && !property.has_hotel) {
                        for (let i = 0; i < Number(property.house_count || 0); i += 1) {
                            items.push({
                                label: `jual 1 rumah di ${property.name}`,
                                value: Math.floor((property.house_price || 0) / 2),
                                operation: { type: 'sell_house', game_property_id: property.id, quantity: 1 },
                            });
                        }
                    }
                    if (property.has_hotel) {
                        items.push({
                            label: `jual hotel di ${property.name}`,
                            value: Math.floor((property.hotel_price || 0) / 2),
                            operation: { type: 'sell_hotel', game_property_id: property.id, quantity: 1 },
                        });
                    }
                    if (Number(property.house_count || 0) === 0 && !property.has_hotel) {
                        items.push({
                            label: `jual tanah ${property.name}`,
                            value: Math.floor((property.price || 0) / 2),
                            operation: { type: 'sell_property', game_property_id: property.id, quantity: 1 },
                        });
                    }
                    return items;
                }).filter((item) => item.value > 0);

                const plans = [];
                const sortedSmall = [...assets].sort((a, b) => a.value - b.value);
                const sortedLarge = [...assets].sort((a, b) => b.value - a.value);
                const single = sortedSmall.find((asset) => asset.value >= shortage);

                const buildPlan = (list) => {
                    const items = [];
                    const operations = [];
                    let total = 0;
                    for (const asset of list) {
                        items.push(asset.label);
                        operations.push(asset.operation);
                        total += asset.value;
                        if (total >= shortage) break;
                    }
                    return total >= shortage ? { items, total, operations } : null;
                };

                const smallPlan = buildPlan(sortedSmall);
                if (smallPlan) plans.push(smallPlan);
                if (single) plans.push({ items: [single.label], total: single.value, operations: [single.operation] });
                const largePlan = buildPlan(sortedLarge);
                if (largePlan) plans.push(largePlan);

                return plans
                    .filter((plan, index, arr) => arr.findIndex((item) => item.items.join('|') === plan.items.join('|')) === index)
                    .slice(0, 3);
            },
            topPlayers() {
                return [...this.players].sort((a, b) => Number(b.total_asset || 0) - Number(a.total_asset || 0));
            },
            myRank() {
                const index = this.topPlayers().findIndex((item) => Number(item.id) === Number(this.player?.id));
                return index >= 0 ? index + 1 : '-';
            },
            duration(seconds) {
                const total = Number(seconds || 0);
                const hours = Math.floor(total / 3600);
                const minutes = Math.floor((total % 3600) / 60);
                const secs = total % 60;

                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            },
            syncRentAmount() {
                if (this.form.type === 'pay_rent') {
                    this.form.amount = this.selectedProperty()?.current_rent || '';
                }
            },
            isMyTurn() {
                return Number(this.game?.current_turn_player_id) === Number(this.player?.id);
            },
            lastRoll() {
                return this.game?.turn?.last_roll || null;
            },
            lastTurnWasSkipped() {
                return this.game?.turn?.last_event?.type === 'dice_timeout_skipped';
            },
            displayedDice(field, rollingValue) {
                if (this.dice.rolling) {
                    return rollingValue;
                }

                if (this.lastTurnWasSkipped()) {
                    return '-';
                }

                return this.lastRoll()?.[field] ?? rollingValue;
            },
            async rollDice() {
                if (!this.isMyTurn()) {
                    alert('Belum giliran kamu.');
                    return;
                }

                if (this.globalPendingAction()) {
                    alert('Selesaikan aksi petak yang sedang terbuka dulu.');
                    return;
                }

                if (this.dice.rolling) {
                    return;
                }

                this.dice.rolling = true;
                const animationStartedAt = performance.now();
                const timer = setInterval(() => {
                    this.dice.first = Math.floor(Math.random() * 6) + 1;
                    this.dice.second = Math.floor(Math.random() * 6) + 1;
                }, 85);

                try {
                    const response = await fetch(`${this.basePath()}/api/player/${this.token}/roll-dice`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        alert(payload.message || 'Dadu belum masuk. Coba kocok ulang satu kali.');
                        return;
                    }
                    const animationRemaining = Math.max(0, 1100 - (performance.now() - animationStartedAt));
                    if (animationRemaining > 0) {
                        await new Promise((resolve) => setTimeout(resolve, animationRemaining));
                    }
                    clearInterval(timer);
                    this.dice.first = payload.roll.dice_one;
                    this.dice.second = payload.roll.dice_two;
                    this.dice.rolling = false;
                    await this.fetchState(true);
                } catch (error) {
                    alert('Dadu belum masuk karena koneksi lambat. Coba kocok ulang satu kali.');
                } finally {
                    clearInterval(timer);
                    this.dice.rolling = false;
                }
            },
            basePath() {
                const path = window.location.pathname;
                const publicIndex = path.indexOf('/public');
                return publicIndex >= 0 ? path.slice(0, publicIndex + '/public'.length) : '';
            },
            money(value) {
                return new Intl.NumberFormat('id-ID').format(Number(value || 0));
            },
        });
    </script>
</body>
</html>
