import os

app_js = r"""const loggedInUserName = document.body.dataset.userName || 'Farmer';
const state = { inventory: [] };

document.addEventListener("DOMContentLoaded", () => {
    // Nav
    document.querySelectorAll(".nav-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
            document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
            
            const pageId = btn.dataset.page;
            document.getElementById(pageId).classList.add("active");
            btn.classList.add("active");
            
            if (pageId === "inventory") loadInventory();
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
            </div>
            <div style="margin-top:10px;">
                <p><strong>Quantity:</strong> ${item.quantity} kg</p>
                <p><strong>Price:</strong> ₹${item.price_per_kg}/kg</p>
                <p><strong>Harvest Date:</strong> ${item.harvest_date}</p>
            </div>
            <div class="form-actions mt-4">
                <button class="btn-secondary" style="color:red; border-color:red;" onclick="deleteInventory(${item.id})">Delete</button>
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
"""

with open('pages/js/app.js', 'w', encoding='utf-8') as f:
    f.write(app_js)

print("app.js written")
