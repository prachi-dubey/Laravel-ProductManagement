# Shop API — E-Commerce REST API

A RESTful e-commerce API built from scratch for managing products, categories, orders, and notifications with role-based access control.

## About the Project

Shop API is a backend-only e-commerce application that exposes a JSON API for:

- **User registration and authentication** with token-based access (Sanctum)
- **Product and category management** with a many-to-many relationship (a product can belong to multiple categories)
- **Order placement** with stock validation, automatic inventory decrement, and order number generation
- **Queued notifications** — order confirmations (to customers) and low-stock alerts (to admins) via email and database channels
- **Role-based access** — Admin and Customer roles with policy-based authorization

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

## What This App Does (vs. What Laravel Provides)

### Built by us

| Feature | What we built |
|---------|--------------|
| **Authentication** | Custom `AuthController` + `AuthService` — register (creates customer role + profile), login (manual credential check, returns Sanctum token), logout (revokes token), me (current user info), update profile (shipping address, phone, bio) |
| **Role-Based Access Control** | Two roles (`admin`, `customer`). Custom `EnsureUserIsAdmin` middleware restricts admin routes. Policies (`OrderPolicy`, `ProductPolicy`, `CategoryPolicy`) control per-resource authorization |
| **Categories (CRUD)** | `CategoryController` + `CategoryService` + `CategoryRepository` — create, read, update, delete categories. Public listing with pagination, sorting, and search |
| **Products (CRUD)** | `ProductController` + `ProductService` + `ProductRepository` — full CRUD with SKU, price, stock tracking, and many-to-many category sync (`PUT /products/{id}/categories`) |
| **Orders** | `OrderController` + `OrderService` + `OrderRepository` — customers place orders with line items; the service validates stock, decrements inventory inside a DB transaction, generates an order number, and fires an `OrderPlaced` event |
| **Order event pipeline** | `OrderPlaced` event → two listeners: `SendOrderConfirmation` (dispatches `SendOrderConfirmationJob`) and `QueueLowStockCheck` (dispatches `CheckLowStockJob`). Both jobs are queued |
| **Notifications** | Two custom Laravel notifications: `OrderPlacedNotification` (sent to the customer with order details) and `LowStockNotification` (sent to all admins when a product's stock drops below 10). Both use `mail` + `database` channels |
| **Notification management API** | `NotificationController` — list notifications, mark one as read, mark all as read |
| **Repository pattern** | Interfaces + implementations for User, Product, Category, and Order repositories, bound in the service container |
| **Service layer** | `AuthService`, `ProductService`, `CategoryService`, `OrderService` encapsulate business logic away from controllers |
| **Form request validation** | 8 custom request classes (`RegisterRequest`, `LoginRequest`, `UpdateProfileRequest`, `SaveCategoryRequest`, `SaveProductRequest`, `StoreOrderRequest`, etc.) with shared rule traits (`IndexQueryRules`, `CategoryIdsRules`) |
| **API resources** | `AuthResource`, `UserResource`, `ProfileResource`, `ProductResource`, `CategoryResource`, `OrderResource`, `OrderItemResource` for consistent response transformation |
| **Standardized API responses** | Base controller `success()` and `paginated()` helpers, `ApiErrorResponse` helper, and a single `ApiException` class — every response follows `{ "success": true/false, "message": "...", "data": {} }` |
| **Pagination & sorting helper** | `ApiListHelper` handles `sort`, `sort_direction`, `per_page`, and paginated payload formatting |
| **Localized messages** | `lang/en/messages.php` — all user-facing API messages in one place |
| **Database seeder** | `ShopDemoSeeder` creates demo admin + customer users with profiles, 3 categories, 6 products, and a sample order (idempotent with `updateOrCreate`) |
| **Docker setup** | Custom `Dockerfile` (PHP-FPM), Nginx config, entrypoint script, and `docker-compose.yml` with 5 services. `start.sh` handles first-run setup |

### Provided by Laravel (we use, but did not build)

| Feature | What Laravel provides |
|---------|----------------------|
| Sanctum token engine | Token creation, hashing, `auth:sanctum` middleware guard |
| Notification system | `Notifiable` trait, `mail` + `database` channels, `notifications` table |
| Queue system | Job dispatching, `database` queue driver, `queue:work` command |
| Event system | `Event` + `Listener` wiring and dispatch |
| Eloquent ORM | Models, relationships, migrations, transactions |
| Form request validation | Base `FormRequest` class and validation rules |
| API resource classes | Base `JsonResource` for response shaping |
| Artisan CLI | `migrate`, `seed`, `key:generate`, `storage:link`, etc. |

---

## Features & Functionality

### 1. Authentication & Authorization

- **Register** — creates a new customer account with a profile, returns an API token
- **Login** — validates credentials, returns a bearer token for subsequent requests
- **Logout** — revokes the current token
- **Update profile** — update shipping address, phone, and bio via `PUT /api/profile` (required before placing an order)
- **Role check** — admin routes are protected by custom middleware; resource-level access is controlled by policies

### 2. Category Management

- Public: browse and view categories with pagination and sorting
- Admin: create, update, and delete categories

### 3. Product Management

- Public: browse and view products with pagination and sorting
- Admin: create, update, delete products; sync product-category assignments
- Products track name, description, SKU, price, and stock quantity
- Many-to-many relationship with categories via `category_product` pivot table

### 4. Order Processing

- Customers must first update their profile with a shipping address (`PUT /api/profile`) before placing an order
- Customers submit an order with a list of products and quantities
- The service validates that sufficient stock exists for every item
- Stock is decremented and the order is created inside a database transaction
- A unique order number is generated automatically
- An `OrderPlaced` event is fired, triggering the notification pipeline

**Order status workflow:**

```
pending → paid → shipped
   ↓        ↓
cancelled cancelled
```

### 5. Queued Notifications

When an order is placed, two queued jobs run:

1. **Order confirmation** — sends the customer an email and stores a database notification with order details
2. **Low-stock alert** — if any ordered product's stock drops below 10, all admin users are notified via email and database

Users can list their notifications and mark them as read through the API.

### 6. Standardized API Responses

Every endpoint returns a consistent JSON envelope:

```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": { }
}
```

List endpoints include pagination metadata (current page, total, per page, etc.).

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
| GET | `/api/orders` | Yes | Any | List orders |
| GET | `/api/orders/{id}` | Yes | Any | View order |
| POST | `/api/orders` | Yes | Customer | Place order |
| GET | `/api/notifications` | Yes | Any | List notifications |
| POST | `/api/notifications/read-all` | Yes | Any | Mark all read |
| POST | `/api/notifications/{id}/read` | Yes | Any | Mark one read |

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
├── Http/Controllers/Api/     # Auth, Category, Product, Order, Notification
├── Http/Middleware/           # EnsureUserIsAdmin
├── Http/Requests/Api/        # Form requests per domain
├── Http/Resources/Api/       # API resources per domain
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

> You do *not* need a local PHP, Composer, MySQL, or Redis installation.

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
