# 📘 SIJAD — Guidelines Pengembangan Aplikasi

> **Sistem Informasi Jabatan Akademik Dosen (SIJAD)**
> Dibangun dengan **Laravel 12** + **Blade Templating Engine**
> Terakhir diperbarui: 19 Februari 2026

---

## Daftar Isi

1. [Ringkasan Proyek](#1-ringkasan-proyek)
2. [Tech Stack](#2-tech-stack)
3. [Struktur Direktori](#3-struktur-direktori)
4. [Arsitektur Aplikasi](#4-arsitektur-aplikasi)
5. [Database & Koneksi](#5-database--koneksi)
6. [Skema Database (Migrations)](#6-skema-database-migrations)
7. [Model Eloquent](#7-model-eloquent)
8. [Routing](#8-routing)
9. [Autentikasi (Fortify)](#9-autentikasi-fortify)
10. [Blade Templating & Layout](#10-blade-templating--layout)
11. [Assets & Frontend Bundling](#11-assets--frontend-bundling)
12. [Konvensi Kode](#12-konvensi-kode)
13. [Testing](#13-testing)
14. [Perintah Penting](#14-perintah-penting)
15. [Alur Pengembangan Fitur Baru](#15-alur-pengembangan-fitur-baru)

---

## 1. Ringkasan Proyek

**SIJAD** adalah sistem informasi untuk mengelola **jabatan akademik dosen**, termasuk:

- 📊 **Manajemen unsur penilaian** (hierarki header/sub-item dengan self-referencing)
- 📝 **Input nilai kredit dosen** per unsur penilaian
- 🔐 **Autentikasi** menggunakan Laravel Fortify (login via `userid`)
- 📋 **Dashboard** admin untuk monitoring

Aplikasi menggunakan **dua koneksi database** sekaligus:

- **MySQL** (`sijad`) — database utama untuk data penilaian
- **PostgreSQL** (`pegawai` / `uwmsdm`) — database pegawai untuk autentikasi user

---

## 2. Tech Stack

| Komponen          | Teknologi                  | Versi    |
| ----------------- | -------------------------- | -------- |
| **Framework**     | Laravel                    | 12.x     |
| **PHP**           | PHP                        | ≥ 8.2    |
| **Templating**    | Blade                      | (bawaan) |
| **CSS Framework** | Tailwind CSS               | 4.x      |
| **UI Template**   | Crovex Admin (Bootstrap 4) | -        |
| **Bundler**       | Vite                       | 7.x      |
| **Auth**          | Laravel Fortify            | 1.x      |
| **API Token**     | Laravel Sanctum            | 4.x      |
| **Testing**       | Pest PHP                   | 4.x      |
| **Code Style**    | Laravel Pint               | 1.x      |
| **DB Utama**      | MySQL                      | -        |
| **DB Pegawai**    | PostgreSQL                 | -        |

---

## 3. Struktur Direktori

```
sijad-lara12/
├── app/
│   ├── Actions/
│   │   └── Fortify/                    # Action classes untuk Fortify
│   │       ├── CreateNewUser.php
│   │       ├── PasswordValidationRules.php
│   │       ├── ResetUserPassword.php
│   │       ├── UpdateUserPassword.php
│   │       └── UpdateUserProfileInformation.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── Controller.php          # Base controller
│   ├── Models/
│   │   └── User.php                    # Model utama (koneksi ke PostgreSQL)
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── FortifyServiceProvider.php  # Konfigurasi Fortify
│
├── bootstrap/
│   ├── app.php                         # ⭐ Entry point aplikasi (middleware, routing, exceptions)
│   ├── providers.php                   # Daftar service providers
│   └── cache/
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php                    # ⭐ Konfigurasi multi-database (mysql + pegawai)
│   ├── fortify.php                     # ⭐ Konfigurasi Fortify (fitur, guard, username)
│   ├── sanctum.php
│   ├── session.php
│   └── ...
│
├── database/
│   ├── factories/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_02_09_090241_create_unsur_penilaians_table.php   # ⭐ Master unsur penilaian
│   │   ├── 2026_02_09_092449_create_nilai_dosens_table.php       # ⭐ Transaksi nilai dosen
│   │   ├── 2026_02_09_092959_create_personal_access_tokens_table.php
│   │   └── 2026_02_12_064627_add_two_factor_columns_to_users_table.php
│   └── seeders/
│
├── public/
│   ├── assets/                         # ⭐ Template Crovex (CSS, JS, images, fonts)
│   │   ├── css/                        # bootstrap.min.css, app.min.css, icons.min.css, dll.
│   │   ├── js/                         # jquery.min.js, bootstrap.bundle.min.js, app.js, dll.
│   │   ├── images/                     # Logo, favicon, user avatars, dll.
│   │   └── fonts/
│   ├── build/                          # Output Vite build
│   └── index.php
│
├── resources/
│   ├── css/
│   │   └── app.css                     # Entry point Tailwind CSS 4
│   ├── js/
│   │   ├── app.js                      # Entry point JS
│   │   └── bootstrap.js                # Axios setup
│   └── views/
│       ├── auth/
│       │   └── login.blade.php         # ⭐ Halaman login
│       ├── dashboard.blade.php         # ⭐ Halaman dashboard utama
│       ├── pages/                      # 📂 Folder untuk halaman konten (BELUM TERISI)
│       ├── partials/
│       │   └── layouts/
│       │       ├── app-layout.blade.php    # ⭐ Layout utama (master template)
│       │       ├── topbar.blade.php        # Top bar (logo + navbar + menu navigasi)
│       │       ├── navbar.blade.php        # Navigasi kanan (notif, profil, search)
│       │       ├── logo.blade.php          # Logo brand
│       │       ├── footer.blade.php        # Footer
│       │       ├── vendorcss.blade.php     # Include semua vendor CSS
│       │       └── vendorjs.blade.php      # Include semua vendor JS
│       └── welcome.blade.php              # Default Laravel welcome page
│
├── routes/
│   ├── web.php                         # ⭐ Route halaman web (Blade)
│   ├── api.php                         # Route API (Sanctum)
│   └── console.php                     # Artisan commands
│
├── storage/
├── tests/
│   ├── Feature/
│   ├── Unit/
│   ├── Pest.php
│   └── TestCase.php
│
├── .env                                # ⭐ Environment variables (DB credentials, dll.)
├── AGENTS.md                           # Laravel Boost guidelines
├── composer.json
├── package.json
└── vite.config.js                      # Konfigurasi Vite + Tailwind CSS 4 plugin
```

---

## 4. Arsitektur Aplikasi

### 4.1. Pola Arsitektur

Aplikasi mengikuti **pola MVC (Model-View-Controller)** bawaan Laravel 12 dengan tambahan:

```
┌─────────────┐     ┌─────────────┐     ┌──────────────────┐
│   Routes     │────▶│ Controllers │────▶│  Blade Views     │
│  (web.php)   │     │ (app/Http)  │     │ (resources/views)│
└─────────────┘     └──────┬──────┘     └──────────────────┘
                           │
                    ┌──────▼──────┐
                    │   Models    │
                    │ (app/Models)│
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐
              ▼                         ▼
    ┌─────────────────┐     ┌─────────────────┐
    │  MySQL (sijad)   │     │ PostgreSQL      │
    │  - unsur_penilai │     │ (uwmsdm/pegawai)│
    │  - nilai_dosens  │     │ - sc_user       │
    │  - sessions      │     └─────────────────┘
    │  - cache, jobs   │
    └─────────────────┘
```

### 4.2. Entry Point Aplikasi (Laravel 12)

Dalam Laravel 12, **`bootstrap/app.php`** menggantikan `Kernel.php` lama:

```php
// bootstrap/app.php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan middleware di sini
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle exceptions di sini
    })->create();
```

### 4.3. Service Providers

Didaftarkan di `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    // FortifyServiceProvider didaftarkan melalui auto-discovery
];
```

---

## 5. Database & Koneksi

### 5.1. Multi-Database Setup

Aplikasi menggunakan **2 koneksi database** yang didefinisikan di `config/database.php`:

| Nama Koneksi | Driver     | Database | Kegunaan                          |
| ------------ | ---------- | -------- | --------------------------------- |
| `mysql`      | MySQL      | `sijad`  | **Default** — data utama aplikasi |
| `pegawai`    | PostgreSQL | `uwmsdm` | Data pegawai/user untuk login     |

### 5.2. Environment Variables (.env)

```env
# Database Utama (MySQL)
DB_CONNECTION=mysql
DB_HOST=202.46.29.135
DB_PORT=3306
DB_DATABASE=sijad
DB_USERNAME=sijad2

# Database Pegawai (PostgreSQL)
DB_CONNECTION_PGW=pegawai
DB_HOST_PGW=202.46.29.7
DB_PORT_PGW=5432
DB_DATABASE_PGW=uwmsdm
DB_USERNAME_PGW=siter

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

> ⚠️ **Penting**: Jangan gunakan `env()` langsung di kode—selalu gunakan `config('database.connections.pegawai')` dan sejenisnya.

---

## 6. Skema Database (Migrations)

### 6.1. Tabel `ms_unsur_penilaians` — Master Unsur Penilaian

```
┌───────────────────────────────────────────────────┐
│ ms_unsur_penilaians                               │
├───────────────┬───────────────┬───────────────────┤
│ Column        │ Type          │ Keterangan         │
├───────────────┼───────────────┼───────────────────┤
│ id            │ BIGINT (PK)   │ Auto-increment     │
│ parent_id     │ BIGINT (FK)   │ Self-ref, nullable │
│ kode_nomor    │ VARCHAR(10)   │ "I", "A", "1"      │
│ nama_unsur    │ TEXT          │ Nama unsur          │
│ is_header     │ BOOLEAN       │ Default: true      │
│ created_at    │ TIMESTAMP     │                    │
│ updated_at    │ TIMESTAMP     │                    │
└───────────────┴───────────────┴───────────────────┘
```

- Menggunakan **self-referencing** foreign key (`parent_id` → `ms_unsur_penilaians.id`)
- Mendukung **hierarki tak terbatas** (tree structure) untuk unsur penilaian
- `is_header` menandakan apakah item adalah header/kategori atau item yang bisa dinilai

### 6.2. Tabel `tr_nilai_dosens` — Transaksi Nilai Dosen

```
┌───────────────────────────────────────────────────┐
│ tr_nilai_dosens                                   │
├───────────────┬───────────────┬───────────────────┤
│ Column        │ Type          │ Keterangan         │
├───────────────┼───────────────┼───────────────────┤
│ id            │ BIGINT (PK)   │ Auto-increment     │
│ dosen_id      │ BIGINT        │ ID dosen           │
│ unsur_id      │ BIGINT (FK)   │ → ms_unsur_penilai│
│ nilai_kredit  │ DECIMAL(10,2) │ Nullable           │
│ keterangan    │ TEXT          │ Nullable           │
│ created_at    │ TIMESTAMP     │                    │
│ updated_at    │ TIMESTAMP     │                    │
└───────────────┴───────────────┴───────────────────┘
UNIQUE constraint: (dosen_id, unsur_id)
```

### 6.3. Tabel Pendukung (Laravel Default)

| Tabel                                | Kegunaan                                     |
| ------------------------------------ | -------------------------------------------- |
| `users`                              | Tabel user default Laravel (migrasi standar) |
| `password_reset_tokens`              | Token reset password                         |
| `sessions`                           | Session database driver                      |
| `cache`, `cache_locks`               | Cache database driver                        |
| `jobs`, `job_batches`, `failed_jobs` | Queue database driver                        |
| `personal_access_tokens`             | Sanctum API tokens                           |

### 6.4. Konvensi Penamaan Tabel

| Prefix   | Jenis          | Contoh                |
| -------- | -------------- | --------------------- |
| `ms_`    | Master data    | `ms_unsur_penilaians` |
| `tr_`    | Transaksi      | `tr_nilai_dosens`     |
| _(none)_ | Sistem/Laravel | `users`, `sessions`   |

---

## 7. Model Eloquent

### 7.1. Model `User`

Model `User` **tidak** menggunakan koneksi MySQL default, melainkan koneksi ke **PostgreSQL** (`pegawai`):

```php
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'pegawai';      // ← Koneksi PostgreSQL
    protected $table = 'sc_user';           // ← Nama tabel custom
    protected $primaryKey = 'userid';       // ← Primary key custom
    public $timestamps = false;             // ← Tidak ada created_at/updated_at

    protected $fillable = ['name', 'userid', 'password'];
    protected $hidden = ['password'];
}
```

### 7.2. Panduan Membuat Model Baru

Buat model menggunakan artisan:

```bash
php artisan make:model NamaModel -mfs --no-interaction
```

Flag:

- `-m` → buat migration
- `-f` → buat factory
- `-s` → buat seeder

**Contoh model yang perlu dibuat:**

```php
// app/Models/UnsurPenilaian.php
class UnsurPenilaian extends Model
{
    protected $table = 'ms_unsur_penilaians';

    protected $fillable = ['parent_id', 'kode_nomor', 'nama_unsur', 'is_header'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(UnsurPenilaian::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(UnsurPenilaian::class, 'parent_id');
    }

    public function nilaiDosens(): HasMany
    {
        return $this->hasMany(NilaiDosen::class, 'unsur_id');
    }
}
```

```php
// app/Models/NilaiDosen.php
class NilaiDosen extends Model
{
    protected $table = 'tr_nilai_dosens';

    protected $fillable = ['dosen_id', 'unsur_id', 'nilai_kredit', 'keterangan'];

    protected function casts(): array
    {
        return [
            'nilai_kredit' => 'decimal:2',
        ];
    }

    public function unsurPenilaian(): BelongsTo
    {
        return $this->belongsTo(UnsurPenilaian::class, 'unsur_id');
    }
}
```

---

## 8. Routing

### 8.1. Web Routes (`routes/web.php`)

```php
// Status saat ini:
Route::get('/', fn() => view('dashboard'));
Route::get('/login', fn() => view('auth.login'));
```

### 8.2. Konvensi Routing

| Metode | URI                     | Nama Route                | Controller Method |
| ------ | ----------------------- | ------------------------- | ----------------- |
| GET    | `/`                     | `dashboard`               | `index`           |
| GET    | `/login`                | `login`                   | (Fortify)         |
| POST   | `/login`                | _(Fortify auto)_          | (Fortify)         |
| POST   | `/logout`               | `logout`                  | (Fortify)         |
| GET    | `/unsur-penilaian`      | `unsur-penilaian.index`   | `index`           |
| POST   | `/unsur-penilaian`      | `unsur-penilaian.store`   | `store`           |
| PUT    | `/unsur-penilaian/{id}` | `unsur-penilaian.update`  | `update`          |
| DELETE | `/unsur-penilaian/{id}` | `unsur-penilaian.destroy` | `destroy`         |

### 8.3. Panduan Routing

- ✅ Gunakan **named routes**: `Route::get(...)->name('nama.route')`
- ✅ Gunakan **Resource Controller** untuk CRUD: `Route::resource('unsur-penilaian', UnsurPenilaianController::class)`
- ✅ Proteksi route dengan middleware `auth`:
    ```php
    Route::middleware('auth')->group(function () {
        Route::get('/', fn() => view('dashboard'))->name('dashboard');
        Route::resource('unsur-penilaian', UnsurPenilaianController::class);
    });
    ```

---

## 9. Autentikasi (Fortify)

### 9.1. Konfigurasi

Fortify dikonfigurasi di **`config/fortify.php`**:

- **Guard**: `web`
- **Username field**: `email` (perlu diubah ke `userid` untuk SIJAD)
- **Home redirect**: `/home`
- **Fitur aktif**: Registration, Reset Password, Update Profile, Update Password, Two-Factor Auth
- **Login view**: `auth.login`

### 9.2. FortifyServiceProvider

Terdaftar di `app/Providers/FortifyServiceProvider.php`:

```php
Fortify::loginView(fn() => view('auth.login'));

// Rate limiter untuk login
RateLimiter::for('login', function (Request $request) {
    $throttleKey = Str::transliterate(
        Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
    );
    return Limit::perMinute(5)->by($throttleKey);
});
```

### 9.3. Action Classes

| File                               | Fungsi                   |
| ---------------------------------- | ------------------------ |
| `CreateNewUser.php`                | Registrasi user baru     |
| `UpdateUserProfileInformation.php` | Update profil user       |
| `UpdateUserPassword.php`           | Ganti password           |
| `ResetUserPassword.php`            | Reset password via email |
| `PasswordValidationRules.php`      | Rules validasi password  |

### 9.4. ⚠️ Yang Perlu Disesuaikan

Agar login berfungsi dengan model `User` SIJAD (yang menggunakan `userid` sebagai primary key dari tabel `sc_user` di PostgreSQL):

1. **`config/fortify.php`** → ubah `'username' => 'userid'`
2. **`config/auth.php`** → pastikan provider users mengarah ke model `App\Models\User`
3. **Form login (`auth/login.blade.php`)** → field input `name="userid"` (bukan `name="email"`)
4. **Form action** → ubah ke `{{ route('login') }}` dengan `method="POST"` + `@csrf`

---

## 10. Blade Templating & Layout

### 10.1. Hierarki Layout

```
app-layout.blade.php (Master Layout)
├── <head>
│   ├── Meta tags
│   ├── Favicon
│   └── vendorcss.blade.php (Bootstrap, jQuery UI, Icons, MetisMenu, App CSS)
├── <body data-layout="horizontal">
│   ├── topbar.blade.php
│   │   ├── logo.blade.php
│   │   ├── navbar.blade.php (Search, Notifikasi, Profil Dropdown)
│   │   └── Navigation Menu (Dashboard, Master, Transaction)
│   ├── .page-wrapper > .page-content
│   │   ├── Breadcrumb
│   │   ├── @yield('content')           ← KONTEN HALAMAN
│   │   └── footer.blade.php
│   └── vendorjs.blade.php (jQuery, Bootstrap, MetisMenu, Waves, dll.)
│       └── @stack('script')            ← SCRIPT PER HALAMAN
```

### 10.2. Cara Membuat Halaman Baru

**Langkah 1**: Buat file blade di `resources/views/pages/`

```blade
{{-- resources/views/pages/unsur-penilaian/index.blade.php --}}

@extends('partials.layouts.app-layout')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Daftar Unsur Penilaian</h4>
                {{-- Konten tabel di sini --}}
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
    // JavaScript khusus halaman ini
</script>
@endpush
```

**Langkah 2**: Tambahkan route di `routes/web.php`

```php
Route::get('/unsur-penilaian', [UnsurPenilaianController::class, 'index'])
    ->name('unsur-penilaian.index');
```

**Langkah 3**: Buat controller

```bash
php artisan make:controller UnsurPenilaianController --resource --model=UnsurPenilaian --no-interaction
```

### 10.3. Blade Directives yang Digunakan

| Directive             | Kegunaan                                 |
| --------------------- | ---------------------------------------- |
| `@extends('layout')`  | Inherit master layout                    |
| `@section('name')`    | Definisikan konten section               |
| `@yield('name')`      | Tampilkan konten section di layout       |
| `@include('partial')` | Include sub-view (partials)              |
| `@push('script')`     | Push ke stack script                     |
| `@stack('script')`    | Render semua yang di-push ke stack       |
| `{{ $var }}`          | Output di-escape (aman dari XSS)         |
| `{!! $html !!}`       | Output raw HTML (hati-hati)              |
| `@csrf`               | Token CSRF untuk form                    |
| `@method('PUT')`      | Method spoofing untuk form PUT/DELETE    |
| `@auth` / `@guest`    | Conditional berdasarkan status login     |
| `@forelse` / `@empty` | Loop dengan fallback jika koleksi kosong |

### 10.4. Konvensi Penamaan View

```
resources/views/
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php          (akan dibuat)
│   └── forgot-password.blade.php   (akan dibuat)
├── dashboard.blade.php
├── pages/
│   ├── unsur-penilaian/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── nilai-dosen/
│   │   ├── index.blade.php
│   │   └── form.blade.php
│   └── laporan/
│       └── index.blade.php
├── partials/
│   ├── layouts/        → Komponen layout (topbar, navbar, footer, dll.)
│   └── components/     → Komponen reusable (modal, alert, table, dll.)  [AKAN DIBUAT]
└── welcome.blade.php
```

---

## 11. Assets & Frontend Bundling

### 11.1. Vendor Assets (Template Crovex)

Disimpan di `public/assets/` dan di-load langsung via `asset()`:

```
public/assets/
├── css/
│   ├── bootstrap.min.css       # Bootstrap 4
│   ├── app.min.css             # Template Crovex main CSS
│   ├── icons.min.css           # Icon fonts (Dripicons, Font Awesome, dll.)
│   ├── metisMenu.min.css       # MetisMenu plugin
│   └── jquery-ui.min.css       # jQuery UI
├── js/
│   ├── jquery.min.js           # jQuery
│   ├── jquery-ui.min.js        # jQuery UI
│   ├── bootstrap.bundle.min.js # Bootstrap 4 + Popper.js
│   ├── metismenu.min.js        # MetisMenu plugin
│   ├── waves.js                # Waves effect
│   ├── feather.min.js          # Feather icons
│   ├── jquery.slimscroll.min.js # Slim scroll
│   └── app.js                  # Template main JS
├── images/
│   ├── logo.png, logo-sm.png, logo-dark.png
│   ├── favicon.ico
│   ├── users/                  # Avatar placeholder
│   └── flags/                  # Flag icons
└── fonts/
```

### 11.2. Vite + Tailwind CSS 4

Entry points Vite (`vite.config.js`):

```js
export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(), // Tailwind CSS v4 Vite plugin
    ],
});
```

> **Catatan**: Saat ini template menggunakan **Bootstrap 4** (dari Crovex) untuk layout admin.
> Tailwind CSS tersedia sebagai opsi tambahan melalui Vite, namun utamakan konsistensi
> dengan CSS bawaan template Crovex.

### 11.3. Cara Menggunakan Assets

```blade
{{-- Vendor CSS (Crovex Template) --}}
<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />

{{-- Vite-managed CSS (Tailwind) --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Vendor JS --}}
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
```

---

## 12. Konvensi Kode

### 12.1. PHP / Laravel

| Aturan                                    | Contoh                                           |
| ----------------------------------------- | ------------------------------------------------ |
| Gunakan **return type** eksplisit         | `public function index(): View`                  |
| Gunakan **type hints** parameter          | `function show(User $user): View`                |
| Gunakan **constructor promotion**         | `public function __construct(public GitHub $gh)` |
| Selalu gunakan **curly braces**           | `if ($x) { ... }` (bukan `if ($x) return;`)      |
| Gunakan **named routes**                  | `route('dashboard')` bukan `/`                   |
| Gunakan **Form Request** untuk validasi   | `php artisan make:request StoreUnsurRequest`     |
| **Jangan** gunakan `env()` di luar config | Gunakan `config('app.name')`                     |
| **Jangan** gunakan `DB::` langsung        | Gunakan `Model::query()->...`                    |
| Gunakan **eager loading**                 | `User::with('nilaiDosens')->get()`               |
| Enum keys **TitleCase**                   | `case Administrator = 'admin';`                  |

### 12.2. Blade

| Aturan                               | Contoh                                  |
| ------------------------------------ | --------------------------------------- |
| Gunakan `{{ }}` (escaped)            | `{{ $user->name }}`                     |
| Gunakan `@csrf` di setiap form       | `<form method="POST">@csrf`             |
| Gunakan `@method()` untuk PUT/DELETE | `@method('PUT')`                        |
| Gunakan `asset()` untuk static files | `{{ asset('assets/css/app.min.css') }}` |
| Gunakan `route()` untuk URL          | `{{ route('unsur-penilaian.index') }}`  |

### 12.3. Code Style

Jalankan **Laravel Pint** sebelum commit:

```bash
vendor/bin/pint --dirty --format agent
```

### 12.4. Penamaan File

| Tipe         | Konvensi            | Contoh                            |
| ------------ | ------------------- | --------------------------------- |
| Controller   | PascalCase          | `UnsurPenilaianController.php`    |
| Model        | PascalCase Singular | `UnsurPenilaian.php`              |
| Migration    | snake_case          | `create_unsur_penilaians_table`   |
| View         | kebab-case          | `unsur-penilaian/index.blade.php` |
| Route name   | dot.notation        | `unsur-penilaian.index`           |
| Form Request | PascalCase          | `StoreUnsurPenilaianRequest.php`  |

---

## 13. Testing

### 13.1. Framework

Menggunakan **Pest PHP v4** (bukan PHPUnit langsung):

```
tests/
├── Feature/        # Test integrasi (HTTP, database, auth)
├── Unit/           # Test unit untuk logic terpisah
├── Pest.php        # Konfigurasi Pest global
└── TestCase.php    # Base test case
```

### 13.2. Perintah Test

```bash
# Jalankan semua test
php artisan test --compact

# Test dengan filter
php artisan test --compact --filter=UnsurPenilaianTest

# Buat test baru
php artisan make:test UnsurPenilaianTest --pest         # Feature test
php artisan make:test UnsurPenilaianTest --pest --unit   # Unit test
```

### 13.3. Contoh Test

```php
// tests/Feature/UnsurPenilaianTest.php
it('dapat menampilkan halaman unsur penilaian', function () {
    $response = $this->get(route('unsur-penilaian.index'));

    $response->assertStatus(200);
    $response->assertViewIs('pages.unsur-penilaian.index');
});

it('dapat menyimpan unsur penilaian baru', function () {
    $data = [
        'kode_nomor' => 'I',
        'nama_unsur' => 'Pendidikan',
        'is_header' => true,
    ];

    $response = $this->post(route('unsur-penilaian.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('ms_unsur_penilaians', $data);
});
```

---

## 14. Perintah Penting

### 14.1. Development

```bash
# Jalankan server development (PHP + Queue + Vite sekaligus)
composer run dev

# Atau jalankan terpisah:
php artisan serve              # PHP server
npm run dev                    # Vite dev server
php artisan queue:listen       # Queue worker
```

### 14.2. Database

```bash
# Jalankan migrasi
php artisan migrate

# Rollback migrasi terakhir
php artisan migrate:rollback

# Fresh migrate + seed
php artisan migrate:fresh --seed

# Buat migrasi baru
php artisan make:migration create_nama_table --no-interaction
```

### 14.3. Generate File

```bash
# Model + Migration + Factory + Seeder
php artisan make:model NamaModel -mfs --no-interaction

# Controller (Resource)
php artisan make:controller NamaController --resource --model=NamaModel --no-interaction

# Form Request
php artisan make:request StoreNamaRequest --no-interaction

# Test
php artisan make:test NamaTest --pest --no-interaction
```

### 14.4. Build & Deploy

```bash
# Build assets untuk production
npm run build

# Cache config, route, views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear semua cache
php artisan optimize:clear
```

---

## 15. Alur Pengembangan Fitur Baru

Berikut langkah-langkah standar untuk menambahkan fitur baru di SIJAD:

### Contoh: Menambahkan CRUD "Unsur Penilaian"

```
📋 CHECKLIST PENGEMBANGAN FITUR BARU
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

☐ 1. MIGRATION
   └── php artisan make:migration create_nama_table

☐ 2. MODEL
   └── php artisan make:model NamaModel -fs
   └── Definisikan: $table, $fillable, relationships, casts()

☐ 3. CONTROLLER
   └── php artisan make:controller NamaController --resource --model=NamaModel

☐ 4. FORM REQUEST (Validasi)
   └── php artisan make:request StoreNamaRequest
   └── php artisan make:request UpdateNamaRequest

☐ 5. ROUTE
   └── Tambahkan di routes/web.php
   └── Route::resource('nama', NamaController::class);

☐ 6. VIEWS (Blade)
   └── resources/views/pages/nama/index.blade.php
   └── resources/views/pages/nama/create.blade.php
   └── resources/views/pages/nama/edit.blade.php

☐ 7. NAVIGATION
   └── Update resources/views/partials/layouts/topbar.blade.php
   └── Tambahkan link menu baru

☐ 8. TESTS
   └── php artisan make:test NamaTest --pest
   └── Test: index, store, update, delete, validasi

☐ 9. CODE STYLE
   └── vendor/bin/pint --dirty --format agent

☐ 10. REVIEW
    └── php artisan test --compact
    └── Manual test di browser
```

---

## Catatan Tambahan

### ⚠️ Hal-Hal yang Perlu Diperhatikan

1. **Model User menggunakan koneksi PostgreSQL** — bukan MySQL default. Pastikan koneksi `pegawai` selalu tersedia.
2. **Template Crovex** menggunakan Bootstrap 4 dengan jQuery. Jangan campurkan dengan komponen Bootstrap 5 tanpa pertimbangan matang.
3. **Tailwind CSS 4** sudah ter-setup via Vite — gunakan secukupnya dan hindari konflik dengan CSS template Crovex.
4. **Session driver = database** — pastikan tabel `sessions` sudah ter-migrate sebelum login.
5. **Folder `pages/` masih kosong** — semua halaman baru harus dibuat di folder ini.
6. **Form login belum terintegrasi Fortify** — field `name` di form login masih perlu disesuaikan (`userid` bukan `username`), dan action form perlu `@csrf` + `method="POST"`.

### 🔗 Referensi

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Fortify](https://laravel.com/docs/12.x/fortify)
- [Blade Templates](https://laravel.com/docs/12.x/blade)
- [Eloquent ORM](https://laravel.com/docs/12.x/eloquent)
- [Pest PHP](https://pestphp.com/docs)
- [Tailwind CSS v4](https://tailwindcss.com/docs)
