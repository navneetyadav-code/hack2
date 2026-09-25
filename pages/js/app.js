/**
 * FarmTrack - Streamlined Farmer Dashboard Logic
 * Uses localStorage for demonstration.
 */

// --- STATE MANAGEMENT ---
const loggedInUserName = document.body.dataset.userName || 'Farmer';
const savedProfile = JSON.parse(localStorage.getItem('ft_profile')) || {};

const state = {
    profile: { ...savedProfile, name: loggedInUserName },
    inventory: JSON.parse(localStorage.getItem('ft_inventory')) || [],
    shipments: JSON.parse(localStorage.getItem('ft_shipments')) || [],
    currentFilter: 'all'
};

function saveState() {
    localStorage.setItem('ft_profile', JSON.stringify(state.profile));
    localStorage.setItem('ft_inventory', JSON.stringify(state.inventory));
    localStorage.setItem('ft_shipments', JSON.stringify(state.shipments));
}

// --- UTILS ---
const el = id => document.getElementById(id);
const generateId = prefix => prefix + '-' + Math.random().toString(36).substr(2, 6).toUpperCase();
const formatDate = dateStr => new Date(dateStr).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });

function showToast(msg) {
    const toast = el('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// --- NAVIGATION ---
document.querySelectorAll('.nav-btn, [data-go]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const pageId = e.currentTarget.dataset.page || e.currentTarget.dataset.go;
        
        // Update nav UI
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        const navBtn = document.querySelector(`.nav-btn[data-page="${pageId}"]`);
        if(navBtn) navBtn.classList.add('active');

        // Update page UI
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        el(pageId).classList.add('active');

        // Update Title
        const titles = { dashboard: 'Overview', inventory: 'Manage Inventory', shipments: 'Shipment Tracking', profile: 'Farm Profile' };
        el('pageTitle').textContent = titles[pageId] || 'Dashboard';

        renderCurrentPage(pageId);
    });
});

// --- RENDERERS ---

function renderCurrentPage(pageId) {
    if (pageId === 'dashboard') renderDashboard();
    if (pageId === 'inventory') renderInventory();
    if (pageId === 'shipments') renderShipments();
    if (pageId === 'profile') renderProfile();
}

function renderDashboard() {
    el('farmerNameTop').textContent = loggedInUserName;
    
    // Stats
    el('dashInventoryCount').textContent = state.inventory.reduce((sum, item) => sum + Number(item.qty), 0) + ' units';
    el('dashPendingCount').textContent = state.shipments.filter(s => s.status === 'pending').length;
    el('dashTransitCount').textContent = state.shipments.filter(s => s.status === 'transit').length;

    // Mini Inventory List
    const invList = el('dashInventoryList');
    if (state.inventory.length === 0) {
        invList.innerHTML = `<div class="empty-state">No produce added yet.</div>`;
    } else {
        invList.innerHTML = state.inventory.slice(0, 4).map(item => `
            <div class="list-item">
                <div class="item-main">
                    <h4>${item.name}</h4>
                    <p class="item-sub">Available: ${formatDate(item.date)}</p>
                </div>
                <div class="item-qty"><strong>${item.qty}</strong> <small>${item.unit}</small></div>
            </div>
        `).join('');
    }

    // Mini Shipments List
    const shipList = el('dashShipmentList');
    if (state.shipments.length === 0) {
        shipList.innerHTML = `<div class="empty-state">No active shipments.</div>`;
    } else {
        shipList.innerHTML = state.shipments.slice(0, 4).map(ship => `
            <div class="list-item">
                <div class="item-main">
                    <h4>${ship.itemName}</h4>
                    <p class="item-sub">To: ${ship.destination}</p>
                </div>
                <span class="badge ${ship.status}">${ship.status}</span>
            </div>
        `).join('');
    }
}

// --- INVENTORY LOGIC ---
function renderInventory() {
    const list = el('inventoryList');
    if (state.inventory.length === 0) {
        list.innerHTML = `<div class="empty-state w-full col-span-full"><i class="fa-solid fa-seedling"></i><p>Your inventory is empty. Add produce to start selling.</p></div>`;
        list.style.display = 'block';
        return;
    }
    
    list.style.display = 'grid';
    list.innerHTML = state.inventory.map(item => `
        <div class="inventory-card">
            <div class="inv-header">
                <h3>${item.name}</h3>
                <span class="badge default">In Stock</span>
            </div>
            <div class="inv-qty">${item.qty} <span>${item.unit}</span></div>
            <div class="inv-meta">
                <span><i class="fa-regular fa-calendar"></i> Available: ${formatDate(item.date)}</span>
            </div>
            <div class="inv-actions">
                <button class="btn-icon" onclick="editInventory('${item.id}')" title="Edit"><i class="fa-solid fa-pen"></i></button>
                <button class="btn-icon delete" onclick="deleteInventory('${item.id}')" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>
    `).join('');
}

el('addInventoryBtn').addEventListener('click', () => {
    el('inventoryForm').reset();
    el('invId').value = '';
    el('inventoryFormTitle').textContent = 'Add Produce';
    el('inventoryModal').classList.remove('hidden');
});

document.querySelectorAll('#closeInventoryModal, #inventoryModal .btn-secondary').forEach(btn => {
    btn.addEventListener('click', () => el('inventoryModal').classList.add('hidden'));
});

el('inventoryForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const id = el('invId').value || generateId('INV');
    const item = {
        id: id,
        name: el('invName').value,
        qty: el('invQty').value,
        unit: el('invUnit').value,
        date: el('invDate').value
    };

    const existingIdx = state.inventory.findIndex(i => i.id === id);
    if (existingIdx > -1) state.inventory[existingIdx] = item;
    else state.inventory.push(item);

    saveState();
    el('inventoryModal').classList.add('hidden');
    renderInventory();
    showToast('Inventory updated successfully');
});

window.editInventory = (id) => {
    const item = state.inventory.find(i => i.id === id);
    if(!item) return;
    el('invId').value = item.id;
    el('invName').value = item.name;
    el('invQty').value = item.qty;
    el('invUnit').value = item.unit;
    el('invDate').value = item.date;
    el('inventoryFormTitle').textContent = 'Edit Produce';
    el('inventoryModal').classList.remove('hidden');
}

window.deleteInventory = (id) => {
    if(confirm('Remove this item from inventory?')) {
        state.inventory = state.inventory.filter(i => i.id !== id);
        saveState();
        renderInventory();
        showToast('Item removed');
    }
}

// --- SHIPMENT LOGIC ---
function renderShipments() {
    const list = el('shipmentListFull');
    let filtered = state.shipments;
    
    if(state.currentFilter !== 'all') {
        filtered = state.shipments.filter(s => s.status === state.currentFilter);
    }

    if (filtered.length === 0) {
        list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-box-open"></i><p>No shipments found in this category.</p></div>`;
        return;
    }

    list.innerHTML = filtered.map(ship => `
        <div class="shipment-card">
            <div class="shipment-info">
                <span class="shipment-id">ID: ${ship.id}</span>
                <h3 class="shipment-title">${ship.itemName} (${ship.qty} ${ship.unit})</h3>
                <div class="shipment-details">
                    <span><i class="fa-solid fa-location-dot"></i> ${ship.destination}</span>
                    <span><i class="fa-regular fa-calendar-check"></i> Target: ${formatDate(ship.date)}</span>
                </div>
            </div>
            <div class="shipment-status-area">
                <span class="badge ${ship.status}">${ship.status}</span>
                ${ship.status === 'pending' ? `<button class="btn-text" style="color:#EF4444" onclick="cancelShipment('${ship.id}')">Cancel</button>` : ''}
            </div>
        </div>
    `).join('');
}

// Tab handling
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        e.currentTarget.classList.add('active');
        state.currentFilter = e.currentTarget.dataset.filter;
        renderShipments();
    });
});

el('createShipmentBtn').addEventListener('click', () => {
    if (state.inventory.length === 0) {
        alert("Please add items to your inventory first.");
        return;
    }
    const select = el('shipInvSelect');
    select.innerHTML = state.inventory.map(i => `<option value="${i.id}">${i.name} (Max: ${i.qty} ${i.unit})</option>`).join('');
    
    el('shipmentForm').reset();
    el('shipmentModal').classList.remove('hidden');
});

document.querySelectorAll('#closeShipmentModal, #shipmentModal .btn-secondary').forEach(btn => {
    btn.addEventListener('click', () => el('shipmentModal').classList.add('hidden'));
});

el('shipmentForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const invId = el('shipInvSelect').value;
    const invItem = state.inventory.find(i => i.id === invId);
    const reqQty = Number(el('shipQty').value);

    if(reqQty > invItem.qty) {
        alert(`Requested quantity exceeds available stock (${invItem.qty} ${invItem.unit})`);
        return;
    }

    const ship = {
        id: generateId('SHP'),
        invId: invId,
        itemName: invItem.name,
        qty: reqQty,
        unit: invItem.unit,
        date: el('shipDate').value,
        destination: el('shipDest').value,
        status: 'pending',
        created: new Date().toISOString()
    };

    // Deduct from inventory
    invItem.qty -= reqQty;
    
    state.shipments.unshift(ship);
    saveState();
    el('shipmentModal').classList.add('hidden');
    renderShipments();
    showToast('Shipment request created');
});

window.cancelShipment = (id) => {
    if(confirm('Cancel this shipment request?')) {
        const shipIndex = state.shipments.findIndex(s => s.id === id);
        if(shipIndex > -1) {
            const ship = state.shipments[shipIndex];
            // Return to inventory
            const invItem = state.inventory.find(i => i.id === ship.invId);
            if(invItem) invItem.qty += Number(ship.qty);
            
            state.shipments.splice(shipIndex, 1);
            saveState();
            renderShipments();
            showToast('Shipment cancelled');
        }
    }
}

// --- PROFILE LOGIC ---
function renderProfile() {
    el('profName').value = state.profile.name;
    el('profPhone').value = state.profile.phone;
    el('profSize').value = state.profile.size;
    el('profLoc').value = state.profile.loc;
}

el('profileForm').addEventListener('submit', (e) => {
    e.preventDefault();
    state.profile = {
        name: el('profName').value,
        phone: el('profPhone').value,
        size: el('profSize').value,
        loc: el('profLoc').value
    };
    saveState();
    el('farmerNameTop').textContent = state.profile.name;
    showToast('Profile updated');
});

// Logout
el('logoutBtn').addEventListener('click', () => {
    window.location.href = '../actions/auth.php?action=logout';
});

// Init
renderDashboard();