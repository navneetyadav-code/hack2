<?php
session_start();
 if (!isset($_SESSION['username'], $_SESSION['id'], $_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
   header('Location: ../index.php');
   exit;
 }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmTrack - Farmer Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body data-user-name="<?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>">
  <div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-leaf"></i>
        <span>FarmTrack</span>
      </div>
      
      <nav class="nav-menu">
        <button class="nav-btn active" data-page="dashboard">
          <i class="fa-solid fa-house"></i> Dashboard
        </button>
        <button class="nav-btn" data-page="inventory">
          <i class="fa-solid fa-boxes-stacked"></i> Inventory
        </button>
        <button class="nav-btn" data-page="shipments">
          <i class="fa-solid fa-truck-fast"></i> Shipments
        </button>
        <button class="nav-btn" data-page="profile">
          <i class="fa-solid fa-user"></i> Profile
        </button>
      </nav>

      <button type="button" class="logout-btn" id="logoutBtn">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </button>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="top-header">
        <div class="header-titles">
          <h1 id="pageTitle">Overview</h1>
        </div>
        <div class="user-profile-sm">
          <div class="avatar"><i class="fa-solid fa-user-tie"></i></div>
          <span id="farmerNameTop"><?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </header>

      <div class="content-area">
        
        <!-- DASHBOARD PAGE -->
        <section class="page active" id="dashboard">
          <div class="stats-grid">
            <div class="stat-card primary-stat">
              <div class="stat-icon"><i class="fa-solid fa-wheat-awn"></i></div>
              <div class="stat-info">
                <p>Active Inventory</p>
                <h3 id="dashInventoryCount">0</h3>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-boxes-packing"></i></div>
              <div class="stat-info">
                <p>Pending Shipments</p>
                <h3 id="dashPendingCount">0</h3>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-route"></i></div>
              <div class="stat-info">
                <p>In Transit</p>
                <h3 id="dashTransitCount">0</h3>
              </div>
            </div>
          </div>

          <div class="split-view">
            <div class="panel">
              <div class="panel-header">
                <h2>Recent Inventory</h2>
                <button class="btn-text" data-go="inventory">View All</button>
              </div>
              <div id="dashInventoryList" class="compact-list"></div>
            </div>
            <div class="panel">
              <div class="panel-header">
                <h2>Active Shipments</h2>
                <button class="btn-text" data-go="shipments">View All</button>
              </div>
              <div id="dashShipmentList" class="compact-list"></div>
            </div>
          </div>
        </section>

        <!-- INVENTORY PAGE (Combined Stock) -->
        <section class="page" id="inventory">
          <div class="page-actions">
            <h2>Manage Inventory</h2>
            <button class="btn-primary" id="addInventoryBtn"><i class="fa-solid fa-plus"></i> Add Produce</button>
          </div>

          <div id="inventoryList" class="data-grid">
            <!-- Inventory items loaded here -->
          </div>

          <!-- Add/Edit Modal -->
          <div class="modal hidden" id="inventoryModal">
            <div class="modal-content panel">
              <div class="modal-header">
                <h2 id="inventoryFormTitle">Add Produce</h2>
                <button class="close-modal" id="closeInventoryModal"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <form id="inventoryForm" class="standard-form">
                <input type="hidden" id="invId">
                <div class="form-row">
                  <div class="form-group">
                    <label>Produce Name</label>
                    <input id="invName" required placeholder="e.g., Basmati Rice">
                  </div>
                </div>
                <div class="form-row two-col">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input id="invQty" type="number" min="0" required placeholder="0">
                  </div>
                  <div class="form-group">
                    <label>Unit</label>
                    <select id="invUnit">
                      <option value="kg">Kilograms (kg)</option>
                      <option value="quintal">Quintals</option>
                      <option value="ton">Tons</option>
                    </select>
                  </div>
                </div>
                <div class="form-row two-col">
                  <div class="form-group">
                    <label>Available Date</label>
                    <input id="invDate" type="date" required>
                  </div>
                  <div class="form-group">
                    <label>Price per kg (₹)</label>
                    <input id="invPrice" type="number" min="0" step="0.01" required placeholder="0.00">
                  </div>
                </div>
                <div class="form-actions">
                  <button type="button" class="btn-secondary close-modal">Cancel</button>
                  <button type="submit" class="btn-primary">Save Inventory</button>
                </div>
              </form>
            </div>
          </div>
        </section>

        <!-- SHIPMENTS PAGE (Combined Create & Track) -->
        <section class="page" id="shipments">
          <div class="page-actions">
            <h2>Shipments</h2>
            <button class="btn-primary" id="createShipmentBtn"><i class="fa-solid fa-truck-arrow-right"></i> New Shipment</button>
          </div>

          <div class="status-tabs">
            <button class="tab-btn active" data-filter="all">All</button>
            <button class="tab-btn" data-filter="pending">Pending</button>
            <button class="tab-btn" data-filter="transit">In Transit</button>
            <button class="tab-btn" data-filter="delivered">Delivered</button>
          </div>

          <div id="shipmentListFull" class="list-container">
            <!-- Shipments loaded here -->
          </div>

          <!-- Create Shipment Modal -->
          <div class="modal hidden" id="shipmentModal">
            <div class="modal-content panel">
              <div class="modal-header">
                <h2>Create Shipment Request</h2>
                <button class="close-modal" id="closeShipmentModal"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <form id="shipmentForm" class="standard-form">
                <div class="form-row">
                  <div class="form-group">
                    <label>Select Produce from Inventory</label>
                    <select id="shipInvSelect" required></select>
                  </div>
                </div>
                <div class="form-row two-col">
                  <div class="form-group">
                    <label>Quantity to Ship</label>
                    <input id="shipQty" type="number" min="1" required>
                  </div>
                  <div class="form-group">
                    <label>Target Delivery Date</label>
                    <input id="shipDate" type="date" required>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Destination / Buyer Note</label>
                    <input id="shipDest" placeholder="City or Buyer details" required>
                  </div>
                </div>
                <div class="form-actions">
                  <button type="button" class="btn-secondary close-modal">Cancel</button>
                  <button type="submit" class="btn-primary">Request Shipment</button>
                </div>
              </form>
            </div>
          </div>
        </section>

        <!-- PROFILE PAGE -->
        <section class="page" id="profile">
          <div class="panel max-w-md mx-auto">
            <h2>Farm Profile</h2>
            <form id="profileForm" class="standard-form mt-4">
              <div class="form-group">
                <label>Farmer Name</label>
                <input id="profName" required>
              </div>
              <div class="form-row two-col">
                <div class="form-group">
                  <label>Contact Number</label>
                  <input id="profPhone" type="tel">
                </div>
                <div class="form-group">
                  <label>Farm Size (Acres)</label>
                  <input id="profSize" type="number" min="0" step="0.1">
                </div>
              </div>
              <div class="form-group">
                <label>Farm Location (Village/City)</label>
                <input id="profLoc">
              </div>
              <div class="form-actions mt-4">
                <button type="submit" class="btn-primary w-full">Update Profile</button>
              </div>
            </form>
          </div>
        </section>

      </div>
    </main>
  </div>

  <div id="toast" class="toast">Action completed successfully.</div>
  
  <script src="js/app.js"></script>
</body>
</html>