let cart = [];

document.addEventListener("DOMContentLoaded", () => {
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
    });

    // Real-time Search
    const searchInput = document.getElementById("productSearch");
    searchInput.addEventListener("input", () => {
        const val = searchInput.value.trim();
        if (val.length >= 3) {
            searchProducts(val);
        } else if (val.length === 0) {
            document.getElementById("searchResults").innerHTML = '<p style="padding:20px; color:#6b7280;">Start typing to see live results...</p>';
        }
    });
    
    document.getElementById("searchBtn").addEventListener("click", () => {
        const val = searchInput.value.trim();
        if (val.length >= 3) searchProducts(val);
    });

    // Logout
    document.getElementById("logoutBtn").addEventListener("click", () => {
        window.location.href = "../actions/auth.php?action=logout";
    });

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
                // Show Animation
                const anim = document.getElementById("truckAnimation");
                anim.classList.remove("hidden");
                
                setTimeout(() => {
                    anim.classList.add("hidden");
                    cart = [];
                    updateCartCount();
                    renderCartPage();
                    document.querySelector('[data-page="orders"]').click();
                }, 3000);
            } else {
                alert("Error: " + result.error);
            }
        } catch (e) {
            alert("Checkout failed");
        }
    });
});

async function searchProducts(query) {
    try {
        const res = await fetch(`../api/buyer_products.php?search=${encodeURIComponent(query)}`);
        const products = await res.json();
        
        const container = document.getElementById("searchResults");
        if (products.length === 0) {
            container.innerHTML = '<p style="padding:20px; color:#6b7280;">No produce found matching your search.</p>';
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
                    <button class="btn-primary" style="margin-left:10px;" onclick="addToCart(${p.product_id}, '${p.product_name}', ${p.price_per_kg}, ${p.quantity}, '${p.farmer_name}')">
                        Add to Cart
                    </button>
                </div>
            </div>
        `).join('');
    } catch (e) {
        console.error(e);
    }
}

function addToCart(id, name, price, maxQty, farmerName) {
    const qty = parseFloat(document.getElementById(`qty_${id}`).value);
    if (isNaN(qty) || qty <= 0 || qty > maxQty) {
        return alert("Invalid quantity");
    }

    const existing = cart.find(i => i.product_id === id);
    if (existing) {
        if (existing.quantity + qty > maxQty) return alert("Cannot exceed available stock");
        existing.quantity += qty;
    } else {
        cart.push({ product_id: id, name, price, quantity: qty, farmer: farmerName });
    }
    updateCartCount();
    
    const toast = document.getElementById('toast');
    toast.textContent = "Added to cart!";
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}

function updateCartCount() {
    // Number of UNIQUE items in cart, not total quantity
    document.getElementById("cartCountSide").textContent = cart.length;
}

function renderCartPage() {
    const list = document.getElementById("cartItemsList");
    
    if (cart.length === 0) {
        list.innerHTML = "<p style='color:#6b7280; padding:20px; text-align:center;'>Your cart is empty. Go browse some produce!</p>";
        document.getElementById("cartTotal").textContent = "0.00";
        document.querySelector("#checkoutForm button[type='submit']").disabled = true;
        return;
    }
    document.querySelector("#checkoutForm button[type='submit']").disabled = false;

    let total = 0;
    list.innerHTML = cart.map((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        return `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:15px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:10px;">
                <div>
                    <h3 style="margin:0;">${item.name}</h3>
                    <p style="margin:5px 0 0 0; color:#6b7280; font-size:14px;">Farmer: ${item.farmer}</p>
                    <p style="margin:5px 0 0 0; color:#374151;">${item.quantity} kg @ ₹${item.price}/kg</p>
                </div>
                <div style="text-align:right;">
                    <h3 style="margin:0 0 10px 0; color:#16a34a;">₹${itemTotal.toFixed(2)}</h3>
                    <button type="button" class="btn-text" style="color:#b91c1c; font-size:14px; padding:0;" onclick="removeFromCart(${index})">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    document.getElementById("cartTotal").textContent = total.toFixed(2);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartCount();
    renderCartPage();
}

async function loadOrders() {
    try {
        const res = await fetch("../api/buyer_orders.php");
        const orders = await res.json();
        const container = document.getElementById("orderList");
        
        if (orders.length === 0) {
            container.innerHTML = "<p style='padding:20px;'>You have no orders yet.</p>";
            return;
        }

        container.innerHTML = orders.map(o => {
            let statusIdx = 0;
            if(o.status === 'pending') statusIdx = -1; // Wait for approval
            if(o.status === 'picked_up') statusIdx = 0;
            if(o.status === 'transit') statusIdx = 1;
            if(o.status === 'dispatched') statusIdx = 2;
            if(o.status === 'delivered') statusIdx = 3;
            
            // Build Tracking UI
            return `
            <div class="tracking-container">
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
        }).join('');
    } catch(e) {
        console.error(e);
    }
}
