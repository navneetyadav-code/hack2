import re

with open('pages/buyer.php', 'r', encoding='utf-8') as f:
    html = f.read()

modal_html = r"""
  <!-- TRACKING MODAL -->
  <div class="modal hidden" id="trackModal">
    <div class="modal-content panel" style="max-width: 700px; width: 90%;">
      <div class="modal-header">
        <h2>Order Tracking</h2>
        <button class="close-modal" onclick="document.getElementById('trackModal').classList.add('hidden')"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div id="trackModalBody" style="margin-top: 15px;"></div>
    </div>
  </div>

  <div id="truckAnimation" """

html = html.replace('  <div id="truckAnimation" ', modal_html)

with open('pages/buyer.php', 'w', encoding='utf-8') as f:
    f.write(html)

with open('pages/js/buyer.js', 'r', encoding='utf-8') as f:
    js = f.read()

# Add buyerOrders to global state
js = js.replace("let cart = [];", "let cart = [];\nlet buyerOrders = [];")

# Rewrite loadOrders and tracking
old_loadOrders = re.search(r'async function loadOrders\(\).*?\}\n\}\n', js, re.DOTALL)

if old_loadOrders:
    new_loadOrders = r"""async function loadOrders() {
    try {
        const res = await fetch("../api/buyer_orders.php");
        buyerOrders = await res.json();
        const container = document.getElementById("orderList");
        
        if (buyerOrders.length === 0) {
            container.innerHTML = "<p style='padding:20px;'>You have no orders yet.</p>";
            return;
        }

        container.innerHTML = buyerOrders.map(o => {
            const itemNames = o.items.map(it => it.name).join(', ');
            const statusColor = o.status === 'pending' ? '#d97706' : (o.status === 'rejected' ? '#b91c1c' : '#16a34a');
            return `
            <div class="panel" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="flex: 1; min-width: 250px;">
                    <h3 style="margin: 0 0 5px 0;">${itemNames}</h3>
                    <p style="margin: 0; color: #6b7280; font-size: 14px;">
                        Order ${o.tracking_number || '#'+o.id} • Placed on ${o.created_at.split(' ')[0]}
                    </p>
                    <p style="margin: 5px 0 0 0; font-weight: bold; color: #374151;">₹${o.total_amount} <span style="margin-left:10px; font-weight:normal; font-size:12px; color:${statusColor}; text-transform:uppercase;">● ${o.status}</span></p>
                </div>
                <div>
                    <button class="btn-primary" onclick="openTrackModal(${o.id})"><i class="fa-solid fa-location-dot"></i> Track Order</button>
                </div>
            </div>
            `;
        }).join('');
    } catch(e) {
        console.error(e);
    }
}

function openTrackModal(id) {
    const o = buyerOrders.find(ord => ord.id === id);
    if (!o) return;
    
    let statusIdx = 0;
    if(o.status === 'pending') statusIdx = -1;
    if(o.status === 'picked_up') statusIdx = 0;
    if(o.status === 'transit') statusIdx = 1;
    if(o.status === 'dispatched') statusIdx = 2;
    if(o.status === 'delivered') statusIdx = 3;
    
    const html = `
    <div class="tracking-container" style="box-shadow:none; border:none; padding:0; margin:0;">
        <div class="tracking-header">
            <h2 style="color:#b91c1c; margin-bottom:15px;">Track Your Order <i class="fa-solid fa-magnifying-glass"></i></h2>
            <p style="color:#6b7280; margin-bottom:15px;">Tracking Number:</p>
            <div class="tracking-number-box">${o.tracking_number || ('ORD-'+o.id)}</div>
        </div>

        <div class="progress-bar-container">
            <div class="progress-line"></div>
            <div class="progress-line-active" style="width: ${statusIdx === -1 ? '0' : (statusIdx * 33.33)}%;"></div>
            
            <div class="step ${statusIdx >= 0 ? 'completed' : (statusIdx===-1?'active':'')}">
                <div class="step-icon"><i class="fa-solid fa-box"></i></div>
                <div class="step-label">Picked Up</div>
            </div>
            <div class="step ${statusIdx >= 1 ? 'completed' : ''}">
                <div class="step-icon"><i class="fa-solid fa-truck"></i></div>
                <div class="step-label">In Transit</div>
            </div>
            <div class="step ${statusIdx >= 2 ? 'completed' : ''}">
                <div class="step-icon"><i class="fa-solid fa-warehouse"></i></div>
                <div class="step-label">Dispatched</div>
            </div>
            <div class="step ${statusIdx >= 3 ? 'completed' : ''}">
                <div class="step-icon"><i class="fa-solid fa-house"></i></div>
                <div class="step-label">Delivered!</div>
            </div>
        </div>

        <div style="background:#f9fafb; padding:15px; border-radius:4px; border:1px solid #e5e7eb; display:flex; justify-content:space-between;">
            <div>
                <p style="font-size:12px; color:#6b7280; margin:0;">Sender (Farmer):</p>
                <p style="font-weight:bold; margin:5px 0 0 0;">${o.farmer_name}</p>
            </div>
            <div style="text-align:right;">
                <p style="font-size:12px; color:#6b7280; margin:0;">Ordered On:</p>
                <p style="font-weight:bold; margin:5px 0 0 0;">${o.created_at.split(' ')[0]}</p>
            </div>
        </div>
        
        <div style="margin-top:15px;">
            <p style="font-weight:bold; margin-bottom:10px;">Items Ordered:</p>
            <ul style="list-style:none; padding:0; margin:0;">
                ${o.items.map(it => `<li style="padding:5px 0; border-bottom:1px solid #eee;">${it.name} - ${it.quantity}kg @ ₹${it.price}/kg</li>`).join('')}
            </ul>
            <h3 style="text-align:right; margin-top:10px; color:#16a34a;">Total: ₹${o.total_amount}</h3>
        </div>
        ${o.status === 'pending' ? '<p style="text-align:center; color:#d97706; margin-top:15px; font-weight:bold;"><i class="fa-solid fa-clock"></i> Waiting for Farmer to Approve & Pick Up</p>' : ''}
        ${o.status === 'rejected' ? '<p style="text-align:center; color:#b91c1c; margin-top:15px; font-weight:bold;"><i class="fa-solid fa-xmark"></i> Order was rejected by farmer due to insufficient stock.</p>' : ''}
    </div>
    `;
    
    document.getElementById('trackModalBody').innerHTML = html;
    document.getElementById('trackModal').classList.remove('hidden');
}
"""
    js = js.replace(old_loadOrders.group(0), new_loadOrders)

with open('pages/js/buyer.js', 'w', encoding='utf-8') as f:
    f.write(js)
