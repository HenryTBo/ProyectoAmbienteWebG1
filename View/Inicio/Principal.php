<?php
// View/Inicio/Principal.php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/InicioController.php';

// Si es admin, redirigir al dashboard admin
if (isset($_SESSION["ConsecutivoPerfil"]) && $_SESSION["ConsecutivoPerfil"] == "1") {
    header("Location: PrincipalAdmin.php");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Distribuidora JJ — Inicio</title>

  <?php showCss(); ?>
  <link href="/proyecto/ProyectoAmbienteWebG1/public/css/principal.css" rel="stylesheet">
  <!-- Google font (opcional) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="site-body">

  <?php showNavBar(); ?>

  <div id="layoutSidenav">
    <?php showSideBar(); ?>

    <div id="layoutSidenav_content">
      <main>

    <!-- ============================
         HERO SECTION
    ============================= -->
    <section class="hero-section">
        <div class="hero-container">
            
            <div class="hero-text">
                <h1>Suministros confiables para toda la Zona Sur</h1>
                <p class="hero-sub">
                    Abastecemos bares, supermercados, negocios y hogares con 
                    <strong>distribución segura</strong>, 
                    <strong>crédito comercial</strong> y 
                    <strong>equipos de refrigeración</strong>.
                </p>

                <form class="hero-search" onsubmit="return doHeroSearch();">
                    <input type="search" id="heroSearchInput" placeholder="Buscar producto, categoría o proveedor...">
                    <button type="submit">Buscar</button>
                </form>

                <div class="hero-benefits">
                    <div class="benefit-item">🚚 Entregas programadas</div>
                    <div class="benefit-item">❄️ Equipos y exhibidores</div>
                    <div class="benefit-item">💳 Créditos comerciales</div>
                </div>
            </div>

            <div class="hero-image">
                <img src="/proyecto/ProyectoAmbienteWebG1/public/images/hero_products.jpg" alt="">
            </div>

        </div>
    </section>


    <!-- ============================
         CATEGORÍAS PRINCIPALES
    ============================= -->
    <section class="section">
        <h2 class="section-title">Categorías principales</h2>
        <p class="section-subtitle">Encuentra rápidamente lo que buscas</p>

        <div class="categories-grid">
            <a class="category-card" href="productos.php?cat=Licorera">
                <div class="cat-icon">🍺</div>
                <h3>Licorera</h3>
                <p>Cerveza, ron y licores</p>
            </a>

            <a class="category-card" href="productos.php?cat=Supermercado">
                <div class="cat-icon">🛒</div>
                <h3>Supermercado</h3>
                <p>Refrescos, snacks y abarrotes</p>
            </a>

            <a class="category-card" href="productos.php?cat=Mayoreo">
                <div class="cat-icon">📦</div>
                <h3>Mayoreo</h3>
                <p>Pedidos grandes y rutas</p>
            </a>

            <a class="category-card" href="productos.php?cat=Equipos">
                <div class="cat-icon">❄️</div>
                <h3>Equipos</h3>
                <p>Congeladores y cámaras</p>
            </a>
        </div>
    </section>


    <!-- ============================
         PRODUCTOS DESTACADOS
    ============================= -->
    <section class="section">
        <div class="section-header">
            <div>
                <h2 class="section-title">Productos destacados</h2>
                <p class="section-subtitle">Los más solicitados por nuestros clientes</p>
            </div>
            <a class="view-all" href="productos.php">Ver todo</a>
        </div>

        <div id="featuredGrid" class="products-grid">
            <div class="loading">Cargando productos…</div>
        </div>
    </section>


    <!-- ============================
         SERVICIOS
    ============================= -->
    <section class="section" id="servicios">
        <h2 class="section-title">Nuestros servicios</h2>

        <div class="services-grid">
            <div class="service-card">
                <h3>Plan Mayoreo</h3>
                <p>Crédito, facturación y entregas programadas para tu negocio.</p>
            </div>

            <div class="service-card">
                <h3>Equipamiento</h3>
                <p>Venta y renta de exhibidores, congeladores y cámaras.</p>
            </div>

            <div class="service-card">
                <h3>Rutas y retornables</h3>
                <p>Recolectamos retornables y optimizamos tus rutas de entrega.</p>
            </div>
        </div>
    </section>


    <!-- ============================
         CONTACTO
    ============================= -->
    <section class="section contact-grid">
        <div class="contact-card">
            <h4>Contacto</h4>
            <p>
                Tel: <strong>2773-4548</strong><br>
                Email: <a href="mailto:ventas@distribuidorajj.cr">ventas@distribuidorajj.cr</a><br>
                San Vito, Zona Sur
            </p>
        </div>

        <div class="contact-card">
            <h4>Horario</h4>
            <p>Lun-Vie: 7:30-17:00<br>Sáb: 8:00-12:00</p>
        </div>

        <div class="contact-card">
            <h4>Formas de pago</h4>
            <p>Contado, tarjeta y crédito autorizado.</p>
        </div>
    </section>

</main>


      <?php showFooter(); ?>
    </div>
  </div>

  <?php showJs(); ?>

  <script>
    const API_LIST = "/proyecto/ProyectoAmbienteWebG1/Controller/ProductController.php?action=list";
    const featuredContainer = document.getElementById('featuredGrid');

    function priceFormat(v){ return '₡ ' + new Intl.NumberFormat('es-CR').format(v); }

    async function loadFeatured() {
      try {
        const res = await fetch(API_LIST);
        const json = await res.json();
        const data = (json.success && Array.isArray(json.data)) ? json.data : [];
        renderFeatured(data.slice(0,8));
      } catch (e) {
        featuredContainer.innerHTML = '<p class="muted">Error cargando productos.</p>';
        console.error(e);
      }
    }

    function renderFeatured(items){
      if (!items.length) {
        featuredContainer.innerHTML = '<p class="muted">No hay productos disponibles.</p>';
        return;
      }
      featuredContainer.innerHTML = '';
      items.forEach(p => {
        const card = document.createElement('article');
        card.className = 'product-card';
        const img = p.imagen || '/proyecto/ProyectoAmbienteWebG1/public/images/placeholder.png';
        card.innerHTML = `
          <a class="pc-thumb" href="/proyecto/ProyectoAmbienteWebG1/View/Inicio/productos.php?id=${p.id}">
            <img loading="lazy" src="${img}" alt="${p.nombre}">
          </a>
          <div class="pc-body">
            <div class="pc-meta">
              <span class="pc-cat">${p.categoria}</span>
              <h3 class="pc-title">${p.nombre}</h3>
            </div>
            <div class="pc-bottom">
              <div class="pc-price">${priceFormat(p.precio)}</div>
              <div class="pc-actions">
                <a class="btn btn-sm" href="/proyecto/ProyectoAmbienteWebG1/View/Inicio/productos.php?id=${p.id}">Ver</a>
                <button class="btn btn-sm btn-ghost" onclick="addToCart(${p.id})">Agregar</button>
              </div>
            </div>
          </div>
        `;
        featuredContainer.appendChild(card);
      });
    }

    function addToCart(id){
      let cart = JSON.parse(localStorage.getItem('jj_cart')||'[]');
      const item = cart.find(x=>x.id==id);
      if(item) item.qty++; else cart.push({id:id, qty:1});
      localStorage.setItem('jj_cart', JSON.stringify(cart));
      // micro-feedback
      const el = document.createElement('div');
      el.className = 'toast';
      el.innerText = 'Agregado al carrito';
      document.body.appendChild(el);
      setTimeout(()=>el.classList.add('visible'),10);
      setTimeout(()=>{ el.classList.remove('visible'); setTimeout(()=>el.remove(),300); },1500);
    }

    function doHeroSearch(){
      const q = document.getElementById('heroSearchInput').value.trim();
      if (!q) {
        window.location.href = '/proyecto/ProyectoAmbienteWebG1/View/Inicio/productos.php';
      } else {
        window.location.href = '/proyecto/ProyectoAmbienteWebG1/View/Inicio/productos.php?q=' + encodeURIComponent(q);
      }
      return false;
    }

    document.addEventListener('DOMContentLoaded', loadFeatured);
  </script>

</body>
</html>
