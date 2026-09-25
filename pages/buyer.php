<?php
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
    .tracking-container { border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: white; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .tracking-header { text-align: center; margin-bottom: 20px; }
    .tracking-number-box { padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; display: inline-block; font-weight: bold; color: #374151; }
    .progress-bar-container { display: flex; justify-content: space-between; align-items: center; position: relative; margin: 40px 0; }
    .progress-line { position: absolute; top: 50%; left: 0; width: 100%; height: 4px; background: #e5e7eb; z-index: 1; transform: translateY(-50%); }
    .progress-line-active { position: absolute; top: 50%; left: 0; height: 4px; background: #16a34a; z-index: 2; transform: translateY(-50%); transition: width 0.3s; }
    .step { position: relative; z-index: 3; text-align: center; background: white; padding: 0 10px; }
    .step-icon { width: 40px; height: 40px; border-radius: 50%; background: #e5e7eb; color: #9ca3af; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 18px; }
    .step.active .step-icon { background: #b91c1c; color: white; }
    .step.completed .step-icon { background: #16a34a; color: white; }
    .step-label { font-size: 12px; font-weight: bold; color: #4b5563; }
    
    .truck-animation-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.9); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .truck-container { font-size: 80px; color: #16a34a; animation: drive 2s ease-in-out infinite; }
    @keyframes drive {
        0% { transform: translateX(-100px); opacity: 0; }
        20% { opacity: 1; }
        80% { opacity: 1; }
        100% { transform: translateX(100px); opacity: 0; }
    }
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
        <button class="nav-btn" data-page="cartPage">
          <i class="fa-solid fa-cart-shopping"></i> My Cart <span class="cart-badge" id="cartCountSide">0</span>
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
          <div class="user-profile-sm">
            <div class="avatar"><i class="fa-solid fa-user"></i></div>
            <span><?= htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>
      </header>

      <div class="content-area">
        <!-- BROWSE PRODUCE -->
        <section class="page active" id="findProduce">
          <div class="panel">
            <div class="search-box">
              <input id="productSearch" type="text" placeholder="Type at least 3 characters to search (e.g. wheat)..." autocomplete="off">
              <button type="button" class="btn-primary" id="searchBtn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
          </div>
          <div class="panel search-results-panel mt-4">
            <div class="panel-header">
              <h2>Available Produce from Farmers</h2>
            </div>
            <div id="searchResults" class="data-grid">
                <p style="padding:20px; color:#6b7280;">Start typing to see live results...</p>
            </div>
          </div>
        </section>

        <!-- CART & CHECKOUT PAGE -->
        <section class="page" id="cartPage">
          <div class="page-actions">
            <h2>Your Cart & Checkout</h2>
          </div>
          <div class="split-view" style="display:flex; gap:20px; align-items: flex-start;">
            <div class="panel" style="flex: 1.5;">
                <div class="panel-header"><h3>Cart Items</h3></div>
                <div id="cartItemsList" style="margin-top: 15px;"></div>
                <h3 style="text-align:right; margin-top: 20px;">Total: ₹<span id="cartTotal">0.00</span></h3>
            </div>
            <div class="panel" style="flex: 1;">
                <div class="panel-header"><h3>Delivery Details</h3></div>
                <form id="checkoutForm" class="standard-form mt-4">
                    <div class="form-group"><label>Full Name</label><input id="chkName" required value="<?= htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="form-group"><label>Phone Number</label><input id="chkPhone" required></div>
                    <div class="form-group"><label>Email</label><input type="email" id="chkEmail"></div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select id="chkPayment" required>
                            <option value="cod">Cash on Delivery (COD)</option>
                            <option value="upi" disabled>UPI (Coming Soon)</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Delivery Address</label><textarea id="chkAddress" required></textarea></div>
                    <button type="submit" class="btn-primary w-full mt-4" style="padding:15px; font-size:16px;">Place Order</button>
                </form>
            </div>
          </div>
        </section>

        <!-- MY ORDERS -->
        <section class="page" id="orders">
          <div class="page-actions">
            <h2>Track My Orders</h2>
          </div>
          <div id="orderList"></div>
        </section>
      </div>
    </main>
  </div>

  <div id="truckAnimation" class="truck-animation-overlay hidden">
    <div class="truck-container">
        <i class="fa-solid fa-truck-fast"></i>
    </div>
    <h1 style="color:#16a34a; margin-top:20px; font-size: 2rem;">Your Order is in process!</h1>
    <p style="color:#6b7280; margin-top:10px;">Waiting for farmer approval...</p>
  </div>

  <div id="toast" class="toast">Action completed successfully.</div>
  <script src="js/buyer.js"></script>
</body>
</html>
