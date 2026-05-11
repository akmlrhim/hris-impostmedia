# HRIS — Sistem Informasi Sumber Daya Manusia

Aplikasi manajemen SDM (Human Resource Information System) berbasis web yang dibangun dengan Laravel 13 dan Livewire 4. Tersedia dalam dua tampilan: **Panel Admin** untuk pengelolaan data perusahaan, dan **Aplikasi Mobile (PWA)** untuk karyawan melakukan absensi, melihat slip gaji, dan mengelola profil.

---

## Fitur Utama

### Panel Admin

| Modul              | Deskripsi                                                                          |
| ------------------ | ---------------------------------------------------------------------------------- |
| **Dashboard**      | Ringkasan statistik karyawan, kehadiran hari ini, dan hari libur terdekat          |
| **Karyawan**       | CRUD data karyawan lengkap — biodata, jabatan, kontrak, bank, BPJS                 |
| **Data Absensi**   | Rekap absensi seluruh karyawan dengan filter tanggal dan status                    |
| **Shift & Jadwal** | Kelola shift kerja (jam masuk/pulang, toleransi terlambat) dan jadwal per karyawan |
| **Payroll**        | Generate periode payroll, kelola komponen gaji, cetak slip gaji                    |
| **Hari Libur**     | Daftar hari libur nasional dan internal perusahaan                                 |
| **Pengumuman**     | Buat dan terbitkan pengumuman untuk seluruh karyawan                               |
| **Lokasi Kantor**  | Kelola titik koordinat kantor dan radius geofencing untuk absensi WFO              |
| **Pengguna**       | Manajemen akun — buat, ubah peran, reset password _(Admin only)_                   |
| **Hak Akses**      | Konfigurasi izin fitur per peran _(Admin only)_                                    |

### Aplikasi Mobile (PWA)

| Fitur                | Deskripsi                                                                              |
| -------------------- | -------------------------------------------------------------------------------------- |
| **Absensi**          | Check-in/check-out real-time dengan selfie (face recognition) + GPS                    |
| **Face Recognition** | Verifikasi wajah berbasis AI (face-api.js) langsung di browser, tanpa server eksternal |
| **Geofencing**       | Validasi lokasi GPS untuk jadwal WFO — cegah absensi di luar radius kantor             |
| **Direktori**        | Daftar kontak karyawan dengan informasi jabatan                                        |
| **Slip Gaji**        | Riwayat dan detail slip gaji per periode                                               |
| **Profil**           | Edit profil, ganti password, daftarkan data wajah biometrik                            |

---

## Hak Akses

Sistem menggunakan **3 peran**. Konfigurasi izin HR dapat diubah kapan saja melalui **Pengaturan → Hak Akses** di panel admin.

### Peran

| Peran        | Panel Admin                         | Aplikasi Mobile |
| ------------ | ----------------------------------- | --------------- |
| **Admin**    | Semua fitur (penuh, tidak terbatas) | Ya              |
| **HR**       | Sesuai izin yang dikonfigurasi      | Ya              |
| **Karyawan** | Tidak ada akses                     | Ya (saja)       |

### Matriks Izin Default

| Fitur                       | Admin | HR  |
| --------------------------- | :---: | :-: |
| Kelola Karyawan             |  ✅   | ✅  |
| Kelola Payroll              |  ✅   | ✅  |
| Kelola Absensi              |  ✅   | ✅  |
| Kelola Shift & Jadwal       |  ✅   | ✅  |
| Kelola Hari Libur           |  ✅   | ✅  |
| Kelola Pengumuman           |  ✅   | ✅  |
| Kelola Lokasi Kantor        |  ✅   | ❌  |
| Kelola Pengguna & Hak Akses |  ✅   | ❌  |

> **Catatan:** Izin HR bersifat fleksibel dan dapat diubah oleh Admin melalui halaman Hak Akses.
> Akses Admin selalu penuh — tidak dapat dikurangi atau dikonfigurasi.

### Aturan Keamanan

- Admin tidak dapat menghapus atau menurunkan peran **Admin terakhir** di sistem
- Admin tidak dapat menonaktifkan atau menghapus **akun milik sendiri**
- Izin `Kelola Pengguna & Hak Akses` terkunci khusus untuk Admin — tidak dapat diberikan ke HR

---

## Teknologi

| Kategori         | Teknologi                                      |
| ---------------- | ---------------------------------------------- |
| Backend          | PHP 8.3, Laravel 13.6                          |
| Frontend Reaktif | Livewire 4.2, Alpine.js                        |
| Styling          | Tailwind CSS 4                                 |
| Build Tool       | Vite 8                                         |
| Database         | MySQL                                          |
| Face Recognition | face-api.js 0.22 (on-device, via CDN jsDelivr) |
| Rich Text Editor | Quill 2                                        |
| PWA              | Web App Manifest + Offline Support             |

---

## Instalasi

### Prasyarat

- PHP >= 8.3
- Composer
- Node.js >= 18
- MySQL

### Langkah Instalasi

**1. Clone repository**

```bash
git clone <repo-url> hris_im
cd hris_im
```

**2. Instal dependensi PHP dan Node**

```bash
composer install
npm install
```

**3. Konfigurasi environment**

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_im
DB_USERNAME=root
DB_PASSWORD=
```

**4. Migrasi dan seed database**

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

**5. Build aset frontend**

```bash
npm run build
```

**6. Storage link** _(untuk foto profil dan absensi)_

```bash
php artisan storage:link
```

**7. Jalankan aplikasi**

```bash
composer run dev
```

Akses di `http://localhost:8000`

> **Shortcut:** Langkah 2–5 bisa dijalankan sekaligus dengan:
>
> ```bash
> composer run setup
> ```

---

## Penggunaan

### Login

| Peran      | URL Setelah Login      |
| ---------- | ---------------------- |
| Admin / HR | `/admin` (Panel Admin) |
| Karyawan   | `/m` (Aplikasi Mobile) |

### Membuat Akun Admin Pertama

Setelah migrasi, buat akun Admin via Artisan Tinker:

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name'     => 'Administrator',
    'email'    => 'admin@perusahaan.com',
    'password' => bcrypt('password123'),
    'role'     => 'admin',
]);
```

### Alur Onboarding Karyawan Baru

1. **Admin** buat akun pengguna di `/admin/users` → peran: Karyawan
2. **Admin** buat data karyawan di `/admin/employees` → hubungkan ke akun pengguna
3. **Admin** atur shift dan jadwal kerja karyawan di `/admin/shift`
4. **Karyawan** login → buka **Profil → Verifikasi Biometrik** → daftarkan wajah
5. Karyawan siap absensi melalui `/m/attendance`

### Jenis Jadwal Kerja untuk Absensi

| Work Type                      | Validasi GPS              | Keterangan                    |
| ------------------------------ | ------------------------- | ----------------------------- |
| **WFO** _(Work from Office)_   | Wajib dalam radius kantor | Divalidasi server-side        |
| **WFH** _(Work from Home)_     | Tidak diperlukan          | Koordinat tetap direkam       |
| **WFA** _(Work from Anywhere)_ | Tidak diperlukan          | Default jika tidak ada jadwal |

### Alur Absensi Mobile

1. Kamera otomatis terbuka saat halaman absensi dibuka
2. Model AI face recognition dimuat dari CDN (±5–10 detik pada akses pertama)
3. Wajah dideteksi dan dicocokkan dengan data wajah terdaftar
4. GPS diambil otomatis dan divalidasi _(khusus WFO)_
5. Tombol **Check-in** aktif setelah semua kondisi terpenuhi
6. Foto selfie tersimpan bersama data absensi sebagai bukti

> Admin dan HR yang memiliki data karyawan terhubung dapat melakukan absensi dari panel admin maupun mobile.

---

## Struktur Direktori Penting

```
app/
├── Enums/
│   ├── UserRole.php           # Admin, HR, Karyawan
│   ├── Permission.php         # Daftar izin fitur sistem
│   ├── AttendanceStatus.php   # Status kehadiran (Hadir, Terlambat, dll.)
│   └── WorkType.php           # WFO, WFH, WFA
├── Livewire/
│   ├── Admin/                 # Komponen panel admin
│   └── Employee/              # Komponen aplikasi mobile
├── Models/
│   ├── RolePermission.php     # Konfigurasi izin per peran (database)
│   ├── OfficeLocation.php     # Lokasi kantor + kalkulasi Haversine
│   └── ...
└── Providers/
    └── AppServiceProvider.php # Definisi Laravel Gates

database/
└── seeders/
    └── RolePermissionSeeder.php  # Izin default untuk HR
```

---

## Perintah Berguna

```bash
# Jalankan dev server (Laravel + Queue + Vite sekaligus)
composer run dev

# Jalankan test suite
composer run test

# Format kode PHP (Laravel Pint)
vendor/bin/pint

# Reset dan seed ulang izin ke default
php artisan db:seed --class=RolePermissionSeeder

# Lihat semua route admin
php artisan route:list --name=admin.

# Bersihkan semua cache
php artisan optimize:clear
```

---

## Lisensi

Dikembangkan untuk keperluan internal **Impost Media**.
