<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Código de Barras - {{ $producto->nombre }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            margin: 0;
            size: auto;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: white;
            text-align: center;
        }
        .container {
            border: 1px dashed #ccc;
            padding: 15px;
            display: inline-block;
        }
        h2 {
            margin: 0 0 5px 0;
            font-size: 14px;
            text-transform: uppercase;
        }
        p {
            margin: 5px 0 0 0;
            font-size: 10px;
            font-weight: bold;
        }
        svg {
            max-width: 100%;
        }
        @media print {
            .no-print {
                display: none;
            }
            .container {
                border: none;
            }
        }
        .btn-print {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">IMPRIMIR ETIQUETA</button>
        <p style="margin-bottom: 20px; font-weight: normal; color: #666;">Optimizado para impresoras de etiquetas y tickets.</p>
    </div>

    <div class="container">
        <h2>{{ $producto->nombre }}</h2>
        <svg id="barcode"></svg>
        <p>SKU: {{ $producto->sku }}</p>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $producto->codigo_barra }}", {
            format: "CODE128",
            width: 2,
            height: 80,
            displayValue: true,
            fontSize: 14,
            margin: 10
        });

        // Opcional: Auto-imprimir al cargar si se desea
        // window.onload = () => { setTimeout(() => window.print(), 500); };
    </script>
</body>
</html>
