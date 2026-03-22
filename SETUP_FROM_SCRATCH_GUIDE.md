# Setup Guide (From Scratch)

Use this guide to run the project on a new device.

## Requirements

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL
- Git

## 1) Clone the repo

```
git clone <your-repo-url>
cd cmc-egs
```

## 2) Install backend dependencies

```
composer install
```

## 3) Create and configure .env

```
copy .env.example .env
```

Update these values in `.env`:

- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Make sure the database exists in MySQL (example: `cmc_egs`).

## 4) Generate app key

```
php artisan key:generate
```

## 5) Migrate and seed data

```
php artisan migrate --seed
```

This loads all seeders, including the test users in `database/seeders/UserSeeder.php`.

## 6) Install frontend dependencies

```
npm install
```

## 7) Build or run the frontend

For production build:

```
npm run build
```

For development (hot reload):

```
npm run dev
```

## 8) Run the app

```
php artisan serve
```

Open: `http://localhost:8000`

## Optional: One command dev mode

```
composer run dev
```

This runs PHP server, queue, logs, and Vite together.

## Common issues

- If the page is blank: confirm `npm run dev` is running.
- If login fails: run `php artisan migrate --seed` again.
- If DB errors: confirm `.env` DB settings and that MySQL is running.
