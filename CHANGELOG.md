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

## [1.0.0] - 2026-10-09

### Agregado
- Versión del sistema visible en el pie del catálogo y en el menú del panel, y en `/version.json`.
- Catálogo nuevo con el diseño del logo, buscador, categorías, paginador en español y botón de pedido por WhatsApp.
- Carga del catálogo del proveedor (573 figuras en 7 categorías) con su costo de compra.
- Tablero del panel con totales de inventario.

### Cambiado
- Se simplificó el modelo: ahora solo hay Categorías y Figuras; la figura es el producto (precio, costo, stock y fotos). Los endpoints `/api/products` siguen funcionando como alias de `/api/figures`.
- Las figuras sin existencia se muestran como "Sobre pedido" en lugar de ocultarse.

### Corregido
- Solo los administradores pueden gestionar el catálogo por API y entrar al panel.
- El costo y las ventas ya no se exponen en el catálogo público ni en la API pública.
- Rutas que nunca respondían (`/catalog/share`, `top-selling`), favicon, logo y fotos rotas.
