<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión de Etiquetas Premium</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --bg-page: #f8fafc;
            --text-main: #1e293b;
            --label-bg: #fff;
        }

        @page {
            margin: 0;
            size: auto;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-page);
            color: var(--text-main);
        }

        /* Dashboard UI - Hidden on Print */
        .dashboard {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .dashboard-content {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .btn-print {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            font-size: 1rem;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        .btn-print:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .stats {
            display: flex;
            gap: 1.5rem;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        .stat-value {
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Label Grid */
        .preview-area {
            padding: 3rem 1rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* The Premium Label Design (Vignette) */
        .vignette {
            background: var(--label-bg);
            width: 300px; /* Base for 80mm approx */
            padding: 12px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        .vignette-header {
            width: 100%;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .vignette-name {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 8px 0;
            text-align: center;
            line-height: 1.2;
            height: 34px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .barcode-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .vignette-footer {
            width: 100%;
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 4px;
            border-top: 1px dashed #f1f5f9;
        }

        .vignette-sku {
            font-size: 10px;
            color: #94a3b8;
            font-family: monospace;
        }

        .vignette-price {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Controls per label (Hidden on Print) */
        .vignette-controls {
            position: absolute;
            top: -15px;
            right: -15px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 2rem;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .qty-input {
            width: 40px;
            border: none;
            background: #f1f5f9;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
        }

        /* Print Specifics */
        @media print {
            .dashboard, .vignette-controls {
                display: none !important;
            }
            body {
                background: white;
            }
            .preview-area {
                padding: 0;
                display: block;
            }
            .vignette {
                box-shadow: none;
                border: none;
                page-break-after: always;
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 10mm; /* Extra padding for thermal margins */
            }
        }
    </style>
</head>
<body>

    <header class="dashboard">
        <div class="dashboard-content">
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-label">Productos</span>
                    <span class="stat-value" id="stats-products">{{ $productos->count() }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Total Etiquetas</span>
                    <span class="stat-value text-primary" id="stats-total">0</span>
                </div>
            </div>

            <div style="flex: 1; text-align: center;">
                <h1 style="margin:0; font-size: 1.2rem; font-weight: 800; color: var(--primary);">PRINT DASHBOARD</h1>
                <p style="margin:0; font-size: 0.8rem; color: #64748b;">Ajusta las cantidades y presiona imprimir</p>
            </div>

            <button onclick="window.print()" class="btn-print">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-2 4H8a2 2 0 01-2-2v-3h12v3a2 2 0 01-2 2z"></path></svg>
                IMPRIMIR AHORA
            </button>
        </div>
    </header>

    <div class="preview-area" id="print-grid">
        @foreach($productos as $producto)
            @for($i = 0; $i < ($producto->print_quantity ?? 1); $i++)
            <div class="vignette" data-product-id="{{ $producto->id }}">
                <!-- On screen controls -->
                <div class="vignette-controls">
                    <span style="font-size: 10px; color: #94a3b8">Copias</span>
                    <input type="number" 
                           class="qty-input product-qty" 
                           data-id="{{ $producto->id }}" 
                           value="{{ $producto->print_quantity ?? 1 }}" 
                           min="0"
                           onchange="updateQuantities()">
                </div>

                <div class="vignette-header">{{ $store_name }}</div>
                <h2 class="vignette-name">{{ $producto->nombre }}</h2>
                
                <div class="barcode-container">
                    <svg class="barcode-item" 
                         data-code="{{ $producto->codigo_barra }}"
                         id="barcode-{{ $producto->id }}-{{ $i }}"></svg>
                </div>

                <div class="vignette-footer">
                    <span class="vignette-sku">{{ $producto->sku }}</span>
                    <span class="vignette-price">{{ format_currency($producto->precio_venta) }}</span>
                </div>
            </div>
            @endfor
        @endforeach
    </div>

    <script>
        function renderBarcodes() {
            document.querySelectorAll('.barcode-item').forEach(function(el) {
                JsBarcode(el, el.getAttribute('data-code'), {
                    format: "CODE128",
                    width: 2,
                    height: 60,
                    displayValue: true,
                    fontSize: 12,
                    margin: 5,
                    background: "transparent"
                });
            });
            calculateStats();
        }

        function calculateStats() {
            let total = 0;
            document.querySelectorAll('.vignette').forEach(el => {
                if(window.getComputedStyle(el).display !== 'none') total++;
            });
            document.getElementById('stats-total').innerText = total;
        }

        function updateQuantities() {
            // Esta función es un poco "truco" para la demo: 
            // En una app real redibujaríamos el DOM. 
            // Para esta versión, si el usuario cambia el input, recargamos con el parámetro quantity si es solo uno, 
            // o simplemente notificamos que para cambios masivos use el dashboard.
            
            // Si solo hay un tipo de producto, podemos recargar
            const productIds = [...new Set([...document.querySelectorAll('.product-qty')].map(i => i.dataset.id))];
            if(productIds.length === 1) {
                const qty = document.querySelector('.product-qty').value;
                if(qty == 0) return;
                const url = new URL(window.location.href);
                url.searchParams.set('quantity', qty);
                window.location.href = url.toString();
            } else {
                alert("Para imprimir múltiples cantidades de diferentes productos, ajusta las selecciones en el catálogo. ¡Pronto añadiremos edición en vivo aquí!");
            }
        }

        window.onload = renderBarcodes;
    </script>
</body>
</html>
