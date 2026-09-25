import re

with open('pages/buyer.php', 'r', encoding='utf-8') as f:
    html = f.read()

# 1. Add the Dashboard button back to the sidebar
nav_replacement = '''<button class="nav-btn active" data-page="dashboard">
          <i class="fa-solid fa-house"></i> Dashboard
        </button>
        <button class="nav-btn" data-page="findProduce">'''
html = html.replace('<button class="nav-btn active" data-page="findProduce">', nav_replacement)

# 2. Add the Dashboard section
dashboard_section = '''
        <!-- DASHBOARD -->
        <section class="page active" id="dashboard">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-box"></i></div>
              <div class="stat-info"><p>Active Orders</p><h3 id="dashActiveOrders">0</h3></div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-truck-fast"></i></div>
              <div class="stat-info"><p>Delivered</p><h3 id="dashDeliveredOrders">0</h3></div>
            </div>
            <div class="stat-card">
              <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
              <div class="stat-info"><p>Total Searches</p><h3 id="dashTotalSearches">0</h3></div>
            </div>
          </div>
          <div class="split-view">
            <div class="panel">
              <div class="panel-header"><h2>Recent Searches</h2></div>
              <div id="dashSearchList" class="compact-list" style="margin-top:15px;"></div>
            </div>
            <div class="panel">
              <div class="panel-header">
                <h2>Recent Orders</h2>
                <button class="btn-text" onclick="document.querySelector('[data-page=\\'orders\\']').click()">View All</button>
              </div>
              <div id="dashOrderList" class="compact-list" style="margin-top:15px;"></div>
            </div>
          </div>
        </section>

        <!-- BROWSE PRODUCE -->
        <section class="page" id="findProduce">'''
html = html.replace('<!-- BROWSE PRODUCE -->\n        <section class="page active" id="findProduce">', dashboard_section)

with open('pages/buyer.php', 'w', encoding='utf-8') as f:
    f.write(html)

print('buyer.php updated successfully')
