import os

farmer_php = r"""<?php
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
    <aside class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-leaf"></i>
        <span>FarmTrack</span>
      </div>
      
      <nav class="nav-menu">
        <button class="nav-btn active" data-page="inventory">
          <i class="fa-solid fa-boxes-stacked"></i> Inventory
        </button>
        <button class="nav-btn" data-page="shipments">
          <i class="fa-solid fa-truck-fast"></i> Shipments <span id="pendingBadge" style="background:red; color:white; border-radius:50%; padding:2px 6px; font-size:10px; display:none;">New</span>
        </button>
      </nav>
      <button type="button" class="logout-btn" id="logoutBtn">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </button>
    </aside>

    <main class="main-content">
      <header class="top-header">
        <div class="header-titles">
          <h1 id="pageTitle">Manage Inventory</h1>
        </div>
        <div class="user-profile-sm">
          <div class="avatar"><i class="fa-solid fa-user"></i></div>
          <span><?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </header>

      <div class="content-area">
        <!-- INVENTORY PAGE -->
        <section class="page active" id="inventory">
          <div class="page-actions">
            <h2>Manage Inventory</h2>
            <button class="btn-primary" id="addInventoryBtn"><i class="fa-solid fa-plus"></i> Add Produce</button>
          </div>
          <div id="inventoryList" class="data-grid"></div>

          <!-- Add Modal -->
          <div class="modal hidden" id="inventoryModal">
            <div class="modal-content panel">
              <div class="modal-header">
                <h2>Add Produce</h2>
                <button class="close-modal"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <form id="inventoryForm" class="standard-form">
                <div class="form-row">
                  <div class="form-group">
                    <label>Produce Name</label>
                    <input id="invName" required placeholder="e.g., Basmati Rice">
                  </div>
                </div>
                <div class="form-row two-col">
                  <div class="form-group">
                    <label>Quantity (kg)</label>
                    <input id="invQty" type="number" min="1" required placeholder="0">
                  </div>
                </div>
                <div class="form-row two-col">
                  <div class="form-group">
                    <label>Available Date</label>
                    <input id="invDate" type="date" required>
                  </div>
                  <div class="form-group">
                    <label>Price per kg (₹)</label>
                    <input id="invPrice" type="number" min="1" step="0.01" required placeholder="0.00">
                  </div>
                </div>
                <div class="form-actions mt-4">
                  <button type="button" class="btn-secondary close-modal">Cancel</button>
                  <button type="submit" class="btn-primary">Save Inventory</button>
                </div>
              </form>
            </div>
          </div>
        </section>

        <!-- SHIPMENTS PAGE -->
        <section class="page" id="shipments">
          <div class="page-actions">
            <h2>Manage Shipments / Orders</h2>
          </div>
          <div id="shipmentsList"></div>
        </section>
      </div>
    </main>
  </div>
  <div id="toast" class="toast">Action completed successfully.</div>
  <script src="js/app.js"></script>
</body>
</html>
"""
with open('pages/farmer.php', 'w', encoding='utf-8') as f:
    f.write(farmer_php)

print("farmer.php written")
