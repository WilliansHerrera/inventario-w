function posApp() {
    return {
        db: null,
        isOnline: navigator.onLine,
        isSyncing: false,
        isShiftOpen: false,
        shiftChecking: true,
        showShiftOpenModal: false,
        showShiftCloseModal: false,
        shiftOpenAmount: '',
        shiftCloseAmount: '',
        paymentMethod: 'efectivo',
        isExpress: false,
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
            CAJA_ID: null,
            PRINTER_NAME: '',
            IS_EXPRESS: false
        },
        availableCajas: [],
        systemPrinters: [],
        lastSale: null,
        importConfig: '',
        
        // Variables de Login
        users: [],
        currentUser: null,
        showLoginModal: true,
        loginPin: '',
        loginSelectedUser: null,


        async initDB() {
            if (this.db) return true;
            try {
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
                return true;
            } catch (err) {
                console.error("Error inicializando DB nativa:", err);
                return false;
            }
        },

        async init() {
            try {
                // Cargar configuración guardada

            const savedConfig = localStorage.getItem('pos_config');
            if (savedConfig) {
                this.config = JSON.parse(savedConfig);
            }
            const savedCajas = localStorage.getItem('pos_cajas');
            if (savedCajas) {
                this.availableCajas = JSON.parse(savedCajas);
            }
            const savedUsers = localStorage.getItem('pos_users');
            if (savedUsers) {
                this.users = JSON.parse(savedUsers);
            }


            window.addEventListener('online', () => this.isOnline = true);
            window.addEventListener('offline', () => this.isOnline = false);

            // Escáner Global
            window.addEventListener('keydown', (e) => {
                const active = document.activeElement;
                if (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA') {
                    if (active.getAttribute('x-model') === 'searchQuery') return;
                }

                const currentTime = Date.now();
                const diff = currentTime - this.lastKeyTime;
                this.lastKeyTime = currentTime;

                if (e.key === 'Enter') {
                    if (this.barcodeBuffer.length > 2) {
                        this.processGlobalBarcode(this.barcodeBuffer);
                        this.barcodeBuffer = '';
                    }
                    return;
                }

                if (diff < 50 || this.barcodeBuffer.length > 0) {
                    if (e.key.length === 1) {
                        this.barcodeBuffer += e.key;
                        clearTimeout(this.barcodeTimeout);
                        this.barcodeTimeout = setTimeout(() => {
                            this.barcodeBuffer = '';
                        }, 500);
                    }
                } else {
                    this.barcodeBuffer = '';
                }
            });

            // Inicializar Base de Datos nativa
            await this.initDB();
            if (this.db) {
                await this.loadLocalProducts();
            }



                
                // Cargar impresoras del sistema
                await this.refreshPrinters();

                // Escucha de F6 para Imprimir
                window.addEventListener('keydown', (e) => {
                    if (e.key === 'F6' && this.showSuccess && this.lastSale) {
                        this.printTicket();
                    }
                });

                // Monitor Activo del Servidor (Heartbeat) y Sincronización
                this.checkServer();
                this.checkTemplateUpdate();
                setInterval(() => this.checkServer(), 15000);
                setInterval(() => this.backgroundSync(), 30000);
                setInterval(() => this.checkTemplateUpdate(), 300000); // Cada 5 min

                
                if (this.isOnline && this.config.SYNC_TOKEN && this.config.CAJA_ID) {
                    await this.checkShiftStatus();
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
                this.checkShiftStatus();
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
                    
                    // Lógica Express: Autoselección de caja
                    const isExpress = data.settings && data.settings.cash_management_mode === 'express';
                    this.config.IS_EXPRESS = isExpress;
                    if (isExpress && this.availableCajas.length > 0) {
                        this.config.CAJA_ID = this.availableCajas[0].id;
                        console.log("Modo Express: Caja autoconfigurada:", this.availableCajas[0].nombre);
                    } else if (this.availableCajas.length === 1) {
                         this.config.CAJA_ID = this.availableCajas[0].id; // Fallback razonable
                    }
                }
                
                // Trigger reactivo explícito si es necesario
                this.$forceUpdate && this.$forceUpdate();
                if (this.config.CAJA_ID) {
                    this.checkShiftStatus();
                }
                
            } catch (e) {
                console.error("Error importando Config:", e);
            }
        },

        async refreshPrinters() {
            try {
                const { invoke } = window.__TAURI__.tauri;
                this.systemPrinters = await invoke('list_printers');
            } catch (e) {
                console.warn("No se pudieron listar las impresoras:", e);
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

        async checkShiftStatus() {
            if (this.config.IS_EXPRESS) {
                this.isShiftOpen = true;
                this.shiftChecking = false;
                return;
            }
            if (!this.config.CAJA_ID) {
                this.shiftChecking = false;
                return;
            }
            if (!this.isOnline) {
                this.shiftChecking = false;
                return; 
            }
            try {
                this.shiftChecking = true;
                const path = `/api/v1/sync/shift/status?caja_id=${this.config.CAJA_ID}`;
                const { signature, timestamp } = await this.generateSignature('GET', path);

                const res = await fetch(`${this.config.API_BASE}/sync/shift/status?caja_id=${this.config.CAJA_ID}`, {
                    headers: {
                        'X-Sync-ID': this.config.LOCAL_ID,
                        'X-Sync-Timestamp': timestamp,
                        'X-Sync-Signature': signature,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.isShiftOpen = data.abierta;
                }
            } catch (e) {
                console.error("Error checking shift status", e);
            } finally {
                this.shiftChecking = false;
            }
        },

        async openShift() {
            if (!this.shiftOpenAmount || isNaN(this.shiftOpenAmount) || this.shiftOpenAmount < 0) {
                alert("Ingrese un monto válido");
                return;
            }
            if (!this.isOnline) {
                alert("Se requiere conexión para abrir la caja.");
                return; // Opcionalmente implementar apertura 100% offline aquí
            }
            try {
                const body = JSON.stringify({
                    caja_id: this.config.CAJA_ID,
                    monto_apertura: parseFloat(this.shiftOpenAmount)
                });
                const path = `/api/v1/sync/shift/open`;
                const { signature, timestamp } = await this.generateSignature('POST', path, body);

                const res = await fetch(`${this.config.API_BASE}/sync/shift/open`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Sync-ID': this.config.LOCAL_ID,
                        'X-Sync-Timestamp': timestamp,
                        'X-Sync-Signature': signature,
                        'Accept': 'application/json'
                    },
                    body
                });
                const data = await res.json();
                if (data.success) {
                    this.isShiftOpen = true;
                    this.showShiftOpenModal = false;
                    this.shiftOpenAmount = '';
                } else {
                    alert("Error al abrir caja: " + (data.error || 'Desconocido'));
                }
            } catch (e) {
                alert("Error de conexión al abrir caja.");
            }
        },

        async closeShift() {
            if (!this.shiftCloseAmount || isNaN(this.shiftCloseAmount) || this.shiftCloseAmount < 0) {
                alert("Ingrese un monto válido");
                return;
            }
            if (!this.isOnline) {
                alert("Se requiere conexión para cerrar la caja.");
                return;
            }
            try {
                const body = JSON.stringify({
                    caja_id: this.config.CAJA_ID,
                    monto_cierre: parseFloat(this.shiftCloseAmount)
                });
                const path = `/api/v1/sync/shift/close`;
                const { signature, timestamp } = await this.generateSignature('POST', path, body);

                const res = await fetch(`${this.config.API_BASE}/sync/shift/close`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Sync-ID': this.config.LOCAL_ID,
                        'X-Sync-Timestamp': timestamp,
                        'X-Sync-Signature': signature,
                        'Accept': 'application/json'
                    },
                    body
                });
                const data = await res.json();
                if (data.success) {
                    this.isShiftOpen = false;
                    this.showShiftCloseModal = false;
                    this.shiftCloseAmount = '';
                    alert("Caja cerrada correctamente. Ya puedes imprimir el reporte Z desde el panel web.");
                } else {
                    alert("Error al cerrar caja: " + (data.error || 'Desconocido'));
                }
            } catch (e) {
                alert("Error de conexión al cerrar caja.");
            }
        },

        async loadLocalProducts() {
            if (this.db) {
                try {
                    this.products = await this.db.select("SELECT * FROM products");
                } catch (e) {
                    console.warn("Error SQLite:", e);
                }
            }
            
            if (!this.products || this.products.length === 0) {
                const saved = localStorage.getItem('pos_products');
                if (saved) {
                    this.products = JSON.parse(saved);
                }
            }
            
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
            if (!this.isShiftOpen) return;
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
            if (!this.isShiftOpen) return;
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
            if (!this.isShiftOpen) return;
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
            if (!this.isShiftOpen || this.cart.length === 0) return;

            const sale = {
                local_uuid: crypto.randomUUID(),
                caja_id: this.config.CAJA_ID,
                metodo_pago: this.paymentMethod,
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

            // 1.5 Guardar para impresión inmediata
            this.lastSale = {
                total: sale.total,
                items: [...this.cart],
                date: sale.created_at,
                id: sale.local_uuid.substring(0, 8)
            };

            // 2. Mostrar éxito
            this.cart = [];
            this.showSuccess = true;

            // Auto-imprimir si hay impresora configurada
            if (this.config.PRINTER_NAME) {
                this.printTicket();
            }

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

        async checkTemplateUpdate() {
            if (!this.isOnline || !this.config.SYNC_TOKEN) return;
            try {
                const path = `/api/v1/sync/template`;
                const { signature, timestamp } = await this.generateSignature('GET', path);

                const res = await fetch(`${this.config.API_BASE}/sync/template`, {
                    headers: {
                        'X-Sync-ID': this.config.LOCAL_ID,
                        'X-Sync-Timestamp': timestamp,
                        'X-Sync-Signature': signature,
                        'Accept': 'application/json'
                    }
                });
                
                if (res.status === 200) {
                    const data = await res.json();
                    if (data.success && data.html) {
                        const localTemplate = localStorage.getItem('pos_template');
                        if (localTemplate !== data.html) {
                            localStorage.setItem('pos_template', data.html);
                            console.log("Nueva plantilla descargada.");
                        }
                    }
                }
            } catch (e) {
                console.warn("No se pudo verificar la actualización de la plantilla:", e);
            }
        },

        async forceSync() {
            if (!this.isOnline || this.isSyncing || !this.config.SYNC_TOKEN) return;
            this.isSyncing = true;
            await this.initDB();

            
            try {
                const path = `/api/v1/sync/products`;
                const { signature, timestamp } = await this.generateSignature('GET', path);

                const res = await fetch(`${this.config.API_BASE}/sync/products?caja_id=${this.config.CAJA_ID}`, {
                    headers: {
                        'X-Sync-ID': this.config.LOCAL_ID,
                        'X-Sync-Timestamp': timestamp,
                        'X-Sync-Signature': signature,
                        'X-Sync-Token': this.config.SYNC_TOKEN,
                        'Accept': 'application/json'

                    }
                });
                
                const result = await res.json();
                
                if (result.success) {
                    if (this.db) {
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
                    }

                    // Respaldar productos en localStorage (Offline real)
                    if (result.data && Array.isArray(result.data)) {
                        localStorage.setItem('pos_products', JSON.stringify(result.data));
                    }




                    // Sincronización Automática de Ajustes y Cajas
                    if (result.settings) {
                        const isExpress = result.settings.cash_management_mode === 'express';
                        this.config.IS_EXPRESS = isExpress;
                        if (isExpress) {
                            this.isShiftOpen = true;
                        }
                    }
                    if (result.cajas && Array.isArray(result.cajas)) {
                        this.availableCajas = result.cajas;
                        localStorage.setItem('pos_cajas', JSON.stringify(this.availableCajas));
                        
                        if (this.config.IS_EXPRESS && this.availableCajas.length > 0) {
                            this.config.CAJA_ID = this.availableCajas[0].id;
                        }
                    }
                    if (result.users && Array.isArray(result.users)) {
                        this.users = result.users;
                        localStorage.setItem('pos_users', JSON.stringify(this.users));
                    }
                    if (result.locale_nombre) {
                        this.config.LOCALE_NOMBRE = result.locale_nombre;
                    }
                    localStorage.setItem('pos_config', JSON.stringify(this.config));
                    
                    alert("¡Datos sincronizados con éxito!");


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
        },

        async printTicket() {
            if (!this.config.PRINTER_NAME || !this.lastSale) return;

            try {
                const { invoke } = window.__TAURI__.tauri;
                
                // Comandos ESC/POS Básicos
                const ESC = 0x1B;
                const GS = 0x1D;
                
                let bytes = [
                    ESC, 0x40, // Initialize
                    ESC, 0x61, 0x01, // Center
                    ESC, 0x45, 0x01, // Bold ON
                    ...this.stringToBytes("INVENTARIO W\n"),
                    ESC, 0x45, 0x00, // Bold OFF
                    ...this.stringToBytes("Terminal de Ventas\n"),
                    ...this.stringToBytes("--------------------------------\n"),
                    ESC, 0x61, 0x00, // Left
                    ...this.stringToBytes(`Ticket: ${this.lastSale.id}\n`),
                    ...this.stringToBytes(`Fecha: ${new Date(this.lastSale.date).toLocaleString()}\n`),
                    ...this.stringToBytes("--------------------------------\n"),
                ];

                // Items
                this.lastSale.items.forEach(item => {
                    const line = `${item.qty} x ${item.nombre.substring(0, 20)}\n`;
                    const price = `   $${(item.precio_venta * item.qty).toFixed(2)}\n`;
                    bytes.push(...this.stringToBytes(line));
                    bytes.push(...this.stringToBytes(price));
                });

                bytes.push(
                    ...this.stringToBytes("--------------------------------\n"),
                    ESC, 0x61, 0x02, // Right
                    ESC, 0x45, 0x01, // Bold ON
                    ...this.stringToBytes(`TOTAL: $${this.lastSale.total.toFixed(2)}\n`),
                    ESC, 0x45, 0x00, // Bold OFF
                    0x0A, 0x0A, // Feed
                    ESC, 0x61, 0x01, // Center
                    ...this.stringToBytes("¡Gracias por su compra!\n"),
                    0x0A, 0x0A, 0x0A, 0x0A, 0x0A, // Space for tear
                    GS, 0x56, 0x41, 0x10 // Partial Cut
                );

                const result = await invoke('print_escpos', { 
                    printerName: this.config.PRINTER_NAME, 
                    content: bytes 
                });
                
                console.log("Print result:", result);
            } catch (e) {
                console.error("Error al imprimir:", e);
                alert("Fallo al imprimir ticket: " + e);
            }
        },

        login() {
            if (!this.loginSelectedUser) {
                alert("Selecciona un usuario.");
                return;
            }
            const user = this.users.find(u => u.id == this.loginSelectedUser);
            if (user && user.pos_pin === this.loginPin) {
                this.currentUser = user;
                this.showLoginModal = false;
                this.loginPin = '';
                console.log(`Usuario ${user.name} autenticado.`);
            } else {
                alert("PIN Incorrecto.");
                this.loginPin = '';
            }
        },

        logout() {
            this.currentUser = null;
            this.showLoginModal = true;
            this.loginPin = '';
            this.loginSelectedUser = null;
        },

        stringToBytes(str) {

            // Conversión simple a bytes (ASCII/Latin1)
            const bytes = [];
            for (let i = 0; i < str.length; i++) {
                let code = str.charCodeAt(i);
                // Mapeo básico para tildes y caracteres especiales (Codepage 850 aproximado o strip)
                if (code > 127) code = 63; // Reemplazar con '?' si no es ASCII
                bytes.push(code);
            }
            return bytes;
        },

        quickBills() {
            const t = this.total();
            if (t <= 0) return [];
            const denominations = [1, 5, 10, 20, 50, 100, 200, 500, 1000];
            const bills = new Set();
            for (const d of denominations) {
                const rounded = Math.ceil(t / d) * d;
                if (rounded >= t) bills.add(rounded);
                if (bills.size >= 5) break;
            }
            return [...bills].sort((a, b) => a - b).slice(0, 4);
        },

        getVueltoBreakdown(amount) {
            if (!amount || amount <= 0) return [];
            let remainingCents = Math.round(parseFloat(amount) * 100);
            let breakdown = [];
            const sortedDenoms = [100, 50, 20, 10, 5, 1, 0.25, 0.10, 0.05, 0.01].sort((a,b) => b - a);
            for (const d of sortedDenoms) {
                const dCents = Math.round(parseFloat(d) * 100);
                const count = Math.floor(remainingCents / dCents);
                if (count > 0) {
                    breakdown.push({ label: this.fmt(d), val: d, count: count, isCoin: d < 1 });
                    remainingCents -= count * dCents;
                }
            }
            return breakdown;
        },

        fmt(v) {
            return '$ ' + parseFloat(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        
        saveSettings() {
            localStorage.setItem('pos_config', JSON.stringify(this.config));
            this.showSettings = false;
            alert("Configuración guardada. Iniciando sincronización...");
            this.forceSync();
        },

        handleImport() {
            if (!this.importConfig) return;
            try {
                let clean = this.importConfig.trim();
                let parsed = {};
                if (clean.startsWith('{')) {
                    parsed = JSON.parse(clean);
                } else {
                    const decoded = atob(clean);
                    parsed = JSON.parse(decoded);
                }
                
                if (parsed.api_url) this.config.API_BASE = parsed.api_url;
                if (parsed.sucursal) {
                    if (parsed.sucursal.sync_token) this.config.SYNC_TOKEN = parsed.sucursal.sync_token;
                    if (parsed.sucursal.id) this.config.LOCAL_ID = parsed.sucursal.id;
                    if (parsed.sucursal.nombre) this.config.LOCALE_NOMBRE = parsed.sucursal.nombre;
                }

                localStorage.setItem('pos_config', JSON.stringify(this.config));
                this.importConfig = '';
                alert("Configuración importada con éxito. Iniciando sincronización...");
                this.forceSync();
            } catch (err) {
                alert("Error al importar configuración: " + err.message);
            }
        },


        handleKey(e) {

            if (e.key === 'F5') { e.preventDefault(); this.processPayment(); }
            if (e.key === 'Escape') {
                if (this.showSuccess) { this.showSuccess = false; }
                else if (this.showSettings) { this.showSettings = false; }
                else { this.searchQuery = ''; this.filteredProducts = [...this.products]; }
            }
        }
    };
}

