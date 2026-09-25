import re

with open('pages/js/buyer.js', 'r', encoding='utf-8') as f:
    js = f.read()

# Fix switchTab
old_switch = r"""        if (targetPage.id === "dashboard") loadDashboard();
        if (targetPage.id === "orders") loadOrders();
        if (targetPage.id === "cartPage") renderCartPage();
    };"""

new_switch = r"""        if (targetPage.id === "dashboard") loadDashboard();
        if (targetPage.id === "orders") loadOrders();
        if (targetPage.id === "cartPage") renderCartPage();
        if (targetPage.id === "findProduce") searchProducts('');
    };"""
js = js.replace(old_switch, new_switch)

# Fix real-time search listener
old_search_listener = r"""    // Real-time Search
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
    });"""

new_search_listener = r"""    // Real-time Search
    const searchInput = document.getElementById("productSearch");
    searchInput.addEventListener("input", () => {
        const val = searchInput.value.trim();
        if (val.length >= 3) {
            searchProducts(val);
        } else if (val.length === 0) {
            searchProducts('');
        }
    });
    
    document.getElementById("searchBtn").addEventListener("click", () => {
        searchProducts(searchInput.value.trim());
    });
    
    // Initial load for catalog
    searchProducts('');"""
js = js.replace(old_search_listener, new_search_listener)

with open('pages/js/buyer.js', 'w', encoding='utf-8') as f:
    f.write(js)

with open('pages/buyer.php', 'r', encoding='utf-8') as f:
    html = f.read()

html = html.replace('<p style="padding:20px; color:#6b7280;">Start typing to see live results...</p>', '<p style="padding:20px; color:#6b7280;">Loading produce catalog...</p>')
html = html.replace('placeholder="Type at least 3 characters to search (e.g. wheat)..."', 'placeholder="Search for wheat, rice, etc..."')

with open('pages/buyer.php', 'w', encoding='utf-8') as f:
    f.write(html)
