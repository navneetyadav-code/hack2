import re

with open('pages/js/app.js', 'r', encoding='utf-8') as f:
    js = f.read()

# Extract from renderOrders() { to }
pattern = re.compile(r'function renderOrders\(\) \{.*?\n\}', re.DOTALL)

new_renderOrders = r"""function renderOrders() {
    const container = document.getElementById("shipmentsList");
    if (state.orders.length === 0) {
        container.innerHTML = "<p style='padding:20px;'>No orders received yet.</p>";
        return;
    }

    container.innerHTML = state.orders.map(o => {
        return `
        <div class="panel" style="margin-bottom: 20px;">
            <div class="panel-header" style="cursor:pointer; display:flex; justify-content:space-between; align-items:center;" onclick="toggleOrderDetails(${o.id})">
                <div style="display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-chevron-down" id="icon_${o.id}" style="transition: transform 0.3s;"></i>
                    <h3 style="margin:0;">Order ${o.tracking_number || '#'+o.id}</h3>
                </div>
                <span class="badge" style="${o.status==='pending' ? 'background:#d97706;color:white;' : (o.status==='rejected'?'background:#b91c1c;color:white;':'background:#16a34a;color:white;')}">${o.status.toUpperCase()}</span>
            </div>
            <div id="details_${o.id}" style="display:none; margin-top:15px; padding-top:15px; border-top:1px solid #eee;">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:20px;">
                    <div style="flex:1;">
                        <p><strong>Buyer:</strong> ${o.buyer_name}</p>
                        <p><strong>Phone:</strong> ${o.delivery_phone}</p>
                        <p><strong>Address:</strong> ${o.delivery_address}</p>
                        <p><strong>Total Amount:</strong> <span style="color:#16a34a; font-weight:bold;">₹${o.total_amount}</span></p>
                    </div>
                    <div style="flex:1; background:#f9fafb; padding:10px; border-radius:4px; border:1px solid #eee;">
                        <strong>Items Requested:</strong>
                        <ul style="margin-top:5px; padding-left:20px;">
                            ${o.items.map(it => `<li>${it.quantity}kg of ${it.name} (₹${it.price}/kg)</li>`).join('')}
                        </ul>
                    </div>
                </div>
                ${o.status === 'pending' ? `
                <div class="form-actions mt-4" style="border-top:1px solid #eee; padding-top:15px;">
                    <button class="btn-primary" style="background:#16a34a;" onclick="updateOrderStatus(${o.id}, 'approved')"><i class="fa-solid fa-check"></i> Approve & Deduct Stock</button>
                    <button class="btn-secondary" style="color:#b91c1c; border-color:#b91c1c;" onclick="updateOrderStatus(${o.id}, 'rejected')"><i class="fa-solid fa-xmark"></i> Reject Order</button>
                </div>
                ` : `
                ${['picked_up', 'transit', 'dispatched'].includes(o.status) ? `
                <div class="form-actions mt-4" style="border-top:1px solid #eee; padding-top:15px;">
                    <select id="status_${o.id}" style="padding:8px; border-radius:4px; border:1px solid #ccc; margin-right:10px;">
                        <option value="picked_up" ${o.status==='picked_up'?'selected':''}>Picked Up</option>
                        <option value="transit" ${o.status==='transit'?'selected':''}>In Transit</option>
                        <option value="dispatched" ${o.status==='dispatched'?'selected':''}>Dispatched</option>
                        <option value="delivered" ${o.status==='delivered'?'selected':''}>Delivered</option>
                    </select>
                    <button class="btn-primary" onclick="updateOrderStatus(${o.id}, document.getElementById('status_${o.id}').value)">Update Status</button>
                </div>
                ` : ''}
                `}
            </div>
        </div>
        `;
    }).join('');
}"""

js = re.sub(pattern, new_renderOrders, js)

with open('pages/js/app.js', 'w', encoding='utf-8') as f:
    f.write(js)

print("Replaced renderOrders in app.js")
