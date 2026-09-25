<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'transporter') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmTrack - Transporter Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body data-user-name="<?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>">
  <div class="app-container">
    <aside class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-truck-fast"></i>
        <span>Logistics</span>
      </div>
      <nav class="nav-menu">
        <button class="nav-btn active" data-page="scannerPage">
          <i class="fa-solid fa-qrcode"></i> Scan Package
        </button>
        <button class="nav-btn" data-page="deliveriesPage">
          <i class="fa-solid fa-box"></i> My Deliveries
        </button>
      </nav>
      <button type="button" class="logout-btn" id="logoutBtn">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </button>
    </aside>

    <main class="main-content">
      <header class="top-header">
        <div class="header-titles">
          <h1 id="pageTitle">Package Scanner</h1>
        </div>
        <div class="user-profile-sm">
          <div class="avatar"><i class="fa-solid fa-user"></i></div>
          <span><?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </header>

      <div class="content-area">
        <!-- SCANNER PAGE -->
        <section class="page active" id="scannerPage">
          <div class="panel" style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div class="panel-header" style="justify-content:center;">
              <h2><i class="fa-solid fa-camera"></i> Scan QR Code</h2>
            </div>
            <p style="color:#6b7280; margin: 15px 0;">Hold the package QR code up to your camera to update its tracking status automatically.</p>
            
            <div id="qr-reader" style="width:100%; margin: 20px 0;"></div>
            
            <div id="scanResult" style="display:none; padding:15px; margin-top:20px; border-radius:8px; border:1px solid #16a34a; background:#f0fdf4;">
                <h3 style="color:#16a34a; margin-bottom:5px;"><i class="fa-solid fa-circle-check"></i> Scan Successful!</h3>
                <p style="color:#374151;" id="scanMessage"></p>
            </div>
          </div>
        </section>

        <!-- DELIVERIES PAGE -->
        <section class="page" id="deliveriesPage">
          <div class="page-actions">
            <h2>My Delivery History</h2>
          </div>
          <div id="deliveriesList">
              <p style="padding:20px;">Loading deliveries...</p>
          </div>
        </section>
      </div>
    </main>
  </div>

  <div id="toast" class="toast">Action completed successfully.</div>
  
  <script src="js/transporter.js"></script>
</body>
</html>
