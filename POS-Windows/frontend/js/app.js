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
            API_BASE: 'http://localhost/Inventario-w/public/api',
            SYNC_TOKEN: '',
            LOCAL_ID: null,
            CAJA_ID: null
        },
        availableCajas: [],
        importConfig: '',

        async init() {
            // Cargar configuración guardada
            const savedConfig = localStorage.getItem('pos_config');
            if (savedConfig) {
                this.config = JSON.parse(savedConfig);
            }
            const savedCajas = localStorage.getItem('pos_cajas');
            if (savedCajas) {
                this.availableCajas = JSON.parse(savedCajas);
            }

            window.addEventListener('online', () => this.isOnline = true);
            window.addEventListener('offline', () => this.isOnline = false);

            try {
                // Inicializar SQLite manualmente a nivel nativo (Vanilla JS sin Vite)
                const dbPath = "sqlite:pos.db";
                const { invoke } = window.__TAURI__.tauri;
                
                await invoke("plugin:sql|load", { db: dbPath });
                
                this.db = {
                    execute: async (query, values = []) => {
                        return await invoke("plugin:sql|execute", { db: dbPath, query, values });
                    },
                    select: async (query, values = []) => {
                        return await invoke("plugin:sql|select", { db: dbPath, query, values });
                    }
                };
                
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
                
                // Monitor Activo del Servidor (Heartbeat) y Sincronización
                this.checkServer();
                setInterval(() => this.checkServer(), 15000);
                setInterval(() => this.backgroundSync(), 30000);
                
                if (this.isOnline && this.config.SYNC_TOKEN && this.config.CAJA_ID) {
                    await this.forceSync();
                }

            } catch (e) {
                console.error("Error inicializando POS:", e);
                this.products = []; 
            }
        },

        saveSettings() {
            localStorage.setItem('pos_config', JSON.stringify(this.config));
            if (this.availableCajas.length > 0) {
                localStorage.setItem('pos_cajas', JSON.stringify(this.availableCajas));
            }
            this.showSettings = false;
            this.importConfig = '';
            // Intentar sincronizar después de guardar
            if (this.isOnline && this.config.SYNC_TOKEN && this.config.CAJA_ID) {
                this.forceSync();
            }
        },

        handleImport() {
            try {
                let jsonStr = this.importConfig.trim();
                
                // Si parece ser Base64 (no empieza con {)
                if (!jsonStr.startsWith('{')) {
                    const bin = atob(jsonStr);
                    const bytes = new Uint8Array(bin.length);
                    for (let i = 0; i < bin.length; i++) {
                        bytes[i] = bin.charCodeAt(i);
                    }
                    jsonStr = new TextDecoder('utf-8').decode(bytes);
                }

                const data = JSON.parse(jsonStr);
                
                if (data.api_url) this.config.API_BASE = data.api_url;
                if (data.sucursal && data.sucursal.sync_token) this.config.SYNC_TOKEN = data.sucursal.sync_token;
                if (data.sucursal && data.sucursal.id) this.config.LOCAL_ID = data.sucursal.id;
                
                if (data.cajas && Array.isArray(data.cajas)) {
                    this.availableCajas = data.cajas;
                    if (this.availableCajas.length > 0) {
                        this.config.CAJA_ID = this.availableCajas[0].id; // Forzar selección de la primera
                    }
                }
                
                // Trigger reactivo explícito si es necesario
                this.$forceUpdate && this.$forceUpdate();
                
            } catch (e) {
                console.error("Error importando Config:", e);
            }
        },

        async checkServer() {
            if (!this.config.API_BASE) return;
            try {
                // Hacemos una petición rápida Options a la base para ver si el servidor responde
                await fetch(this.config.API_BASE, { method: 'OPTIONS' });
                this.isOnline = navigator.onLine; // Si responde XAMPP Y tenemos WiFi = Online
                
                // Verificar actualizaciones silenciosamente
                this.checkAppUpdate();
            } catch (e) {
                this.isOnline = false; // Sin servidor local (XAMPP apagado)
            }
        },

        async checkAppUpdate() {
            try {
                const res = await fetch(`${this.config.API_BASE}/update/check`);
                if (res.status === 200) {
                    const data = await res.json();
                    const currentVersion = "1.0.0"; // Versión Hardcoded de la App actual
                    if (data.version && data.version !== currentVersion) {
                        console.log("Nueva versión disponible:", data.version);
                        // Opcional: alert("Hay una nueva versión disponible (" + data.version + "). Descárgala desde el panel.");
                    }
                }
            } catch (e) { /* Silencioso */ }
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
                        try {
                            await this.db.execute(
                                "INSERT INTO products (id, nombre, sku, codigo_barra, precio_venta, stock, imagen, locale_id) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)",
                                [
                                    p.id, 
                                    p.nombre || '', 
                                    p.sku || '', 
                                    p.codigo_barra || '', 
                                    p.precio_venta || 0, 
                                    p.stock || 0, 
                                    p.imagen || '', 
                                    p.locale_id
                                ]
                            );
                        } catch (sqlErr) {
                            console.error("SQL Error con producto ID " + p.id + ":", sqlErr);
                        }
                    }
                    await this.loadLocalProducts();
                } else if (result.error) {
                    alert("Error servidor al sincronizar: " + result.error);
                    console.error("Sync error:", result.error);
                } else {
                    alert("Respuesta inválida del servidor al descargar productos.");
                }
            } catch (e) {
                this.isOnline = false;
                alert("Fallo de red al conectar con el servidor: " + e.message);
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
