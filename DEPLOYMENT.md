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
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan storage:link
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

## Deploy to Railway

The repository includes a `Dockerfile`, `docker/start.sh`, and `railway.json`. The container builds the Vite assets, serves the app with Apache, and on each start runs migrations and caches config, routes, and views.

1. Create a Railway project and choose **Deploy from GitHub repo** with this repository.
2. Add a **MySQL** database to the same project.
3. On the web service, set these variables (Railway reference variables fill in the database values):

```dotenv
APP_NAME=CampusCare
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:generate-with-php-artisan-key:generate---show
APP_URL=https://your-service.up.railway.app
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
LOG_CHANNEL=stderr
SESSION_SECURE_COOKIE=true
```

4. Add a **Volume** to the web service mounted at `/var/www/html/storage/app/public`. Without it, uploaded evidence files are lost on every redeploy.
5. Generate a public domain under **Settings → Networking**, and set `APP_URL` to it.
6. For the first deploy only, set `RUN_SEED=true` to create the roles and demo accounts, then remove it after the first successful start. Change the demo account passwords immediately, because they are public in this repository.

`QUEUE_CONNECTION=sync` avoids needing a separate queue worker process. Railway uses `GET /up` as the health check.
