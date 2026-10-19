# CampusCare Testing and Deployment

CampusCare is a Laravel 12 university maintenance system with a Blade/Vite frontend, session authentication, role-based access control, request evidence uploads, notifications, audit logs, CSV export, and session-backed API endpoints.

## Local Setup

1. Start Apache and MySQL in XAMPP.
2. Create a MySQL database named `maintenance_db`.
3. Configure `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maintenance_db
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
SESSION_DOMAIN=null
```

4. Install dependencies and prepare the database:

```powershell
composer install
npm install
# Illuminate\Database\QueryException - Internal Server Error

SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: maintenance_db, SQL: select * from `sessions` where `id` = q9jf6hW8I7ebcFi0h8OuqUm8YESnQkHqrnSkegzG limit 1)

PHP 8.2.12
Laravel 12.69.2
localhost

## Stack Trace

0 - vendor\laravel\framework\src\Illuminate\Database\Connection.php:838
1 - vendor\laravel\framework\src\Illuminate\Database\Connection.php:999
2 - vendor\laravel\framework\src\Illuminate\Database\Connection.php:978
3 - vendor\laravel\framework\src\Illuminate\Database\Connection.php:796
4 - vendor\laravel\framework\src\Illuminate\Database\Connection.php:411
5 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:3505
6 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:3490
7 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:4080
8 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:3489
9 - vendor\laravel\framework\src\Illuminate\Database\Concerns\BuildsQueries.php:366
10 - vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:3412
11 - vendor\laravel\framework\src\Illuminate\Session\DatabaseSessionHandler.php:96
12 - vendor\laravel\framework\src\Illuminate\Session\Store.php:128
13 - vendor\laravel\framework\src\Illuminate\Session\Store.php:116
14 - vendor\laravel\framework\src\Illuminate\Session\Store.php:100
15 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:146
16 - vendor\laravel\framework\src\Illuminate\Support\helpers.php:393
17 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:143
18 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:115
19 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
20 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
21 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
22 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
23 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
24 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
25 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
26 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
27 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
28 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
29 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
30 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
31 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
32 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
33 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
34 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
35 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
36 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
37 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
38 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
39 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
40 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
41 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
42 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
43 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
44 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
45 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
46 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
47 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
48 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
49 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
50 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
51 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
52 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
53 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
54 - public\index.php:20

## Request

GET /

## Headers

* **host**: localhost
* **connection**: keep-alive
* **cache-control**: max-age=0
* **sec-ch-ua**: "Brave";v="155", "Chromium";v="155", "Not(A:Brand";v="24"
* **sec-ch-ua-mobile**: ?0
* **sec-ch-ua-platform**: "Windows"
* **upgrade-insecure-requests**: 1
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/jxl,image/avif,image/webp,image/apng,*/*;q=0.8
* **sec-gpc**: 1
* **sec-fetch-site**: same-origin
* **sec-fetch-mode**: navigate
* **sec-fetch-user**: ?1
* **sec-fetch-dest**: document
* **referer**: http://localhost/university-maintenance-system/
* **accept-encoding**: gzip, deflate, br, zstd
* **accept-language**: en-US,en;q=0.9

## Route Context

controller: \Illuminate\Routing\ViewController
middleware: web

## Route Parameters

{
    "view": "welcome",
    "data": [],
    "status": 200,
    "headers": []
}

## Database Queries

No database queries detected.

```

5. Run the application in separate terminals:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

Open `http://127.0.0.1:8000`.

Demo accounts created by `RolePermissionSeeder`:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@univ.edu` | `admin123` |
| Officer | `officer@univ.edu` | `officer123` |
| Student/staff | `student@univ.edu` | `student123` |

## Testing

Run the complete suite:

```powershell
php artisan config:clear
php artisan test
```

Run the frontend page tests:

```powershell
php artisan test tests/Feature/FrontendPagesTest.php
```

Run the API tests:

```powershell
php artisan test tests/Feature/ApiEndpointsTest.php
```

Build the production frontend bundle:

```powershell
npm run build
```

The frontend tests cover the landing page, authentication pages, and authenticated dashboard. The API tests cover session login, protected access, request creation, search, pagination, and guest rejection.

## API Endpoints

The API uses the same Laravel session authentication as the web interface. API clients must preserve cookies between requests.

| Method | Endpoint | Access |
|---|---|---|
| `POST` | `/api/auth/register` | Public |
| `POST` | `/api/auth/login` | Public |
| `GET` | `/api/auth/me` | Authenticated |
| `POST` | `/api/auth/logout` | Authenticated |
| `GET` | `/api/notifications` | Authenticated |
| `PATCH` | `/api/notifications/{id}/read` | Notification owner |
| `GET` | `/api/requests` | Authenticated |
| `POST` | `/api/requests` | Student/staff or admin |
| `GET` | `/api/requests/{id}` | Authenticated |
| `PUT/PATCH` | `/api/requests/{id}` | Request owner or admin |
| `DELETE` | `/api/requests/{id}` | Request owner or admin |
| `POST` | `/api/requests/{id}/assign` | Admin |
| `PATCH` | `/api/requests/{id}/status` | Officer or admin |
| `GET` | `/api/audit-logs` | Admin |

Example request listing:

```text
GET /api/requests?search=lighting&status=PENDING&priority=HIGH&limit=10
```

## Production Deployment

Set production environment values on the host. Do not commit production secrets:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=base64:your-generated-key
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=maintenance_db
DB_USERNAME=your-database-user
DB_PASSWORD=your-database-password
SESSION_DRIVER=database
SESSION_DOMAIN=null
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Run these commands during deployment:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder
php artisan storage:link
php artisan optimize
```

Configure the web server document root to the project's `public` directory. The server must route all unknown paths to `public/index.php` and must allow write access to `storage` and `bootstrap/cache`.

For queued notifications or other queued work, run a persistent worker:

```bash
php artisan queue:work --sleep=3 --tries=3
```

After deployment, verify:

1. `GET /` returns the CampusCare landing page.
2. Login works and redirects to `/dashboard`.
3. `/dashboard` can read the `service_requests` table.
4. A request can be created with an evidence file.
5. An admin can download `/admin/requests/export`.
6. `GET /api/requests` returns JSON for an authenticated session.
7. `php artisan migrate:status` shows all migrations as `Ran`.

A hosting provider and database credentials are required to perform the final online deployment; this repository contains the repeatable deployment procedure but no external hosting access.



Then log in with student@univ.edu / student123 (or use the admin account admin@univ.edu / admin123).

Email: officer@univ.edu
Password: officer123
