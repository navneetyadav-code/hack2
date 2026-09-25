<?php 
   session_start();
   include "../db_conn.php";
   if (isset($_SESSION['username']) && isset($_SESSION['id']))    ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmTrack - Farmer Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="brand">🌾 FarmTrack</div>
      <div class="role">Farmer Account</div>
      <nav>
        <button class="nav-btn active" data-page="dashboard">🏠 Dashboard</button>
        <button class="nav-btn" data-page="stock">🌾 My Stock</button>
        <button class="nav-btn" data-page="shipments">📦 My Shipments</button>
        <button class="nav-btn" data-page="create">➕ Create Shipment</button>
        <button class="nav-btn" data-page="track">📍 Track Shipment</button>
        <button class="nav-btn" data-page="history">📜 History</button>
        <button class="nav-btn" data-page="notifications">🔔 Notifications</button>
        <button class="nav-btn" data-page="profile">👤 Profile</button>
      </nav>
      <button class="logout" id="logoutBtn">Logout</button>
    </aside>

    <main class="main">
      <header class="topbar">
        <div>
          <h1 id="pageTitle">Farmer Dashboard</h1>
          <p id="welcomeText">Welcome back, Farmer.</p>
        </div>
        <div class="user-chip">👨‍🌾 <span id="farmerNameTop">Farmer</span></div>
      </header>

      <!-- DASHBOARD -->
      <section class="page active" id="dashboard">
        <div class="cards">
          <div class="card"><span>🌾</span><h3 id="stockCount">0</h3><p>Stock Items</p></div>
          <div class="card"><span>📦</span><h3 id="shipmentCount">0</h3><p>My Shipments</p></div>
          <div class="card"><span>🚚</span><h3 id="transitCount">0</h3><p>In Transit</p></div>
          <div class="card"><span>✅</span><h3 id="deliveredCount">0</h3><p>Delivered</p></div>
        </div>

        <div class="grid-2">
          <div class="panel">
            <div class="panel-head">
              <h2>My Stock</h2>
              <button class="small-btn" data-go="stock">Manage</button>
            </div>
            <div id="dashboardStock" class="empty">No stock added yet.</div>
          </div>
          <div class="panel">
            <div class="panel-head">
              <h2>Recent Shipments</h2>
              <button class="small-btn" data-go="shipments">View All</button>
            </div>
            <div id="dashboardShipments" class="empty">No shipments yet.</div>
          </div>
        </div>
      </section>

      <!-- STOCK -->
      <section class="page" id="stock">
        <div class="section-head">
          <div>
            <h2>My Stock</h2>
            <p>Manage produce available on your farm.</p>
          </div>
          <button class="primary" id="addStockBtn">+ Add Stock</button>
        </div>

        <div id="stockList" class="list"></div>

        <div class="panel form-panel hidden" id="stockFormPanel">
          <h2 id="stockFormTitle">Add Stock</h2>
          <form id="stockForm">
            <input type="hidden" id="stockId">
            <div class="form-grid">
              <label>Product
                <input id="stockProduct" required placeholder="e.g. Wheat">
              </label>
              <label>Quantity
                <input id="stockQuantity" type="number" min="0" required placeholder="500">
              </label>
              <label>Unit
                <select id="stockUnit">
                  <option>kg</option><option>quintal</option><option>ton</option>
                </select>
              </label>
              <label>Available From
                <input id="stockAvailableFrom" type="date">
              </label>
              <label>Expected Delivery Date
                <input id="stockDeliveryDate" type="date">
              </label>
              <label>Notes
                <input id="stockNotes" placeholder="Quality, grade, etc.">
              </label>
            </div>
            <div class="form-actions">
              <button type="submit" class="primary">Save Stock</button>
              <button type="button" class="secondary" id="cancelStockBtn">Cancel</button>
            </div>
          </form>
        </div>
      </section>

      <!-- SHIPMENTS -->
      <section class="page" id="shipments">
        <div class="section-head">
          <div><h2>My Shipments</h2><p>Only real shipments returned by the backend will appear here.</p></div>
          <button class="primary" data-go="create">+ Create Shipment</button>
        </div>
        <div id="shipmentList" class="list"></div>
      </section>

      <!-- CREATE -->
      <section class="page" id="create">
        <div class="panel form-panel">
          <h2>Create Shipment Request</h2>
          <p class="muted">This creates a shipment request for the backend. Buyer and transporter information should be loaded from the backend later.</p>

          <form id="shipmentForm">
            <div class="form-grid">
              <label>Stock Item
                <select id="shipmentStock" required></select>
              </label>
              <label>Quantity
                <input id="shipmentQuantity" type="number" min="1" required>
              </label>
              <label>Buyer
                <select id="shipmentBuyer">
                  <option value="">Select buyer from backend</option>
                </select>
              </label>
              <label>Expected Delivery Date
                <input id="shipmentDeliveryDate" type="date" required>
              </label>
              <label>Pickup Location
                <input id="pickupLocation" placeholder="Farm / village / collection point">
              </label>
              <label>Delivery Location
                <input id="deliveryLocation" placeholder="Buyer destination">
              </label>
            </div>
            <label>Additional Notes
              <textarea id="shipmentNotes" rows="4" placeholder="Any details for buyer/transporter..."></textarea>
            </label>
            <div class="form-actions">
              <button type="submit" class="primary">Create Shipment Request</button>
              <button type="reset" class="secondary">Clear</button>
            </div>
          </form>
        </div>
        <div class="info-box">
          <strong>Backend-ready:</strong> This page does not create fake orders. On backend integration,
          submit the form to something like <code>POST /api/shipments</code> and use the returned shipment ID.
        </div>
      </section>

      <!-- TRACK -->
      <section class="page" id="track">
        <div class="section-head">
          <div><h2>Track Shipment</h2><p>Enter a real shipment ID returned by the backend.</p></div>
        </div>
        <div class="track-search">
          <input id="trackId" placeholder="e.g. SHP-2026-001">
          <button class="primary" id="trackBtn">Track</button>
        </div>
        <div id="trackingResult" class="panel empty">No shipment selected.</div>
      </section>

      <!-- HISTORY -->
      <section class="page" id="history">
        <div class="section-head"><div><h2>Shipment History</h2><p>Historical events will be loaded from the backend.</p></div></div>
        <div id="historyList" class="list"></div>
      </section>

      <!-- NOTIFICATIONS -->
      <section class="page" id="notifications">
        <div class="section-head"><div><h2>Notifications</h2><p>Only notifications received from the backend will appear here.</p></div></div>
        <div id="notificationList" class="list"></div>
      </section>

      <!-- PROFILE -->
      <section class="page" id="profile">
        <div class="panel form-panel">
          <h2>Farmer Profile</h2>
          <form id="profileForm">
            <div class="form-grid">
              <label>Full Name<input id="profileName" placeholder="Farmer name"></label>
              <label>Phone<input id="profilePhone" placeholder="Phone number"></label>
              <label>Email<input id="profileEmail" type="email" placeholder="Email"></label>
              <label>Village / Farm Location<input id="profileLocation" placeholder="Village"></label>
              <label>Farm Size<input id="profileFarmSize" placeholder="e.g. 5 acres"></label>
              <label>Main Crops<input id="profileCrops" placeholder="Wheat, Rice"></label>
            </div>
            <button class="primary" type="submit">Save Profile</button>
          </form>
        </div>
      </section>
    </main>
  </div>

  <div id="toast" class="toast"></div>
  <script src="js/app.js"></script>
</body>
</html>
