function posApp() {
    return {
        db: null,
        isOnline: navigator.onLine,
        isSyncing: false,
        showSuccess: false,
        showSettings: false,
        searchQuery: '',
        products: [],
        filteredProducts: [],
        cart: [],
        
        // Configuración persistente
        config: {
            API_BASE: 'http://localhost:8000/api/v1',
            SYNC_TOKEN: '',
            LOCAL_ID: 1,
            CAJA_ID: 1
        },

        async init() {
            // Cargar configuración guardada
            const savedConfig = localStorage.getItem('pos_config');
            if (savedConfig) {
                this.config = JSON.parse(savedConfig);
            }

            window.addEventListener('online', () => this.isOnline = true);
            window.addEventListener('offline', () => this.isOnline = false);

            try {
                // Inicializar SQLite (Tauri Plugin SQL)
                const Database = window.__TAURI__.sql;
                this.db = await Database.load("pos.db");
                
                await this.db.execute(`
                    CREATE TABLE IF NOT EXISTS products (
                        id INTEGER PRIMARY KEY,
                        nombre TEXT,
                        sku TEXT,
                        codigo_barra TEXT,
                        precio_venta REAL,
                        stock INTEGER,
                        imagen TEXT,
                        locale_id INTEGER
                    )
                `);

                await this.db.execute(`
                    CREATE TABLE IF NOT EXISTS pending_sales (
                        local_uuid TEXT PRIMARY KEY,
                        caja_id INTEGER,
                        user_id INTEGER,
                        metodo_pago TEXT,
                        total REAL,
                        items TEXT,
                        created_at TEXT
                    )
                `);

                await this.loadLocalProducts();
                
                // Sincronización automática de fondo
                setInterval(() => this.backgroundSync(), 30000); // Cada 30 seg
                
                if (this.isOnline && this.config.SYNC_TOKEN) {
                    await this.forceSync();
                }

            } catch (e) {
                console.error("Error inicializando POS:", e);
                this.products = []; 
            }
        },

        saveSettings() {
            localStorage.setItem('pos_config', JSON.stringify(this.config));
            this.showSettings = false;
            // Intentar sincronizar después de guardar
            if (this.isOnline && this.config.SYNC_TOKEN) {
                this.forceSync();
            }
        },

        async loadLocalProducts() {
            if (!this.db) return;
            this.products = await this.db.select("SELECT * FROM products");
            this.filteredProducts = [...this.products];
        },

        search() {
            const q = this.searchQuery.toLowerCase();
            this.filteredProducts = this.products.filter(p => 
                p.nombre.toLowerCase().includes(q) || 
                p.sku.toLowerCase().includes(q) || 
                (p.codigo_barra && p.codigo_barra.includes(q))
            );
        },

        addToCart(p) {
            const existing = this.cart.find(i => i.id === p.id);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({ ...p, qty: 1 });
            }
        },

        updateQty(idx, delta) {
            this.cart[idx].qty += delta;
            if (this.cart[idx].qty <= 0) {
                this.cart.splice(idx, 1);
            }
        },

        total() {
            return this.cart.reduce((sum, item) => sum + (item.precio_venta * item.qty), 0);
        },

        async processPayment() {
            if (this.cart.length === 0) return;

            const sale = {
                local_uuid: crypto.randomUUID(),
                caja_id: this.config.CAJA_ID,
                metodo_pago: 'efectivo',
                total: this.total(),
                items: JSON.stringify(this.cart.map(i => ({ id: i.id, qty: i.qty, price: i.precio_venta }))),
                created_at: new Date().toISOString()
            };

            // 1. Guardar localmente
            if (this.db) {
                await this.db.execute(
                    "INSERT INTO pending_sales (local_uuid, caja_id, metodo_pago, total, items, created_at) VALUES ($1, $2, $3, $4, $5, $6)",
                    [sale.local_uuid, sale.caja_id, sale.metodo_pago, sale.total, sale.items, sale.created_at]
                );
            }

            // 2. Mostrar éxito
            this.cart = [];
            this.showSuccess = true;

            // 3. Intentar subir inmediatamente si hay internet
            if (this.isOnline && this.config.SYNC_TOKEN) {
                this.backgroundSync();
            }
        },

        async forceSync() {
            if (!this.isOnline || this.isSyncing || !this.config.SYNC_TOKEN) return;
            this.isSyncing = true;
            
            try {
                // Descargar productos actualizados
                const res = await fetch(`${this.config.API_BASE}/sync/products?locale_id=${this.config.LOCAL_ID}&caja_id=${this.config.CAJA_ID}`, {
                    headers: {
                        'X-Sync-Token': this.config.SYNC_TOKEN,
                        'Accept': 'application/json'
                    }
                });
                
                const result = await res.json();
                
                if (result.success && this.db) {
                    await this.db.execute("DELETE FROM products");
                    for (const p of result.data) {
                        await this.db.execute(
                            "INSERT INTO products (id, nombre, sku, codigo_barra, precio_venta, stock, imagen, locale_id) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)",
                            [p.id, p.nombre, p.sku, p.codigo_barra, p.precio_venta, p.stock, p.imagen, p.locale_id]
                        );
                    }
                    await this.loadLocalProducts();
                } else if (result.error) {
                    console.error("Sync error:", result.error);
                }
            } catch (e) {
                console.error("Sync products failed:", e);
            } finally {
                this.isSyncing = false;
            }
        },

        async backgroundSync() {
            if (!this.isOnline || this.isSyncing || !this.db || !this.config.SYNC_TOKEN) return;
            
            const pending = await this.db.select("SELECT * FROM pending_sales LIMIT 10");
            if (pending.length === 0) return;

            this.isSyncing = true;
            
            try {
                const salesToUpload = pending.map(s => ({
                    ...s,
                    items: JSON.parse(s.items)
                }));

                const res = await fetch(`${this.config.API_BASE}/sync/sales`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Sync-Token': this.config.SYNC_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ sales: salesToUpload })
                });

                const result = await res.json();
                if (result.success || result.synced_ids) {
                    // Limpiar ventas sincronizadas
                    for (const uuid of result.synced_ids) {
                        await this.db.execute("DELETE FROM pending_sales WHERE local_uuid = $1", [uuid]);
                    }
                }
            } catch (e) {
                console.error("Upload sync failed:", e);
            } finally {
                this.isSyncing = false;
            }
        }
    };
}
