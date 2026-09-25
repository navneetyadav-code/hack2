import re

with open('pages/js/buyer.js', 'r', encoding='utf-8') as f:
    js = f.read()

# Replace openTrackModal
old_func = r"""function openTrackModal(id) {
    const o = buyerOrders.find(ord => ord.id === id);
    if (!o) return;
    
    let statusIdx = 0;
    if(o.status === 'pending') statusIdx = -1;
    if(o.status === 'picked_up') statusIdx = 0;
    if(o.status === 'transit') statusIdx = 1;
    if(o.status === 'dispatched') statusIdx = 2;
    if(o.status === 'delivered') statusIdx = 3;"""

new_func = r"""function openTrackModal(id) {
    const o = buyerOrders.find(ord => ord.id === id);
    if (!o) return;
    
    // Status mapping using shipment logs
    let statusIdx = -1;
    const s_stat = o.shipment_status;
    if (s_stat) {
        if(s_stat === 'PENDING') statusIdx = 0;
        if(s_stat === 'COLLECTION_POINT') statusIdx = 1;
        if(s_stat === 'IN_TRANSIT') statusIdx = 2;
        if(s_stat === 'DELIVERED') statusIdx = 3;
    }
    
    // Extract timestamps from logs if available
    const getTime = (st) => {
        if (!o.logs) return '';
        const log = o.logs.find(l => l.status === st);
        return log ? `<br><small style='color:#16a34a; font-weight:normal; font-size:10px;'>${log.updated_at.split(' ')[1]}</small>` : '';
    };
    const t_pickup = getTime('PENDING');
    const t_collection = getTime('COLLECTION_POINT');
    const t_transit = getTime('IN_TRANSIT');
    const t_delivered = getTime('DELIVERED');"""

js = js.replace(old_func, new_func)

old_steps = r"""            <div class="step ${statusIdx >= 0 ? 'completed' : (statusIdx===-1?'active':'')}">
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
            </div>"""

new_steps = r"""            <div class="step ${statusIdx >= 0 ? 'completed' : (statusIdx===-1?'active':'')}">
                <div class="step-icon"><i class="fa-solid fa-box"></i></div>
                <div class="step-label">Picked Up${t_pickup}</div>
            </div>
            <div class="step ${statusIdx >= 1 ? 'completed' : ''}">
                <div class="step-icon"><i class="fa-solid fa-warehouse"></i></div>
                <div class="step-label">Hub Scan${t_collection}</div>
            </div>
            <div class="step ${statusIdx >= 2 ? 'completed' : ''}">
                <div class="step-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <div class="step-label">In Transit${t_transit}</div>
            </div>
            <div class="step ${statusIdx >= 3 ? 'completed' : ''}">
                <div class="step-icon"><i class="fa-solid fa-house"></i></div>
                <div class="step-label">Delivered!${t_delivered}</div>
            </div>"""
js = js.replace(old_steps, new_steps)

with open('pages/js/buyer.js', 'w', encoding='utf-8') as f:
    f.write(js)
