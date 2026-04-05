<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; background: #fff; padding: 12px; width: 300px; }
        .title    { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 4px; }
        .sub      { text-align: center; color: #555; margin-bottom: 8px; font-size: 11px; }
        hr        { border: none; border-top: 1px dashed #aaa; margin: 8px 0; }
        table     { width: 100%; border-collapse: collapse; }
        th        { text-align: left; font-weight: bold; font-size: 11px; padding: 2px 0; }
        td        { padding: 2px 0; vertical-align: top; }
        .right    { text-align: right; }
        .total-row{ font-weight: bold; font-size: 13px; }
        .footer   { text-align: center; margin-top: 12px; font-size: 10px; color: #666; }
        @media print {
            @page { size: 80mm auto; margin: 4mm; }
            body { width: auto; }
        }
    </style>
</head>
<body>

    <p class="title">{{ get_global_setting('company_name', 'Mi Empresa') }}</p>
    <p class="sub">Punto de Venta</p>

    <hr>

    <table>
        <tr><td>Ticket:</td><td class="right">#{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><td>Caja:</td><td class="right">{{ $venta->caja->nombre ?? '—' }}</td></tr>
        <tr><td>Fecha:</td><td class="right">{{ $venta->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Pago:</td><td class="right">{{ ucfirst($venta->metodo_pago) }}</td></tr>
    </table>

    <hr>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="right">Cant.</th>
                <th class="right">Precio</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $d)
            <tr>
                <td style="max-width:120px; word-break:break-word;">{{ $d->producto->nombre }}</td>
                <td class="right">{{ $d->cantidad }}</td>
                <td class="right">{{ format_currency($d->precio_unitario) }}</td>
                <td class="right">{{ format_currency($d->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <table>
        <tr class="total-row">
            <td>TOTAL</td>
            <td class="right">{{ format_currency($venta->total) }}</td>
        </tr>
    </table>

    <p class="footer">Gracias por su compra.<br>{{ now()->format('d/m/Y H:i:s') }}</p>

    <script>window.onload = () => window.print();</script>
</body>
</html>
