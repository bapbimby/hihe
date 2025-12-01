# Local Setup Checklist

This project includes user registration, login, email verification and an OTP flow.

Quick steps to run locally (Windows PowerShell):

1. Install dependencies
```powershell
composer install
```

2. Create `.env` from example and ensure SQLite is present
```powershell
copy .env.example .env
if (!(Test-Path 'database\database.sqlite')) { New-Item -ItemType File -Path 'database\database.sqlite' | Out-Null }
```

3. Generate app key and clear config
```powershell
php artisan key:generate
php artisan config:clear
```

4. Run migrations
```powershell
php artisan migrate
```

5. Mail configuration for safe local testing
- By default use `MAIL_MAILER=log` in `.env` so emails are written to `storage/logs/laravel.log`.
- To test real SMTP delivery, set `MAIL_MAILER=smtp` and provide credentials (use app-specific passwords for Gmail).

6. Start dev server
```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

7. Register and verify
- Register at `http://127.0.0.1:8000/register`.
- If using `MAIL_MAILER=log`, open the latest `storage/logs/laravel.log` and copy the verification URL into your browser.
- Alternatively the `Verify Email` page supports OTP: click `Send OTP` and check the log for the OTP or configure SMTP to receive it.

Security notes:
- Do not commit `.env` with credentials.
- The `GET /dev-send-test-email` route is removed for safety; restore only for local testing and ensure environment checks.

