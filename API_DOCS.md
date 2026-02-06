# EcomERP REST API Documentation

Base URL: `http://tu-dominio/api/v1`

## Authentication

### Login
Valida credenciales y devuelve un token de acceso.

- **URL**: `/login`
- **Method**: `POST`
- **Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```

### Register
Registra un nuevo usuario.

- **URL**: `/register`
- **Method**: `POST`
- **Body**:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
  }
  ```

## Products

### List Products
Obtiene lista paginada de productos.

- **URL**: `/products`
- **Method**: `GET`
- **Query Params**:
  - `page`: Número de página
  - `search`: Término de búsqueda
  - `category_id`: Filtrar por categoría

### Product Details
Obtiene detalles de un producto específico.

- **URL**: `/products/{id}`
- **Method**: `GET`

## Orders

### Create Order
Crea una nueva orden de venta.

- **URL**: `/orders`
- **Method**: `POST`
- **Body**:
  ```json
  {
    "customer_id": 1,
    "sales_channel_id": 2, // Mobile App
    "warehouse_id": 1,
    "items": [
      {
        "product_id": 10,
        "quantity": 2,
        "unit_price": 50.00
      }
    ]
  }
  ```

## Customers

### Get Customer Profile
Obtiene datos del cliente autenticado.

- **URL**: `/user`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer <token>`

### List Addresses
Obtiene direcciones guardadas del cliente.

- **URL**: `/customers/{customerId}/addresses`
- **Method**: `GET`
