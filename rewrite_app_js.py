import os

app_js = r"""const state = { inventory: [], orders: [] };

document.addEventListener("DOMContentLoaded", () => {
    // Nav
    document.querySelectorAll(".nav-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
            document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
            
            const pageId = btn.dataset.page;
            document.getElementById(pageId).classList.add("active");
            btn.classList.add("active");
            
            const titles = { inventory: "Manage Inventory", shipments: "Shipments & Orders" };
            document.getElementById("pageTitle").textContent = titles[pageId] || "Dashboard";
            
            if (pageId === "inventory") loadInventory();
            if (pageId === "shipments") loadOrders();
        });
    });

    // Logout
    document.getElementById("logoutBtn").addEventListener("click", () => {
        window.location.href = "../actions/auth.php?action=logout";
    });

    // Inventory Modal
    document.getElementById("addInventoryBtn").addEventListener("click", () => {
        document.getElementById("inventoryForm").reset();
        document.getElementById("inventoryModal").classList.remove("hidden");
    });
    
    document.querySelectorAll(".close-modal").forEach(btn => {
        btn.addEventListener("click", () => document.getElementById("inventoryModal").classList.add("hidden"));
    });

    // Save Inventory
    document.getElementById("inventoryForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        const data = {
            name: document.getElementById("invName").value,
            quantity: document.getElementById("invQty").value,
            price_per_kg: document.getElementById("invPrice").value,
            harvest_date: document.getElementById("invDate").value
        };

        try {
            const res = await fetch("../api/farmer_products.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                document.getElementById("inventoryModal").classList.add("hidden");
                loadInventory();
                showToast("Product added successfully");
            }
        } catch (e) {
            console.error(e);
        }
    });

    // Initial Load
    loadInventory();
    loadOrders();
});

function showToast(msg) {
    const toast = document.getElementById('toast');
    if(!toast) return;
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

async function loadInventory() {
    try {
        const res = await fetch("../api/farmer_products.php");
        state.inventory = await res.json();
        renderInventory();
    } catch (e) {
        console.error(e);
    }
}

function renderInventory() {
    const list = document.getElementById('inventoryList');
    if (!list) return;

    if (state.inventory.length === 0) {
        list.innerHTML = `<div style="padding:20px;">No produce added yet.</div>`;
        return;
    }
    
    list.innerHTML = state.inventory.map(item => `
        <div class="panel">
            <div class="panel-header">
                <h3>${item.name}</h3>
                <span class="badge" style="background:#e5e7eb;color:#374151">₹${item.price_per_kg}/kg</span>
            </div>
            <div style="margin-top:10px;">
                <p><strong>Available Quantity:</strong> ${item.quantity} kg</p>
                <p><strong>Harvest Date:</strong> ${item.harvest_date}</p>
            </div>
            <div class="form-actions mt-4">
                <button class="btn-secondary" style="color:#b91c1c; border-color:#b91c1c;" onclick="deleteInventory(${item.id})"><i class="fa-solid fa-trash"></i> Delete</button>
            </div>
        </div>
    `).join('');
}

async function deleteInventory(id) {
    if (!confirm("Are you sure you want to delete this product?")) return;
    
    try {
        const res = await fetch(`../api/farmer_products.php?id=${id}`, { method: 'DELETE' });
        const result = await res.json();
        if (result.success) {
            loadInventory();
        }
    } catch (e) {
        console.error(e);
    }
}

async function loadOrders() {
    try {
        const res = await fetch("../api/farmer_orders.php");
        state.orders = await res.json();
        renderOrders();
        
        const hasPending = state.orders.some(o => o.status === 'pending');
        document.getElementById("pendingBadge").style.display = hasPending ? 'inline-block' : 'none';
    } catch (e) {
        console.error(e);
    }
}

function renderOrders() {
    const container = document.getElementById("shipmentsList");
    if (state.orders.length === 0) {
        container.innerHTML = "<p style='padding:20px;'>No orders received yet.</p>";
        return;
    }

    container.innerHTML = state.orders.map(o => {
        return `
        <div class="panel" style="margin-bottom: 20px;">
            <div class="panel-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom:10px; margin-bottom:10px;">
                <h3>Order #${o.id} - ${o.tracking_number}</h3>
                <span class="badge" style="${o.status==='pending' ? 'background:#d97706;color:white;' : (o.status==='rejected'?'background:#b91c1c;color:white;':'background:#16a34a;color:white;')}">${o.status.toUpperCase()}</span>
            </div>
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
        `;
    }).join('');
}

async function updateOrderStatus(id, newStatus) {
    if (newStatus === 'rejected' && !confirm("Are you sure you want to reject this order?")) return;
    if (newStatus === 'approved' && !confirm("This will approve the order and automatically deduct the stock from your inventory. Proceed?")) return;

    try {
        const res = await fetch("../api/farmer_approve_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ order_id: id, status: newStatus })
        });
        const result = await res.json();
        if (result.success) {
            showToast("Order status updated!");
            loadOrders();
            loadInventory(); // refresh stock
        } else {
            alert("Error: " + result.error);
        }
    } catch (e) {
        alert("Failed to update status.");
    }
}
"""

with open('pages/js/app.js', 'w', encoding='utf-8') as f:
    f.write(app_js)

print("app.js written")
