<?php
// View/Inicio/verPedido.php
session_start();

if (!isset($_SESSION["ConsecutivoUsuario"])) {
    header("Location: InicioSesion.php");
    exit;
}

$order  = isset($_SESSION['order_view']) && is_array($_SESSION['order_view']) ? $_SESSION['order_view'] : null;
$items  = isset($_SESSION['order_details']) && is_array($_SESSION['order_details']) ? $_SESSION['order_details'] : [];

$isAdmin = (isset($_SESSION["ConsecutivoPerfil"]) && (int)$_SESSION["ConsecutivoPerfil"] === 1);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

if (!$order) {
    // si entraron directo sin pasar por controller
    header("Location: " . ($isAdmin ? "../../Controller/OrderController.php?action=list" : "../../Controller/OrderController.php?action=my"));
    exit;
}

$id = (int)($order['id'] ?? 0);
$cliente = $order['nombreUsuario'] ?? '';
$fecha = $order['fecha'] ?? '';
$estado = $order['estado'] ?? 'Pendiente';
$total = isset($order['total']) ? (float)$order['total'] : 0;

$entrega = $order['entrega_tipo'] ?? 'Tienda';
$direccion = $order['direccion'] ?? '';

$chofer = $order['nombreConductor'] ?? '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido #<?php echo h($id); ?> | Detalle</title>

    <style>
        :root{
            --bg: #0b1220;
            --card: rgba(255,255,255,.04);
            --line: rgba(255,255,255,.10);
            --text: #fff;
            --muted: rgba(255,255,255,.75);
            --accent: #f4b000;
            --accent2: #ffd46a;
            --ok: #22c55e;
        }
        *{ box-sizing:border-box; }
        body{
            margin:0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: radial-gradient(1200px 600px at 20% -10%, rgba(244,176,0,.22), transparent 60%),
                        radial-gradient(900px 500px at 85% 10%, rgba(255,212,106,.14), transparent 55%),
                        var(--bg);
            color: var(--text);
        }
        .container{
            width: min(1050px, 92vw);
            margin: 26px auto 60px;
        }
        .topbar{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            margin-bottom: 18px;
        }
        .brand{
            display:flex;
            align-items:center;
            gap:12px;
        }
        .brand img{
            width: 46px;
            height: 46px;
            object-fit: contain;
            border-radius: 12px;
            background: rgba(255,255,255,.08);
            padding: 8px;
            border: 1px solid var(--line);
        }
        .brand strong{ display:block; font-size: 16px; }
        .brand span{ display:block; font-size: 12.5px; color: var(--muted); margin-top:2px; }

        .actions{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            justify-content:flex-end;
        }
        a.btn, button.btn{
            appearance:none;
            border: 1px solid var(--line);
            background: rgba(255,255,255,.06);
            color: var(--text);
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration:none;
            cursor:pointer;
            font-weight:700;
            display:inline-flex;
            align-items:center;
            gap:8px;
        }
        a.btn:hover, button.btn:hover{ border-color: rgba(255,255,255,.22); }
        .btn.primary{
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none;
            color: #1a1200;
        }

        .grid{
            display:grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .card{
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow:hidden;
            box-shadow: 0 12px 30px rgba(0,0,0,.22);
        }
        .card .hd{
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
        }
        .card .hd h2{
            margin:0;
            font-size: 15px;
            letter-spacing: .2px;
        }

        .meta{
            padding: 14px 16px;
            display:grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 14px;
        }
        .kv{
            border: 1px solid var(--line);
            background: rgba(0,0,0,.18);
            border-radius: 14px;
            padding: 10px 12px;
        }
        .kv .k{
            font-size: 12px;
            color: var(--muted);
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.8px;
        }
        .kv .v{
            margin-top:4px;
            font-weight:800;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }
        th, td{
            padding: 12px 12px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }
        th{
            text-align:left;
            color: var(--muted);
            font-weight:800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .8px;
        }
        .right{ text-align:right; }
        .muted{ color: var(--muted); }

        .pill{
            display:inline-flex;
            align-items:center;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,.06);
            font-weight:800;
            font-size: 12px;
            white-space:nowrap;
        }
        .pill.pend{ border-color: rgba(244,176,0,.35); color: var(--accent2); background: rgba(244,176,0,.12); }
        .pill.proc{ border-color: rgba(255,255,255,.22); color: #fff; background: rgba(255,255,255,.08); }
        .pill.ent{ border-color: rgba(34,197,94,.35); color: #d9ffe8; background: rgba(34,197,94,.10); }

        @media (max-width: 820px){
            .meta{ grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="topbar">
        <div class="brand">
            <img src="../img/Logo_Empresa.png" alt="Logo">
            <div>
                <strong>Pedido #<?php echo h($id); ?></strong>
                <span>Detalle del pedido</span>
            </div>
        </div>
        <div class="actions">
            <?php if ($isAdmin): ?>
                <a class="btn" href="../../Controller/OrderController.php?action=list">Volver</a>
                <a class="btn primary" href="../../Controller/OrderController.php?action=list">Actualizar</a>
            <?php else: ?>
                <a class="btn" href="../../Controller/OrderController.php?action=my">Mis pedidos</a>
                <a class="btn primary" href="productos.php">Productos</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid">

        <div class="card">
            <div class="hd">
                <h2>Información del pedido</h2>
                <?php
                $pillClass = 'pend';
                if ($estado === 'En proceso') $pillClass = 'proc';
                if ($estado === 'Entregado') $pillClass = 'ent';
                ?>
                <span class="pill <?php echo h($pillClass); ?>"><?php echo h($estado); ?></span>
            </div>

            <div class="meta">
                <?php if ($isAdmin): ?>
                    <div class="kv">
                        <div class="k">Cliente</div>
                        <div class="v"><?php echo h($cliente); ?></div>
                    </div>
                <?php endif; ?>

                <div class="kv">
                    <div class="k">Fecha</div>
                    <div class="v"><?php echo h($fecha); ?></div>
                </div>

                <div class="kv">
                    <div class="k">Entrega</div>
                    <div class="v">
                        <?php echo h($entrega); ?>
                        <?php if ($entrega === 'Domicilio' && $direccion): ?>
                            <div class="muted" style="font-weight:650; margin-top:6px;"><?php echo h($direccion); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="kv">
                    <div class="k">Chofer</div>
                    <div class="v"><?php echo $chofer ? h($chofer) : 'Sin asignar'; ?></div>
                </div>

                <div class="kv" style="grid-column: 1 / -1;">
                    <div class="k">Total</div>
                    <div class="v">₡<?php echo number_format($total, 0, '.', ','); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="hd">
                <h2>Productos</h2>
                <span class="muted"><?php echo count($items); ?> ítems</span>
            </div>

            <?php if (empty($items)): ?>
                <div style="padding: 18px 16px;" class="muted">No hay detalle disponible para este pedido.</div>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="right">Cantidad</th>
                        <th class="right">Precio</th>
                        <th class="right">Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $it): ?>
                        <?php
                        $nombreP = $it['nombreProducto'] ?? '';
                        $cant = isset($it['cantidad']) ? (int)$it['cantidad'] : 0;
                        $precio = isset($it['precio']) ? (float)$it['precio'] : 0;
                        $sub = $precio * $cant;
                        ?>
                        <tr>
                            <td><?php echo h($nombreP); ?></td>
                            <td class="right"><strong><?php echo h($cant); ?></strong></td>
                            <td class="right" class="muted">₡<?php echo number_format($precio, 0, '.', ','); ?></td>
                            <td class="right"><strong>₡<?php echo number_format($sub, 0, '.', ','); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

</div>

</body>
</html>
