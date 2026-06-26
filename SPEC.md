# Sistema Funkomacetas - Especificación Técnica

## 1. Project Overview

- **Project Name**: Sistema Funkomacetas
- **Project Type**: Laravel 12 + FilamentPHP v5 Full-Stack Application with REST API
- **Core Functionality**: Administration panel for managing a funko pop planter catalog with public sharing via WhatsApp and API for React Native hybrid app
- **Target Users**: Administrators (web panel), Customers (public catalog via WhatsApp), Mobile App Users (React Native)

## 2. Technology Stack

- **Framework**: Laravel 12
- **Admin Panel**: FilamentPHP v5
- **Database**: MySQL/SQLite
- **API**: Laravel REST API with Sanctum authentication
- **Frontend**: Blade templates for public catalog
- **Mobile**: React Native (external)

## 3. Data Models

### 3.1 Categories (Categorías)
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Category name (e.g., "Marvel", "DC", "Anime") |
| slug | string | URL-friendly name |
| description | text | Optional description |
| is_active | boolean | Visibility status |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

### 3.2 Figures (Figuras)
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Figure name |
| slug | string | URL-friendly name |
| description | text | Optional description |
| sku | string | Stock keeping unit |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

### 3.3 Funkomacetas (Productos)
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Product name |
| slug | string | URL-friendly name |
| description | text | Product description |
| price | decimal(10,2) | Sale price |
| cost | decimal(10,2) | Cost price |
| stock | integer | Available inventory |
| min_stock | integer | Minimum stock threshold |
| sku | string | Stock keeping unit |
| image | string | Main image URL/path |
| images | json | Additional images array |
| is_active | boolean | Visibility status |
| is_featured | boolean | Featured product flag |
| category_id | bigint | Foreign key to categories |
| figure_id | bigint | Foreign key to figures (nullable) |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

### 3.4 Users (Users - Laravel default + extensions)
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | User name |
| email | string | User email (unique) |
| password | string | Hashed password |
| is_admin | boolean | Admin flag |
| api_token | string | API token (for mobile app) |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

## 4. Functionality Specification

### 4.1 Admin Panel (FilamentPHP v5)

#### Dashboard
- Overview cards: Total products, low stock alerts, categories count
- Quick actions: Add product, view catalog

#### Categories Resource
- List view with search and filters
- Create/Edit form with validation
- Delete with confirmation
- Toggle active status

#### Figures Resource
- List view with search
- Create/Edit form
- Delete with confirmation

#### Funkomacetas Resource
- **List View**:
  - Table with image thumbnail, name, category, price, stock
  - Filters: category, price range, stock status
  - Search by name/SKU
  - Bulk actions: activate/deactivate, delete
- **Create/Edit Form**:
  - Name, slug (auto-generated)
  - Category selection (dropdown)
  - Figure selection (dropdown, optional)
  - Price and cost fields
  - Stock and min_stock fields
  - Image upload with preview
  - Additional images (multi-upload)
  - Description (rich text)
  - Active/Featured toggles
- **View Page**:
  - Full product details
  - Inventory history
  - Edit/Delete actions

### 4.2 Public Catalog (WhatsApp Share)

#### Features
- Mobile-responsive design
- Category filtering
- Product grid with images
- Individual product modal/detail view
- WhatsApp share button per product
- Share entire catalog link
- Direct WhatsApp contact button

#### URL Structure
- `/catalog` - Public catalog homepage
- `/catalog/{slug}` - Individual product detail
- `/catalog/share/{token}` - Shareable catalog link

### 4.3 REST API

#### Authentication
- `POST /api/auth/login` - Login with email/password, returns Sanctum token
- `POST /api/auth/logout` - Revoke current token
- `POST /api/auth/register` - Register new admin user

#### Categories API
- `GET /api/categories` - List all active categories
- `POST /api/categories` - Create category (auth required)
- `PUT /api/categories/{id}` - Update category (auth required)
- `DELETE /api/categories/{id}` - Delete category (auth required)

#### Figures API
- `GET /api/figures` - List all figures
- `POST /api/figures` - Create figure (auth required)
- `PUT /api/figures/{id}` - Update figure (auth required)
- `DELETE /api/figures/{id}` - Delete figure (auth required)

#### Products API
- `GET /api/products` - List products (with filters)
- `GET /api/products/{id}` - Get product details
- `POST /api/products` - Create product (auth required)
- `PUT /api/products/{id}` - Update product (auth required)
- `DELETE /api/products/{id}` - Delete product (auth required)
- `PUT /api/products/{id}/stock` - Update stock only (auth required)

#### Public API (No auth)
- `GET /api/public/catalog` - Get public catalog data
- `GET /api/public/products` - List active products

## 5. Configuration

### 5.1 Environment Variables
```
APP_NAME="Funkomacetas"
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=funkomacetas
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost
SESSION_DRIVER=database
CACHE_DRIVER=database
```

### 5.2 Filament Configuration
- Admin panel: `/admin`
- Theme: Custom light theme with brand colors
- Resource pages with simple table layouts

## 6. File Structure
```
sistema-funkos/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CatalogController.php
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── FigureController.php
│   │   │       └── ProductController.php
│   │   └── Resources/
│   │       ├── CategoryResource.php
│   │       ├── FigureResource.php
│   │       └── FunkomacetaResource.php
│   └── Models/
│       ├── Category.php
│       ├── Figure.php
│       └── Funkomaceta.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── catalog/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       └── layouts/
│           └── catalog.blade.php
├── routes/
│   ├── web.php
│   └── api.php
└── config/
    └── filament.php
```

## 7. Acceptance Criteria

### Admin Panel
- [ ] Can create, read, update, delete categories
- [ ] Can create, read, update, delete figures
- [ ] Can create, read, update, delete funkomacetas
- [ ] Can upload product images
- [ ] Can filter products by category and stock status
- [ ] Low stock products are highlighted

### Public Catalog
- [ ] Catalog page loads on mobile devices
- [ ] Products display with images, names, prices
- [ ] Category filtering works
- [ ] WhatsApp share button opens WhatsApp with product link
- [ ] Shareable link generates valid catalog URL

### API
- [ ] Authentication returns valid Sanctum token
- [ ] All CRUD operations work for categories, figures, products
- [ ] Stock update endpoint works
- [ ] Public catalog endpoint returns active products
- [ ] API responses follow consistent JSON structure

## 8. Design Language

### Color Palette
- Primary: `#6C5CE7` (Purple - Funko brand inspired)
- Secondary: `#00CEC9` (Teal)
- Accent: `#FD79A8` (Pink)
- Background: `#F8F9FA` (Light gray)
- Text: `#2D3436` (Dark gray)
- Success: `#00B894` (Green)
- Warning: `#FDCB6E` (Yellow)
- Danger: `#E17055` (Red)

### Typography
- Headings: Inter, sans-serif
- Body: Inter, sans-serif

### Spacing
- Base unit: 4px
- Common spacings: 8px, 16px, 24px, 32px

## 9. WhatsApp Integration

### Share Features
- Individual product share: Opens WhatsApp with product name, price, image URL
- Catalog share: Opens WhatsApp with catalog URL and summary
- Contact button: Opens WhatsApp with pre-filled message

### Share Message Template
```
🎉 ¡Mira esta Funkomaceta!
{Product Name}
💰 Precio: ${Price}
📦 Stock disponible: {Stock}
🔗 {URL}
```
