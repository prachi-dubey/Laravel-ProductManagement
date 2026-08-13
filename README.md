# Shop API (Laravel 12)

Postman-only e-commerce API — no Blade UI. Auth via Sanctum Bearer tokens.

**Product ↔ Category** is many-to-many via the `category_product` pivot table (a product can appear in multiple categories).

## Folder layout (by domain)

```
app/Events/Order/OrderPlaced.php
app/Http/Requests/Api/{Auth,Category,Product,Order}/
app/Http/Resources/Api/{Auth,Category,Product,Order}/
app/Interfaces/{Auth,Category,Product,Order}/
```

Store + update share one request where rules overlap (`SaveCategoryRequest`, `SaveProductRequest`).

## Architecture

```
Controller → Service → Interface → Repository → Model
```

- Messages: `lang/en/messages.php`
- Success envelope: base `Controller` methods `success()` / `paginated()`
- Errors: `App\Helper\ApiErrorResponse` + single `App\Exceptions\ApiException` (static helpers per error)
- List helpers: `App\Helper\ApiListHelper` (sort, per_page, paginated payload)

## Local PHP setup (XAMPP dual version)

```bash
cd shop-api
./bin/artisan migrate
./bin/serve                 # http://127.0.0.1:8000
```

Open **XAMPP Manager** → start **MySQL**.

## Postman

Import [`postman/shop-api-categories-products.postman_collection.json`](postman/shop-api-categories-products.postman_collection.json)

**Public (no Bearer token):**
- `GET /api/categories`, `GET /api/categories/{id}`
- `GET /api/products`, `GET /api/products/{id}`
- `POST /api/register`, `POST /api/login`

**Authenticated (Bearer token required):**
- `GET /api/me`, `POST /api/logout`
- Orders, notifications

**Admin only (Bearer + admin role):**
- Catalog writes (POST/PUT/DELETE products & categories)

Seeded users: `admin@shop.test` / `customer@shop.test` — password `password`

```bash
./bin/artisan storage:link   # product images on POST/PUT /api/products
./bin/artisan queue:work     # notifications after orders
```
