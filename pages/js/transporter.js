let html5QrcodeScanner = null;
let lastScanTime = 0;

document.addEventListener("DOMContentLoaded", () => {
    // Navigation
    const switchTab = (pageId) => {
        document.querySelectorAll(".page").forEach(p => p.classList.remove("active"));
        document.querySelectorAll(".nav-btn").forEach(b => b.classList.remove("active"));
        
        const targetPage = document.getElementById(pageId);
        if (!targetPage) return;
        targetPage.classList.add("active");
        
        const btn = document.querySelector(`[data-page="${targetPage.id}"]`);
        if (btn) btn.classList.add("active");
        
        const titles = { scannerPage: "Package Scanner", deliveriesPage: "My Deliveries" };
        document.getElementById("pageTitle").textContent = titles[targetPage.id];
        
        if (targetPage.id === "scannerPage") {
            startScanner();
        } else {
            stopScanner();
            loadDeliveries();
        }
    };

    document.querySelectorAll(".nav-btn").forEach(btn => {
        btn.addEventListener("click", () => switchTab(btn.dataset.page));
    });

    document.getElementById("logoutBtn").addEventListener("click", () => {
        window.location.href = "../actions/auth.php?action=logout";
    });

    // Start on scanner
    startScanner();
});

function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

function startScanner() {
    if (html5QrcodeScanner) return;
    
    html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader", { fps: 10, qrbox: { width: 250, height: 250 } }
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
}

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear().catch(e => console.error(e));
        html5QrcodeScanner = null;
    }
}

async function onScanSuccess(decodedText, decodedResult) {
    const now = Date.now();
    if (now - lastScanTime < 3000) return; // prevent spamming 3 seconds
    lastScanTime = now;

    try {
        const res = await fetch("../api/scan_qr.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ qr_code_text: decodedText })
        });
        const result = await res.json();
        
        const resDiv = document.getElementById("scanResult");
        const msgP = document.getElementById("scanMessage");
        resDiv.style.display = "block";
        
        if (result.success) {
            msgP.textContent = `Tracking updated to: ${result.new_status}. Timestamp securely logged.`;
            resDiv.style.background = "#f0fdf4";
            resDiv.style.borderColor = "#16a34a";
            resDiv.querySelector("h3").style.color = "#16a34a";
            resDiv.querySelector("h3").innerHTML = `<i class="fa-solid fa-circle-check"></i> Scan Successful!`;
            showToast("Package updated!");
        } else {
            msgP.textContent = result.error || "Invalid QR Code.";
            resDiv.style.background = "#fef2f2";
            resDiv.style.borderColor = "#b91c1c";
            resDiv.querySelector("h3").style.color = "#b91c1c";
            resDiv.querySelector("h3").innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Scan Failed`;
        }
        
        // Hide result after 5 seconds
        setTimeout(() => { resDiv.style.display = "none"; }, 5000);
        
    } catch (e) {
        console.error(e);
        alert("Network error processing scan.");
    }
}

function onScanFailure(error) {
    // handled implicitly
}

async function loadDeliveries() {
    try {
        const res = await fetch("../api/transporter_deliveries.php");
        const deliveries = await res.json();
        const container = document.getElementById("deliveriesList");
        
        if (deliveries.length === 0) {
            container.innerHTML = "<p style='padding:20px;'>No deliveries processed yet.</p>";
            return;
        }

        container.innerHTML = deliveries.map(d => `
            <div class="panel" style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0 0 5px 0;">${d.tracking_id}</h3>
                    <p style="margin:0; color:#6b7280; font-size:14px;">Last scanned on ${d.updated_at}</p>
                </div>
                <span class="badge" style="background:#16a34a;color:white;">${d.status}</span>
            </div>
        `).join('');
    } catch(e) {
        console.error(e);
    }
}
