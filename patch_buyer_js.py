import re

with open('pages/js/buyer.js', 'r', encoding='utf-8') as f:
    js = f.read()

# 1. Update Navigation Logic for Persistence
nav_logic_old = """document.addEventListener("DOMContentLoaded", () => {
    // Navigation
    document.querySelectorAll(".nav-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
            document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
            
            const pageId = btn.dataset.page;
            document.getElementById(pageId).classList.add("active");
            btn.classList.add("active");
            
            const titles = { findProduce: "Browse Produce", orders: "My Orders", cartPage: "Cart & Checkout" };
            document.getElementById("pageTitle").textContent = titles[pageId] || "Dashboard";
            
            if (pageId === "orders") loadOrders();
            if (pageId === "cartPage") renderCartPage();
        });
    });"""

nav_logic_new = """document.addEventListener("DOMContentLoaded", () => {
    // Navigation with Persistence
    const switchTab = (pageId) => {
        document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
        document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
        
        const targetPage = document.getElementById(pageId) || document.getElementById("dashboard");
        if (!targetPage) return;
        targetPage.classList.add("active");
        
        const btn = document.querySelector(`[data-page="${targetPage.id}"]`);
        if (btn) btn.classList.add("active");
        
        const titles = { dashboard: "Overview", findProduce: "Browse Produce", orders: "My Orders", cartPage: "Cart & Checkout" };
        document.getElementById("pageTitle").textContent = titles[targetPage.id] || "Dashboard";
        
        localStorage.setItem("buyerActiveTab", targetPage.id);
        
        if (targetPage.id === "dashboard") loadDashboard();
        if (targetPage.id === "orders") loadOrders();
        if (targetPage.id === "cartPage") renderCartPage();
    };

    document.querySelectorAll(".nav-btn").forEach(btn => {
        btn.addEventListener("click", () => switchTab(btn.dataset.page));
    });
    
    // Restore tab
    const savedTab = localStorage.getItem("buyerActiveTab") || "dashboard";
    switchTab(savedTab);
"""
js = js.replace(nav_logic_old, nav_logic_new)

# 2. Add Recent Search logic to searchProducts
search_products_old = """async function searchProducts(query) {"""
search_products_new = """async function searchProducts(query) {
    if(query && query.length >= 3) {
        let history = JSON.parse(localStorage.getItem("recentSearches") || "[]");
        history = [query, ...history.filter(q => q !== query)].slice(0, 5);
        localStorage.setItem("recentSearches", JSON.stringify(history));
    }
"""
js = js.replace(search_products_old, search_products_new)

# 3. Add loadDashboard function at the end
dashboard_js = """
async function loadDashboard() {
    try {
        const res = await fetch("../api/buyer_orders.php");
        const orders = await res.json();
        
        let activeOrders = 0;
        let deliveredOrders = 0;
        
        orders.forEach(o => {
            if (o.status === 'delivered') deliveredOrders++;
            else if (o.status !== 'rejected') activeOrders++;
        });
        
        document.getElementById("dashActiveOrders").textContent = activeOrders;
        document.getElementById("dashDeliveredOrders").textContent = deliveredOrders;
        
        // Searches
        const history = JSON.parse(localStorage.getItem("recentSearches") || "[]");
        document.getElementById("dashTotalSearches").textContent = history.length;
        document.getElementById("dashSearchList").innerHTML = history.length ? history.map(h => `
            <div style="padding:10px; border-bottom:1px solid #eee;">
                <i class="fa-solid fa-clock-rotate-left"></i> <strong>${h}</strong>
            </div>
        `).join('') : '<p style="padding:10px; color:#6b7280;">No recent searches</p>';
        
        // Recent Orders
        const recent = orders.slice(0, 3);
        document.getElementById("dashOrderList").innerHTML = recent.length ? recent.map(o => `
            <div style="padding:10px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong>${o.items[0]?.name || 'Items'}</strong><br>
                    <small>Ord #${o.id}</small>
                </div>
                <span class="badge" style="background:#e5e7eb; color:#374151;">${o.status}</span>
            </div>
        `).join('') : '<p style="padding:10px; color:#6b7280;">No recent orders</p>';
        
    } catch (e) {
        console.error(e);
    }
}
"""
js += dashboard_js

with open('pages/js/buyer.js', 'w', encoding='utf-8') as f:
    f.write(js)

print('buyer.js updated successfully')
