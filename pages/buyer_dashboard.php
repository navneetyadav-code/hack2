<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmTrack - Buyer Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body data-user-name="<?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

  <div class="app-container">

    <aside class="sidebar">

      <div class="brand">
        <i class="fa-solid fa-leaf"></i>
        <span>FarmTrack</span>
      </div>

      <nav class="nav-menu">

        <button class="nav-btn active" data-page="dashboard">
          <i class="fa-solid fa-house"></i>
          Dashboard
        </button>

        <button class="nav-btn" data-page="findProduce">
          <i class="fa-solid fa-magnifying-glass"></i>
          Find Produce
        </button>

        <button class="nav-btn" data-page="farmers">
          <i class="fa-solid fa-tractor"></i>
          Farmers
        </button>

        <button class="nav-btn" data-page="orders">
          <i class="fa-solid fa-cart-shopping"></i>
          My Orders
        </button>

        <button class="nav-btn" data-page="profile">
          <i class="fa-solid fa-user"></i>
          Profile
        </button>

      </nav>

      <button type="button" class="logout-btn" id="logoutBtn">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
      </button>

    </aside>

    <main class="main-content">

      <header class="top-header">

        <div class="header-titles">
          <h1 id="pageTitle">Overview</h1>
        </div>

        <div class="user-profile-sm">
          <div class="avatar">
            <i class="fa-solid fa-user"></i>
          </div>

          <span id="buyerNameTop">
            <?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
          </span>
        </div>

      </header>

      <div class="content-area">

        <section class="page active" id="dashboard">

          <div class="stats-grid">

            <div class="stat-card primary-stat">
              <div class="stat-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
              </div>

              <div class="stat-info">
                <p>Products Found</p>
                <h3 id="dashProductsFound">0</h3>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">
                <i class="fa-solid fa-cart-shopping"></i>
              </div>

              <div class="stat-info">
                <p>Active Orders</p>
                <h3 id="dashActiveOrders">0</h3>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">
                <i class="fa-solid fa-truck-fast"></i>
              </div>

              <div class="stat-info">
                <p>Deliveries</p>
                <h3 id="dashDeliveries">0</h3>
              </div>
            </div>

          </div>

          <div class="split-view">

            <div class="panel">

              <div class="panel-header">
                <h2>Recent Searches</h2>

                <button class="btn-text" data-go="findProduce">
                  Search Produce
                </button>
              </div>

              <div id="dashSearchList" class="compact-list"></div>

            </div>

            <div class="panel">

              <div class="panel-header">
                <h2>Recent Orders</h2>

                <button class="btn-text" data-go="orders">
                  View All
                </button>
              </div>

              <div id="dashOrderList" class="compact-list"></div>

            </div>

          </div>

        </section>


        <section class="page" id="findProduce">

          <div class="page-actions">
            <h2>Find Produce</h2>
          </div>

          <div class="panel">

            <form id="productSearchForm" class="standard-form">

              <div class="form-row">

                <div class="form-group">

                  <label>Search Product</label>

                  <div class="search-box">

                    <input
                      id="productSearch"
                      type="text"
                      placeholder="Enter the product you want"
                      autocomplete="off"
                    >

                    <button type="submit" class="btn-primary">
                      <i class="fa-solid fa-magnifying-glass"></i>
                      Search
                    </button>

                  </div>

                </div>

              </div>

            </form>

          </div>

          <div class="panel search-results-panel">

            <div class="panel-header">

              <h2>Available Farmers</h2>

              <span id="searchResultText"></span>

            </div>

            <div id="farmerSearchResults" class="data-grid">

            </div>

          </div>

        </section>


        <section class="page" id="farmers">

          <div class="page-actions">
            <h2>Farmers</h2>
          </div>

          <div class="panel">

            <div class="panel-header">
              <h2>Farmers With Available Produce</h2>
            </div>

            <div id="farmerList" class="data-grid">

            </div>

          </div>

        </section>


        <section class="page" id="orders">

          <div class="page-actions">
            <h2>My Orders</h2>
          </div>

          <div class="status-tabs">

            <button class="tab-btn active" data-filter="all">
              All
            </button>

            <button class="tab-btn" data-filter="pending">
              Pending
            </button>

            <button class="tab-btn" data-filter="confirmed">
              Confirmed
            </button>

            <button class="tab-btn" data-filter="transit">
              In Transit
            </button>

            <button class="tab-btn" data-filter="delivered">
              Delivered
            </button>

          </div>

          <div id="orderList" class="list-container">

          </div>

        </section>


        <section class="page" id="profile">

          <div class="panel max-w-md mx-auto">

            <h2>Buyer Profile</h2>

            <form id="profileForm" class="standard-form mt-4">

              <div class="form-group">

                <label>Buyer Name</label>

                <input
                  id="profName"
                  required
                >

              </div>

              <div class="form-row two-col">

                <div class="form-group">

                  <label>Contact Number</label>

                  <input
                    id="profPhone"
                    type="tel"
                  >

                </div>

                <div class="form-group">

                  <label>Business / Organization</label>

                  <input
                    id="profBusiness"
                  >

                </div>

              </div>

              <div class="form-group">

                <label>Location</label>

                <input
                  id="profLoc"
                >

              </div>

              <div class="form-actions mt-4">

                <button
                  type="submit"
                  class="btn-primary w-full"
                >
                  Update Profile
                </button>

              </div>

            </form>

          </div>

        </section>

      </div>

    </main>

  </div>

  <div id="toast" class="toast">
    Action completed successfully.
  </div>

  <script src="js/buyer.js"></script>

</body>
</html>