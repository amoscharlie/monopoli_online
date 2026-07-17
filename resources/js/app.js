import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Chart = Chart;
window.Pusher = Pusher;

const sanitizeHost = (value) => String(value || '')
    .trim()
    .replace(/^https?:\/\//, '')
    .replace(/\/.*$/, '')
    .split(':')[0];

const resolvePublicHost = () => sanitizeHost(import.meta.env.VITE_PUBLIC_HOST) || window.location.hostname;

window.createMonopolyEcho = () => {
    if (window.monopolyEcho) {
        return window.monopolyEcho;
    }

    const host = sanitizeHost(import.meta.env.VITE_REVERB_HOST) || window.location.hostname;
    const scheme = import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '') || 'http';
    const port = Number(import.meta.env.VITE_REVERB_PORT || 8080);

    window.monopolyEcho = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: host,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    return window.monopolyEcho;
};

window.monopolyBank = () => ({
    loadingInitial: true,
    loading: false,
    view: 'home',
    basePath: '',
    darkMode: localStorage.getItem('monopoly-bank-theme') !== 'light',
    activeGames: [],
    historyGames: [],
    settings: {
        starting_balance: 15000,
        go_bonus: 20000,
        tax_amount: 20000,
        fine_amount: 10000,
        starting_cash: 0,
    },
    settingsForm: {},
    properties: [],
    current: null,
    newGame: {
        player_count: 2,
        starting_balance: 15000,
        starting_cash: 0,
        duration_minutes: null,
        players: [],
    },
    rfid: {
        uid: '',
    },
    quick: {
        property_id: '',
        amount: '',
        receiver_id: '',
        transfer_source: 'bank',
    },
    modal: {
        open: false,
        type: '',
        title: '',
        data: {},
    },
    propertyForm: {
        name: '',
        price: 0,
        house_price: 0,
        hotel_price: 0,
        rent: 0,
        rent_1_house: 0,
        rent_2_houses: 0,
        rent_3_houses: 0,
        rent_4_houses: 0,
        rent_hotel: 0,
        color: '#10b981',
        sort_order: 0,
    },
    toasts: [],
    charts: {},
    poller: null,
    clock: null,
    now: Date.now(),
    music: {
        audio: null,
        playing: false,
        volume: 0.35,
    },
    soundCache: {},
    spinWheel: {
        open: false,
        spinning: false,
        rotation: 0,
        winner: null,
    },
    finishResult: {
        open: false,
        pending: false,
        autoFinishedGameId: null,
        drumAudio: null,
    },
    realtime: {
        echo: null,
        connected: false,
        gameId: null,
    },
    stateRequestInFlight: false,
    chartSignature: '',
    manualSelection: {
        playerId: null,
        expiresAt: null,
        timer: null,
    },
    transactionButtons: [
        { type: 'transfer', label: 'Kirim Uang', icon: 'arrow-left-right', color: 'bg-blue-400 text-slate-950' },
        { type: 'deposit', label: 'Setor Tunai', icon: 'plus-circle', color: 'bg-emerald-400 text-slate-950' },
        { type: 'withdraw', label: 'Tarik Tunai', icon: 'download', color: 'bg-slate-200 text-slate-950' },
        { type: 'bank_to_player', label: 'Bank ke Pemain', icon: 'coins', color: 'bg-emerald-300 text-slate-950' },
        { type: 'player_to_bank', label: 'Pemain ke Bank', icon: 'receipt', color: 'bg-rose-400 text-white' },
        { type: 'buy_property', label: 'Beli Tanah', icon: 'home', color: 'bg-amber-300 text-slate-950' },
        { type: 'sell_property', label: 'Jual Tanah', icon: 'badge-dollar-sign', color: 'bg-orange-300 text-slate-950' },
        { type: 'transfer_property', label: 'Pindah Pemilik Tanah', icon: 'building-2', color: 'bg-purple-400 text-white' },
        { type: 'add_house', label: 'Tambah Rumah', icon: 'house-plus', color: 'bg-emerald-400 text-slate-950' },
        { type: 'sell_house', label: 'Jual Rumah', icon: 'house-minus', color: 'bg-orange-400 text-slate-950' },
        { type: 'add_hotel', label: 'Tambah Hotel', icon: 'building', color: 'bg-purple-400 text-white' },
        { type: 'sell_hotel', label: 'Jual Hotel', icon: 'building-2', color: 'bg-rose-400 text-white' },
        { type: 'auction', label: 'Lelang Tanah', icon: 'gavel', color: 'bg-amber-400 text-slate-950' },
    ],
    cardActions: [
        { key: 'dana_bayar_dokter', deck: 'Dana Umum', label: 'Bayar dokter', amount: 5000, flow: 'pay' },
        { key: 'dana_bayar_rs', deck: 'Dana Umum', label: 'Bayar Rumah Sakit', amount: 10000, flow: 'pay' },
        { key: 'dana_bayar_asuransi', deck: 'Dana Umum', label: 'Bayar Asuransi', amount: 2000, flow: 'pay' },
        { key: 'dana_sumbangan_bencana', deck: 'Dana Umum', label: 'Sumbangan bencana alam', amount: 5000, flow: 'pay' },
        { key: 'dana_sisa_pajak_jalan', deck: 'Dana Umum', label: 'Dapat sisa uang pajak jalan', amount: 5000, flow: 'receive' },
        { key: 'dana_terima_bunga', deck: 'Dana Umum', label: 'Terima bunga', amount: 10000, flow: 'receive' },
        { key: 'dana_hadiah_totalisator', deck: 'Dana Umum', label: 'Dapat hadiah totalisator', amount: 1000, flow: 'receive' },
        { key: 'dana_komisi', deck: 'Dana Umum', label: 'Dapat komisi', amount: 5000, flow: 'receive' },
        { key: 'dana_kesalahan_bank', deck: 'Dana Umum', label: 'Kesalahan bank', amount: 20000, flow: 'receive' },
        { key: 'dana_bunga_bank_7', deck: 'Dana Umum', label: 'Terima Bunga dari Bank 7%', amount: 2500, flow: 'receive' },
        { key: 'dana_warisan', deck: 'Dana Umum', label: 'Dapat warisan', amount: 10000, flow: 'receive' },
        { key: 'dana_ulang_tahun', deck: 'Dana Umum', label: 'Hari ulang tahun, terima dari tiap pemain', amount: 1000, flow: 'collect_players' },
        { key: 'kesempatan_lalu_lintas', deck: 'Kesempatan', label: 'Melanggar lalu lintas', amount: 1500, flow: 'pay' },
        { key: 'kesempatan_uang_sekolah', deck: 'Kesempatan', label: 'Bayar uang sekolah', amount: 15000, flow: 'pay' },
        { key: 'kesempatan_mabuk', deck: 'Kesempatan', label: 'Mabuk di muka umum', amount: 1500, flow: 'pay' },
        { key: 'kesempatan_pajak_penghasilan', deck: 'Kesempatan', label: 'Bayar pajak penghasilan', amount: 15000, flow: 'pay' },
        { key: 'kesempatan_sewa_bank', deck: 'Kesempatan', label: 'Terima uang sewa dari bank', amount: 15000, flow: 'receive' },
        { key: 'kesempatan_bunga_bank', deck: 'Kesempatan', label: 'Terima bunga bank', amount: 5000, flow: 'receive' },
        { key: 'kesempatan_tts', deck: 'Kesempatan', label: 'Dapat hadiah pertama Teka-Teki Silang', amount: 10000, flow: 'receive' },
    ],

    async init() {
        this.basePath = this.detectBasePath();
        this.applyTheme();
        this.setPlayerCount(2);
        await this.fetchMeta();
        this.loadingInitial = false;
        this.renderIcons();

        this.poller = setInterval(() => {
            if (!this.realtime.connected && this.view === 'dashboard' && this.current?.game?.id && this.current.game.status !== 'finished') {
                this.refreshState(true);
            }
        }, 2500);

        this.clock = setInterval(() => {
            this.now = Date.now();
            this.checkTimerAutoFinish();
        }, 1000);
    },

    detectBasePath() {
        const path = window.location.pathname.replace(/\/$/, '');
        const publicIndex = path.indexOf('/public');

        return publicIndex >= 0 ? path.slice(0, publicIndex + '/public'.length) : '';
    },

    applyTheme() {
        document.documentElement.classList.toggle('light-surface', !this.darkMode);
        localStorage.setItem('monopoly-bank-theme', this.darkMode ? 'dark' : 'light');
    },

    toggleTheme() {
        this.darkMode = !this.darkMode;
        this.applyTheme();
    },

    async api(url, options = {}) {
        this.loading = !options.silent;
        const isFormData = options.body instanceof FormData;
        const headers = {
            Accept: 'application/json',
            ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
            ...(options.headers || {}),
        };

        try {
            const response = await fetch(`${this.basePath}${url}`, {
                ...options,
                headers,
            });
            const contentType = response.headers.get('content-type') || '';
            const payload = contentType.includes('application/json') ? await response.json() : {};

            if (!response.ok) {
                const message = payload.message || Object.values(payload.errors || {}).flat()[0] || 'Request gagal.';
                const error = new Error(message);
                error.payload = payload;
                throw error;
            }

            return payload;
        } finally {
            this.loading = false;
        }
    },

    async fetchMeta() {
        const payload = await this.api('/api/games/meta', { silent: true });
        this.settings = payload.settings;
        this.settingsForm = { ...payload.settings };
        this.newGame.starting_balance = payload.settings.starting_balance;
        this.newGame.starting_cash = payload.settings.starting_cash || 0;
        this.properties = payload.properties;
        this.activeGames = payload.active_games;
        this.historyGames = payload.history_games;
    },

    setView(view) {
        this.view = view;
        if (view === 'settings') {
            this.loadSettings();
        }
        this.renderIcons();
    },

    setPlayerCount(count) {
        this.newGame.player_count = count;
        const existing = this.newGame.players;
        this.newGame.players = Array.from({ length: count }, (_, index) => existing[index] || {
            name: '',
            rfid_uid: '',
        });
    },

    async startGame() {
        const payload = {
            starting_balance: Number(this.newGame.starting_balance),
            starting_cash: Number(this.newGame.starting_cash),
            duration_minutes: this.newGame.duration_minutes === null || this.newGame.duration_minutes === '' || this.newGame.duration_minutes === 'null' ? null : Number(this.newGame.duration_minutes),
            players: this.newGame.players,
        };
        const data = await this.api('/api/games', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        this.applyState(data.state);
        await this.fetchMeta();
        this.toast(data.message, 'success');
        this.playSound('success');
    },

    async loadGame(id, mode = 'dashboard') {
        const data = await this.api(`/api/games/${id}`);
        this.applyState(data.state);
        this.view = mode;
    },

    async deleteContinueGame(game) {
        if (!confirm(`Hapus permainan ${game.code}? Catatan permainan ini akan dihapus.`)) {
            return;
        }

        const data = await this.api(`/api/games/${game.id}`, {
            method: 'DELETE',
        });

        this.activeGames = data.active_games;
        this.historyGames = data.history_games || this.historyGames;

        if (this.current?.game?.id === game.id) {
            this.current = null;
            this.view = 'continue';
        }

        this.toast(data.message, 'success');
        this.playSound('success');
    },

    async deleteHistoryGame(game) {
        const confirmed = await this.confirmAction({
            title: `Hapus riwayat ${game.code}?`,
            text: 'Catatan permainan yang sudah selesai ini akan dihapus.',
            confirmButtonText: 'Ya, hapus',
        });

        if (!confirmed) {
            return;
        }

        const data = await this.api(`/api/games/${game.id}/history`, {
            method: 'DELETE',
        });
        this.activeGames = data.active_games;
        this.historyGames = data.history_games || [];
        this.toast(data.message, 'success');
        this.playSound('success');
    },

    async refreshState(silent = false) {
        if (!this.current?.game?.id || this.stateRequestInFlight) {
            return;
        }

        this.stateRequestInFlight = true;
        try {
            const data = await this.api(`/api/games/${this.current.game.id}`, { silent });
            this.applyState(data.state, true);
        } finally {
            this.stateRequestInFlight = false;
        }
    },

    playerPortalUrl(token) {
        const host = resolvePublicHost();
        const port = window.location.port ? `:${window.location.port}` : '';

        return `${window.location.protocol}//${host}${port}${this.basePath}/player/${token}`;
    },

    qrUrl(url) {
        return `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(url)}`;
    },

    async copyText(text) {
        await navigator.clipboard?.writeText(text);
        this.toast('Link sudah disalin.', 'success');
    },

    async copyBankUrl() {
        const host = resolvePublicHost();
        const port = window.location.port ? `:${window.location.port}` : '';

        await this.copyText(`${window.location.protocol}//${host}${port}${this.basePath}/`);
    },

    liveViewUrl() {
        const host = resolvePublicHost();
        const port = window.location.port ? `:${window.location.port}` : '';

        return `${window.location.protocol}//${host}${port}${this.basePath}/live/${this.current.game.id}`;
    },

    openLiveView() {
        window.open(this.liveViewUrl(), '_blank', 'noopener,noreferrer');
    },

    async approvePlayerRequest(request) {
        const confirmed = await this.confirmAction({
            title: 'Setujui permintaan pemain?',
            text: request.reason || 'Transaksi akan diproses oleh Bank.',
            confirmButtonText: 'Setujui',
        });

        if (!confirmed) {
            return;
        }

        const payload = await this.api(`/api/games/${this.current.game.id}/requests/${request.id}/approve`, {
            method: 'POST',
            body: JSON.stringify({}),
        });
        this.applyState(payload.state);
        this.toast(payload.message, 'success');
        this.playSound(request.type);
    },

    async rejectPlayerRequest(request) {
        const confirmed = await this.confirmAction({
            title: 'Tolak permintaan pemain?',
            text: request.reason || 'Permintaan ini akan dibatalkan.',
            confirmButtonText: 'Tolak',
        });

        if (!confirmed) {
            return;
        }

        const payload = await this.api(`/api/games/${this.current.game.id}/requests/${request.id}/reject`, {
            method: 'POST',
            body: JSON.stringify({}),
        });
        this.applyState(payload.state);
        this.toast(payload.message, 'success');
        this.playSound('error');
    },

    applyState(state) {
        this.current = state;
        this.view = 'dashboard';
        if (state?.game?.needs_first_player_spin) {
            this.openSpinWheel();
        }
        this.$nextTick(() => {
            this.drawCharts();
            this.renderIcons();
            this.connectRealtime(state.game.id);
        });
    },

    connectRealtime(gameId) {
        if (!gameId || this.realtime.gameId === gameId) {
            return;
        }

        try {
            if (!this.realtime.echo) {
                this.realtime.echo = window.createMonopolyEcho();
                const connection = this.realtime.echo.connector.pusher.connection;
                connection.bind('connected', () => {
                    this.realtime.connected = true;
                    this.toast('Realtime aktif.', 'success');
                });
                connection.bind('disconnected', () => {
                    this.realtime.connected = false;
                });
                connection.bind('error', () => {
                    this.realtime.connected = false;
                });
            }

            if (this.realtime.gameId) {
                this.realtime.echo.leave(`game.${this.realtime.gameId}`);
            }

            this.realtime.gameId = gameId;
            this.realtime.echo
                .channel(`game.${gameId}`)
                .listen('.game.updated', () => this.refreshState(true));
        } catch (error) {
            this.realtime.connected = false;
        }
    },

    renderIcons() {
        this.$nextTick(() => window.lucide?.createIcons({
            attrs: {
                'stroke-width': 2.2,
            },
        }));
    },

    openSpinWheel() {
        this.spinWheel.open = true;
        this.spinWheel.winner = null;
    },

    async spinFirstPlayer() {
        if (!this.current?.players?.length || this.spinWheel.spinning) {
            return;
        }

        this.spinWheel.spinning = true;
        this.spinWheel.winner = null;
        const winnerIndex = Math.floor(Math.random() * this.current.players.length);
        const slice = 360 / this.current.players.length;
        const target = 360 - ((winnerIndex * slice) + (slice / 2));
        this.spinWheel.rotation += (360 * 6) + target;
        this.playSound('spin');

        setTimeout(async () => {
            const winner = this.current.players[winnerIndex];
            this.spinWheel.winner = winner;
            const payload = await this.api(`/api/games/${this.current.game.id}/first-player`, {
                method: 'POST',
                body: JSON.stringify({ player_id: winner.id }),
            });
            this.applyState(payload.state);
            this.spinWheel.open = true;
            this.spinWheel.winner = winner;
            this.spinWheel.spinning = false;
            this.toast(`${winner.name} jadi pemain pertama.`, 'success');
            this.playSound('success');
        }, 3300);
    },

    closeSpinWheel() {
        if (!this.current?.game?.first_player_id) {
            return;
        }

        this.spinWheel.open = false;
    },

    wheelStyle() {
        const players = this.current?.players || [];
        if (!players.length) {
            return '';
        }

        const slice = 100 / players.length;
        const stops = players.map((player, index) => {
            const start = (slice * index).toFixed(2);
            const end = (slice * (index + 1)).toFixed(2);
            return `${player.avatar_color} ${start}% ${end}%`;
        }).join(', ');

        return `background: conic-gradient(${stops}); transform: rotate(${this.spinWheel.rotation}deg);`;
    },

    playerWheelLabelStyle(index) {
        const count = this.current?.players?.length || 1;
        const angle = (360 / count) * index + (180 / count);
        return `transform: rotate(${angle}deg) translateY(-112px) rotate(-${angle}deg);`;
    },

    async gameControl(action) {
        if (action === 'finish') {
            await this.finishGameWithDrum();
            return;
        }

        const data = await this.api(`/api/games/${this.current.game.id}/${action}`, {
            method: 'POST',
            body: JSON.stringify({}),
        });
        this.applyState(data.state);
        this.activeGames = data.active_games || this.activeGames;
        this.historyGames = data.history_games || this.historyGames;
        this.toast(data.message, 'success');
        this.playSound('success');
    },

    checkTimerAutoFinish() {
        if (!this.current?.game?.id || this.current.game.status === 'finished' || this.finishResult.pending) {
            return;
        }

        if (this.countdownSeconds() === 0 && this.current.game.duration_minutes !== null) {
            this.finishGameWithDrum(true);
        }
    },

    async finishGameWithDrum(isAuto = false) {
        if (!this.current?.game?.id || this.current.game.status === 'finished' || this.finishResult.pending) {
            return;
        }

        this.finishResult.pending = true;
        this.finishResult.autoFinishedGameId = this.current.game.id;
        this.playDrumRoll();

        setTimeout(async () => {
            try {
                const data = await this.api(`/api/games/${this.current.game.id}/finish`, {
                    method: 'POST',
                    body: JSON.stringify({}),
                    silent: true,
                });
                this.applyState(data.state);
                this.activeGames = data.active_games || this.activeGames;
                this.historyGames = data.history_games || this.historyGames;
                this.openFinishResult();
            this.toast(isAuto ? 'Waktu permainan habis. Hasil akhir sedang ditampilkan.' : 'Permainan selesai. Hasil akhir sedang ditampilkan.', 'success');
            } catch (error) {
                this.toast(error.message, 'error');
            } finally {
                this.finishResult.pending = false;
            }
        }, 6000);
    },

    playDrumRoll() {
        try {
            const audio = new Audio(`${this.basePath}/audio/drum-roll.mp3`);
            audio.volume = 0.85;
            this.finishResult.drumAudio = audio;
            audio.play().catch(() => this.playBeep('finish'));
        } catch (error) {
            this.playBeep('finish');
        }
    },

    openFinishResult() {
        if (this.current?.game?.status === 'finished') {
            this.finishResult.open = true;
        }
    },

    closeFinishResult() {
        this.finishResult.open = false;
    },

    async scanUid(target = null) {
        if (!this.rfid.uid.trim()) {
            this.toast('Tempelkan atau ketik kode kartu pemain dulu.', 'error');
            this.playSound('error');
            return;
        }

        try {
            const data = await this.api('/api/rfid', {
                method: 'POST',
                body: JSON.stringify({
                    uid: this.rfid.uid,
                    game_id: this.current?.game?.id,
                }),
            });
            this.applyState(data.state);
            this.clearManualSelection();
            if (data.bankrupt) {
                await this.showAlert('Pemain bangkrut', data.message, 'warning');
                this.playSound('error');
                return;
            }
            this.toast(`${data.player.name} sudah dipilih.`, 'success');
            this.playSound('scan');

            if (target && data.player?.id) {
                this.modal.data[target] = data.player.id;
            }
        } catch (error) {
            if (error.payload?.state) {
                this.applyState(error.payload.state);
            }
            this.toast(error.message, 'error');
            this.playSound('error');
        } finally {
            this.rfid.uid = '';
        }
    },

    selectActivePlayer(player) {
        if (!player || player.is_bankrupt) {
            this.toast(player?.is_bankrupt ? `${player.name} sudah bangkrut.` : 'Pemain tidak tersedia.', 'error');
            this.playSound('error');
            return;
        }

        if (this.current?.game?.status !== 'active') {
            this.toast('Permainan belum aktif.', 'error');
            return;
        }

        this.manualSelection.playerId = player.id;
        this.manualSelection.expiresAt = this.now + 60000;
        this.toast(`${player.name} dipilih oleh Bank selama 60 detik.`, 'success');
        this.playSound('scan');

        if (this.manualSelection.timer) {
            clearTimeout(this.manualSelection.timer);
        }

        this.manualSelection.timer = setTimeout(() => {
            if (this.manualSelection.playerId === player.id && this.manualSelectionRemaining() <= 0) {
                this.clearManualSelection();
                this.renderIcons();
            }
        }, 60100);
    },

    clearManualSelection() {
        if (this.manualSelection.timer) {
            clearTimeout(this.manualSelection.timer);
        }

        this.manualSelection = {
            playerId: null,
            expiresAt: null,
            timer: null,
        };
    },

    manualSelectionRemaining() {
        if (!this.manualSelection.playerId || !this.manualSelection.expiresAt) {
            return 0;
        }

        return Math.max(0, Math.ceil((this.manualSelection.expiresAt - this.now) / 1000));
    },

    isManualSelected(player) {
        return Number(this.manualSelection.playerId) === Number(player?.id) && this.manualSelectionRemaining() > 0;
    },

    openModal(type) {
        const titles = {
            transfer: 'Kirim Uang ke Pemain',
            deposit: 'Setor Uang Tunai ke Bank',
            withdraw: 'Tarik Uang dari Bank',
            bank_to_player: 'Bank Memberi Uang',
            player_to_bank: 'Pemain Membayar ke Bank',
            buy_property: 'Beli Tanah',
            sell_property: 'Jual Tanah',
            transfer_property: 'Pindah Pemilik Tanah',
            add_house: 'Tambah Rumah',
            sell_house: 'Jual Rumah',
            add_hotel: 'Tambah Hotel',
            sell_hotel: 'Jual Hotel',
            auction: 'Lelang Properti',
        };
        const lastPlayerId = this.current?.game?.last_scanned_player_id || this.current?.players?.[0]?.id || '';

        this.modal = {
            open: true,
            type,
            title: titles[type] || 'Transaksi',
            data: {
                player_id: lastPlayerId,
                from_player_id: lastPlayerId,
                to_player_id: '',
                game_property_id: '',
                amount: type === 'bank_to_player' ? this.current?.game?.go_bonus || 20000 : '',
                source: 'bank',
                reason: '',
            },
        };
    },

    closeModal() {
        this.modal.open = false;
    },

    async submitModal() {
        const gameId = this.current.game.id;
        const pathMap = {
            transfer: 'transfer',
            deposit: 'deposit',
            withdraw: 'withdraw',
            bank_to_player: 'bank-to-player',
            player_to_bank: 'player-to-bank',
            buy_property: 'buy-property',
            sell_property: 'sell-property',
            transfer_property: 'transfer-property',
            add_house: 'add-house',
            sell_house: 'sell-house',
            add_hotel: 'add-hotel',
            sell_hotel: 'sell-hotel',
            auction: 'auction',
        };
        const data = this.coerceModalData();
        this.closeModal();
        const payload = await this.api(`/api/games/${gameId}/transactions/${pathMap[this.modal.type]}`, {
            method: 'POST',
            body: JSON.stringify(data),
        });

        this.applyState(payload.state);
        this.toast(payload.message, 'success');
        this.playSound(this.modal.type);
    },

    async quickTransaction(type, overrides = {}) {
        if (!this.current?.game?.id || this.current.game.status !== 'active') {
            this.toast('Permainan harus berjalan dulu untuk melakukan transaksi.', 'error');
            this.playSound('error');
            return;
        }

        const player = this.lastScannedPlayer();
        if (!player) {
            this.toast('Pilih pemain dulu sebelum transaksi.', 'error');
            this.playSound('error');
            return;
        }

        const gameId = this.current.game.id;
        const calls = {
            bank_to_player: {
                path: 'bank-to-player',
                data: {
                    player_id: player.id,
                    amount: overrides.amount || this.current.game.go_bonus,
                    reason: overrides.reason || 'Gaji',
                    card_key: overrides.card_key,
                },
            },
            player_to_bank: {
                path: 'player-to-bank',
                data: {
                    player_id: player.id,
                    amount: overrides.amount,
                    reason: overrides.reason,
                    card_key: overrides.card_key,
                },
            },
            deposit: {
                path: 'deposit',
                data: {
                    player_id: player.id,
                    amount: overrides.amount,
                    reason: overrides.reason || 'Setor uang tunai',
                },
            },
            withdraw: {
                path: 'withdraw',
                data: {
                    player_id: player.id,
                    amount: overrides.amount,
                    reason: overrides.reason || 'Tarik uang tunai',
                },
            },
            buy_property: {
                path: 'buy-property',
                data: {
                    player_id: player.id,
                    game_property_id: overrides.game_property_id || this.quick.property_id,
                },
            },
            add_house: {
                path: 'add-house',
                data: {
                    player_id: player.id,
                    game_property_id: overrides.game_property_id || this.quick.property_id,
                },
            },
            sell_house: {
                path: 'sell-house',
                data: {
                    player_id: player.id,
                    game_property_id: overrides.game_property_id || this.quick.property_id,
                },
            },
            add_hotel: {
                path: 'add-hotel',
                data: {
                    player_id: player.id,
                    game_property_id: overrides.game_property_id || this.quick.property_id,
                },
            },
            sell_hotel: {
                path: 'sell-hotel',
                data: {
                    player_id: player.id,
                    game_property_id: overrides.game_property_id || this.quick.property_id,
                },
            },
            transfer: {
                path: 'transfer',
                data: {
                    from_player_id: player.id,
                    to_player_id: overrides.to_player_id || this.quick.receiver_id,
                    amount: overrides.amount || this.quick.amount,
                    source: overrides.source || this.quick.transfer_source || 'bank',
                },
            },
            pay_rent: {
                path: 'pay-rent',
                data: {
                    player_id: player.id,
                    game_property_id: overrides.game_property_id || this.quick.property_id,
                    source: overrides.source || this.quick.transfer_source || 'bank',
                },
            },
            collect_from_players: {
                path: 'collect-from-players',
                data: {
                    player_id: player.id,
                    amount: overrides.amount,
                    reason: overrides.reason,
                    card_key: overrides.card_key,
                },
            },
        };

        const call = calls[type];
        if (!call) {
            return;
        }

        Object.keys(call.data).forEach((key) => {
            if (call.data[key] === undefined || call.data[key] === null) {
                delete call.data[key];
            }
        });

        if (Object.values(call.data).some((value) => value === '' || Number.isNaN(value))) {
            this.toast('Lengkapi dulu pilihan dan jumlah uangnya.', 'error');
            this.playSound('error');
            return;
        }

        try {
            const payload = await this.api(`/api/games/${gameId}/transactions/${call.path}`, {
                method: 'POST',
                body: JSON.stringify(call.data),
            });

            this.applyState(payload.state);
            this.toast(payload.message, 'success');
            this.playSound(overrides.sound || type);
        } catch (error) {
            this.toast(error.message, 'error');
            this.playSound('error');
        }
    },

    async quickPayRent(property) {
        const payer = this.lastScannedPlayer();

        if (!payer) {
            this.toast('Pilih pemain yang harus membayar sewa dulu.', 'error');
            this.playSound('error');
            return;
        }

        if (!property?.owner_id) {
            this.toast('Tanah ini belum ada pemiliknya.', 'error');
            this.playSound('error');
            return;
        }

        if (Number(property.owner_id) === Number(payer.id)) {
            this.toast('Pemain ini adalah pemilik tanah tersebut.', 'error');
            this.playSound('error');
            return;
        }

        await this.quickTransaction('pay_rent', {
            game_property_id: property.id,
            source: this.quick.transfer_source || 'bank',
            sound: 'rent',
        });
    },

    async runCardAction(action) {
        const typeMap = {
            receive: 'bank_to_player',
            pay: 'player_to_bank',
            collect_players: 'collect_from_players',
        };

        await this.quickTransaction(typeMap[action.flow], {
            amount: action.amount,
            reason: `${action.deck}: ${action.label}`,
            card_key: action.key,
        });
    },

    async drawRandomCard(deck = null) {
        const available = this.cardActions.filter((action) => (!deck || action.deck === deck) && !this.isCardUsed(action));
        if (!available.length) {
            this.toast('Semua kartu di pilihan ini sudah dipakai. Klik Acak Ulang Kartu dulu.', 'error');
            this.playSound('error');
            return;
        }

        const action = available[Math.floor(Math.random() * available.length)];
        this.toast(`${action.deck}: ${action.label}`, 'success');
        await this.runCardAction(action);
    },

    async toggleMusic() {
        try {
            if (!this.music.audio) {
                this.music.audio = new Audio(`${this.basePath}/audio/main-theme-song.mp3`);
                this.music.audio.loop = true;
                this.music.audio.volume = this.music.volume;
            }

            if (this.music.playing) {
                this.music.audio.pause();
                this.music.playing = false;
                return;
            }

            await this.music.audio.play();
            this.music.playing = true;
        } catch (error) {
            this.toast('Klik sekali lagi kalau musik belum terdengar.', 'error');
        }
    },

    setMusicVolume(value) {
        this.music.volume = Number(value);
        if (this.music.audio) {
            this.music.audio.volume = this.music.volume;
        }
    },

    isCardUsed(action) {
        return (this.current?.cards?.used_keys || []).includes(action.key);
    },

    async refreshCards() {
        if (!this.current?.game?.id) {
            return;
        }

        const payload = await this.api(`/api/games/${this.current.game.id}/cards/refresh`, {
            method: 'POST',
            body: JSON.stringify({}),
        });
        this.applyState(payload.state);
        this.toast(payload.message, 'success');
        this.playSound('success');
    },

    async bankruptPlayer(player) {
        if (!this.current?.game?.id || player.is_bankrupt) {
            return;
        }

        const preview = await this.api(`/api/games/${this.current.game.id}/transactions/bankruptcy-preview`, {
            method: 'POST',
            body: JSON.stringify({ player_id: player.id }),
        });
        const summary = preview.summary;
        const confirmed = await this.confirmAction({
            title: `${player.name} dinyatakan bangkrut?`,
            html: `
                <div style="text-align:left;line-height:1.7">
                    <b>Jual semua properti dan aset?</b><br>
                    Properti: ${summary.property_count} = ${this.money(summary.property_sale)}<br>
                    Rumah: ${summary.house_count} = ${this.money(summary.house_sale)}<br>
                    Hotel: ${summary.hotel_count} = ${this.money(summary.hotel_sale)}<br>
                    <hr style="margin:8px 0;border-color:rgba(148,163,184,.25)">
                    Total nilai jual 1/2: <b>${this.money(summary.sale_total)}</b>
                </div>
            `,
            confirmButtonText: 'Iya, bangkrut',
        });

        if (!confirmed) {
            return;
        }

        const payload = await this.api(`/api/games/${this.current.game.id}/transactions/bankrupt`, {
            method: 'POST',
            body: JSON.stringify({ player_id: player.id }),
        });
        this.applyState(payload.state);
        this.toast(payload.message, 'success');
        this.playSound('sell_property');
    },

    async confirmAction({ title, text = '', html = '', confirmButtonText = 'Ya' }) {
        if (window.Swal) {
            const result = await window.Swal.fire({
                title,
                text,
                html,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText,
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                background: '#0f172a',
                color: '#e2e8f0',
            });

            return result.isConfirmed;
        }

        return confirm(text ? `${title}\n${text}` : title);
    },

    async showAlert(title, text, icon = 'info') {
        if (window.Swal) {
            await window.Swal.fire({
                title,
                text,
                icon,
                confirmButtonText: 'OK',
                confirmButtonColor: '#10b981',
                background: '#0f172a',
                color: '#e2e8f0',
            });
            return;
        }

        alert(`${title}\n${text}`);
    },

    coerceModalData() {
        const data = { ...this.modal.data };
        ['player_id', 'from_player_id', 'to_player_id', 'game_property_id', 'amount'].forEach((key) => {
            if (data[key] !== '' && data[key] !== null && data[key] !== undefined) {
                data[key] = Number(data[key]);
            }
        });

        return data;
    },

    selectableProperties(type, playerId = null) {
        const properties = this.current?.properties || [];
        const ownerId = Number(playerId || this.modal.data.player_id || this.modal.data.from_player_id);

        if (['buy_property', 'auction'].includes(type)) {
            return properties.filter((property) => !property.owner_id);
        }

        if (['sell_property', 'add_house', 'sell_house', 'add_hotel', 'sell_hotel'].includes(type)) {
            return properties.filter((property) => Number(property.owner_id) === ownerId);
        }

        if (type === 'transfer_property') {
            return properties.filter((property) => Number(property.owner_id) === Number(this.modal.data.from_player_id));
        }

        return properties;
    },

    playerById(id) {
        return (this.current?.players || []).find((player) => Number(player.id) === Number(id));
    },

    lastScannedPlayer() {
        if (this.manualSelection.playerId) {
            if (this.manualSelectionRemaining() <= 0) {
                return null;
            }

            return this.playerById(this.manualSelection.playerId);
        }

        const player = this.playerById(this.current?.game?.last_scanned_player_id);

        return player?.is_bankrupt ? null : player;
    },

    activePlayers() {
        return (this.current?.players || []).filter((player) => !player.is_bankrupt);
    },

    isRichest(player) {
        return !player?.is_bankrupt && Number(this.current?.stats?.richest_player?.id) === Number(player.id);
    },

    isPoorest(player) {
        return !player?.is_bankrupt && Number(this.current?.stats?.poorest_player?.id) === Number(player.id);
    },

    propertyProgress(player) {
        const total = this.current?.properties?.length || 0;
        if (!total) {
            return 0;
        }

        return Math.min(100, Math.round((Number(player?.property_count || 0) / total) * 100));
    },

    dashboardStats() {
        const sold = this.current?.stats?.owned_property_count || 0;
        return [
            {
                label: 'Uang Bank',
                value: this.money(this.current?.game?.total_money),
                icon: 'landmark',
                iconClass: 'bg-emerald-300 text-slate-950',
                valueClass: 'text-emerald-300',
            },
            {
                label: 'Pemain',
                value: this.current?.game?.player_count || 0,
                icon: 'users',
                iconClass: 'bg-blue-300 text-slate-950',
                valueClass: 'text-blue-300',
            },
            {
                label: 'Tanah Terjual',
                value: `${sold}/${this.current?.properties?.length || 0}`,
                icon: 'home',
                iconClass: 'bg-amber-300 text-slate-950',
                valueClass: 'text-amber-300',
            },
            {
                label: 'Transaksi',
                value: this.current?.stats?.transaction_count || 0,
                icon: 'receipt',
                iconClass: 'bg-purple-300 text-slate-950',
                valueClass: 'text-purple-300',
            },
            {
                label: 'Sisa Waktu',
                value: this.countdownLabel(),
                icon: 'timer',
                iconClass: 'bg-orange-300 text-slate-950',
                valueClass: this.countdownTone(),
            },
            {
                label: 'Status Main',
                value: (this.current?.game?.status_label || '').toUpperCase(),
                icon: 'gamepad-2',
                iconClass: this.current?.game?.status === 'active' ? 'bg-emerald-300 text-slate-950' : 'bg-slate-300 text-slate-950',
                valueClass: this.current?.game?.status === 'active' ? 'text-emerald-300' : 'text-slate-200',
            },
        ];
    },

    topPlayers() {
        return [...this.activePlayers()].sort((a, b) => Number(b.total_asset || 0) - Number(a.total_asset || 0)).slice(0, 3);
    },

    playerAssetDelta(player) {
        const starting = Number(this.current?.game?.starting_balance || 0) + Number(this.current?.game?.starting_cash || 0);

        return Number(player?.total_asset || 0) - starting;
    },

    assetDeltaLabel(player) {
        const delta = this.playerAssetDelta(player);
        const sign = delta >= 0 ? '+' : '-';

        return `${sign}${this.money(Math.abs(delta))}`;
    },

    statPlayer(field) {
        return [...this.activePlayers()].sort((a, b) => Number(b[field] || 0) - Number(a[field] || 0))[0] || null;
    },

    mostTransactions() {
        const counts = {};
        (this.current?.transactions || []).forEach((transaction) => {
            [transaction.from_player_id, transaction.to_player_id, transaction.player_id]
                .filter(Boolean)
                .forEach((id) => {
                    counts[id] = (counts[id] || 0) + 1;
                });
        });

        const winner = this.activePlayers()
            .map((player) => ({ player, count: counts[player.id] || 0 }))
            .sort((a, b) => b.count - a.count)[0];

        return { name: winner?.player?.name || '-', count: winner?.count || 0 };
    },

    biggestTransaction() {
        return [...(this.current?.transactions || [])].sort((a, b) => Number(b.amount || 0) - Number(a.amount || 0))[0] || null;
    },

    transactionIcon(type) {
        const icons = {
            transfer: 'arrow-left-right',
            deposit: 'wallet',
            withdraw: 'download',
            bank_to_player: 'coins',
            player_to_bank: 'receipt',
            buy_property: 'home',
            sell_property: 'badge-dollar-sign',
            transfer_property: 'building-2',
            add_house: 'house-plus',
            sell_house: 'house-minus',
            add_hotel: 'building',
            sell_hotel: 'building-2',
            auction: 'gavel',
            rfid_scan: 'scan-line',
            game_started: 'play',
            game_finished: 'crown',
            first_player_selected: 'dice-5',
            cards_refreshed: 'refresh-cw',
            bankrupt: 'skull',
        };

        return icons[type] || 'activity';
    },

    transactionTone(type) {
        if (['bank_to_player', 'deposit'].includes(type)) {
            return 'bg-emerald-400 text-slate-950';
        }

        if (['player_to_bank', 'withdraw', 'bankrupt'].includes(type)) {
            return 'bg-rose-500 text-white';
        }

        if (['transfer'].includes(type)) {
            return 'bg-blue-400 text-slate-950';
        }

        if (['buy_property', 'sell_property', 'auction'].includes(type)) {
            return 'bg-amber-300 text-slate-950';
        }

        if (['add_house', 'sell_house', 'add_hotel', 'sell_hotel', 'transfer_property'].includes(type)) {
            return 'bg-purple-400 text-white';
        }

        return 'bg-slate-300 text-slate-950';
    },

    transactionAmountClass(transaction) {
        if (['bank_to_player', 'deposit'].includes(transaction.type)) {
            return 'text-emerald-300';
        }

        if (['player_to_bank', 'withdraw'].includes(transaction.type)) {
            return 'text-rose-300';
        }

        if (transaction.type === 'transfer') {
            return 'text-blue-300';
        }

        return 'text-amber-300';
    },

    awards() {
        const richest = this.current?.stats?.richest_player;
        const propertyOwner = this.current?.stats?.top_property_owner;
        const cashKing = this.statPlayer('cash_balance');
        const bankMaster = this.statPlayer('balance');
        const dealMaker = this.mostTransactions();
        const bankruptCount = (this.current?.players || []).filter((player) => player.is_bankrupt).length;

        return [
            { title: 'Pemain Terkaya', description: richest ? `${richest.name} punya total aset tertinggi` : '-', icon: 'crown', color: 'bg-amber-300 text-slate-950' },
            { title: 'Raja Properti', description: propertyOwner ? `${propertyOwner.name} punya tanah terbanyak` : '-', icon: 'home', color: 'bg-orange-300 text-slate-950' },
            { title: 'Uang Tunai Terbanyak', description: cashKing ? `${cashKing.name} memegang uang tunai terbesar` : '-', icon: 'wallet', color: 'bg-blue-300 text-slate-950' },
            { title: 'Saldo Bank Tertinggi', description: bankMaster ? `${bankMaster.name} punya uang bank terbanyak` : '-', icon: 'landmark', color: 'bg-emerald-300 text-slate-950' },
            { title: 'Paling Aktif', description: `${dealMaker.name} paling sering bertransaksi`, icon: 'handshake', color: 'bg-purple-400 text-white' },
            { title: 'Transaksi Terbesar', description: this.biggestTransaction()?.description || '-', icon: 'receipt', color: 'bg-rose-400 text-white' },
            { title: 'Penerima Sewa', description: 'Berdasarkan catatan pembayaran sewa', icon: 'trending-up', color: 'bg-lime-300 text-slate-950' },
            { title: 'Bangkrut', description: `${bankruptCount} pemain sudah kalah karena bangkrut`, icon: 'skull', color: 'bg-slate-300 text-slate-950' },
        ];
    },

    countdownSeconds() {
        if (!this.current?.game?.ends_at || this.current.game.status === 'finished') {
            return this.current?.game?.remaining_seconds ?? null;
        }

        const end = new Date(this.current.game.ends_at).getTime();

        return Math.max(0, Math.floor((end - this.now) / 1000));
    },

    countdownLabel() {
        const seconds = this.countdownSeconds();

        if (seconds === null || seconds === undefined) {
            return 'Tanpa timer';
        }

        return this.duration(seconds);
    },

    countdownTone() {
        const seconds = this.countdownSeconds();

        if (seconds === null || seconds === undefined) {
            return 'text-slate-200';
        }

        if (seconds <= 300) {
            return 'text-rose-300';
        }

        if (seconds <= 900) {
            return 'text-orange-300';
        }

        return 'text-emerald-300';
    },

    selectedQuickProperty() {
        return (this.current?.properties || []).find((property) => Number(property.id) === Number(this.quick.property_id));
    },

    currentRent(property) {
        if (!property) {
            return 0;
        }

        if (property.current_rent !== undefined && property.current_rent !== null) {
            return property.current_rent;
        }

        if (property.has_hotel) {
            return property.rent_hotel;
        }

        const rents = {
            1: property.rent_1_house,
            2: property.rent_2_houses,
            3: property.rent_3_houses,
            4: property.rent_4_houses,
        };

        return rents[property.house_count] || property.rent;
    },

    diceStatusText() {
        const roll = this.current?.turn?.last_roll;

        if (!roll) {
            return 'Belum ada dadu';
        }

        if (roll.result === 'go_to_jail') {
            return `${roll.player_name} masuk penjara`;
        }

        if (roll.result === 'jail_wait') {
            return `${roll.player_name} masih penjara`;
        }

        if (roll.result === 'jail_released') {
            return `${roll.player_name} keluar penjara`;
        }

        if (roll.is_double) {
            return `${roll.dice_one}+${roll.dice_two} double`;
        }

        return `${roll.player_name}: ${roll.dice_one}+${roll.dice_two}=${roll.total}`;
    },

    pendingBoardAction() {
        const player = (this.current?.players || []).find((item) => item.pending_space_action);

        return player ? { player, action: player.pending_space_action } : null;
    },

    async loadSettings() {
        const payload = await this.api('/api/settings', { silent: true });
        this.settings = payload.settings;
        this.settingsForm = { ...payload.settings };
        this.properties = payload.properties;
    },

    async saveSettings() {
        const payload = await this.api('/api/settings', {
            method: 'PUT',
            body: JSON.stringify(this.settingsForm),
        });
        this.settings = payload.settings;
        this.toast(payload.message, 'success');
    },

    async createProperty() {
        const payload = await this.api('/api/settings/properties', {
            method: 'POST',
            body: JSON.stringify(this.propertyForm),
        });
        this.properties = payload.properties;
        this.propertyForm = {
            name: '',
            price: 0,
            house_price: 0,
            hotel_price: 0,
            rent: 0,
            rent_1_house: 0,
            rent_2_houses: 0,
            rent_3_houses: 0,
            rent_4_houses: 0,
            rent_hotel: 0,
            color: '#10b981',
            sort_order: this.properties.length + 1,
        };
        this.toast(payload.message, 'success');
    },

    editProperty(property) {
        property._editing = true;
    },

    async saveProperty(property) {
        const payload = await this.api(`/api/settings/properties/${property.id}`, {
            method: 'PUT',
            body: JSON.stringify(property),
        });
        this.properties = payload.properties;
        this.toast(payload.message, 'success');
    },

    async deleteProperty(property) {
        const payload = await this.api(`/api/settings/properties/${property.id}`, {
            method: 'DELETE',
        });
        this.properties = payload.properties;
        this.toast(payload.message, 'success');
    },

    async importProperties(event) {
        const file = event.target.files[0];
        if (!file) {
            return;
        }
        const form = new FormData();
        form.append('csv', file);
        const payload = await this.api('/api/settings/properties/import', {
            method: 'POST',
            body: form,
        });
        this.properties = payload.properties;
        this.toast(payload.message, 'success');
        event.target.value = '';
    },

    exportProperties() {
        window.location.href = `${this.basePath}/api/settings/properties/export`;
    },

    drawCharts() {
        if (!this.current || this.view !== 'dashboard') {
            return;
        }

        const signature = JSON.stringify({
            wealth: this.current.charts?.wealth,
            transactions: this.current.charts?.transactions,
            ownership: this.current.charts?.ownership,
        });

        if (signature === this.chartSignature) {
            return;
        }

        this.chartSignature = signature;

        requestAnimationFrame(() => {
            this.destroyCharts();
            this.drawWealthChart();
            this.drawTransactionChart();
            this.drawOwnershipChart();
        });
    },

    destroyCharts() {
        Object.values(this.charts).forEach((chart) => chart?.destroy());
        this.charts = {};
    },

    chartTextColor() {
        return this.darkMode ? '#dbeafe' : '#334155';
    },

    drawWealthChart() {
        const canvas = document.getElementById('wealthChart');
        if (!canvas) {
            return;
        }

        this.charts.wealth = new Chart(canvas, {
            type: 'line',
            data: {
                labels: this.current.charts.wealth.labels,
                datasets: this.current.charts.wealth.datasets,
            },
            options: this.chartOptions(false, true),
        });
    },

    drawTransactionChart() {
        const canvas = document.getElementById('transactionChart');
        if (!canvas) {
            return;
        }

        this.charts.transactions = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: this.current.charts.transactions.labels,
                datasets: [{
                    data: this.current.charts.transactions.data,
                    backgroundColor: ['#38bdf8', '#10b981', '#f97316', '#a78bfa', '#fb7185', '#facc15', '#22c55e', '#60a5fa'],
                    borderRadius: 8,
                }],
            },
            options: this.chartOptions(false),
        });
    },

    drawOwnershipChart() {
        const canvas = document.getElementById('ownershipChart');
        if (!canvas) {
            return;
        }

        this.charts.ownership = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: this.current.charts.ownership.labels,
                datasets: [{
                    data: this.current.charts.ownership.data,
                    backgroundColor: this.current.charts.ownership.colors,
                    borderWidth: 0,
                }],
            },
            options: this.chartOptions(true),
        });
    },

    chartOptions(showLegend, forceLegend = false) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: showLegend || forceLegend,
                    labels: { color: this.chartTextColor(), boxWidth: 10 },
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label || context.label}: ${this.money(context.parsed.y ?? context.parsed)}`,
                    },
                },
            },
            scales: showLegend ? {} : {
                x: {
                    ticks: { color: this.chartTextColor() },
                    grid: { color: 'rgba(148, 163, 184, 0.12)' },
                },
                y: {
                    ticks: { color: this.chartTextColor() },
                    grid: { color: 'rgba(148, 163, 184, 0.12)' },
                },
            },
        };
    },

    toast(message, type = 'success') {
        const id = crypto.randomUUID();
        this.toasts.push({ id, message, type });
        setTimeout(() => {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        }, 3600);
    },

    playSound(kind) {
        const soundFiles = {
            player_to_bank: 'bayar-pajak.mp3',
            bank_to_player: 'dapat-komisi.mp3',
            transfer: 'bayar-sewa.mp3',
            pay_rent: 'bayar-sewa.mp3',
            rent: 'bayar-sewa.mp3',
            buy_property: 'beli-asset.mp3',
            add_house: 'beli-asset.mp3',
            add_hotel: 'beli-asset.mp3',
            go_salary: 'gajian.mp3',
            tax: 'bayar-pajak.mp3',
            fine: 'bayar-denda.mp3',
            drum_roll: 'drum-roll.mp3',
        };

        if (soundFiles[kind]) {
            const src = `${this.basePath}/audio/${soundFiles[kind]}`;
            const fileAudio = this.soundCache[src] || new Audio(src);
            this.soundCache[src] = fileAudio;
            fileAudio.currentTime = 0;
            fileAudio.volume = 0.65;
            fileAudio.play().catch(() => this.playBeep(kind));
            return;
        }

        this.playBeep(kind);
    },

    playBeep(kind) {
        try {
            const audio = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audio.createOscillator();
            const gain = audio.createGain();
            const frequencies = {
                error: 150,
                transfer: 420,
                buy_property: 520,
                sell_property: 360,
                add_house: 620,
                sell_house: 360,
                add_hotel: 720,
                sell_hotel: 340,
                finish: 880,
                success: 540,
                scan: 460,
                spin: 760,
            };
            oscillator.frequency.value = frequencies[kind] || 500;
            oscillator.type = kind === 'error' ? 'sawtooth' : 'triangle';
            gain.gain.setValueAtTime(0.0001, audio.currentTime);
            gain.gain.exponentialRampToValueAtTime(kind === 'error' ? 0.16 : 0.09, audio.currentTime + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + 0.22);
            oscillator.connect(gain).connect(audio.destination);
            oscillator.start();
            oscillator.stop(audio.currentTime + 0.24);
        } catch (error) {
            // Browser audio can be blocked until the first user gesture.
        }
    },

    money(value) {
        return new Intl.NumberFormat('id-ID').format(Number(value || 0));
    },

    duration(seconds) {
        const total = Number(seconds || 0);
        const hours = Math.floor(total / 3600);
        const minutes = Math.floor((total % 3600) / 60);
        const secs = total % 60;

        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    },
});

window.Alpine = Alpine;
Alpine.start();
