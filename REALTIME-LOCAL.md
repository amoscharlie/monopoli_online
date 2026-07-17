# Realtime Local Monopoly Bank

Untuk update realtime tanpa delay antara Bank dan HP pemain, jalankan Reverb di PC Bank.

## Saat Main

1. Nyalakan Apache dan MySQL dari XAMPP.
2. Buka terminal di folder project:

```bash
cd C:\xampp\htdocs\monopoly.unai.edu
php artisan reverb:start --host=0.0.0.0 --port=8080
```

3. Buka aplikasi Bank dari IP PC:

```text
http://10.0.0.155/monopoly.unai.edu/public/
```

4. HP pemain scan QR seperti biasa.

## Jika Reverb Tidak Jalan

Aplikasi tetap jalan. Browser otomatis fallback ke polling 1 detik.

## Firewall

Kalau HP belum realtime atau tidak bisa connect, izinkan port berikut di Windows Firewall:

- Apache: 80
- Reverb WebSocket: 8080
