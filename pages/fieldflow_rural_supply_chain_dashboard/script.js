const $=s=>document.querySelector(s), $$=s=>document.querySelectorAll(s);
const sidebar=$("#sidebar"), overlay=$("#overlay"), modal=$("#modalBackdrop"), toast=$("#toast");
const pageTitle=$("#pageTitle"), dashboard=$("#dashboardView"), secondary=$("#secondaryView");
const subTitle=$("#subTitle"), subText=$("#subText"), subEyebrow=$("#subEyebrow"), subContent=$("#subContent"), subAction=$("#subAction");

function showToast(title,text="Your update was successful."){ $("#toastTitle").textContent=title; $("#toastText").textContent=text; toast.classList.add("show"); setTimeout(()=>toast.classList.remove("show"),3000); }
function openModal(){modal.classList.add("show");document.body.style.overflow="hidden"}
function closeModal(){modal.classList.remove("show");document.body.style.overflow=""}
$("#addStockBtn").onclick=openModal; $("#modalClose").onclick=closeModal;
modal.onclick=e=>{if(e.target===modal)closeModal()};
$("#menuBtn").onclick=()=>{sidebar.classList.toggle("open");overlay.classList.toggle("show")};
overlay.onclick=()=>{sidebar.classList.remove("open");overlay.classList.remove("show")};
$("#notificationBtn").onclick=()=>showToast("4 notifications","You have new buyer activity and shipment updates.");
$("#logoutBtn").onclick=()=>showToast("Demo mode","Sign-out is disabled in this front-end prototype.");

const pages={
 stock:{
  title:"My Stock", eyebrow:"INVENTORY CONTROL", text:"Keep your available produce accurate so buyers see what can actually be ordered.",
  html:`<div class="inventory-grid">
   <article class="inventory-card"><div class="crop-icon wheat">W</div><h3>Wheat</h3><p>BAT-1001 · Grade A · Published</p><strong class="inventory-number">500 <small>kg</small></strong><div class="inventory-meta"><span>₹28 / kg</span><b>Available</b></div></article>
   <article class="inventory-card"><div class="crop-icon rice">R</div><h3>Rice</h3><p>BAT-1002 · Grade A · Published</p><strong class="inventory-number">300 <small>kg</small></strong><div class="inventory-meta"><span>₹42 / kg</span><b>Available</b></div></article>
   <article class="inventory-card"><div class="crop-icon potato">P</div><h3>Potato</h3><p>BAT-1003 · Grade B · Published</p><strong class="inventory-number">200 <small>kg</small></strong><div class="inventory-meta"><span>₹24 / kg</span><b>Available</b></div></article>
   <article class="inventory-card"><div class="crop-icon wheat">M</div><h3>Mustard</h3><p>BAT-1004 · Grade A · Low stock</p><strong class="inventory-number">80 <small>kg</small></strong><div class="inventory-meta"><span>₹56 / kg</span><b style="color:#b4771b">Low stock</b></div></article>
  </div>`
 },
 orders:{
  title:"Buyer Orders", eyebrow:"ORDER DESK", text:"Review requests, accept the right quantities, and move accepted orders into shipment preparation.",
  html:`<div class="sub-content-card order-large">
   ${[
    ["#ORD-1024","ABC Foods","Wheat · 200 kg","₹5,600","Needs review","pending"],
    ["#ORD-1023","FreshMart","Rice · 150 kg","₹6,300","Accepted","accepted"],
    ["#ORD-1022","GreenBuy","Potato · 100 kg","₹2,400","In transit","transit"],
    ["#ORD-1021","North Grain Co.","Wheat · 300 kg","₹8,400","Delivered","delivered"]
   ].map((o,i)=>`<div class="order-row"><div><strong>${o[0]}</strong><small>${i<2?"Today":"Yesterday"}</small></div><div>${o[1]}</div><div>${o[2]}</div><div><strong>${o[3]}</strong></div><div><span class="status ${o[5]}">${o[4]}</span></div></div>`).join("")}
  </div>`
 },
 shipments:{
  title:"Shipments", eyebrow:"CHAIN OF CUSTODY", text:"Every accepted order becomes a traceable shipment with a unique QR identity.",
  html:`<div class="sub-content-card order-large">
   ${[
    ["SHP-2026-00124","Wheat","200 kg","ABC Foods","In transit"],
    ["SHP-2026-00123","Rice","150 kg","FreshMart","Ready for pickup"],
    ["SHP-2026-00122","Potato","100 kg","GreenBuy","Delivered"]
   ].map((s,i)=>`<div class="order-row"><div><strong>${s[0]}</strong><small>${i===0?"Last update 10:42 AM":"Shipment label generated"}</small></div><div>${s[1]} · ${s[2]}</div><div>${s[3]}</div><div>${i===0?"QR active":"QR linked"}</div><div><span class="status ${i===0?"transit":i===2?"delivered":"accepted"}">${s[4]}</span></div></div>`).join("")}
  </div>
  <div class="panel" style="margin-top:15px"><div class="panel-head"><div><span class="eyebrow">SELECTED SHIPMENT</span><h2>SHP-2026-00124 · QR label</h2></div><button class="primary-small" id="qrBtn">Generate QR label</button></div><div style="padding:24px;display:flex;gap:24px;align-items:center;flex-wrap:wrap"><div style="width:130px;height:130px;border:10px solid #17211c;background:repeating-linear-gradient(45deg,#fff 0 6px,#17211c 6px 10px);display:grid;place-items:center;color:#fff;font-weight:800;font-size:10px">QR<br>SHIP-124</div><div><strong>Wheat · 200 kg</strong><p style="font-size:10px;color:#87938c;line-height:1.7">Batch BAT-1001<br>Rajesh Farm → ABC Foods<br>Transporter: Amit Kumar</p></div></div></div>`
 },
 tracking:{
  title:"Live Tracking", eyebrow:"MOVEMENT VISIBILITY", text:"Follow the shipment after pickup and keep the buyer-facing trail current.",
  html:`<div class="tracking-layout"><div class="tracking-map"><div class="map-card"><strong>SHP-2026-00124</strong><span>Wheat · 200 kg · In transit</span></div><div class="map-route"></div><div class="map-pin one"></div><div class="map-pin two"></div></div><div class="track-events">
   ${[["Picked up","Rajesh Farm · Sonipat, Haryana","10:12 AM"],["In transit","NH-44 · Panipat corridor","10:42 AM"],["Next checkpoint","Kundli logistics point","Expected 11:35 AM"],["Destination","ABC Foods · Okhla, Delhi","ETA 03:40 PM"]].map((e,i)=>`<div class="track-event"><span class="bullet"></span><div><strong>${e[0]}</strong><small>${e[1]}</small><small>${e[2]}</small></div></div>`).join("")}
  </div></div>`
 },
 reports:{
  title:"Reports", eyebrow:"FARM PERFORMANCE", text:"A compact view of inventory movement and order value for your operation.",
  html:`<div class="report-grid"><div class="chart"><span class="eyebrow">ORDER VALUE · LAST 7 DAYS</span><div class="bars">${[38,55,48,72,61,88,100].map((h,i)=>`<div class="bar ${i===6?"active":""}" style="height:${h}%"><span>${["19","20","21","22","23","24","25"][i]}</span></div>`).join("")}</div></div><div class="report-stat"><span class="eyebrow" style="color:#8fa79a">PROCESSED VALUE</span><h3>₹34,800</h3><p>Accepted orders converted into active or completed shipments this month.</p><div class="progress" style="position:static;margin-top:24px"><span style="width:72%"></span></div></div></div>`
 }
};

function renderPage(name){
 const p=pages[name];
 if(!p){dashboard.classList.remove("hidden-view");secondary.classList.remove("show");pageTitle.textContent="Overview";return}
 dashboard.classList.add("hidden-view");secondary.classList.add("show");
 pageTitle.textContent=p.title; subTitle.textContent=p.title; subEyebrow.textContent=p.eyebrow; subText.textContent=p.text; subContent.innerHTML=p.html;
 subAction.textContent=name==="stock"?"＋ Add stock":"";
 subAction.style.display=name==="stock"?"block":"none";
 sidebar.classList.remove("open");overlay.classList.remove("show");
 if(name==="shipments") $("#qrBtn")?.addEventListener("click",()=>showToast("QR label ready","Shipment label generated for SHP-2026-00124."));
}
function go(name){renderPage(name);history.replaceState(null,"",name==="dashboard"?"#dashboard":"#"+name)}
$$(".nav-item").forEach(a=>a.addEventListener("click",e=>{e.preventDefault();go(a.dataset.view)}));
$$("[data-view-link]").forEach(a=>a.addEventListener("click",()=>go(a.dataset.viewLink)));
subAction.onclick=()=>{if(location.hash!=="#stock")return;openModal()};
$("#stockForm").addEventListener("submit",e=>{
 e.preventDefault(); const fd=new FormData(e.target); closeModal(); showToast("Stock published",`${fd.get("quantity")} ${fd.get("unit")} of ${fd.get("product")} is now visible to buyers.`); e.target.reset();
});
$$(".review-btn").forEach(btn=>btn.addEventListener("click",()=>{
 showToast("Order opened","ORD-1024 is ready for acceptance.");
 setTimeout(()=>go("orders"),350);
}));
window.addEventListener("hashchange",()=>{const n=location.hash.slice(1)||"dashboard";n==="dashboard"?renderPage("dashboard"):renderPage(n)});
const initial=location.hash.slice(1); if(initial&&pages[initial])renderPage(initial);
