const API_URL = "http://localhost:8080/crud-libros-php/backend-php/api.php";

const btnAgregar = document.getElementById("btn-agregar");
const modal = document.getElementById("modal");
const modalOverlay = document.getElementById("modal-overlay");
const modalClose = document.getElementById("modal-close");
const btnCancelar = document.getElementById("btn-cancelar");
const formLibro = document.getElementById("form-libro");

function openModal(isEdit = false) {
  if (!modal) return;
  modal.hidden = false;
  if (!isEdit) clearForm();
  setTimeout(() => {
    const titulo = document.getElementById("titulo");
    if (titulo) titulo.focus();
  }, 50);
}

function closeModal() {
  if (!modal) return;
  modal.hidden = true;
  clearForm();
}

function clearForm() {
  if (!formLibro) return;
  formLibro.reset();
  const id = document.getElementById("id");
  if (id) id.value = "";
}

if (btnAgregar) btnAgregar.addEventListener("click", () => openModal(false));
if (modalClose) modalClose.addEventListener("click", closeModal);
if (modalOverlay) modalOverlay.addEventListener("click", closeModal);
if (btnCancelar) btnCancelar.addEventListener("click", closeModal);

// Cerrar con ESC
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeModal();
});

// ====================== CARGAR LIBROS ======================
async function cargarLibros(filtros = {}) {
  let url = API_URL;
  const params = new URLSearchParams(filtros).toString();
  if (params) url += "?" + params;

  const res = await fetch(url);
  let data = await res.json();
  
  if (Array.isArray(data)) data.sort((a, b) => b.id - a.id);

  const tabla = document.getElementById("tabla-libros");
  if (!tabla) return;
  tabla.innerHTML = "";

  data.forEach((libro) => {
    const fila = `
            <tr>
                <td>${libro.id}</td>
                <td>${libro.titulo}</td>
                <td>${libro.autor}</td>
                <td>${libro.anio_publicacion}</td>
                <td>${libro.genero || ""}</td>
                <td>${libro.isbn || ""}</td>
                <td>
                    <button class="btn-editar" onclick="editarLibro(${
                      libro.id
                    })"><img src="assets/img/icons8-editar-480.png" width="20" height="20" alt="Editar"></button>
                    <button class="btn-eliminar" onclick="eliminarLibro(${
                      libro.id
                    })"><img src="assets/img/icons8-eliminar-500.png" width="20" height="20" alt="Eliminar"></button>
                </td>
            </tr>
        `;
    tabla.innerHTML += fila;
  });
}

// ====================== CREAR O EDITAR LIBRO ======================
if (formLibro) {
  formLibro.addEventListener("submit", async (e) => {
    e.preventDefault();

    const id = document.getElementById("id").value;

    const libro = {
      titulo: document.getElementById("titulo").value,
      autor: document.getElementById("autor").value,
      anio_publicacion: parseInt(document.getElementById("anio").value),
      genero: document.getElementById("genero").value,
      isbn: document.getElementById("isbn").value,
    };

    const metodo = id ? "PUT" : "POST";
    const url = API_URL + (id ? "?id=" + id : "");

    const res = await fetch(url, {
      method: metodo,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(libro),
    });

    const data = await res.json();
    alert(data.message || data.error);

    closeModal();
    cargarLibros();
  });
}

// ====================== EDITAR LIBRO ======================
async function editarLibro(id) {
  const res = await fetch(API_URL + "?id=" + id);
  const libro = await res.json();

  document.getElementById("id").value = libro.id;
  document.getElementById("titulo").value = libro.titulo;
  document.getElementById("autor").value = libro.autor;
  document.getElementById("anio").value = libro.anio_publicacion;
  document.getElementById("genero").value = libro.genero;
  document.getElementById("isbn").value = libro.isbn;

  openModal(true);
}

// ====================== ELIMINAR LIBRO ======================
async function eliminarLibro(id) {
  if (!confirm("¿Seguro que deseas eliminar este libro?")) return;

  const res = await fetch(API_URL + "?id=" + id, { method: "DELETE" });
  const data = await res.json();
  alert(data.message || data.error);

  cargarLibros();
}

// ====================== BUSCAR LIBROS ======================
const btnBuscar = document.getElementById("btn-buscar");
if (btnBuscar) {
  btnBuscar.addEventListener("click", () => {
    const filtros = {
      titulo: document.getElementById("bus-titulo").value,
      autor: document.getElementById("bus-autor").value,
      anio: document.getElementById("bus-anio").value,
      genero: document.getElementById("bus-genero").value,
    };
    cargarLibros(filtros);
  });
}

// ====================== INICIAL ======================
cargarLibros();
