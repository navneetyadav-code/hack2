import re

with open('pages/js/app.js', 'r', encoding='utf-8') as f:
    js = f.read()

nav_old = r"""        const titles = { inventory: "Manage Inventory", shipments: "Shipments & Orders" };
        document.getElementById("pageTitle").textContent = titles[targetPage.id] || "Dashboard";
        
        localStorage.setItem("farmerActiveTab", targetPage.id);
        
        if (targetPage.id === "inventory") loadInventory();
        if (targetPage.id === "shipments") loadOrders();
    };"""

nav_new = r"""        const titles = { dashboard: "Overview", inventory: "Manage Inventory", shipments: "Shipments & Orders" };
        document.getElementById("pageTitle").textContent = titles[targetPage.id] || "Dashboard";
        
        localStorage.setItem("farmerActiveTab", targetPage.id);
        
        if (targetPage.id === "dashboard") { loadInventory(); loadOrders(); updateDashboard(); }
        if (targetPage.id === "inventory") loadInventory();
        if (targetPage.id === "shipments") loadOrders();
    };"""
js = js.replace(nav_old, nav_new)

# Add loadDashboard
dashboard_fn = r"""
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
"""
js += dashboard_fn

# Default tab to dashboard if not set
restore_old = r"""    const savedTab = localStorage.getItem("farmerActiveTab") || "inventory";"""
restore_new = r"""    const savedTab = localStorage.getItem("farmerActiveTab") || "dashboard";"""
js = js.replace(restore_old, restore_new)

with open('pages/js/app.js', 'w', encoding='utf-8') as f:
    f.write(js)
print("app.js updated")
