# Laravel Wallet Package

Sebuah package Laravel untuk mengelola sistem wallet digital, termasuk fitur:

- Deposit
- Withdraw
- Transfer antar wallet
- Refund
- Pending transfer
- Lock / unlock wallet
- Transaction log
- Auto-create wallet saat user register

---

## 📦 Instalasi

Tambahkan di composer.json:

```json
{
  "require": {
    "vergatan10/laravel-wallet": "dev-main"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/vergatan10/laravel-wallet"
    }
  ]
}
```

Install via composer:

```bash
composer require vergatan10/laravel-wallet:dev-main
```

Publish config & migration:

```bash
php artisan vendor:publish --provider="vendor\vergatan10\laravel-wallet\WalletServiceProvider" --tag=wallet-config
php artisan migrate
```

---

## ⚙️ Konfigurasi

Tambahkan ke .env jika ingin memberikan saldo awal otomatis saat user dibuat:

```env
WALLET_DEFAULT_BALANCE=10000
```

---

## 🧬 Fitur Otomatis

Wallet akan dibuat otomatis saat user mendaftar:

- Auto-create saat event Registered di-trigger
- Saldo awal mengikuti config wallet.default_balance

---

## 📥 API Endpoints

Semua route menggunakan prefix /api/wallet dan middleware auth:sanctum.

| Method | Endpoint                                  | Keterangan              |
| ------ | ----------------------------------------- | ----------------------- |
| GET    | /api/wallet/{id}                          | Get wallet balance      |
| GET    | /api/wallet/{id}/transactions             | Riwayat transaksi       |
| POST   | /api/wallet/{id}/deposit                  | Deposit ke wallet       |
| POST   | /api/wallet/{id}/withdraw                 | Withdraw dari wallet    |
| POST   | /api/wallet/{id}/transfer                 | Transfer ke wallet lain |
| POST   | /api/wallet/{id}/transactions/{tx}/refund | Refund transaksi        |
| POST   | /api/wallet/{id}/lock                     | Lock wallet             |
| POST   | /api/wallet/{id}/unlock                   | Unlock wallet           |

📌 Semua endpoint kecuali create hanya bisa diakses oleh pemilik wallet (dengan middleware wallet.owner)

---

## 💡 Penggunaan via Facade

Gunakan facade Wallet untuk mengakses fitur:

```php
use Vergatan10\Wallet\Facades\Wallet;
use Vergatan10\Wallet\Models\Wallet as WalletModel;

$wallet = WalletModel::find(1);

// Deposit
Wallet::deposit($wallet, 10000, 'Topup manual');

// Withdraw
Wallet::withdraw($wallet, 5000, 'Penarikan');

// Transfer
Wallet::transfer($fromWallet, $toWallet, 2500, 'Transfer antar user');

// Refund transaksi
Wallet::refund($transaction);

// Konfirmasi transfer (jika transfer pending)
Wallet::confirmTransfer($transaction);

// Lock / Unlock
$wallet->update(['is_locked' => true]);
```

---

## 🥪 Testing

Kamu bisa menambahkan unit test dengan Laravel built-in test tools.

Contoh:

```php
public function test_user_can_deposit()
{
    $wallet = Wallet::factory()->create();
    Wallet::deposit($wallet, 10000, 'Test deposit');

    $this->assertEquals(10000, $wallet->fresh()->balance);
}
```

---

## 💠 Artisan Command (Opsional)

Tambahkan command wallet:create jika kamu ingin bisa buat wallet via CLI:

```bash
php artisan wallet:create {user_id}
```

---

## 📂 Struktur File Package

packages/Vergatan10/Wallet/
├── config/
│ └── wallet.php
├── src/
│ ├── WalletServiceProvider.php
│ ├── Services/
│ ├── Facades/
│ ├── Listeners/
│ ├── Models/
│ ├── Http/
│ │ ├── Controllers/
│ │ ├── Middleware/
│ ├── routes/
└── database/
└── migrations/

---

## 🔐 Security

- Semua transaksi diperiksa apakah wallet sedang dikunci
- Transaksi tidak dapat dilakukan dari atau ke wallet yang locked
- Transfer bisa menggunakan pending state → butuh konfirmasi manual

---

## ✅ Todo & Rencana

- [x] Deposit / Withdraw / Transfer
- [x] Refund
- [x] Lock / Unlock
- [x] Konfirmasi transfer
- [ ] Limit transaksi harian
- [ ] Event listener / notifikasi

---

## 🧑‍💻 Kontribusi

Pull request & issue sangat diterima!  
Silakan fork repo dan kirim PR.
