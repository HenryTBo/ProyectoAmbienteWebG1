<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

if (!isset($_SESSION["ConsecutivoUsuario"]) && !isset($_SESSION["User"])) {
    header("Location: InicioSesion.php");
    exit;
}

require_once __DIR__ . '/../../Model/ProductModel.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$perfil = $_SESSION["ConsecutivoPerfil"] ?? ($_SESSION["User"]["ConsecutivoPerfil"] ?? "2");
$esAdmin = ($perfil == "1");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$producto = $id > 0 ? getProductById($id) : null;

$relacionados = [];
if ($producto) {
    $cat = $producto['categoria'] ?? '';
    if (!empty($cat)) {
        $relacionados = getProductsByCategory($cat);
        $relacionados = array_values(array_filter($relacionados, function($p) use ($id){
            return (int)($p['id'] ?? 0) !== (int)$id;
        }));
        $relacionados = array_slice($relacionados, 0, 6);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php showCss(); ?>
    <style>
      :root{
        --jj-navy:#0c2c3c;
        --jj-navy-2:#143044;
        --jj-gold:#cca44c;
        --jj-gold-2:#a97823;
        --jj-maroon:#8c1c1c;
        --jj-cream:#f7f5f0;
        --jj-graphite:#1c2430;
      }
      .wrap{
        background: rgba(255,255,255,.94);
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(0,0,0,.12);
        overflow: hidden;
      }
      .hero{
        background: linear-gradient(90deg, rgba(12,44,60,1) 0%, rgba(20,48,68,1) 70%, rgba(204,164,76,.18) 100%);
        color:#fff;
        padding: 16px 18px;
      }
      .hero h3{ margin:0; font-weight: 900; }
      .content{ padding: 18px; }
      .img{
        width:100%;
        height: 360px;
        object-fit: cover;
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,.08);
      }
      .price{ font-size: 22px; font-weight: 900; color: var(--jj-graphite); }
      .meta{ color: rgba(0,0,0,.65); font-size: 13px; }
      .cta-row{ display:flex; gap: 10px; flex-wrap:wrap; }
      .btn-primary{
        background: linear-gradient(135deg, var(--jj-gold), #f1c55a) !important;
        border: none !important;
        color: #1b1b1b !important;
        font-weight: 900 !important;
        border-radius: 12px !important;
      }
      .btn-outline-primary{
        border-color: rgba(204,164,76,.55) !important;
        color: var(--jj-navy) !important;
        font-weight: 800 !important;
        border-radius: 12px !important;
      }
      .qty{
        width: 110px;
        border-radius: 12px !important;
      }

      .floating-cart-btn{
        position: fixed;
        bottom: 18px;
        right: 18px;
        z-index: 9999;
        background-color: var(--jj-gold);
        color: #1b1b1b;
        border: none;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        box-shadow: 0 18px 40px rgba(0,0,0,.22);
        display:flex;
        align-items:center;
        justify-content:center;
      }
      .floating-cart-btn .badge{
        position:absolute;
        top:-6px;
        right:-6px;
        background: var(--jj-maroon);
        color:#fff;
        border-radius: 999px;
        padding: 6px 8px;
        font-size: 12px;
      }
      .toast{
        position: fixed;
        bottom: 22px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(12,44,60,.96);
        color: #fff;
        padding: 10px 14px;
        border-radius: 12px;
        box-shadow: 0 18px 40px rgba(0,0,0,.25);
        opacity: 0;
        transition: .15s ease-in-out;
        z-index: 99999;
        font-weight: 700;
        font-size: 13px;
      }
      .toast.visible{ opacity: 1; }

      .rec-card{
        border: 1px solid rgba(0,0,0,.08);
        background: rgba(255,255,255,.94);
        border-radius: 16px;
        overflow:hidden;
        box-shadow: 0 12px 26px rgba(0,0,0,.10);
        height: 100%;
      }
      .rec-img{ width:100%; height: 140px; object-fit: cover; background:#fff; }
      .rec-body{ padding: 10px 12px; }
      .rec-name{ font-weight: 900; color: var(--jj-navy); font-size: 14px; min-height: 34px; }
      .rec-price{ font-weight: 900; color: var(--jj-graphite); }
    </style>
</head>
<body class="sb-nav-fixed">
<?php showNavBar(); ?>

<div id="layoutSidenav">
    <?php showSideBar(); ?>

    <div id="layoutSidenav_content">
        <main class="container-fluid px-4 mt-4">

            <?php if (!$producto): ?>
                <div class="alert alert-danger">Producto no encontrado.</div>
            <?php else: ?>

            <div class="wrap">
                <div class="hero d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h3><?= h($producto['nombre'] ?? '') ?></h3>

                    <?php if(!$esAdmin): ?>
                    <button id="viewCartBtn" type="button" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Carrito (<span id="cartCount">0</span>)
                    </button>
                    <?php endif; ?>
                </div>

                <div class="content">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <img class="img" src="<?= h($producto['imagen'] ?? '') ?>" alt="<?= h($producto['nombre'] ?? '') ?>">
                        </div>

                        <div class="col-lg-6">
                            <div class="price mb-2">₡<?= number_format((float)($producto['precio'] ?? 0), 2) ?></div>
                            <div class="meta mb-2">
                                Categoría: <strong><?= h($producto['categoria'] ?? '') ?></strong> |
                                Stock: <strong><?= (int)($producto['stock'] ?? 0) ?></strong>
                            </div>

                            <?php if (!empty($producto['descripcion'])): ?>
                                <p><?= nl2br(h($producto['descripcion'])) ?></p>
                            <?php endif; ?>

                            <?php if(!$esAdmin): ?>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <label class="form-label m-0">Cantidad:</label>
                                    <input id="qty" type="number" min="1" value="1" class="form-control qty">
                                </div>

                                <div class="cta-row">
                                    <button id="btnAdd" type="button" class="btn btn-primary"
                                            <?= ((int)($producto['stock'] ?? 0) <= 0) ? 'disabled' : '' ?>>
                                        <i class="fas fa-cart-plus me-1"></i> Agregar al carrito
                                    </button>

                                    <a class="btn btn-outline-primary" href="carrito.php">
                                        <i class="fas fa-credit-card me-1"></i> Ir a pagar
                                    </a>
                                </div>

                                <?php if (((int)($producto['stock'] ?? 0) <= 0)): ?>
                                    <div class="text-danger mt-2 fw-bold">Sin stock.</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    Estás en modo administrador. Aquí solo se visualiza el producto.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($relacionados)): ?>
                        <hr class="my-4">
                        <h5 class="fw-bold mb-3" style="color: var(--jj-navy);">Relacionados</h5>
                        <div class="row">
                            <?php foreach ($relacionados as $r): ?>
                                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <a href="verProducto.php?id=<?= (int)($r['id'] ?? 0) ?>" style="text-decoration:none; color:inherit;">
                                        <div class="rec-card">
                                            <img class="rec-img" src="<?= h($r['imagen'] ?? '') ?>" alt="<?= h($r['nombre'] ?? '') ?>">
                                            <div class="rec-body">
                                                <div class="rec-name"><?= h($r['nombre'] ?? '') ?></div>
                                                <div class="rec-price">₡<?= number_format((float)($r['precio'] ?? 0), 2) ?></div>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-secondary"><?= h($r['categoria'] ?? '') ?></span>
                                                    <span class="badge bg-light text-dark">Stock: <?= (int)($r['stock'] ?? 0) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <?php endif; ?>

        </main>

        <?php if(!$esAdmin): ?>
        <button id="floatingCartBtn" class="floating-cart-btn" title="Ver carrito">
            <i class="fas fa-shopping-cart"></i>
            <span id="floatingCartBadge" class="badge d-none">0</span>
        </button>
        <?php endif; ?>

        <?php showFooter(); ?>
    </div>
</div>

<?php showJs(); ?>

<script>
(function(){
  function updateCartCount(){
    fetch('../../Controller/CartController.php?action=count', { method: 'GET' })
      .then(r => r.json())
      .then(json => {
        const count = Number(json.count || 0);

        const countEl = document.getElementById('cartCount');
        if(countEl) countEl.textContent = count;

        const badge = document.getElementById('floatingCartBadge');
        if(badge){
          badge.textContent = count;
          badge.classList.toggle('d-none', !(count > 0));
        }
      })
      .catch(()=>{});
  }

  function toast(msg){
    const t = document.createElement('div');
    t.className = 'toast';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(()=>t.classList.add('visible'), 10);
    setTimeout(()=>{
      t.classList.remove('visible');
      setTimeout(()=>t.remove(), 250);
    }, 1200);
  }

  function addToCart(id, qty){
    const formData = new FormData();
    formData.append('product_id', id);
    formData.append('qty', qty);

    fetch('../../Controller/CartController.php?action=addAjax', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(json => {
        if(!json.success){
          toast(json.message || 'No se pudo agregar al carrito.');
          if((json.message || '').toLowerCase().includes('iniciar sesión')){
            setTimeout(()=>window.location='InicioSesion.php', 700);
          }
          return;
        }
        updateCartCount();
        toast('Producto agregado al carrito');
      })
      .catch(()=>toast('No se pudo agregar al carrito.'));
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();

    const floatingBtn = document.getElementById('floatingCartBtn');
    if(floatingBtn) floatingBtn.addEventListener('click', () => window.location.href = 'carrito.php');

    const viewCartBtn = document.getElementById('viewCartBtn');
    if(viewCartBtn) viewCartBtn.addEventListener('click', () => window.location.href = 'carrito.php');

    const btn = document.getElementById('btnAdd');
    if(btn){
      btn.addEventListener('click', () => {
        const qtyEl = document.getElementById('qty');
        let qty = 1;
        if(qtyEl){
          qty = parseInt(qtyEl.value || '1', 10);
          if(isNaN(qty) || qty <= 0) qty = 1;
        }
        addToCart(<?= (int)$id ?>, qty);
      });
    }
  });
})();
</script>
</body>
</html>
