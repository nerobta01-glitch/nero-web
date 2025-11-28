document.addEventListener('DOMContentLoaded', () => {
  const apiUrl = 'admin/api/products.php';

  const featuredRow   = document.getElementById('featuredRow');
  const featuredCount = document.getElementById('featuredCount');
  const productsGrid  = document.getElementById('productsGrid');
  const lookbookGrid  = document.getElementById('lookbookGrid');
  const collectionSelect = document.getElementById('collectionSelect');
  const searchInput   = document.getElementById('searchInput');
  const filtersBox    = document.getElementById('filters');
  const emptyState    = document.getElementById('emptyState');
  const yearSpan      = document.getElementById('year');

  if (yearSpan) yearSpan.textContent = new Date().getFullYear();

  let productos = [];
  let filtrados = [];

  // -----------------------------
  // Cargar productos desde el backend
  // -----------------------------
  function cargarProductos() {
    fetch(apiUrl + '?action=list')
      .then(r => r.json())
      .then(data => {
        productos = Array.isArray(data.products) ? data.products : [];
        filtrados = [...productos];
        poblarFiltros();
        render();
      })
      .catch(err => {
        console.error('Error cargando productos', err);
      });
  }

  // -----------------------------
  // Filtros (colección / categoría / tags)
  // -----------------------------
  function poblarFiltros() {
    const colecciones = new Set();
    const categorias  = new Set();
    const tags        = new Set();

    productos.forEach(p => {
      if (p.coleccion) colecciones.add(p.coleccion);
      if (p.categoria) categorias.add(p.categoria);
      if (p.tags) {
        p.tags.split(',')
          .map(t => t.trim())
          .filter(Boolean)
          .forEach(t => tags.add(t));
      }
    });

    // Select colecciones
    collectionSelect.innerHTML = '<option value="">Todas las colecciones</option>';
    colecciones.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c;
      opt.textContent = c;
      collectionSelect.appendChild(opt);
    });

    // Botones de categoría + tags
    filtersBox.innerHTML = '';
    categorias.forEach(c => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-sm btn-outline-secondary rounded-pill';
      btn.textContent = c;
      btn.addEventListener('click', () => filtrar({ categoria: c }));
      filtersBox.appendChild(btn);
    });

    if (tags.size) {
      const sep = document.createElement('span');
      sep.className = 'small text-muted';
      sep.textContent = '· Tags';
      filtersBox.appendChild(sep);

      tags.forEach(t => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary rounded-pill';
        btn.textContent = '#' + t;
        btn.addEventListener('click', () => {
          searchInput.value = t;
          aplicarFiltros();
        });
        filtersBox.appendChild(btn);
      });
    }
  }

  function filtrar(extra = {}) {
    const col = collectionSelect.value;
    const q   = (searchInput.value || '').toLowerCase();

    filtrados = productos.filter(p => {
      if (col && p.coleccion !== col) return false;
      if (extra.categoria && p.categoria !== extra.categoria) return false;
      if (q) {
        const blob = (
          (p.nombre || '') + ' ' +
          (p.descripcion || '') + ' ' +
          (p.tags || '') + ' ' +
          (p.coleccion || '') + ' ' +
          (p.categoria || '')
        ).toLowerCase();
        if (!blob.includes(q)) return false;
      }
      return true;
    });

    render();
  }

  function aplicarFiltros() {
    filtrar();
  }

  // -----------------------------
  // Utilidad: imagen principal
  // -----------------------------
  function getImagenPrincipal(p) {
    if (p.imagenes && p.imagenes.length) return p.imagenes[0];
    if (p.imagen_url) return p.imagen_url;
    return 'https://via.placeholder.com/800x1000/000000/ffffff?text=NERO';
  }

  // -----------------------------
  // Render general (destacados / catálogo / lookbook)
  // -----------------------------
  function render() {
    featuredRow.innerHTML = '';
    productsGrid.innerHTML = '';
    lookbookGrid.innerHTML = '';

    if (!productos.length) {
      emptyState.classList.remove('d-none');
      if (featuredCount) featuredCount.textContent = '';
      return;
    }
    emptyState.classList.add('d-none');

    // Destacados
    const destacados = productos.filter(p => Number(p.destacado) === 1);
    if (featuredCount) {
      featuredCount.textContent = destacados.length
        ? `${destacados.length} piezas`
        : 'Sin destacados';
    }
    if (destacados.length) {
      destacados.forEach(p => {
        featuredRow.appendChild(crearCardProducto(p));
      });
    }

    // Catálogo
    if (!filtrados.length) {
      productsGrid.innerHTML =
        '<div class="col-12 text-center"><p class="text-muted">No hay prendas para este filtro.</p></div>';
    } else {
      filtrados.forEach(p => {
        productsGrid.appendChild(crearCardProducto(p));
      });
    }

    // Lookbook (primeras 6 prendas)
    productos.slice(0, 6).forEach(p => {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-4 col-lg-2';
      const img = document.createElement('img');
      img.src = getImagenPrincipal(p);
      img.alt = p.nombre || 'Lookbook NERO';
      img.className = 'w-100 rounded-3';
      img.loading = 'lazy';
      col.appendChild(img);
      lookbookGrid.appendChild(col);
    });
  }

  // -----------------------------
  // Tarjeta de producto (lo que ves en la grilla)
  // -----------------------------
  function crearCardProducto(p) {
    const col = document.createElement('div');
    col.className = 'col-6 col-md-4 col-lg-3';

    const card = document.createElement('article');
    card.className = 'card bg-dark border-secondary rounded-4 overflow-hidden h-100';

    // Imagen
    const img = document.createElement('img');
    img.src = getImagenPrincipal(p);
    img.alt = p.nombre || 'Prenda NERO';
    img.className = 'card-img-top';
    img.loading = 'lazy';

    // Cuerpo
    const body = document.createElement('div');
    body.className = 'card-body d-flex flex-column';

    // Nombre
    const title = document.createElement('h3');
    title.className = 'h6 mb-1';
    title.textContent = p.nombre || 'Prenda NERO';

    // Categoría / colección
    const meta = document.createElement('div');
    meta.className = 'd-flex justify-content-between align-items-center mb-1 small text-muted';
    const catSpan = document.createElement('span');
    catSpan.textContent = p.categoria || '';
    const colSpan = document.createElement('span');
    colSpan.textContent = p.coleccion || '';
    meta.appendChild(catSpan);
    meta.appendChild(colSpan);

    // Precio
    const price = document.createElement('div');
    price.className = 'fw-semibold mb-1';
    price.textContent = p.precio
      ? '$ ' + Number(p.precio).toLocaleString('es-CO')
      : '';

    // Tallas (mini resumen)
    const sizesLine = document.createElement('div');
    sizesLine.className = 'small text-muted mb-2';
    if (p.tallas) {
      sizesLine.textContent = 'Tallas: ' + p.tallas;
    } else {
      sizesLine.textContent = '\u00a0'; // espacio
    }

    // Botón
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-light btn-sm w-100 mt-auto';
    btn.textContent = 'Ver detalle';
    btn.addEventListener('click', () => abrirModal(p));

    body.appendChild(title);
    body.appendChild(meta);
    body.appendChild(price);
    body.appendChild(sizesLine);
    body.appendChild(btn);

    card.appendChild(img);
    card.appendChild(body);
    col.appendChild(card);
    return col;
  }

  // -----------------------------
  // Modal de detalle
  // -----------------------------
  function abrirModal(p) {
    const modalTitle  = document.getElementById('modalTitle');
    const modalImg    = document.getElementById('modalImg');
    const modalCat    = document.getElementById('modalCategory');
    const modalCol    = document.getElementById('modalCollection');
    const modalPrice  = document.getElementById('modalPrice');
    const modalStock  = document.getElementById('modalStock');
    const modalDesc   = document.getElementById('modalDesc');
    const modalSizes  = document.getElementById('modalSizes');
    const modalColors = document.getElementById('modalColors');
    const modalTags   = document.getElementById('modalTags');
    const modalThumbs = document.getElementById('modalThumbs');
    const waBtn       = document.getElementById('waBtn');

    modalTitle.textContent = p.nombre || 'Prenda NERO';
    modalImg.src = getImagenPrincipal(p);
    modalCat.textContent = p.categoria || '';
    modalCol.textContent = p.coleccion || '';
    modalPrice.textContent = p.precio
      ? '$ ' + Number(p.precio).toLocaleString('es-CO')
      : '';
    modalStock.textContent = p.stock ? `Stock: ${p.stock}` : '';
    modalDesc.textContent  = p.descripcion || '';

    // Tallas
    modalSizes.innerHTML = '';
    if (p.tallas) {
      p.tallas.split(',')
        .map(t => t.trim())
        .filter(Boolean)
        .forEach(t => {
          const span = document.createElement('span');
          span.className = 'badge bg-secondary';
          span.textContent = t;
          modalSizes.appendChild(span);
        });
    }

    // Colores
    modalColors.innerHTML = '';
    if (p.colores) {
      p.colores.split(',')
        .map(c => c.trim())
        .filter(Boolean)
        .forEach(c => {
          const span = document.createElement('span');
          span.className = 'badge bg-secondary';
          span.textContent = c;
          modalColors.appendChild(span);
        });
    }

    // Tags
    modalTags.innerHTML = '';
    if (p.tags) {
      p.tags.split(',')
        .map(t => t.trim())
        .filter(Boolean)
        .forEach(t => {
          const span = document.createElement('span');
          span.className = 'badge bg-dark border border-secondary';
          span.textContent = '#' + t;
          modalTags.appendChild(span);
        });
    }

    // Galería de imágenes
    modalThumbs.innerHTML = '';
    const images = (p.imagenes && p.imagenes.length)
      ? p.imagenes
      : [getImagenPrincipal(p)];

    images.forEach((src, idx) => {
      const thumb = document.createElement('img');
      thumb.src = src;
      thumb.alt = (p.nombre || 'Prenda NERO') + ' ' + (idx + 1);
      thumb.className = 'rounded-3';
      thumb.style.width  = '60px';
      thumb.style.height = '60px';
      thumb.style.objectFit = 'cover';
      thumb.style.cursor = 'pointer';
      thumb.addEventListener('click', () => {
        modalImg.src = src;
      });
      modalThumbs.appendChild(thumb);
    });

    // WhatsApp
    const phone = (p.whatsapp || '').replace(/[^0-9]/g, '');
    const msg = encodeURIComponent(
      `Hola, estoy interesado en la prenda: ${p.nombre || ''} (${p.coleccion || 'NERO'})`
    );
    if (phone) {
      waBtn.href = `https://wa.me/${phone}?text=${msg}`;
      waBtn.classList.remove('disabled');
    } else {
      waBtn.href = '#';
      waBtn.classList.add('disabled');
    }

    const modalEl = document.getElementById('productModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }

  // -----------------------------
  // Eventos
  // -----------------------------
  collectionSelect.addEventListener('change', aplicarFiltros);
  searchInput.addEventListener('input', aplicarFiltros);

  // Arranque
  cargarProductos();
});
