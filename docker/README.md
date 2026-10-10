# Funkomacetas - Docker Deployment

## Prerequisites

- Docker & Docker Compose installed
- Dokploy account (for deployment)

## Quick Start (Local)

1. Copy environment file:
```bash
cp .env.docker .env
```

2. Generate application key:
```bash
docker compose run --rm app php artisan key:generate
```

3. Run migrations:
```bash
docker compose run --rm app php artisan migrate
```

4. Start containers:
```bash
docker compose up -d
```

5. Access at: http://localhost:8080

## Dokploy Deployment

### 1. Push to Git Repository

Push this project to a Git repository (GitHub, GitLab, Gitea, etc.)

### 2. Create Project in Dokploy

- Go to Dokploy Dashboard
- Create New Project
- Select Git Provider and connect your repository

### 3. Configure Build Settings

**Build Command:**
```bash
docker compose -f docker-compose.yml build
```

**Publish Directory:**
```bash
/var/www
```

### 4. Environment Variables

Set these in Dokploy dashboard:

```
APP_NAME=Funkomacetas
APP_ENV=production
APP_KEY=YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST= HOST provided by Dokploy
DB_PORT=3306
DB_DATABASE=funkomacetas
DB_USERNAME= DATABASE_USER from Dokploy
DB_PASSWORD= DATABASE_PASSWORD from Dokploy

REDIS_HOST= REDIS_HOST from Dokploy
REDIS_PASSWORD= REDIS_PASSWORD from Dokploy
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### 5. Domains

Add your domain in Dokploy (optional)

### 6. Deploy

Click Deploy and Dokploy will:
1. Build the Docker image
2. Run migrations automatically
3. Start all containers

## Services

- **app**: Main PHP/Nginx application
- **database**: MySQL 8.0
- **redis**: Redis 7 for caching & queues
- **queue**: Laravel queue worker

## Useful Commands

```bash
# Enter app container
docker compose exec app bash

# Run artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan queue:restart

# View logs
docker compose logs -f app
docker compose logs -f queue

# Rebuild after changes
docker compose build --no-cache
docker compose up -d
```

## Ports

- App: 8080 (HTTP)
- MySQL: 3307 (external)
- Redis: 6380 (external)

## Migraciones y catálogo al arrancar

`docker/entrypoint.sh` corre estas tareas al iniciar el contenedor, según variables de entorno
cuyos valores por defecto vienen en cada Dockerfile (Dokploy puede sobrescribirlos):

| Variable | `Dockerfile.dev` | `Dockerfile.prod` | Qué hace |
|---|---|---|---|
| `RUN_MIGRATIONS` | `true` | `true` | `php artisan migrate --force` (si falla, el contenedor no arranca) |
| `SEED_CATALOG` | `true` | `false` | Carga las 585 figuras del proveedor con sus fotos WebP |

El seeder es idempotente: no duplica ni pisa precios, stock o fotos subidas a mano. En producción
actívalo una sola vez con `SEED_CATALOG=true` y quítalo después.

> Respalda la base antes del primer despliegue con migraciones: `merge_funkomacetas_into_figures`
> borra la tabla `funkomacetas` y no es reversible.

Las variables de Dokploy (`APP_DEBUG`, `APP_URL`, etc.) mandan sobre el `.env` de la imagen:
en dev y producción debe estar `APP_DEBUG=false`.
