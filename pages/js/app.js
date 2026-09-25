/*
  FARMTRACK FRONTEND
  ------------------
  This file currently uses localStorage only for UI testing.

  BACKEND INTEGRATION:
  Replace the functions marked "BACKEND TODO" with fetch() calls.
  Do NOT hard-code real shipments, buyers, or transporters here.

  Suggested API:
  GET  /api/me
  GET  /api/farmer/stock
  POST /api/farmer/stock
  PUT  /api/farmer/stock/:id
  DELETE /api/farmer/stock/:id
  GET  /api/farmer/shipments
  POST /api/shipments
  GET  /api/shipments/:id
  GET  /api/shipments/:id/history
  GET  /api/farmer/notifications
  GET  /api/buyers
*/

const state = {
  farmer: JSON.parse(localStorage.getItem("farmer_profile") || "null") || {
    id: null, name: "Farmer", phone: "", email: "", location: "", farmSize: "", crops: ""
  },
  stock: JSON.parse(localStorage.getItem("farmer_stock") || "[]"),
  shipments: JSON.parse(localStorage.getItem("farmer_shipments") || "[]"),
  notifications: JSON.parse(localStorage.getItem("farmer_notifications") || "[]")
};

function saveLocal() {
  localStorage.setItem("farmer_profile", JSON.stringify(state.farmer));
  localStorage.setItem("farmer_stock", JSON.stringify(state.stock));
  localStorage.setItem("farmer_shipments", JSON.stringify(state.shipments));
  localStorage.setItem("farmer_notifications", JSON.stringify(state.notifications));
}

function $(id) { return document.getElementById(id); }

function showToast(message) {
  const toast = $("toast");
  toast.textContent = message;
  toast.style.display = "block";
  setTimeout(() => toast.style.display = "none", 2500);
}

/* ---------- NAVIGATION ---------- */
const pageTitles = {
  dashboard: "Farmer Dashboard",
  stock: "My Stock",
  shipments: "My Shipments",
  create: "Create Shipment",
  track: "Track Shipment",
  history: "Shipment History",
  notifications: "Notifications",
  profile: "My Profile"
};

function openPage(page) {
  document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
  document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));

  const target = $(page);
  if (target) target.classList.add("active");

  const nav = document.querySelector(`[data-page="${page}"]`);
  if (nav) nav.classList.add("active");

  $("pageTitle").textContent = pageTitles[page] || "Farmer Dashboard";
  renderAll();

  if (page === "create") populateShipmentStock();
}

document.querySelectorAll(".nav-btn").forEach(btn => {
  btn.addEventListener("click", () => openPage(btn.dataset.page));
});

document.querySelectorAll("[data-go]").forEach(btn => {
  btn.addEventListener("click", () => openPage(btn.dataset.go));
});

/* ---------- DASHBOARD ---------- */
function renderDashboard() {
  $("farmerNameTop").textContent = state.farmer.name || "Farmer";
  $("welcomeText").textContent = `Welcome back, ${state.farmer.name || "Farmer"}.`;

  $("stockCount").textContent = state.stock.length;
  $("shipmentCount").textContent = state.shipments.length;
  $("transitCount").textContent =
    state.shipments.filter(s => ["IN_TRANSIT", "PICKED_UP"].includes(s.status)).length;
  $("deliveredCount").textContent =
    state.shipments.filter(s => s.status === "DELIVERED").length;

  const stockBox = $("dashboardStock");
  if (!state.stock.length) {
    stockBox.innerHTML = "No stock added yet.";
  } else {
    stockBox.innerHTML = state.stock.slice(0, 4).map(s => `
      <div class="list-item">
        <h3>${escapeHtml(s.product)}</h3>
        <div class="meta">${s.quantity} ${escapeHtml(s.unit)} • Delivery: ${s.deliveryDate || "Not set"}</div>
      </div>
    `).join("");
  }

  const shipBox = $("dashboardShipments");
  if (!state.shipments.length) {
    shipBox.innerHTML = "No shipments yet.";
  } else {
    shipBox.innerHTML = state.shipments.slice(0, 4).map(shipmentCard).join("");
  }
}

/* ---------- STOCK ---------- */
function renderStock() {
  const box = $("stockList");

  if (!state.stock.length) {
    box.innerHTML = `<div class="empty">No stock added yet. Click "+ Add Stock" to add your produce.</div>`;
    return;
  }

  box.innerHTML = state.stock.map(s => `
    <div class="list-item">
      <div class="panel-head">
        <div>
          <h3>${escapeHtml(s.product)}</h3>
          <div class="meta">Quantity: ${s.quantity} ${escapeHtml(s.unit)}</div>
          <div class="meta">Available: ${s.availableFrom || "Not set"} | Delivery: ${s.deliveryDate || "Not set"}</div>
          ${s.notes ? `<div class="meta">Notes: ${escapeHtml(s.notes)}</div>` : ""}
        </div>
        <span class="status">${s.quantity > 0 ? "Available" : "Out of stock"}</span>
      </div>
      <div class="stock-actions">
        <button class="small-btn" onclick="editStock('${s.id}')">Edit</button>
        <button class="small-btn" onclick="deleteStock('${s.id}')">Delete</button>
      </div>
    </div>
  `).join("");
}

$("addStockBtn").addEventListener("click", () => {
  $("stockFormTitle").textContent = "Add Stock";
  $("stockForm").reset();
  $("stockId").value = "";
  $("stockFormPanel").classList.remove("hidden");
});

$("cancelStockBtn").addEventListener("click", () => {
  $("stockFormPanel").classList.add("hidden");
});

$("stockForm").addEventListener("submit", e => {
  e.preventDefault();

  const id = $("stockId").value || crypto.randomUUID();

  const item = {
    id,
    farmerId: state.farmer.id,
    product: $("stockProduct").value.trim(),
    quantity: Number($("stockQuantity").value),
    unit: $("stockUnit").value,
    availableFrom: $("stockAvailableFrom").value,
    deliveryDate: $("stockDeliveryDate").value,
    notes: $("stockNotes").value.trim()
  };

  const existing = state.stock.findIndex(s => s.id === id);
  if (existing >= 0) state.stock[existing] = item;
  else state.stock.push(item);

  saveLocal();
  $("stockFormPanel").classList.add("hidden");
  renderAll();
  showToast("Stock saved.");
});

function editStock(id) {
  const s = state.stock.find(x => x.id === id);
  if (!s) return;

  $("stockFormTitle").textContent = "Edit Stock";
  $("stockId").value = s.id;
  $("stockProduct").value = s.product;
  $("stockQuantity").value = s.quantity;
  $("stockUnit").value = s.unit;
  $("stockAvailableFrom").value = s.availableFrom || "";
  $("stockDeliveryDate").value = s.deliveryDate || "";
  $("stockNotes").value = s.notes || "";
  $("stockFormPanel").classList.remove("hidden");
}

function deleteStock(id) {
  if (!confirm("Delete this stock item?")) return;
  state.stock = state.stock.filter(s => s.id !== id);
  saveLocal();
  renderAll();
  showToast("Stock deleted.");
}

/* ---------- SHIPMENTS ---------- */
function shipmentCard(s) {
  return `
    <div class="list-item">
      <div class="panel-head">
        <div>
          <h3>${escapeHtml(s.id || "Shipment")}</h3>
          <div class="meta">${escapeHtml(s.product || "Product")} • ${s.quantity || 0} ${escapeHtml(s.unit || "kg")}</div>
          <div class="meta">Buyer: ${escapeHtml(s.buyerName || "Not assigned")}</div>
          <div class="meta">Expected delivery: ${s.deliveryDate || "Not set"}</div>
        </div>
        <span class="status">${formatStatus(s.status || "CREATED")}</span>
      </div>
      <div class="stock-actions">
        <button class="small-btn" onclick="trackShipment('${s.id}')">Track</button>
      </div>
    </div>
  `;
}

function renderShipments() {
  const box = $("shipmentList");
  box.innerHTML = state.shipments.length
    ? state.shipments.map(shipmentCard).join("")
    : `<div class="empty">No shipments yet. Shipments created by the connected backend will appear here.</div>`;
}

function populateShipmentStock() {
  const select = $("shipmentStock");
  select.innerHTML = state.stock.length
    ? `<option value="">Select stock</option>` +
      state.stock.map(s => `<option value="${s.id}">${escapeHtml(s.product)} - ${s.quantity} ${escapeHtml(s.unit)}</option>`).join("")
    : `<option value="">No stock available</option>`;
}

/*
  BACKEND TODO:
  Load real buyers here:
  const response = await fetch('/api/buyers');
  const buyers = await response.json();
  Populate #shipmentBuyer.
*/
function populateBuyersPlaceholder() {
  // Intentionally empty. No fake buyer data.
}

$("shipmentForm").addEventListener("submit", e => {
  e.preventDefault();

  const stock = state.stock.find(s => s.id === $("shipmentStock").value);
  if (!stock) {
    showToast("Please select a valid stock item.");
    return;
  }

  /*
    FRONTEND DEMO ONLY:
    This local object exists so you can test the form.
    DELETE/REPLACE this section when Flask backend is connected.
    POST the form data to /api/shipments instead.
  */
  const shipment = {
    id: "LOCAL-" + Date.now(),
    farmerId: state.farmer.id,
    stockId: stock.id,
    product: stock.product,
    quantity: Number($("shipmentQuantity").value),
    unit: stock.unit,
    buyerId: $("shipmentBuyer").value || null,
    buyerName: null,
    deliveryDate: $("shipmentDeliveryDate").value,
    pickupLocation: $("pickupLocation").value,
    deliveryLocation: $("deliveryLocation").value,
    notes: $("shipmentNotes").value,
    status: "CREATED",
    transporterId: null,
    createdAt: new Date().toISOString()
  };

  state.shipments.push(shipment);
  saveLocal();
  $("shipmentForm").reset();
  showToast("Shipment request saved locally. Connect the backend to make it real.");
  openPage("shipments");
});

/* ---------- TRACKING ---------- */
function trackShipment(id) {
  openPage("track");
  $("trackId").value = id;
  loadTracking(id);
}

$("trackBtn").addEventListener("click", () => {
  const id = $("trackId").value.trim();
  if (!id) return showToast("Enter a shipment ID.");
  loadTracking(id);
});

/*
  BACKEND TODO:
  GET /api/shipments/:id
  GET /api/shipments/:id/history
*/
function loadTracking(id) {
  const shipment = state.shipments.find(s => s.id === id);

  if (!shipment) {
    $("trackingResult").innerHTML = `
      <div class="empty">
        Shipment not found in current frontend data.<br>
        When backend is connected, this area will load the real shipment by ID.
      </div>`;
    return;
  }

  $("trackingResult").classList.remove("empty");
  $("trackingResult").innerHTML = `
    <h2>${escapeHtml(shipment.id)}</h2>
    <p><strong>${escapeHtml(shipment.product)}</strong> — ${shipment.quantity} ${escapeHtml(shipment.unit)}</p>
    <p>Status: <span class="status">${formatStatus(shipment.status)}</span></p>
    <div class="timeline">
      <div class="timeline-item">
        <strong>Shipment Created</strong>
        <div class="meta">${formatDate(shipment.createdAt)}</div>
      </div>
      <div class="timeline-item">
        <strong>Current Status: ${formatStatus(shipment.status)}</strong>
        <div class="meta">Transporter: ${shipment.transporterId || "Not assigned"}</div>
      </div>
    </div>
  `;
}

/* ---------- HISTORY ---------- */
function renderHistory() {
  $("historyList").innerHTML = state.shipments.length
    ? state.shipments.map(s => `
        <div class="list-item">
          <h3>${escapeHtml(s.id)}</h3>
          <div class="meta">${escapeHtml(s.product)} • ${s.quantity} ${escapeHtml(s.unit)}</div>
          <div class="meta">Created: ${formatDate(s.createdAt)}</div>
          <span class="status">${formatStatus(s.status)}</span>
        </div>
      `).join("")
    : `<div class="empty">No shipment history yet.</div>`;
}

/* ---------- NOTIFICATIONS ---------- */
function renderNotifications() {
  $("notificationList").innerHTML = state.notifications.length
    ? state.notifications.map(n => `
      <div class="list-item">
        <h3>${escapeHtml(n.title)}</h3>
        <div class="meta">${escapeHtml(n.message)}</div>
        <div class="meta">${formatDate(n.createdAt)}</div>
      </div>
    `).join("")
    : `<div class="empty">No notifications yet.</div>`;
}

/* ---------- PROFILE ---------- */
function renderProfile() {
  $("profileName").value = state.farmer.name || "";
  $("profilePhone").value = state.farmer.phone || "";
  $("profileEmail").value = state.farmer.email || "";
  $("profileLocation").value = state.farmer.location || "";
  $("profileFarmSize").value = state.farmer.farmSize || "";
  $("profileCrops").value = state.farmer.crops || "";
}

$("profileForm").addEventListener("submit", e => {
  e.preventDefault();

  state.farmer = {
    ...state.farmer,
    name: $("profileName").value.trim(),
    phone: $("profilePhone").value.trim(),
    email: $("profileEmail").value.trim(),
    location: $("profileLocation").value.trim(),
    farmSize: $("profileFarmSize").value.trim(),
    crops: $("profileCrops").value.trim()
  };

  saveLocal();
  renderAll();
  showToast("Profile saved.");
});

$("logoutBtn").addEventListener("click", () => {
  /*
    BACKEND TODO:
    Replace with:
    await fetch('/api/logout', { method: 'POST' });
    window.location.href = '/login';
  */
  showToast("Backend logout will be connected here.");
});

/* ---------- HELPERS ---------- */
function renderAll() {
  renderDashboard();
  renderStock();
  renderShipments();
  renderHistory();
  renderNotifications();
  renderProfile();
}

function formatStatus(status) {
  return String(status).replaceAll("_", " ");
}

function formatDate(value) {
  if (!value) return "Unknown";
  const d = new Date(value);
  return isNaN(d) ? value : d.toLocaleString();
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

renderAll();
