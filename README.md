# Shop API — E-Commerce REST API

A RESTful e-commerce API built from scratch for managing products, categories, orders, and notifications with role-based access control.

## About the Project

Shop API is a backend-only e-commerce application that exposes a JSON API for a small online shop.

**Authentication & roles**

- Token-based auth with Laravel Sanctum
- Two roles: **Admin** and **Customer**
- Supported auth flows: **register**, **login**, **me**, and **logout**
- Users can **update their profile** (shipping address, phone, bio)

**Catalog**

- **Admin** can create, update, and delete **categories**; customers can list and view categories
- **Admin** can create, update, and delete **products**, and **sync** product–category links; customers can list and view products
- Products and categories have a many-to-many relationship (one product can belong to multiple categories)

**Orders**

- Both **admin** and **customer** can place orders, list orders, and view a single order
- On **list** and **show**: an admin sees **all customers’ orders**; a customer sees **only their own** orders

**Notifications**

- When an order is placed, the **customer** receives an **order confirmation** email
- If stock for an ordered product falls **low**, **admins** receive a **low-stock** alert email

The entire stack runs in Docker, so there is nothing to install locally beyond Docker itself.

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | 8.2 |
| Framework | Laravel | 12 |
| Authentication | Laravel Sanctum | 4.3 |
| Database | MySQL | 8.0 |
| Queue | Laravel Database Queue | — |
| Containerization | Docker & Docker Compose | — |
| Web Server | Nginx | — |

---

## Features & Functionality

### 1. Authentication & roles

Authentication is implemented with Sanctum personal access tokens. There are **two roles**: `admin` and `customer`.

| Action | Who | What it does |
|--------|-----|--------------|
| **Register** | Public | Creates a new account as a **customer** (role cannot be chosen via the API), creates an empty profile, returns a bearer token |
| **Login** | Public | Validates email/password and returns a bearer token |
| **Me** | Authenticated | Returns the current user (and related profile data) |
| **Logout** | Authenticated | Revokes the current access token |
| **Update profile** | Authenticated | Updates phone, bio, and shipping address (`line1`, `city`, `postal_code`, `country`, etc.) via `PUT /api/profile` |

Admin-only routes are protected by `EnsureUserIsAdmin` middleware. Resource access (especially orders) is enforced with policies.

A shipping address on the profile is required before an order can be placed (the address is copied onto the order as a shipping snapshot at checkout).

### 2. Category management

| Action | Admin | Customer |
|--------|-------|----------|
| **List** categories | Yes | Yes |
| **Show** a category | Yes | Yes |
| **Create** category | Yes | No |
| **Update** category | Yes | No |
| **Delete** category | Yes | No |

- List/show support pagination, sorting, and search
- Delete is blocked if products are still linked to the category

### 3. Product management

| Action | Admin | Customer |
|--------|-------|----------|
| **List** products | Yes | Yes |
| **Show** a product | Yes | Yes |
| **Create** product | Yes | No |
| **Update** product | Yes | No |
| **Delete** product | Yes | No |
| **Sync** product categories | Yes | No |

- Products store name, description, price, stock, active flag, and optional image
- **Sync** (`PUT /api/products/{id}/categories`) replaces the product’s category links
- Many-to-many relationship via the `category_product` pivot table
- Delete is blocked if the product appears on existing orders

### 4. Order processing

Both **admin** and **customer** can place an order, list orders, and show an order. Visibility differs by role:

| Action | Admin | Customer |
|--------|-------|----------|
| **Place** order | Yes | Yes |
| **List** orders | **All** customers’ orders | **Own** orders only |
| **Show** order | Any order | **Own** order only |

When an order is placed:

1. Profile must have a complete shipping address
2. Line items (product + quantity) are validated
3. Stock is checked for every item; insufficient stock fails the request
4. Inside a database transaction: order is created, items are saved, stock is decremented, shipping fields are copied from the profile snapshot
5. A unique order number is generated (e.g. `ORD-YYYYMMDD-XXXXXX`)
6. An `OrderPlaced` event starts the notification pipeline

**Order status workflow:**

```
pending → paid → shipped
   ↓        ↓
cancelled cancelled
```

### 5. Email notifications (queued)

After a successful order placement, two queued jobs run:

1. **Order confirmation** — email to the **customer** who placed the order (order details)
2. **Low-stock check** — if any ordered product’s stock drops **below 10**, email to **all admin** users

Jobs use the database queue (`QUEUE_CONNECTION=database`) and are processed by `queue:work` in Docker.

### 6. Standardized API responses

Every endpoint returns a consistent JSON envelope:

```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": { }
}
```

List endpoints include a `pagination` object (`current_page`, `per_page`, `total`).

---

## API Endpoints

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| POST | `/api/register` | No | — | Create account |
| POST | `/api/login` | No | — | Get auth token |
| POST | `/api/logout` | Yes | Any | Revoke token |
| GET | `/api/me` | Yes | Any | Current user info |
| PUT | `/api/profile` | Yes | Any | Update profile (address, phone, bio) |
| GET | `/api/categories` | No | — | List categories |
| GET | `/api/categories/{id}` | No | — | View category |
| POST | `/api/categories` | Yes | Admin | Create category |
| PUT | `/api/categories/{id}` | Yes | Admin | Update category |
| DELETE | `/api/categories/{id}` | Yes | Admin | Delete category |
| GET | `/api/products` | No | — | List products |
| GET | `/api/products/{id}` | No | — | View product |
| POST | `/api/products` | Yes | Admin | Create product |
| PUT | `/api/products/{id}` | Yes | Admin | Update product |
| DELETE | `/api/products/{id}` | Yes | Admin | Delete product |
| PUT | `/api/products/{id}/categories` | Yes | Admin | Sync categories |
| GET | `/api/orders` | Yes | Any | List orders (admin: all; customer: own) |
| GET | `/api/orders/{id}` | Yes | Any | View order (admin: any; customer: own) |
| POST | `/api/orders` | Yes | Any | Place order (admin or customer) |

---

## Architecture

```
Controller → Service → Interface → Repository → Model
```

- **Controllers** handle HTTP, delegate to services
- **Services** contain business logic (validation, transactions, event dispatch)
- **Repositories** abstract database queries behind interfaces
- **Models** define Eloquent relationships and attributes
- **Events + Listeners + Jobs** handle async side-effects (notifications)
- **Form Requests** validate input before it reaches the controller
- **API Resources** transform models into consistent JSON responses

### Project Structure

```
app/
├── Events/Order/             # OrderPlaced event
├── Exceptions/               # ApiException
├── Helper/                   # ApiErrorResponse, ApiListHelper
├── Http/Controllers/Api/     # Auth, Category, Product, Order
├── Http/Middleware/           # EnsureUserIsAdmin
├── Http/Requests/Api/        # Form requests per domain
├── Http/Resources/Api/       # API resources per domain
├── Http/Traits/              # Shared form-request rule traits
├── Interfaces/               # Repository interfaces
├── Jobs/                     # SendOrderConfirmationJob, CheckLowStockJob
├── Listeners/                # SendOrderConfirmation, QueueLowStockCheck
├── Models/                   # User, Profile, Category, Product, Order, OrderItem
├── Notifications/            # OrderPlacedNotification, LowStockNotification
├── Policies/                 # Order, Product, Category policies
├── Repositories/             # Repository implementations
└── Services/                 # Auth, Product, Category, Order services
database/migrations/          # 8 custom + Laravel defaults
database/seeders/             # ShopDemoSeeder
dev/                          # Docker config (PHP-FPM, Nginx, entrypoint)
postman/                      # Postman collection
```

---

## Getting Started

### Prerequisites

- **Docker** and **Docker Compose**
- **Git**

> You do *not* need a local PHP, Composer, or MySQL installation.

### 1. Clone the repository

```bash
git clone https://github.com/prachi-dubey/Laravel-ProductManagement.git
cd Laravel-ProductManagement
```

### 2. Environment configuration

```bash
cp .env.example .env
```

Default Docker values (no changes needed):

```text
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=shop_api
DB_USERNAME=root
DB_PASSWORD=password
QUEUE_CONNECTION=database
```

`start.sh` copies `.env.example` to `.env` automatically if `.env` is missing.

### 3. Build and start containers

```bash
./start.sh
# or: docker compose up -d --build
```

On startup the app container runs:

- `composer install`
- `php artisan key:generate` (if `APP_KEY` is missing)
- `php artisan migrate --seed`
- `php artisan storage:link`
- `php artisan queue:work` (background worker for notifications)

### Docker services

| Service | URL / Port |
|---------|-----------|
| API | http://localhost:84 |
| phpMyAdmin | http://localhost:8080 |
| MySQL | localhost:3307 |

### 4. Verify

```bash
curl http://localhost:84/api/products
```

### Default seeded users

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@shop.test | password |
| Customer | customer@shop.test | password |

---

## Development Workflow

```bash
docker compose up -d          # start
docker compose down           # stop
docker compose logs -f app    # view logs
```

Re-run commands manually when needed:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan db:seed
```

### Postman collection

Import `postman/shop-api-categories-products.postman_collection.json` and set:

- `base_url` → `http://localhost:84`
- `token` → value from login response

---

## Email Setup (Gmail SMTP)

By default emails are written to `storage/logs/laravel.log` (`MAIL_MAILER=log`). To send real emails to customers on order placement:

1. Enable **2-Step Verification** on your Google account at https://myaccount.google.com/security
2. Generate an **App Password** at https://myaccount.google.com/apppasswords (select "Mail" → "Other", name it "Shop API")
3. Update your `.env`:

```text
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD="your-16-char-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Shop API"
```

> **Important:** Wrap the App Password in double quotes if it contains spaces.

4. Restart Docker: `docker compose down && docker compose up -d --build`

After this, order confirmation emails are sent from your Gmail to the customer's registered email, and low-stock alerts are sent to all admin users.

---

## Testing

```bash
docker compose exec app php artisan test
```

---

## New Machine Setup (macOS)

Run once on a fresh machine, then follow the setup above.

```bash
# Install Docker (pick one)
brew install colima docker && colima start          # Option A: Colima
# or install Docker Desktop and start it            # Option B: Docker Desktop

# Install Docker Compose plugin (if needed)
brew install docker-compose
mkdir -p ~/.docker/cli-plugins
ln -sf "$(brew --prefix)/opt/docker-compose/bin/docker-compose" ~/.docker/cli-plugins/docker-compose
```

---

## License

MIT
