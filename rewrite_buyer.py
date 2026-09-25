import os
import re

buyer_php = r"""<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmTrack - Buyer Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .cart-btn { position: relative; }
    .cart-badge { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; }
  </style>
</head>
<body data-user-name="<?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <div class="app-container">
    <aside class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-leaf"></i>
        <span>FarmTrack</span>
      </div>
      <nav class="nav-menu">
        <button class="nav-btn active" data-page="findProduce">
          <i class="fa-solid fa-magnifying-glass"></i> Browse Produce
        </button>
        <button class="nav-btn" data-page="orders">
          <i class="fa-solid fa-box-open"></i> My Orders
        </button>
      </nav>
      <button type="button" class="logout-btn" id="logoutBtn">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </button>
    </aside>

    <main class="main-content">
      <header class="top-header">
        <div class="header-titles">
          <h1 id="pageTitle">Browse Produce</h1>
        </div>
        <div style="display:flex; gap:15px; align-items:center;">
          <button class="btn-primary cart-btn" id="openCartBtn">
            <i class="fa-solid fa-cart-shopping"></i> Cart <span class="cart-badge" id="cartCount">0</span>
          </button>
          <div class="user-profile-sm">
            <div class="avatar"><i class="fa-solid fa-user"></i></div>
            <span><?= htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>
      </header>

      <div class="content-area">
        <section class="page active" id="findProduce">
          <div class="panel">
            <div class="search-box">
              <input id="productSearch" type="text" placeholder="Search for wheat, rice, etc..." autocomplete="off">
              <button type="button" class="btn-primary" id="searchBtn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
          </div>
          <div class="panel search-results-panel mt-4">
            <div class="panel-header">
              <h2>Available Produce from Farmers</h2>
            </div>
            <div id="searchResults" class="data-grid"></div>
          </div>
        </section>

        <section class="page" id="orders">
          <div class="page-actions">
            <h2>My Orders</h2>
          </div>
          <div id="orderList" class="list-container"></div>
        </section>
      </div>
    </main>
  </div>

  <!-- Cart Modal -->
  <div class="modal hidden" id="cartModal">
    <div class="modal-content panel" style="max-width: 600px;">
      <div class="modal-header">
        <h2>Your Cart & Checkout</h2>
        <button class="close-modal" id="closeCartModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div id="cartItemsList" style="margin-bottom:20px; max-height:200px; overflow-y:auto;">
        <!-- Cart items -->
      </div>
      <h3 style="text-align:right; margin-bottom: 20px;">Total: ₹<span id="cartTotal">0.00</span></h3>
      
      <form id="checkoutForm" class="standard-form">
        <h4>Delivery Details</h4>
        <div class="form-row">
          <div class="form-group"><label>Full Name</label><input id="chkName" required value="<?= htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
          <div class="form-group"><label>Phone Number</label><input id="chkPhone" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Email</label><input type="email" id="chkEmail"></div>
          <div class="form-group">
            <label>Payment Method</label>
            <select id="chkPayment" required>
              <option value="cod">Cash on Delivery (COD)</option>
              <option value="upi" disabled>UPI (Coming Soon)</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Delivery Address</label><textarea id="chkAddress" required></textarea></div>
        </div>
        <div class="form-actions mt-4">
          <button type="button" class="btn-secondary close-modal" id="cancelCheckout">Cancel</button>
          <button type="submit" class="btn-primary">Place Order</button>
        </div>
      </form>
    </div>
  </div>

  <div id="toast" class="toast">Action completed successfully.</div>
  <script src="js/buyer.js"></script>
</body>
</html>
"""

with open('pages/buyer.php', 'w', encoding='utf-8') as f:
    f.write(buyer_php)

print("buyer.php written")
