# Implementación de Estados de Pedidos con Colores

## Cambios Realizados

### 1. Base de Datos

#### Migraciones Creadas:
- **2026_02_06_000002_add_color_to_order_statuses_table.php**: Agrega el campo `color` (varchar 7, ej. #FFFFFF) a la tabla `order_statuses`
- **2026_02_06_000003_change_status_to_foreign_key_sales_orders.php**: Cambia el campo `status` (enum) por `order_status_id` (foreign key) en `sales_orders`

#### Seeder Creado:
- **OrderStatusSeeder.php**: Crea los 6 estados con colores predefinidos:
  - Pending (Naranja)
  - Confirmed (Azul Real)
  - Processing (Dorado)
  - Shipped (Turquesa)
  - Delivered (Verde Lima)
  - Cancelled (Rojo Carmín)

### 2. Modelos

#### OrderStatus.php
- Agregado campo `color` al array `fillable`
- Nueva relación `salesOrders()` para acceder a los pedidos

#### SalesOrder.php
- Cambió campo `status` por `order_status_id` en `fillable`
- Nueva relación `orderStatus()` para acceder al estado actual
- Se reemplazó `currentStatus()` (que usaba slug) por `orderStatus()` (que usa foreign key)

### 3. Backend (API)

#### OrderStatusController.php
- Agregada validación de `color` en `validateRequest()` con regex para validar formato hex (#RRGGBB)

### 4. Frontend (Vue.js)

#### OrderStatusForm.vue
- Agregado campo de color con selector visual (input color + input text)
- Preview del color seleccionado
- Carga/guardado del color al editar

#### OrderForm.vue
- Cambio de `form.status` a `form.order_status_id`
- Nueva computed property `currentStatus` que busca el objeto de estado completo
- Actualización de todas las comparaciones de estado para usar `currentStatus`
- Display del estado con color dinámico en lugar de clases fijas
- Mostrar historial de cambios de estado con indicador de color
- Mensaje "No status assigned" cuando no hay estado

#### OrderList.vue
- Actualización del template `#status` para mostrar el color del estado
- Display con `item.order_status.color` en lugar de clases predefinidas

## Pasos para Ejecutar

### 1. Ejecutar migraciones
```bash
php artisan migrate
```

### 2. Ejecutar seeder (crear los estados base)
```bash
php artisan db:seed --class=OrderStatusSeeder
```

O si tienes un DatabaseSeeder que lo llama automáticamente:
```bash
php artisan db:seed
```

### 3. (Opcional) Rollback si necesitas revertir
```bash
php artisan migrate:rollback --step=2
```

## Consideraciones Importantes

1. **Datos Existentes**: Si ya tienes pedidos en la BD, necesitarás migrar los datos del campo `status` (enum) al campo `order_status_id` (FK) usando seeders o migrations adicionales.

2. **API Response**: Asegúrate de que cuando la API devuelve ordenes, incluya la relación eager-loaded:
   ```php
   // En el controller o en la query
   ->with('orderStatus')
   ```

3. **Compatibilidad**: El código frontend ahora espera `item.order_status.color` en lugar de `item.status`. Verifica que tu API devuelva la estructura correcta.

4. **Validación de Color**: El color debe ser un código hexadecimal válido (ej. #FF5733, #000000, etc.)

## Campos de Base de Datos

### order_statuses
- `id`: int (PK)
- `name`: string
- `slug`: string (unique)
- `description`: text
- `color`: string(7) [código hexadecimal]
- `created_at`, `updated_at`: timestamps

### sales_orders
- `status`: ~~enum~~ → `order_status_id`: bigint (FK a order_statuses)
