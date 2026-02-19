# Sistema Avanzado de Gestión de Imágenes para Productos

## 🚀 Características Implementadas

Este sistema completo de gestión de imágenes para productos incluye:

### 1. **Reordenamiento por Drag & Drop** 🔄
- Arrastra imágenes para reordenarlas
- Insidcador de posición (X/Y)
- Feedback visual durante el arrastre
- Actualización automática del `sort_order`

### 2. **Editor/Cropper de Imágenes** ✂️
- Modal completo de cropping
- Múltiples relaciones de aspecto (1:1, 4:3, 16:9, 3:2, 2:1, Free)
- Control de calidad (50-100%)
- Selección de formato (JPEG, PNG, WebP)
- Preview en tiempo real

### 3. **Compresión Automática de Imágenes** 🗜️
- Reducción automática de tamaño
- Control de calidad configurable (50-100%)
- Redimensionamiento inteligente manteniendo aspecto
- Max 2000x2000px
- Opción activable/desactivable

### 4. **Watermark** 🏷️
- Soporte para watemark con texto e imagen
- Posicionamiento personalizable
- Control de opacidad
- Integración en upload (opcional)

### 5. **Galería Mejorada** 🖼️
- Grid responsive (2-4 columnas)
- Badge de cantidad de imágenes
- Modal de previsualización
- Iconos para cada acción
- Estado de imagen default

## 📦 Instalación

### Paso 1: Instalar Dependencia de Imagen

```bash
composer require intervention/image
```

### Paso 2: Publicar Config (si es necesario)

```bash
php artisan vendor:publish --provider="Intervention\Image\ImageServiceProviderLaravel5"
```

### Paso 3: Editar `.env`

```env
IMAGE_DRIVER=imagick
# o
IMAGE_DRIVER=gd
```

Si usas `imagick` asegúrate de instalarlo en el servidor:
```bash
# En Windows (usando Laragon)
# Descargar ImageMagick desde https://imagemagick.org/

# En Ubuntu/Debian
sudo apt-get install imagemagick php-imagick

# En macOS
brew install imagemagick
```

### Paso 4: Opcional - Crear Watermark

Crea un archivo `public/images/watermark.png` (recomendado: 200x200 px con fondo transparente)

Si no existe, el sistema saltará el watermark sin errores.

## 🎨 Uso en Componentes

### En ProductForm.vue

```vue
<!-- Las imágenes se cargan con compresión automática -->
<!-- Puedes editar cada imagen con el botón de crop -->
<!-- Arrastra para reordenar -->
<!-- Click en estrella para establecer default -->
```

### En ProductList.vue

```vue
<!-- Ver thumbnail de imagen default -->
<!-- Badge muestra cantidad total -->
<!-- Click en imagen para ver galería completa -->
```

## 🔧 Configuración

### En ProductForm.vue

```javascript
// Habilitar/Deshabilitar compresión automática
autoCompress.value = true;

// Calidad de compresión (50-100%)
compressionQuality.value = 85;
```

### En UploadController.php

```php
// Parámetros de upload
'compress' => true,           // Activar compresión
'quality' => 85,              // Calidad
'add_watermark' => false,     // Agregar watermark
```

## 📊 Estructura de Datos

### product_images table

```php
- id
- product_id
- url (string)
- is_default (boolean)
- sort_order (integer) - Nueva columna para ordenar
- created_at
- updated_at
```

## 🎯 Funciones Helper Disponibles

### ImageManager.php

```php
// Validar imagen
ImageManager::validateImage($file);

// Comprimir imagen
ImageManager::compressImage($path, $quality, $maxWidth, $maxHeight);

// Watermark de texto
ImageManager::addTextWatermark($image, $text, $position, $opacity);

// Watermark de imagen
ImageManager::addImageWatermark($image, $watermarkPath, $position, $padding, $opacity);

// Generar thumbnails
ImageManager::generateThumbnails($path, $sizes);

// Obtener dimensiones
ImageManager::getImageDimensions($path);
```

## 🖥️ API Endpoints

### POST `/api/upload`

**Parámetros:**
```
- file (required): archivo imagen
- folder (optional): carpeta de destino
- compress (optional): bool, activar compresión
- quality (optional): 50-100, calidad
- add_watermark (optional): bool, agregar watermark
```

**Respuesta:**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "url": "storage/products/...",
  "path": "products/...",
  "size": 123456
}
```

## 📸 Flujos de Trabajo

### Crear Producto con Imágenes
1. Fill formulario básico
2. Arrastra imágenes o hace click
3. Espera a que se comprima automáticamente
4. Puedes editar (crop) cada imagen
5. Arrastra para reordenar
6. Click en estrella para establecer default
7. Guarda el producto

### Editar Imágenes Existentes
1. Ve a Edit Producto
2. Haz click en icono de crop para editar
3. Selecciona aspecto ratio y quality
4. Click "Apply Crop"
5. La imagen se reemplaza automáticamente

### Ver Galería Completa
1. En ProductList, haz click en imagen
2. Se abre modal con galería
3. Scroll para ver todas
4. Puedes expandir cada una

## 🐛 Troubleshooting

### "Class 'Intervention\Image\Facades\Image' not found"
```bash
composer require intervention/image
php artisan cache:clear
php artisan config:cache
```

### Imagick no encontrado
```bash
# Verificar que está instalado
php -m | grep imagick

# Si no aparece, instalarlo:
# En Ubuntu: sudo apt-get install php-imagick
# En Windows: actualizar php.ini y agregar extension=imagick.so
```

### Las imágenes no comprimen
```php
// Verificar en .env
IMAGE_DRIVER=gd  // o imagick

// Reiniciar el servidor
php artisan serve
```

### Watermark no aparece
- Asegúrate que `public/images/watermark.png` existe
- El archivo debe ser PNG con fondo transparente
- El sistema silenciosamente ignora si no existe

## 🚀 Performance Tips

1. **Compresión**: Usa 80-90% de quality para balance
2. **Tamaño max**: Limitar a 2000x2000 para web
3. **Formatos**: 
   - JPEG: Mejores para fotos
   - PNG: Para imágenes con transparencia
   - WebP: Mejor compresión (soporte limitado)

## 📝 Notas

- Las imágenes se almacenan en `storage/app/public/products/`
- Los URLs son accesibles vía `/storage/...`
- El `sort_order` se actualiza automáticamente
- Solo se guarca `url` en BD, no blobs
- El cropping es local antes de subir

## 🔐 Seguridad

- Validación de MIME type
- Máximo 2MB por archivo
- Extensiones permitidas: jpg, jpeg, png, webp, gif
- Nombres de archivo randomizados
- Almacenamiento en carpeta pública controlada

