# AGENTS.md

HRIS (Sistem Informasi Sumber Daya Manusia) for Impost Media — Laravel 13 + Livewire 4 + Tailwind 4, MySQL. Two apps in one repo: Admin panel (`/admin`) and mobile PWA (`/m`). All UI copy, validation, and log messages are Indonesian (`APP_LOCALE=id`, `lang/id/`) — keep new strings in Indonesian.

## Commands
- `composer run dev` — runs `php artisan serve` + `queue:listen --tries=1` + Vite dev concurrently. The queue worker matters: announcement notifications are queued.
- `composer run test` — `config:clear` then full Pest suite. Focus one: `php artisan test --compact --filter=AdminAttendance`.
- `vendor/bin/pint --dirty --format agent` — required after any PHP edit (see CLAUDE.md).
- `npm run build` — required after Blade/Tailwind edits before changes render in the UI.
- `php artisan db:seed --class=RolePermissionSeeder` — reseeds HR permission defaults (wipes `role_permissions`). Plain `db:seed` runs UserSeeder + AttendanceSeeder, **not** this one.
- `php artisan storage:link` — required for avatar/attendance photo URLs.

## Testing quirks
- Pest + in-memory sqlite. `uses(RefreshDatabase::class)` is per test file, NOT global (commented out in `tests/Pest.php`).
- Tests fix time with `$this->travelTo(Carbon::parse('2026-07-08 10:00:00', 'Asia/Makassar'))` and assert against concrete dates (Sundays, holidays, contract windows). Don't use `now()` where dates matter.
- Convention: `Livewire::actingAs($user)->test(Component::class)`. Gates read the DB, so grant access via `RolePermission::firstOrCreate(['role' => 'hr', 'permission' => '...'])`.

## Access control (easy to break)
- `User::roles` is a JSON **array** column (`['admin']`, `['hr', 'employee']`), not a single value. Enums live in `app/Enums/` (`UserRole`, `Permission`, `WorkType`, `AttendanceStatus`, ...).
- Gates are defined in `AppServiceProvider::boot()`. Admin bypasses every gate EXCEPT `view_salary` (deliberately HR-only — Admin sees masked salaries). Use `rupiah_masked()` from `app/helpers.php` in admin views; do NOT "fix" Admin's salary censorship.
- Routes use `can:` middleware (`manage_attendance`, `manage_payroll`, ...). Adding a permission = update `Permission` enum + `RolePermissionSeeder` + the `configurable()` list in AppServiceProvider.
- `EnsureActiveEmployee` middleware logs the user out with a flash warning on inactive accounts — don't change to a 403.

## Architecture
- Nearly everything is a Livewire component (class `app/Livewire/{Admin,Employee,Auth}/Xxx.php` ↔ view `resources/views/livewire/...`); routes are one-liners in `routes/web.php`. The only plain controllers are PDF generators (`Admin/Payroll`, `Employee/Payslip`).
- Absensi rules depend on `WorkScheduleService`, which caches working-day data — call `forgetCache()` after changing `working_days`/holidays in tests or seeders. Working days, holidays, and `Employee::contract_start_date` all feed the attendance recap.
- Attendance photos, avatars, and overtime approval evidence live on the `local` disk and are streamed through auth-gated `/files/...` routes (owner or `manage_overtime` only) — don't expose them via public symlink.
- Face recognition runs fully client-side from the vendored `public/face-api.min.js` (0.22.2); no external server.
- Models use Laravel 13 attribute syntax: `#[Fillable]`, `#[Hidden]`, `#[ObservedBy]` (see `app/Models/User.php`). Observers exist for User/Employee/Announcement.

## Deployment / environment gotchas
- Pushing to `main` auto-deploys to production (hris.impostmedia.com) via `.github/workflows/deploy.yml`, which runs `migrate --force` on prod. Be deliberate about what lands on `main`.
- `.npmrc` sets `ignore-scripts=true` — `npm install` will NOT run postinstall build scripts.
- Keep `server.host = "localhost"` in `vite.config.js` — without it Vite binds to `::1` and `public/hot` points at an unreachable IPv6 URL (see the comment in that file).
