import re

with open('pages/js/app.js', 'r', encoding='utf-8') as f:
    js = f.read()

# 1. Add event listener to the initial load
init_old = r"""    // Initial Load
    loadInventory();
});"""
init_new = r"""    // Search Orders
    const orderSearch = document.getElementById("orderSearch");
    if (orderSearch) {
        orderSearch.addEventListener("input", (e) => {
            renderOrders(e.target.value.trim().toLowerCase());
        });
    }

});"""
js = js.replace(init_old, init_new)

# 2. Modify renderOrders to accept a filter
render_old = r"""function renderOrders() {
    const container = document.getElementById("shipmentsList");
    if (state.orders.length === 0) {
        container.innerHTML = "<p style='padding:20px;'>No orders received yet.</p>";
        return;
    }

    container.innerHTML = state.orders.map(o => {"""

render_new = r"""function renderOrders(filterText = "") {
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

    container.innerHTML = filteredOrders.map(o => {"""
js = js.replace(render_old, render_new)

with open('pages/js/app.js', 'w', encoding='utf-8') as f:
    f.write(js)

print("app.js patched for search")
