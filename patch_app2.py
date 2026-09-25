import re

with open('pages/js/app.js', 'r', encoding='utf-8') as f:
    js = f.read()

old_render = r"""    container.innerHTML = state.orders.map(o => {
        return `
        <div class="panel" style="margin-bottom: 20px;">
            <div class="panel-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom:10px; margin-bottom:10px;">
                <h3>Order ${o.tracking_number || '#'+o.id}</h3>
                <span class="badge" style="${o.status==='pending' ? 'background:#d97706;color:white;' : (o.status==='rejected'?'background:#b91c1c;color:white;':'background:#16a34a;color:white;')}">${o.status.toUpperCase()}</span>
            </div>
            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:20px;">"""

new_render = r"""    container.innerHTML = state.orders.map(o => {
        return `
        <div class="panel" style="margin-bottom: 20px;">
            <div class="panel-header" style="cursor:pointer; display:flex; justify-content:space-between; align-items:center;" onclick="toggleOrderDetails(${o.id})">
                <div style="display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-chevron-down" id="icon_${o.id}" style="transition: transform 0.3s;"></i>
                    <h3 style="margin:0;">Order ${o.tracking_number || '#'+o.id}</h3>
                </div>
                <span class="badge" style="${o.status==='pending' ? 'background:#d97706;color:white;' : (o.status==='rejected'?'background:#b91c1c;color:white;':'background:#16a34a;color:white;')}">${o.status.toUpperCase()}</span>
            </div>
            <div id="details_${o.id}" style="display:none; margin-top:15px; padding-top:15px; border-top:1px solid #eee;">
            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:20px;">"""

js = js.replace(old_render, new_render)

old_end = r"""            </div>
            ` : ''}
            `}
        </div>
        `;
    }).join('');
}"""

new_end = r"""            </div>
            ` : ''}
            `}
            </div>
        </div>
        `;
    }).join('');
}

window.toggleOrderDetails = function(id) {
    const details = document.getElementById('details_' + id);
    const icon = document.getElementById('icon_' + id);
    if (details.style.display === 'none') {
        details.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        details.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}
"""

js = js.replace(old_end, new_end)

with open('pages/js/app.js', 'w', encoding='utf-8') as f:
    f.write(js)

print("Patch applied to app.js")
