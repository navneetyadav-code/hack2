import os

buyer_js = r"""let cart = [];

document.addEventListener("DOMContentLoaded", () => {
    // Navigation
    document.querySelectorAll(".nav-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
            document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
            
            const pageId = btn.dataset.page;
            document.getElementById(pageId).classList.add("active");
            btn.classList.add("active");
            
            const titles = { findProduce: "Browse Produce", orders: "My Orders" };
            document.getElementById("pageTitle").textContent = titles[pageId] || "Dashboard";
            
            if (pageId === "orders") loadOrders();
        });
    });

    // Search
    document.getElementById("searchBtn").addEventListener("click", searchProducts);
    document.getElementById("productSearch").addEventListener("keypress", (e) => {
        if(e.key === 'Enter') searchProducts();
    });

    // Initial load
    searchProducts();

    // Logout
    document.getElementById("logoutBtn").addEventListener("click", () => {
        window.location.href = "../actions/auth.php?action=logout";
    });

    // Cart Modal
    document.getElementById("openCartBtn").addEventListener("click", renderCartModal);
    document.getElementById("closeCartModal").addEventListener("click", () => document.getElementById("cartModal").classList.add("hidden"));
    document.getElementById("cancelCheckout").addEventListener("click", () => document.getElementById("cartModal").classList.add("hidden"));

    // Checkout Submit
    document.getElementById("checkoutForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        if (cart.length === 0) return alert("Your cart is empty");

        const data = {
            name: document.getElementById("chkName").value,
            phone: document.getElementById("chkPhone").value,
            email: document.getElementById("chkEmail").value,
            address: document.getElementById("chkAddress").value,
            payment_method: document.getElementById("chkPayment").value,
            cart: cart
        };

        try {
            const res = await fetch("../api/checkout.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                alert("Order placed successfully!");
                cart = [];
                updateCartCount();
                document.getElementById("cartModal").classList.add("hidden");
                document.querySelector('[data-page="orders"]').click();
            } else {
                alert("Error: " + result.error);
            }
        } catch (e) {
            alert("Checkout failed");
        }
    });
});

async function searchProducts() {
    const q = document.getElementById("productSearch").value.trim();
    try {
        const res = await fetch(`../api/buyer_products.php?search=${encodeURIComponent(q)}`);
        const products = await res.json();
        
        const container = document.getElementById("searchResults");
        if (products.length === 0) {
            container.innerHTML = '<p style="padding:20px;">No produce found.</p>';
            return;
        }

        container.innerHTML = products.map(p => `
            <div class="panel">
                <div class="panel-header">
                    <h2>${p.product_name}</h2>
                    <span class="badge" style="background:#e5e7eb;color:#374151">₹${p.price_per_kg}/kg</span>
                </div>
                <div style="margin-top:10px;">
                    <p><strong>Farmer:</strong> ${p.farmer_name}</p>
                    <p><strong>Available:</strong> ${p.quantity} kg</p>
                    <p><strong>Harvest Date:</strong> ${p.harvest_date || 'N/A'}</p>
                </div>
                <div class="form-row mt-4" style="align-items:center;">
                    <input type="number" id="qty_${p.product_id}" min="1" max="${p.quantity}" value="1" style="width:80px; padding:5px;">
                    <button class="btn-primary" style="margin-left:10px;" onclick="addToCart(${p.product_id}, '${p.product_name}', ${p.price_per_kg}, ${p.quantity})">
                        Add to Cart
                    </button>
                </div>
            </div>
        `).join('');
    } catch (e) {
        console.error(e);
    }
}

function addToCart(id, name, price, maxQty) {
    const qty = parseFloat(document.getElementById(`qty_${id}`).value);
    if (isNaN(qty) || qty <= 0 || qty > maxQty) {
        return alert("Invalid quantity");
    }

    const existing = cart.find(i => i.product_id === id);
    if (existing) {
        if (existing.quantity + qty > maxQty) return alert("Cannot exceed available stock");
        existing.quantity += qty;
    } else {
        cart.push({ product_id: id, name, price, quantity: qty });
    }
    updateCartCount();
    
    const toast = document.getElementById('toast');
    toast.textContent = "Added to cart!";
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}

function updateCartCount() {
    document.getElementById("cartCount").textContent = cart.reduce((acc, item) => acc + item.quantity, 0);
}

function renderCartModal() {
    document.getElementById("cartModal").classList.remove("hidden");
    const list = document.getElementById("cartItemsList");
    
    if (cart.length === 0) {
        list.innerHTML = "<p>Cart is empty</p>";
        document.getElementById("cartTotal").textContent = "0.00";
        return;
    }

    let total = 0;
    list.innerHTML = cart.map((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        return `
            <div style="display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #eee;">
                <div><strong>${item.name}</strong><br><small>${item.quantity} kg @ ₹${item.price}/kg</small></div>
                <div style="text-align:right;">₹${itemTotal.toFixed(2)}<br>
                    <button type="button" class="btn-text" style="color:red; font-size:12px;" onclick="removeFromCart(${index})">Remove</button>
                </div>
            </div>
        `;
    }).join('');
    
    document.getElementById("cartTotal").textContent = total.toFixed(2);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartCount();
    renderCartModal();
}

async function loadOrders() {
    // To be implemented fully, just showing placeholder
    document.getElementById("orderList").innerHTML = "<p style='padding:20px;'>Orders fetched from DB will appear here.</p>";
}
"""

with open('pages/js/buyer.js', 'w', encoding='utf-8') as f:
    f.write(buyer_js)

print("buyer.js written")
