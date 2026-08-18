# Shop API (Laravel 12 + Docker + MySQL + Redis)

A RESTful e-commerce API for catalog, authentication, orders, and notifications.

Built with Laravel and Docker so you can run the full stack without installing PHP, MySQL, or Redis locally.

**Product ↔ Category** is many-to-many via the `category_product` pivot table (a product can appear in multiple categories).

### Tech Stack

- **Backend**: Laravel 12, PHP 8.2
- **Runtime & Services**: Docker & Docker Compose
- **Database**: MySQL 8
- **Cache**: Redis service included and configured
- **Queue**: Laravel database queue (worker runs inside the app container)
- **Auth**: Laravel Sanctum (token-based)
- **Testing**: PHPUnit

### Key Features

- **Dockerized development** — PHP-FPM, Nginx, MySQL, Redis, phpMyAdmin, and a queue worker via Docker Compose
- **Authentication** — register, login, logout with Sanctum tokens
- **RBAC** — Admin and Customer roles with middleware
- **Categories & products** — CRUD, image upload, inventory, many-to-many categories
- **Orders** — place orders with status workflow (`pending → paid → shipped`, plus `cancelled`)
- **Notifications** — order confirmation and low-stock alerts (queued)
- **Redis-ready setup** — Redis runs in Docker and is configured for Laravel cache usage
- **Consistent JSON responses** — `{ "success": true, "message": "...", "data": {} }`
- **API rate limiting** — 60 requests/minute (Laravel API default)

---

## Getting Started

### Prerequisites

- **Docker** and **Docker Compose**
- **Git**

> You do *not* need a local PHP, Composer, MySQL, or Redis installation when using the Docker setup.

---

## Project Setup (Docker)

### 1. Clone the repository

```bash
git clone https://github.com/prachi-dubey/Laravel-ProductManagement.git
cd Laravel-ProductManagement
```

### 2. Environment configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Default Docker values (no changes needed for local development):

```text
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=shop_api
DB_USERNAME=root
DB_PASSWORD=password

REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=database
```

`start.sh` also copies `.env.example` to `.env` automatically if `.env` is missing.

### 3. Build and start Docker containers

```bash
./start.sh
# or: docker compose up -d --build
```

On startup the app container automatically runs:

- `composer install` (also during image build, using this project's `composer.lock`)
- `php artisan key:generate` (if `APP_KEY` is missing)
- `php artisan migrate --seed`
- `php artisan storage:link`
- queue worker (`php artisan queue:work`) for order confirmation and low-stock jobs

This starts 5 services:

| Service    | URL / Port              |
|------------|-------------------------|
| API        | http://localhost:84     |
| phpMyAdmin | http://localhost:8080   |
| MySQL      | localhost:3307          |
| Redis      | localhost:6384          |

Redis is currently available for cache/configuration, but the app queue still uses the database driver and there is no Redis-specific caching logic in the domain code yet.

### 4. Verify the API

```bash
curl http://localhost:84/api/products
```

### Default seeded users

| Role     | Email              | Password |
|----------|--------------------|----------|
| Admin    | admin@shop.test    | password |
| Customer | customer@shop.test | password |

---

## Development Workflow

### Start / stop containers

```bash
docker compose up -d          # start
docker compose down           # stop
docker compose logs -f app    # view app logs
```

### Useful Artisan commands (optional)

These are already handled by Docker on startup. Use them only when you need to re-run manually:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan db:seed
docker compose exec app php artisan storage:link
```

### API endpoint testing

Base URL: `http://localhost:84`

```text
GET  /api/products           # public product listing
GET  /api/categories         # public category listing
POST /api/login              # get auth token
POST /api/orders             # place order (requires customer token)
GET  /api/notifications      # user notifications (requires token)
```

Use the Postman collection in `postman/shop-api-categories-products.postman_collection.json`.

Set variables:

- `base_url` → `http://localhost:84`
- `token` → value from login response

---

## API Endpoints

| Method | Endpoint                         | Auth | Role     |
|--------|----------------------------------|------|----------|
| POST   | `/api/register`                  | No   | —        |
| POST   | `/api/login`                     | No   | —        |
| POST   | `/api/logout`                    | Yes  | Any      |
| GET    | `/api/me`                        | Yes  | Any      |
| GET    | `/api/categories`                | No   | —        |
| GET    | `/api/categories/{id}`           | No   | —        |
| POST   | `/api/categories`                | Yes  | Admin    |
| PUT    | `/api/categories/{id}`           | Yes  | Admin    |
| DELETE | `/api/categories/{id}`           | Yes  | Admin    |
| GET    | `/api/products`                  | No   | —        |
| GET    | `/api/products/{id}`             | No   | —        |
| POST   | `/api/products`                  | Yes  | Admin    |
| PUT    | `/api/products/{id}`             | Yes  | Admin    |
| DELETE | `/api/products/{id}`             | Yes  | Admin    |
| PUT    | `/api/products/{id}/categories`  | Yes  | Admin    |
| GET    | `/api/orders`                    | Yes  | Any      |
| GET    | `/api/orders/{id}`               | Yes  | Any      |
| POST   | `/api/orders`                    | Yes  | Customer |
| GET    | `/api/notifications`             | Yes  | Any      |
| POST   | `/api/notifications/read-all`    | Yes  | Any      |
| POST   | `/api/notifications/{id}/read`   | Yes  | Any      |

---

## Testing

```bash
docker compose exec app php artisan test
```

---

## Architecture

```
Controller → Service → Interface → Repository → Model
```

- Messages: `lang/en/messages.php`
- Success envelope: base `Controller` methods `success()` / `paginated()`
- Errors: `App\Helper\ApiErrorResponse` + single `App\Exceptions\ApiException`
- List helpers: `App\Helper\ApiListHelper` (sort, per_page, paginated payload)

### Project Structure

```
app/
├── Events/Order/
├── Http/Controllers/Api/
├── Http/Requests/Api/{Auth,Category,Product,Order}/
├── Http/Resources/Api/{Auth,Category,Product,Order}/
├── Interfaces/{Auth,Category,Product,Order}/
├── Jobs/
├── Listeners/
├── Models/
├── Repositories/
└── Services/
database/migrations/
database/seeders/
dev/                    # Docker (PHP-FPM, Nginx, entrypoint)
postman/                # Postman collection
tests/
```

---

## Order Status Workflow

```
pending → paid → shipped
   ↓        ↓
cancelled cancelled
```

---

## New Machine Setup (macOS)

Run these steps **once** on a fresh machine, then follow [Project Setup](#project-setup-docker) above.

```bash
# 1. Install Docker (pick one)
brew install colima docker && colima start          # Option A: Colima
# or install Docker Desktop and start it            # Option B: Docker Desktop

# 2. Install Docker Compose plugin (if needed)
brew install docker-compose
mkdir -p ~/.docker/cli-plugins
ln -sf "$(brew --prefix)/opt/docker-compose/bin/docker-compose" ~/.docker/cli-plugins/docker-compose

# 3. Optional — host Composer (dependencies also install inside Docker)
brew install composer
composer install
```

Verify Docker is running:

```bash
docker compose version
docker info
```

---

## Optional: local PHP (XAMPP) without Docker

If you already have XAMPP PHP 8.2 and MySQL locally:

```bash
cd shop-api
cp .env.example .env
# Set DB_HOST=127.0.0.1, DB_PORT=3306, DB_PASSWORD= (XAMPP default)
./bin/artisan key:generate
./bin/artisan migrate --seed
./bin/artisan storage:link
./bin/serve                 # http://127.0.0.1:8000
./bin/artisan queue:work    # notifications after orders
```

Open **XAMPP Manager** → start **MySQL**.

Postman `base_url` for this mode: `http://127.0.0.1:8000`

---

## License

MIT
