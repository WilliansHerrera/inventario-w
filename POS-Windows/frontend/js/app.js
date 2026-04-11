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
        barcodeBuffer: '',
        lastKeyTime: 0,
        
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

            // Escáner Global: Escuchar teclas en toda la ventana
            window.addEventListener('keydown', (e) => {
                // Si el foco está en un input o textarea (que no sea el de búsqueda), dejar que fluya normal
                const active = document.activeElement;
                if (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA') {
                    // Si es el input de búsqueda, procesar normalmente (ya tiene su binding)
                    if (active.getAttribute('x-model') === 'searchQuery') return;
                }

                const currentTime = Date.now();
                const diff = currentTime - this.lastKeyTime;
                this.lastKeyTime = currentTime;

                // Si se presiona Enter, intentar procesar el buffer
                if (e.key === 'Enter') {
                    if (this.barcodeBuffer.length > 2) {
                        this.processGlobalBarcode(this.barcodeBuffer);
                        this.barcodeBuffer = '';
                    }
                    return;
                }

                // Detectar velocidad de escaneo (típicamente < 50ms entre teclas)
                // O si el buffer ya tiene algo, seguir acumulando
                if (diff < 50 || this.barcodeBuffer.length > 0) {
                    if (e.key.length === 1) { // Solo caracteres imprimibles
                        this.barcodeBuffer += e.key;
                        
                        // Limpiar buffer si pasa mucho tiempo sin actividad (ej. 500ms)
                        clearTimeout(this.barcodeTimeout);
                        this.barcodeTimeout = setTimeout(() => {
                            this.barcodeBuffer = '';
                        }, 500);
                    }
                } else {
                    // Si el tiempo es lento, probablemente sea un humano, resetear buffer
                    this.barcodeBuffer = '';
                }
            });

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
            const q = this.searchQuery.toLowerCase().trim();
            this.filteredProducts = this.products.filter(p => 
                p.nombre.toLowerCase().includes(q) || 
                p.sku.toLowerCase().includes(q) || 
                (p.codigo_barra && p.codigo_barra.toLowerCase().includes(q))
            );
        },

        processGlobalBarcode(code) {
            const q = code.toLowerCase().trim();
            const product = this.products.find(p => 
                (p.codigo_barra && p.codigo_barra.toLowerCase() === q) || 
                (p.sku && p.sku.toLowerCase() === q)
            );

            if (product) {
                this.addToCart(product);
                // Opcional: Sonido de éxito o feedback visual
                console.log("Escaneado:", product.nombre);
            } else {
                console.warn("Producto no encontrado:", q);
            }
        },

        handleBarcode() {
            const q = this.searchQuery.trim().toLowerCase();
            if (!q) return;

            // 1. Prioridad: Coincidencia exacta por Código de Barras o SKU
            const exactMatch = this.products.find(p => 
                (p.codigo_barra && p.codigo_barra.toLowerCase() === q) || 
                (p.sku && p.sku.toLowerCase() === q)
            );

            if (exactMatch) {
                this.addToCart(exactMatch);
                this.searchQuery = '';
                this.search();
                return;
            }

            // 2. Si no hay coincidencia exacta pero solo quedó un producto en el filtro
            if (this.filteredProducts.length === 1) {
                this.addToCart(this.filteredProducts[0]);
                this.searchQuery = '';
                this.search();
            }
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

        async generateSignature(method, path, body = '') {
            const timestamp = Math.floor(Date.now() / 1000).toString();
            const message = timestamp + method.toUpperCase() + path + body;
            
            const encoder = new TextEncoder();
            const keyData = encoder.encode(this.config.SYNC_TOKEN);
            const messageData = encoder.encode(message);

            const cryptoKey = await crypto.subtle.importKey(
                'raw', 
                keyData, 
                { name: 'HMAC', hash: 'SHA-256' }, 
                false, 
                ['sign']
            );

            const signatureBuffer = await crypto.subtle.sign(
                'HMAC', 
                cryptoKey, 
                messageData
            );

            // Convert buffer to hex string
            const hashArray = Array.from(new Uint8Array(signatureBuffer));
            const signatureHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

            return { signature: signatureHex, timestamp };
        },

        async forceSync() {
            if (!this.isOnline || this.isSyncing || !this.config.SYNC_TOKEN) return;
            this.isSyncing = true;
            
            try {
                const path = `/api/v1/sync/products`;
                const { signature, timestamp } = await this.generateSignature('GET', path);

                const res = await fetch(`${this.config.API_BASE}/sync/products?caja_id=${this.config.CAJA_ID}`, {
                    headers: {
                        'X-Sync-ID': this.config.LOCAL_ID,
                        'X-Sync-Timestamp': timestamp,
                        'X-Sync-Signature': signature,
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

                const body = JSON.stringify({ sales: salesToUpload });
                const path = `/api/v1/sync/sales`;
                const { signature, timestamp } = await this.generateSignature('POST', path, body);

                const res = await fetch(`${this.config.API_BASE}/sync/sales`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Sync-ID': this.config.LOCAL_ID,
                        'X-Sync-Timestamp': timestamp,
                        'X-Sync-Signature': signature,
                        'Accept': 'application/json'
                    },
                    body: body
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
