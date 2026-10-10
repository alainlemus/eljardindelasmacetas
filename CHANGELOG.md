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

## [1.5.0] - 2026-10-10

### Corregido
- El tablero del panel aparecía vacío porque su widget esperaba a cargarse por scroll; ahora carga con la página.
- Los precios del panel salían como "160,00 US$"; ahora se muestran en pesos ($160.00).
- Al escribir el nombre en el formulario de Figura o Categoría el panel marcaba error; ahora el campo "slug" se llena solo.
- Acentos en el panel (Catálogo, Categoría, Descripción, Stock mínimo, Imágenes).

### Cambiado
- El panel de administración (`/admin`) adopta el verde del logo: barra lateral verde con degradado y opción activa resaltada, encabezado crema, botones verdes sólidos, títulos en Fredoka y filas de tablas con acento verde; también en modo oscuro.
- Tablero del panel con "Total de figuras", "Sin precio", "Stock bajo" y "Valor del inventario" (antes decía "Productos" y "Sin stock").
- La pantalla de inicio de sesión del panel (`/admin/login`) ahora tiene dos secciones: a la izquierda la marca (logo, mensaje y círculos, burbujas y hojitas animados) y a la derecha el formulario con el saludo "Bienvenido de nuevo", la versión y un enlace al catálogo. En celular la marca queda arriba y el formulario abajo; funciona en modo claro y oscuro y respeta "reducir movimiento".

## [1.4.0] - 2026-10-10

### Agregado
- Al compartir el enlace de una figura en WhatsApp, Facebook, X u otras redes sale una tarjeta con su foto, nombre, precio y el botón "Pídela por WhatsApp" (imagen JPEG de 1200×630 y menos de 300 KB, como piden las redes); se regenera sola si la figura cambia. El enlace del catálogo usa la imagen de portada del sitio.

### Cambiado
- Las fuentes (Fredoka y Nunito) se sirven desde el propio sitio en vez de Google Fonts: carga más rápida y sin pedir nada a terceros.
- El seeder del catálogo borra las 8 figuras de ejemplo del primer seeder (Iron Man, etc.) mientras sigan sin categoría ni precio; si ya las editaste, no las toca.

### Corregido
- En el celular, el botón ✨ se encimaba con la barra de pedido de la ficha de la figura.

## [1.3.0] - 2026-10-10

### Agregado
- SEO: `robots.txt` y `sitemap.xml` generados por el sitio (el sitemap lista las figuras activas y las categorías), título y descripción propios por categoría y por figura, URL canónica única, datos estructurados (Organización, Sitio web, Producto con precio y disponibilidad, migas de pan) y etiquetas completas para compartir en redes (Open Graph y Twitter).
- Dev y staging ya no se indexan en buscadores (`noindex` y `Disallow: /`); se detectan por el dominio (`dev.`, `staging.`, `.test`) o con `SITE_INDEXABLE`.
- Página 404 con el diseño del sitio y página de error 500/503 propias.
- Enlace "Saltar al contenido" y textos alternativos y dimensiones en todas las imágenes (menos saltos al cargar); las primeras fotos cargan con prioridad.

### Cambiado
- `/catalog` redirige a `/` (una sola URL para el listado); las búsquedas no se indexan.
- El catálogo público no crea sesión ni cookies por visitante (menos carga en la base de datos) y se puede cachear 1 minuto en el navegador y 5 en CDN.
- Servidor: encabezados de seguridad (`Referrer-Policy`, `Permissions-Policy`), sin versión de nginx ni de PHP, archivos de `/build` con caché de un año y compresión también para JSON.

### Corregido
- `robots.txt` permitía indexar `/admin` y `/api`, y no apuntaba a un sitemap.

## [1.2.0] - 2026-10-10

### Agregado
- Catálogo con más vida: los círculos del encabezado se mueven dentro de la tarjeta y siguen al cursor, suben burbujitas, se mecen hojitas, el título aparece palabra por palabra y el botón "Ver figuras" late.
- Burbujas de colores flotando por todo el sitio que se pueden reventar con un clic o toque, y confeti: una lluvia de bienvenida (una vez por visita) y explosiones al tocar el logo o al pulsar "Compartir" y "Pedir por WhatsApp".
- Las tarjetas aparecen con animación al hacer scroll y tienen un brillo al pasar el cursor.
- Botón ✨ (abajo a la izquierda) para apagar o encender las burbujas y el confeti; respeta el ajuste de "reducir movimiento" del dispositivo y se pausa cuando la pestaña no se ve.

## [1.1.4] - 2026-10-10

### Corregido
- Con `APP_LOCALE=es` los errores de validación salían como `validation.required` porque no había textos en español; se agregaron (`lang/es`) y el español queda como idioma por defecto. La app y la API ahora muestran mensajes como "El campo nombre es obligatorio.".

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
