---
name: offline-first-sync
description: Manual de estrategias para la sincronización entre SQLite (Local) y MySQL (Laravel API), manejo de colas de envío y actualización de inventario local.
---

# Sincronización Offline-First (SQLite <-> Laravel API)

Esta skill define cómo manejar la sincronización de datos en una arquitectura de Punto de Venta (POS) que debe operar sin internet.

## 1. Estrategia de Base de Datos (Dual-Side)
- **Local (SQLite):** Esquema simplificado para lectura rápida de productos y guardado de ventas pendientes.
- **Servidor (MySQL):** Fuente de verdad central (Laravel + MoonShine).

### Tablas Clave en SQLite
- `products`: Réplica de productos con stock local.
- `pending_sales`: Cola de ventas realizadas localmente sin sincronizar.
- `sync_log`: Registro de última sincronización exitosa (Unix timestamp).

## 2. Lógica de Sincronización
### Descarga (Server -> Client)
1. El cliente pide `/api/sync/products?since=TIMESTAMP`.
2. El servidor responde sólo con los productos modificados (delta sync).
3. El cliente actualiza su SQLite.

### Subida (Client -> Server)
1. El cliente verifica periódicamente si hay internet (`navigator.onLine` o `ping`).
2. Envía `pending_sales` en bloques (batches) a `/api/sync/sales`.
3. Si el servidor confirma (200 OK), el cliente marca las ventas como sincronizadas o las elimina.

## 3. Manejo de Conflictos
- **Stock:** El servidor siempre es el juez final durante el procesamiento de la venta en la nube.
- **Cierre de Caja:** Las ventas locales deben agruparse por ID de caja para que el servidor pueda calcular los turnos correctamente.

## 4. Middleware de Sincronización (Frontend)
Implementar una capa intermedia que decida si enviar la petición directamente al servidor (si hay red) o encolarla en SQLite (si no hay red), garantizando una experiencia fluida al cajero.
