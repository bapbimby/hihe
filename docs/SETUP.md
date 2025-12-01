# Setup & Run (Windows PowerShell)

Follow these exact steps from the project root `C:\Users\COMPUTER\Documents\loginP` to run the application locally on Windows using PowerShell.

Prerequisites
- PHP 8.1+ installed and on PATH
- Composer installed and on PATH
- Node.js + npm (optional; only needed for `tools/screenshotter2`)
- Git (for cloning and branching)

1) Clone the repository (if you haven't already)

```powershell
git clone https://github.com/bapbimby/hihe.git
cd hihe
```

2) Create a working branch (optional but recommended)

```powershell
git checkout -b fix/otp-delete-user
```

3) Create `.env` from the example

If you already have a `.env` and want to keep it, skip this step. Otherwise:

```powershell
copy .env.example .env
```

4) Install PHP dependencies

```powershell
composer install --no-interaction --prefer-dist
```

5) Generate an application key

```powershell
php artisan key:generate
```

6) Prepare the database (SQLite)

The project uses SQLite by default in `.env.example`. Create the file and run migrations:

```powershell
if (-Not (Test-Path .\database\database.sqlite)) { New-Item -ItemType File .\database\database.sqlite | Out-Null }
php artisan migrate
```

7) Create storage link (optional, for public storage)

```powershell
php artisan storage:link
```

8) Clear caches so `.env` changes apply

```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

9) Run the development server

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000` in your browser.

10) Quick health/test endpoints

In a new PowerShell window:

```powershell
Invoke-RestMethod -Uri http://127.0.0.1:8000/test

Invoke-RestMethod -Uri "http://127.0.0.1:8000/dev-send-test-email?email=you@example.com"
```

11) Optional: run screenshot tool (requires Node + npm)

```powershell
cd tools\screenshotter2
npm install
npm run capture
```

12) Revert `.env` mailer after testing (recommended)

If you used real SMTP credentials for a short test, revert the mailer to `log` and clear caches:

```powershell
(Get-Content .env) -replace 'MAIL_MAILER=smtp','MAIL_MAILER=log' | Set-Content .env
# remove or blank MAIL_USERNAME and MAIL_PASSWORD manually if present
php artisan config:clear
php artisan cache:clear
```

Security reminders
- `.env` contains secrets — never commit it to Git. `.gitignore` in this repo already ignores `.env`.
- Dev-only routes (like `/dev-send-test-email` and `scripts/delete_user.php`) should be removed or restricted before production.

If anything in these steps fails, copy the failing command and the terminal output and open an issue or contact the maintainer.
