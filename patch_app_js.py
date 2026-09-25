import re

with open('pages/js/app.js', 'r', encoding='utf-8') as f:
    js = f.read()

nav_logic_old = """document.addEventListener("DOMContentLoaded", () => {
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
    });"""

nav_logic_new = """document.addEventListener("DOMContentLoaded", () => {
    // Nav with Persistence
    const switchTab = (pageId) => {
        document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
        document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
        
        const targetPage = document.getElementById(pageId) || document.getElementById("inventory");
        if (!targetPage) return;
        targetPage.classList.add("active");
        
        const btn = document.querySelector(`[data-page="${targetPage.id}"]`);
        if (btn) btn.classList.add("active");
        
        const titles = { inventory: "Manage Inventory", shipments: "Shipments & Orders" };
        document.getElementById("pageTitle").textContent = titles[targetPage.id] || "Dashboard";
        
        localStorage.setItem("farmerActiveTab", targetPage.id);
        
        if (targetPage.id === "inventory") loadInventory();
        if (targetPage.id === "shipments") loadOrders();
    };

    document.querySelectorAll(".nav-btn").forEach(btn => {
        btn.addEventListener("click", () => switchTab(btn.dataset.page));
    });
    
    // Restore tab
    const savedTab = localStorage.getItem("farmerActiveTab") || "inventory";
    switchTab(savedTab);
"""
js = js.replace(nav_logic_old, nav_logic_new)

# Remove the initial load statements at the end of DOMContentLoaded since switchTab handles it
js = js.replace("loadInventory();\n    loadOrders();", "")

with open('pages/js/app.js', 'w', encoding='utf-8') as f:
    f.write(js)

print('app.js updated successfully')
