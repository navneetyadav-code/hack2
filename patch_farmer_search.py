import re

with open('pages/farmer.php', 'r', encoding='utf-8') as f:
    html = f.read()

search_bar = r"""        <!-- SHIPMENTS PAGE -->
        <section class="page" id="shipments">
          <div class="page-actions">
            <h2>Manage Shipments / Orders</h2>
          </div>
          
          <div class="panel" style="margin-bottom: 20px;">
            <div class="search-box">
              <input id="orderSearch" type="text" placeholder="Search orders by tracking number, buyer name, or status..." autocomplete="off">
              <button type="button" class="btn-primary" style="pointer-events:none;"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
          </div>
          
          <div id="shipmentsList"></div>"""

original = r"""        <!-- SHIPMENTS PAGE -->
        <section class="page" id="shipments">
          <div class="page-actions">
            <h2>Manage Shipments / Orders</h2>
          </div>
          <div id="shipmentsList"></div>"""

html = html.replace(original, search_bar)

with open('pages/farmer.php', 'w', encoding='utf-8') as f:
    f.write(html)
