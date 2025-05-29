# Laravel Wallet Package

<b style="color:red">\*_Tested on Laravel 10 & 11_</b>

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

Install via composer:

```bash
composer require vergatan10/laravel-wallet:dev-main
```

Publish config & migration:

```bash
php artisan vendor:publish --provider="vendor\vergatan10\laravel-wallet\WalletServiceProvider" --tag=wallet-config
php artisan migrate
```

Tambahkan kode ini di model User

```php
// App/Models/User.php
use Vergatan10\Wallet\Models\Wallet;

// relasi ke wallet
public function wallet()
{
    return $this->hasOne(Wallet::class);
}
```

---

## ⚙️ Konfigurasi

Tambahkan ke .env jika ingin memberikan saldo awal otomatis saat user dibuat:

```env
// .env
WALLET_DEFAULT_BALANCE=10000
```

---

## 🧬 Fitur Otomatis

<b>_Khusus Laravel Breeze/Jetstream_</b>
Wallet akan dibuat otomatis saat user mendaftar:

- Auto-create saat event Registered di-trigger
- Saldo awal mengikuti config wallet.default_balance

\*Jika kamu create user secara manual ikuti langkah ini:

Tambahkan kode ini untuk menjalankan event setelah create user

```php
// UserController.php
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

// create user
$user = User::create([...]);
// tambahkan kode ini untuk menjalankan event
event(new Registered($user));
```

---

## 📥 API Endpoints

Semua route menggunakan prefix `/api/wallet` dan middleware `auth:sanctum`.

| Method | Endpoint                                            | Keterangan              | Body Contoh                                                                                                                               |
| ------ | --------------------------------------------------- | ----------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| GET    | /api/wallet/{wallet_id}                             | Get wallet balance      | -                                                                                                                                         |
| GET    | /api/wallet/{wallet_id}/transactions                | Riwayat transaksi       | -                                                                                                                                         |
| POST   | /api/wallet/{wallet_id}/deposit                     | Deposit ke wallet       | <pre>{<br> "amount": 10000,<br> "description": "Top up"<br>}</pre>                                                                        |
| POST   | /api/wallet/{wallet_id}/withdraw                    | Withdraw dari wallet    | <pre>{<br> "amount": 5000,<br> "description": "Beli pulsa",<br> "meta": {<br> "ref": "REF123456"<br> }<br>}</pre>                         |
| POST   | /api/wallet/{wallet_id}/transfer                    | Transfer ke wallet lain | <pre>{<br> "to_wallet_id": 2,<br> "amount": 2500,<br> "description": "Bayar utang",<br> "meta": {<br> "ref": "REF123456"<br> }<br>}</pre> |
| POST   | /api/wallet/{wallet_id}/transactions/{tx_id}/refund | Refund transaksi        | -                                                                                                                                         |

**Contoh Body Request:**

- **Deposit**

  ```json
  {
    "amount": 10000,
    "description": "Top up"
  }
  ```

- **Withdraw**

  ```json
  {
    "amount": 5000,
    "description": "Beli pulsa",
    "meta": {
      "ref": "REF123456",
      ...
    }
  }
  ```

- **Transfer**
  ```json
  {
    "to_wallet_id": 2,
    "amount": 2500,
    "description": "Bayar utang",
    "meta": {
      "ref": "REF123456",
      ...
    }
  }
  ```

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

## 📂 Struktur File Package

```
packages/Vergatan10/Wallet/
├── src/
│   ├── config/
│   │   └── wallet.php
│   ├── Contracts/
│   ├── database/
│   │   └── migrations/
│   ├── Facades/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Listeners/
│   ├── Models/
│   ├── routes/
│   ├── Services/
│   └── WalletServiceProvider.php
└── composer.json
```

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
