import re

with open('pages/farmer.php', 'r', encoding='utf-8') as f:
    html = f.read()

# 1. Add Dashboard nav button
nav_old = r"""      <nav class="nav-menu">
        <button class="nav-btn active" data-page="inventory">"""
nav_new = r"""      <nav class="nav-menu">
        <button class="nav-btn active" data-page="dashboard">
          <i class="fa-solid fa-house"></i> Dashboard
        </button>
        <button class="nav-btn" data-page="inventory">"""
html = html.replace(nav_old, nav_new)

# 2. Add Dashboard section
content_old = r"""      <div class="content-area">
        <!-- INVENTORY PAGE -->
        <section class="page active" id="inventory">"""

dashboard_html = r"""      <div class="content-area">
        <!-- DASHBOARD PAGE -->
        <section class="page active" id="dashboard">
          <div class="stats-grid">
            <div class="stat-card primary-stat">
              <div class="stat-icon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
              <div class="stat-info">
                <p>Total Revenue</p>
                <h3 id="dashRevenue">₹0.00</h3>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
              <div class="stat-info">
                <p>Active Inventory</p>
                <h3 id="dashActiveInventory">0</h3>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-truck-fast"></i></div>
              <div class="stat-info">
                <p>Pending Orders</p>
                <h3 id="dashPendingOrders">0</h3>
              </div>
            </div>
          </div>
          
          <div class="split-view mt-4">
            <div class="panel">
              <div class="panel-header">
                <h2>Recent Orders</h2>
                <button class="btn-text" onclick="document.querySelector('[data-page=\'shipments\']').click()">View All</button>
              </div>
              <div id="dashRecentOrders" class="compact-list" style="margin-top:15px;"></div>
            </div>
            <div class="panel">
              <div class="panel-header">
                <h2>Low Stock Produce</h2>
                <button class="btn-text" onclick="document.querySelector('[data-page=\'inventory\']').click()">Manage</button>
              </div>
              <div id="dashLowStock" class="compact-list" style="margin-top:15px;"></div>
            </div>
          </div>
        </section>

        <!-- INVENTORY PAGE -->
        <section class="page" id="inventory">"""
html = html.replace(content_old, dashboard_html)

with open('pages/farmer.php', 'w', encoding='utf-8') as f:
    f.write(html)
print("farmer.php updated")
