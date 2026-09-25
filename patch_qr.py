import re

with open('pages/js/app.js', 'r', encoding='utf-8') as f:
    js = f.read()

# I will replace the manual dropdown block with QR code rendering.
# Let's use a regex to grab the block since I don't want to mess up the exact string match.

pattern = re.compile(r'\$\{o\.status === \'pending\' \? `.*?\`} }', re.DOTALL)
# Actually, I'll just write a custom script to find it or replace the whole renderOrders again.

new_renderOrders = r"""function renderOrders(filterText = "") {
    const container = document.getElementById("shipmentsList");
    if (state.orders.length === 0) {
        container.innerHTML = "<p style='padding:20px;'>No orders received yet.</p>";
        return;
    }

    const filteredOrders = state.orders.filter(o => {
        if (!filterText) return true;
        const searchStr = `${o.tracking_number} ${o.buyer_name} ${o.id} ${o.status}`.toLowerCase();
        return searchStr.includes(filterText);
    });

    if (filteredOrders.length === 0) {
        container.innerHTML = "<p style='padding:20px;'>No orders match your search.</p>";
        return;
    }

    container.innerHTML = filteredOrders.map(o => {
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
                    <button class="btn-primary" style="background:#16a34a;" onclick="updateOrderStatus(${o.id}, 'approved')"><i class="fa-solid fa-check"></i> Approve & Generate QR</button>
                    <button class="btn-secondary" style="color:#b91c1c; border-color:#b91c1c;" onclick="updateOrderStatus(${o.id}, 'rejected')"><i class="fa-solid fa-xmark"></i> Reject Order</button>
                </div>
                ` : `
                ${(o.status === 'approved' && o.qr_code_text) ? `
                <div class="mt-4" style="border-top:1px solid #eee; padding-top:15px; text-align:center;">
                    <p style="font-weight:bold; margin-bottom:10px;">Package Tracking QR Code</p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(o.qr_code_text)}" alt="QR Code" style="border: 5px solid #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-radius: 8px;">
                    <p style="margin-top:10px; color:#6b7280; font-size:12px;">Waiting for collection point to scan.</p>
                </div>
                ` : ''}
                `}
            </div>
        </div>
        `;
    }).join('');
}"""

pattern = re.compile(r'function renderOrders\(.*?\n\}', re.DOTALL)
js = re.sub(pattern, new_renderOrders, js)

with open('pages/js/app.js', 'w', encoding='utf-8') as f:
    f.write(js)
