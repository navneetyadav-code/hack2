import re

with open('pages/js/buyer.js', 'r', encoding='utf-8') as f:
    js = f.read()

# Replace searchProducts function
old_search_products = r"""async function searchProducts(query) {
    if(query && query.length >= 3) {
        let history = JSON.parse(localStorage.getItem("recentSearches") || "[]");
        history = [query, ...history.filter(q => q !== query)].slice(0, 5);
        localStorage.setItem("recentSearches", JSON.stringify(history));
    }

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
}"""

new_search_products = r"""let currentPage = 1;

async function searchProducts(query, page = 1) {
    currentPage = page;
    if(query && query.length >= 3 && page === 1) {
        let history = JSON.parse(localStorage.getItem("recentSearches") || "[]");
        history = [query, ...history.filter(q => q !== query)].slice(0, 5);
        localStorage.setItem("recentSearches", JSON.stringify(history));
    }

    try {
        const res = await fetch(`../api/buyer_products.php?search=${encodeURIComponent(query)}&page=${page}`);
        const result = await res.json(); // contains {data, total, page, total_pages}
        
        const container = document.getElementById("searchResults");
        const pagination = document.getElementById("paginationControls");
        
        if (!result.data || result.data.length === 0) {
            container.innerHTML = '<p style="padding:20px; color:#6b7280;">No produce found matching your search.</p>';
            pagination.innerHTML = '';
            return;
        }

        container.innerHTML = result.data.map(p => `
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

        // Build pagination controls
        if (result.total_pages > 1) {
            let btns = '';
            btns += `<button class="btn-secondary" ${page === 1 ? 'disabled' : ''} onclick="searchProducts('${query}', ${page - 1})">Prev</button>`;
            btns += `<span style="padding: 0 10px;">Page ${page} of ${result.total_pages}</span>`;
            btns += `<button class="btn-secondary" ${page === result.total_pages ? 'disabled' : ''} onclick="searchProducts('${query}', ${page + 1})">Next</button>`;
            pagination.innerHTML = btns;
        } else {
            pagination.innerHTML = '';
        }

    } catch (e) {
        console.error(e);
    }
}"""

js = js.replace(old_search_products, new_search_products)

with open('pages/js/buyer.js', 'w', encoding='utf-8') as f:
    f.write(js)
