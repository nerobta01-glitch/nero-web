<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>NERO · Administrador de catálogo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<nav class="navbar navbar-dark bg-black border-bottom border-secondary">
  <div class="container">
    <span class="navbar-brand fw-bold">NERO · Admin</span>
    <div class="d-flex gap-2">
      <a href="../index.html" class="btn btn-outline-light btn-sm" target="_blank">Ver catálogo</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card bg-black border-secondary">
        <div class="card-body">
          <h2 class="h5 mb-3">Nueva / Editar prenda</h2>
          <form id="formProducto" enctype="multipart/form-data">
            <input type="hidden" name="id" id="campo-id">

            <div class="mb-2">
              <label class="form-label small">Nombre</label>
              <input type="text" name="nombre" id="campo-nombre" class="form-control form-control-sm bg-dark text-light border-secondary" required>
            </div>

            <div class="mb-2">
              <label class="form-label small">Categoría</label>
              <input type="text" name="categoria" id="campo-categoria" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Camiseta, Buzo...">
            </div>

            <div class="mb-2">
              <label class="form-label small">Colección</label>
              <input type="text" name="coleccion" id="campo-coleccion" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Colección o temporada">
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small">Precio</label>
                <input type="number" step="0.01" name="precio" id="campo-precio" class="form-control form-control-sm bg-dark text-light border-secondary">
              </div>
              <div class="col-6">
                <label class="form-label small">Stock</label>
                <input type="number" name="stock" id="campo-stock" class="form-control form-control-sm bg-dark text-light border-secondary">
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label small">Tallas</label>
              <input type="text" name="tallas" id="campo-tallas" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="S,M,L,XL">
            </div>

            <div class="mb-2">
              <label class="form-label small">Colores</label>
              <input type="text" name="colores" id="campo-colores" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="Negro, Blanco...">
            </div>

            <div class="mb-2">
              <label class="form-label small">WhatsApp (número con indicativo)</label>
              <input type="text" name="whatsapp" id="campo-whatsapp" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="57300...">
            </div>

            <div class="mb-2">
              <label class="form-label small">Tags (separados por coma)</label>
              <input type="text" name="tags" id="campo-tags" class="form-control form-control-sm bg-dark text-light border-secondary">
            </div>

            <div class="mb-2">
              <label class="form-label small">Descripción</label>
              <textarea name="descripcion" id="campo-descripcion" rows="3" class="form-control form-control-sm bg-dark text-light border-secondary"></textarea>
            </div>

            <div class="mb-2">
              <label class="form-label small">Imagen principal</label>
              <input type="file" name="imagen" id="campo-imagen" class="form-control form-control-sm bg-dark text-light border-secondary" accept="image/*">
              <div class="small text-muted mt-1">Se usa como portada en el catálogo.</div>
            </div>

            <div class="mb-3">
              <label class="form-label small">Galería (múltiples imágenes)</label>
              <input type="file" name="galeria[]" id="campo-galeria" class="form-control form-control-sm bg-dark text-light border-secondary" accept="image/*" multiple>
              <div class="small text-muted mt-1">
                Si subes nuevas imágenes, se reemplaza toda la galería anterior de este producto.
              </div>
            </div>

            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" role="switch" id="campo-destacado" name="destacado">
              <label class="form-check-label small" for="campo-destacado">Marcar como destacado</label>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-light btn-sm flex-fill" type="submit">Guardar prenda</button>
              <button class="btn btn-outline-light btn-sm flex-fill" type="button" id="btn-limpiar">Limpiar</button>
            </div>
            <div class="small mt-2" id="estado-form"></div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card bg-black border-secondary">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h5 mb-0">Prendas cargadas</h2>
            <button class="btn btn-outline-light btn-sm" id="btn-recargar">Recargar</button>
          </div>
          <div class="table-responsive" style="max-height:70vh">
            <table class="table table-dark table-sm align-middle">
              <thead class="small">
                <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Categoría</th>
                  <th>Colección</th>
                  <th>Precio</th>
                  <th>Imgs</th>
                  <th>Dest.</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="tabla-productos"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
const apiUrl = 'api/products.php';

const tabla = document.getElementById('tabla-productos');
const form  = document.getElementById('formProducto');
const estado= document.getElementById('estado-form');

const campos = {
  id:         document.getElementById('campo-id'),
  nombre:     document.getElementById('campo-nombre'),
  categoria:  document.getElementById('campo-categoria'),
  coleccion:  document.getElementById('campo-coleccion'),
  precio:     document.getElementById('campo-precio'),
  stock:      document.getElementById('campo-stock'),
  tallas:     document.getElementById('campo-tallas'),
  colores:    document.getElementById('campo-colores'),
  whatsapp:   document.getElementById('campo-whatsapp'),
  tags:       document.getElementById('campo-tags'),
  descripcion:document.getElementById('campo-descripcion'),
  destacado:  document.getElementById('campo-destacado'),
};

function limpiarFormulario() {
  form.reset();
  campos.id.value = '';
  estado.textContent = '';
}

function cargarProductos() {
  tabla.innerHTML = '<tr><td colspan="8" class="text-center small text-muted">Cargando...</td></tr>';
  fetch(apiUrl + '?action=list')
    .then(r => r.json())
    .then(data => {
      const productos = data.products || [];
      if (!productos.length) {
        tabla.innerHTML = '<tr><td colspan="8" class="text-center small text-muted">Sin prendas aún.</td></tr>';
        return;
      }
      tabla.innerHTML = '';
      productos.forEach(p => {
        const tr = document.createElement('tr');
        const imgsCount = (p.imagenes && p.imagenes.length)
          ? p.imagenes.length
          : (p.imagen_url ? 1 : 0);

        tr.innerHTML = `
          <td class="small">${p.id}</td>
          <td class="small">${p.nombre || ''}</td>
          <td class="small">${p.categoria || ''}</td>
          <td class="small">${p.coleccion || ''}</td>
          <td class="small">${p.precio || ''}</td>
          <td class="small">${imgsCount}</td>
          <td class="small">${Number(p.destacado) === 1 ? 'Sí' : ''}</td>
          <td class="text-end">
            <button class="btn btn-outline-light btn-xs btn-sm me-1" data-id="${p.id}" data-action="edit">Editar</button>
            <button class="btn btn-outline-danger btn-xs btn-sm" data-id="${p.id}" data-action="delete">Eliminar</button>
          </td>
        `;
        tabla.appendChild(tr);
      });
    })
    .catch(err => {
      console.error(err);
      tabla.innerHTML = '<tr><td colspan="8" class="text-center small text-danger">Error al cargar.</td></tr>';
    });
}

tabla.addEventListener('click', (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const id = btn.getAttribute('data-id');
  const action = btn.getAttribute('data-action');

  if (action === 'edit') {
    editarProducto(id);
  } else if (action === 'delete') {
    if (confirm('¿Eliminar esta prenda?')) {
      eliminarProducto(id);
    }
  }
});

function editarProducto(id) {
  fetch(apiUrl + '?action=list')
    .then(r => r.json())
    .then(data => {
      const productos = data.products || [];
      const p = productos.find(x => String(x.id) === String(id));
      if (!p) return;
      campos.id.value         = p.id;
      campos.nombre.value     = p.nombre || '';
      campos.categoria.value  = p.categoria || '';
      campos.coleccion.value  = p.coleccion || '';
      campos.precio.value     = p.precio || '';
      campos.stock.value      = p.stock || '';
      campos.tallas.value     = p.tallas || '';
      campos.colores.value    = p.colores || '';
      campos.whatsapp.value   = p.whatsapp || '';
      campos.tags.value       = p.tags || '';
      campos.descripcion.value= p.descripcion || '';
      campos.destacado.checked= Number(p.destacado) === 1;
      window.scrollTo({ top: 0, behavior: 'smooth' });
      estado.textContent = 'Editando ID ' + p.id + '. Si subes nueva galería, reemplaza la anterior.';
    });
}

function eliminarProducto(id) {
  const fd = new FormData();
  fd.append('id', id);

  fetch(apiUrl + '?action=delete', {
    method: 'POST',
    body: fd
  })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        cargarProductos();
      } else {
        alert(res.error || 'No se pudo eliminar');
      }
    })
    .catch(() => alert('Error al eliminar'));
}

form.addEventListener('submit', (e) => {
  e.preventDefault();
  estado.textContent = 'Guardando...';
  const fd = new FormData(form);
  fetch(apiUrl + '?action=save', {
    method: 'POST',
    body: fd
  })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        estado.textContent = 'Guardado correctamente';
        limpiarFormulario();
        cargarProductos();
      } else {
        estado.textContent = res.error || 'Error al guardar';
      }
    })
    .catch(err => {
      console.error(err);
      estado.textContent = 'Error al guardar';
    });
});

document.getElementById('btn-limpiar').addEventListener('click', limpiarFormulario);
document.getElementById('btn-recargar').addEventListener('click', cargarProductos);

cargarProductos();
</script>
</body>
</html>
