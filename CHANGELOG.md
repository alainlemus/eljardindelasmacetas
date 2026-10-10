# Changelog

Todas las versiones del sistema. El número vive en el archivo `VERSION` y sigue
[versionado semántico](https://semver.org/lang/es/):

- **Mayor** (2.0.0): cambios que rompen algo (por ejemplo, la API que usa la app).
- **Menor** (1.1.0): funciones nuevas.
- **Parche** (1.0.1): correcciones.

Cada paso de `develop` a `main` sube la versión y agrega su sección aquí; al llegar
a `main`, la GitHub Action `release.yml` crea la etiqueta `vX.Y.Z` y el Release con
estas notas. Fuera de producción la versión se muestra como `vX.Y.Z-dev · commit`.
El encabezado de cada sección debe ser exacto: `## [X.Y.Z] - AAAA-MM-DD`.

## [1.1.3] - 2026-10-10

### Cambiado
- El precio de venta del catálogo del proveedor es el costo + $75 (las de costo $85 se venden a $160). El costo es siempre el precio que trae el Excel. Al volver a correr el seeder se corrigen los precios que él mismo había calculado como costo × 2; los capturados a mano no se tocan.

## [1.1.2] - 2026-10-10

### Cambiado
- Las 466 figuras del proveedor que no traían precio cuestan $85 (confirmado por el proveedor): el catálogo las carga con ese costo y precio de venta de $170, y quedan activas. Si ya estaban cargadas sin precio, el seeder se los aplica; lo capturado a mano no se toca.

## [1.1.1] - 2026-10-10

### Corregido
- **Seguridad:** el inicio de sesión y `/api/auth/me` devolvían el hash de la contraseña y el `remember_token` del usuario; ya no se envían.
- Guardar una figura con un nombre que ya existe en otra categoría (por ejemplo "Cenicienta" en Personajes y en Posket) daba un error 500; ahora cada figura conserva un identificador (slug) único.
- Una imagen dañada o enorme respondía con error 500; ahora responde 422 con un mensaje claro.
- Las categorías no pueden repetir nombre.

## [1.1.0] - 2026-10-09

### Agregado
- La API de figuras permite buscar por nombre o SKU y filtrar por activas, inactivas, sin precio, stock bajo y categoría; el listado viene ordenado por nombre y admite hasta 100 por página.
- Nuevo `GET /api/figures/stats` con los totales de todo el inventario (figuras, activas, sin precio, stock bajo, valor del inventario y categorías), que usa el tablero de la app.
- Migraciones automáticas en cada despliegue (`RUN_MIGRATIONS`) y carga del catálogo en dev (`SEED_CATALOG`); en producción el catálogo queda apagado.

### Cambiado
- La app móvil debe ser la versión 2.0.0 o posterior (usa las rutas `/api/figures`).

## [1.0.0] - 2026-10-09

### Agregado
- Versión del sistema visible en el pie del catálogo y en el menú del panel, y en `/version.json`.
- Catálogo nuevo con el diseño del logo, buscador, categorías, paginador en español y botón de pedido por WhatsApp.
- Carga del catálogo del proveedor (573 figuras en 7 categorías) con su costo de compra.
- Tablero del panel con totales de inventario.
- Fotos del catálogo del proveedor (PDF Kiwi Art) ya convertidas a WebP: 381 figuras con foto.
- 12 personajes que estaban en el catálogo del proveedor y no en el Excel (Shrek, Skeletor, Cinamoroll, Zenitsu, Foxy, Haas, Boogie, Marcus Fenix, Doc, Homero arbusto, Snoopy Navidad XL y Nezuko Posket); quedan inactivos hasta ponerles precio.
- Las fotos que se suben desde el panel o desde la app se convierten a WebP, se comprimen (lado mayor 1200 px), se corrige su orientación y se les quitan los metadatos.
- Comando `php artisan figures:optimize-images` para convertir las fotos ya subidas.

### Cambiado
- Se simplificó el modelo: ahora solo hay Categorías y Figuras; la figura es el producto (precio, costo, stock y fotos). Los endpoints `/api/products` siguen funcionando como alias de `/api/figures`.
- Las figuras sin existencia se muestran como "Sobre pedido" en lugar de ocultarse.

### Corregido
- Solo los administradores pueden gestionar el catálogo por API y entrar al panel.
- El costo y las ventas ya no se exponen en el catálogo público ni en la API pública.
- Rutas que nunca respondían (`/catalog/share`, `top-selling`), favicon, logo y fotos rotas.
- La imagen de Docker instalaba GD sin soporte de WebP ni JPEG.
