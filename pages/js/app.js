const state = { inventory: [], orders: [] };

document.addEventListener("DOMContentLoaded", () => {
    // Nav with Persistence
    const switchTab = (pageId) => {
        document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
        document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
        
        const targetPage = document.getElementById(pageId) || document.getElementById("inventory");
        if (!targetPage) return;
        targetPage.classList.add("active");
        
        const btn = document.querySelector(`[data-page="${targetPage.id}"]`);
        if (btn) btn.classList.add("active");
        
        const titles = { dashboard: "Overview", inventory: "Manage Inventory", shipments: "Shipments & Orders" };
        document.getElementById("pageTitle").textContent = titles[targetPage.id] || "Dashboard";
        
        localStorage.setItem("farmerActiveTab", targetPage.id);
        
        if (targetPage.id === "dashboard") { loadInventory(); loadOrders(); updateDashboard(); }
        if (targetPage.id === "inventory") loadInventory();
        if (targetPage.id === "shipments") loadOrders();
    };

    document.querySelectorAll(".nav-btn").forEach(btn => {
        btn.addEventListener("click", () => switchTab(btn.dataset.page));
    });
    
    // Restore tab
    const savedTab = localStorage.getItem("farmerActiveTab") || "dashboard";
    switchTab(savedTab);


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

function renderOrders(filterText = "") {
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
}

window.toggleOrderDetails = function(id) {
    const details = document.getElementById('details_' + id);
    const icon = document.getElementById('icon_' + id);
    if (details.style.display === 'none') {
        details.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        details.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
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

function updateDashboard() {
    // Wait slightly to ensure state is loaded if they fired concurrently
    setTimeout(() => {
        // Calculate Revenue (all non-rejected, non-pending)
        let revenue = 0;
        let pendingCount = 0;
        
        state.orders.forEach(o => {
            if (o.status !== 'rejected' && o.status !== 'pending') {
                revenue += parseFloat(o.total_amount);
            }
            if (o.status === 'pending') {
                pendingCount++;
            }
        });
        
        document.getElementById("dashRevenue").textContent = "₹" + revenue.toFixed(2);
        document.getElementById("dashActiveInventory").textContent = state.inventory.length;
        document.getElementById("dashPendingOrders").textContent = pendingCount;
        
        // Recent Orders
        const recentOrders = state.orders.slice(0, 4);
        document.getElementById("dashRecentOrders").innerHTML = recentOrders.length ? recentOrders.map(o => `
            <div style="padding:10px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>Order ${o.tracking_number || '#'+o.id}</strong><br>
                    <small>${o.buyer_name}</small>
                </div>
                <span class="badge" style="${o.status==='pending' ? 'background:#d97706;color:white;' : (o.status==='rejected'?'background:#b91c1c;color:white;':'background:#16a34a;color:white;')}">${o.status}</span>
            </div>
        `).join('') : '<p style="padding:10px; color:#6b7280;">No recent orders</p>';
        
        // Low Stock (Quantity < 20)
        const lowStock = state.inventory.filter(i => i.quantity < 20).slice(0, 4);
        document.getElementById("dashLowStock").innerHTML = lowStock.length ? lowStock.map(i => `
            <div style="padding:10px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>${i.name}</strong>
                </div>
                <span style="color:#b91c1c; font-weight:bold;">${i.quantity} kg left</span>
            </div>
        `).join('') : '<p style="padding:10px; color:#6b7280;">Stock levels are good</p>';
    }, 500);
}
