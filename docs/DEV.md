# Developer Notes (local-only helpers)

This document describes small dev-only helpers added to the repository for local testing.

Files added/changed

- `routes/web.php` — fixed a Carbon method in the OTP response (`toISOStringString()` → `toDateTimeString()`).
- `scripts/delete_user.php` — utility script to delete a user and related OTP rows from the local SQLite DB.

Usage

- Run the delete script to remove a test user and any OTPs:

```powershell
php scripts\delete_user.php notinugummy@gmail.com
```

- The project contains a local-only route to exercise mail sending:

```
GET /dev-send-test-email?email=you@example.com

This route is guarded to `APP_ENV=local` and localhost requests only.
```

Security notes

- Do NOT commit `.env` or any real credentials. If you accidentally commit secrets, rotate them immediately and purge the history.
- Remove or secure dev-only routes before sharing the repository publicly.

Clear caches after changing `.env`:

```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```
