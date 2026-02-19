# Pasos de Instalación y Configuración

## 🎯 Instalación Completa del Sistema de Gestión de Imágenes

### Paso 1: Instalar Dependencias

```bash
# Instalar Intervention Image
composer require intervention/image

# Limpiar caché
php artisan cache:clear
php artisan config:cache
```

### Paso 2: Configurar Imagen Driver

En tu archivo `.env`:

```env
# Opción 1: Usar GD (más ligero, pero menos potente)
IMAGE_DRIVER=gd

# Opción 2: Usar Imagick (más potente, requiere instalación)
IMAGE_DRIVER=imagick
```

Si usas **Imagick** (recomendado para producción), instálalo:

**Windows (Laragon):**
1. Descargar desde https://imagemagick.org/script/download.php#windows
2. Instalar en el mismo directorio que PHP
3. Agregar PHP extension en `php.ini`:
   ```
   extension=imagick.so
   ```
4. Reiniciar Apache

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install imagemagick php-imagick
sudo systemctl restart apache2
```

**macOS:**
```bash
brew install imagemagick
# Luego actualizar php.ini
```

### Paso 3: Crear Directorios

```bash
# Crear carpetas de almacenamiento
mkdir -p public/images
mkdir -p storage/app/public/products
mkdir -p storage/app/public/categories

# Crear symlink
php artisan storage:link
```

### Paso 4: Crear Logo Watermark (Opcional)

Crea un archivo PNG con tu logo en:
```
public/images/watermark.png
```

Dimensiones recomendadas: **200x200px** con fondo transparente

Si no existe, el sistema funcionará sin watermark.

### Paso 5: Ejecutar Migraciones

```bash
# Si es la primera vez
php artisan migrate

# Si ya existe product_images, la migración de sort_order ya está incluida
php artisan migrate --seed
```

### Paso 6: Establecer Permisos (Importante para Producción)

```bash
# Linux/Mac
chmod -R 775 storage/app/public
chmod -R 775 public/storage

# Si tienes issues de permisos
sudo chown -R www-data:www-data storage/app/public
```

## ✅ Verificar Instalación

```bash
# Verificar que Intervention Image está cargado
php -r "print_r(class_exists('Intervention\Image\Facades\Image') ? 'OK' : 'FAIL');"

# Verificar driver configurado
php artisan config:get image.driver

# Test de upload
php artisan tinker
>>> Intervention\Image\Facades\Image::make('path/to/image.jpg')->save('test.jpg')
```

## 🎨 Características Disponibles

### En el Formulario de Productos

```
✅ Upload múltiple con drag & drop
✅ Compresión automática configurable
✅ Cropper de imágenes (múltiples aspectos)
✅ Reordenamiento por drag & drop
✅ Establecer imagen default
✅ Eliminación de imágenes
✅ Previsualización
```

### En la Lista de Productos

```
✅ Thumbnail de imagen default
✅ Badge con cantidad de imágenes
✅ Modal de galería completa
✅ Visualización de todas las imágenes
```

## 🔧 Configuración Recomendada en .env

```env
# DB
DATABASE_URL=mysql://user:password@localhost/database

# Image
IMAGE_DRIVER=imagick
# o
IMAGE_DRIVER=gd

# Upload
MAX_UPLOAD_SIZE=2048  # 2MB

# Cache (importante para performance)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## 🚀 Uso en la Aplicación

### 1. Crear Producto con Imágenes

```
1. Ir a: /admin/products/create
2. Llenar datos básicos
3. Arrastra imágenes o haz click en el área de upload
4. Espera a procesamiento (compresión)
5. Edita/corta si necesario
6. Arrastra para reordenar
7. Click en estrella si quieres cambiar default
8. Guarda el producto
```

### 2. Editar Producto Existente

```
1. Ir a: /admin/products/{id}/edit
2. En la sección "Product Images"
3. Click en icono de crop para editar imagen
4. Click en X para eliminar
5. Puedes agregar más imágenes
6. Recuerda guardar cambios
```

### 3. Ver Galería

```
1. Ir a: Products list
2. Click en thumbnail de imagen
3. Se abre modal con todas las imágenes
4. Scroll dentro del modal
```

## 📊 Estructura de Carpetas

```
storage/
├── app/
│   ├── public/
│   │   ├── products/          # Imágenes de productos
│   │   │   ├── xxxxx.jpg
│   │   │   └── ...
│   │   ├── categories/        # Imágenes de categorías
│   │   │   ├── xxxxx.jpg
│   │   │   └── ...
│   │   └── uploads/           # Otros uploads
│   └── ...
├── framework/
└── logs/

public/
├── storage/                   # Link simbólico a storage/app/public
└── images/
    └── watermark.png          # (Opcional)
```

## 🐛 Troubleshooting

### Error: "Class 'Intervention\Image\Facades\Image' not found"

```bash
composer require intervention/image
php artisan cache:clear
php artisan config:cache
# Reinicia el servidor
```

### Error: "imagick not found" (si IMAGE_DRIVER=imagick)

```bash
# Windows: Instalar ImageMagick
# Ubuntu: sudo apt-get install php-imagick
# Mac: brew install imagemagick
```

### Las imágenes no se guardan

```bash
# Verificar permisos
ls -la storage/app/public

# Dar permisos (Linux)
chmod 775 storage/app/public
chmod 775 public/storage

# Verificar symlink
php artisan storage:link
```

### Error 413 (Payload Too Large)

```nginx
# En nginx.conf
client_max_body_size 50M;

# En Apache .htaccess
php_value post_max_size 50M
php_value upload_max_filesize 50M
```

## 🎯 Performance en Producción

### Recomendaciones

```env
# Usar Redis para cache
CACHE_DRIVER=redis

# Usar imagick para mejor calidad
IMAGE_DRIVER=imagick

# Queue para uploads pesados (opcional)
QUEUE_CONNECTION=redis

# Maxima compresión pero buena calidad
# En ProductForm.vue: compressionQuality = 80
```

### Optimizar Imágenes

```php
// En UploadController:
- Máximo 2000x2000px
- Compresión JPEG: 85% de quality
- Convertir PNG a JPEG cuando sea posible
- Usar WebP si el navegador lo soporta
```

## 📝 Migraciones Incluidas

- ✅ `2026_01_29_202845_create_product_images_table.php` - Ya incluye `sort_order`

No necesitas más migraciones. El sistema está listo.

## 🔒 Seguridad

```php
// Validaciones implementadas:
✅ MIME type validation
✅ File size limit (2MB)
✅ Extension whitelist
✅ Random filename generation
✅ Storage path isolation
```

## 📚 Referencia API

### POST /api/upload

**Request:**
```json
{
  "file": "file",
  "folder": "products",
  "compress": true,
  "quality": 85,
  "add_watermark": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "url": "/storage/products/xxx.jpg",
  "path": "products/xxx.jpg",
  "size": 123456
}
```

## ✨ Listo

Completa estos pasos y estarás listo para usar el sistema completo de gestión de imágenes con:

- ✅ Upload múltiple
- ✅ Compresión automática
- ✅ Cropping/Edición
- ✅ Reordenamiento
- ✅ Watermark
- ✅ Galería
- ✅ Performance optimizado

