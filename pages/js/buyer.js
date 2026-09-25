document.addEventListener("DOMContentLoaded", () => {

  const navButtons = document.querySelectorAll(".nav-btn");
  const pages = document.querySelectorAll(".page");
  const pageTitle = document.getElementById("pageTitle");

  const pageNames = {
    dashboard: "Overview",
    findProduce: "Find Produce",
    farmers: "Farmers",
    orders: "My Orders",
    profile: "Profile"
  };

  function showPage(pageId) {

    pages.forEach(page => {
      page.classList.remove("active");
    });

    navButtons.forEach(button => {
      button.classList.remove("active");
    });

    const page = document.getElementById(pageId);
    const button = document.querySelector(`[data-page="${pageId}"]`);

    if (page) {
      page.classList.add("active");
    }

    if (button) {
      button.classList.add("active");
    }

    if (pageTitle) {
      pageTitle.textContent = pageNames[pageId] || "Overview";
    }
  }

  navButtons.forEach(button => {

    button.addEventListener("click", () => {

      const pageId = button.dataset.page;

      showPage(pageId);

    });

  });


  document.querySelectorAll("[data-go]").forEach(button => {

    button.addEventListener("click", () => {

      const pageId = button.dataset.go;

      showPage(pageId);

    });

  });


  const searchForm = document.getElementById("productSearchForm");

  if (searchForm) {

    searchForm.addEventListener("submit", event => {

      event.preventDefault();

      const searchValue =
        document.getElementById("productSearch").value.trim();

      if (!searchValue) {
        return;
      }

      searchProducts(searchValue);

    });

  }


  function searchProducts(product) {

    const resultsContainer =
      document.getElementById("farmerSearchResults");

    const resultText =
      document.getElementById("searchResultText");

    if (!resultsContainer) {
      return;
    }

    resultsContainer.innerHTML = "";

    if (resultText) {
      resultText.textContent = product;
    }

    loadFarmersFromBackend(product);

  }


  async function loadFarmersFromBackend(product) {

    const resultsContainer =
      document.getElementById("farmerSearchResults");

    try {

      const response = await fetch(
        `api/search_products.php?product=${encodeURIComponent(product)}`
      );

      if (!response.ok) {
        throw new Error("Request failed");
      }

      const farmers = await response.json();

      resultsContainer.innerHTML = "";

      farmers.forEach(farmer => {

        const card = document.createElement("div");

        card.className = "panel";

        card.innerHTML = `
          <div class="panel-header">
            <h2>${farmer.farmer_name ?? ""}</h2>
          </div>

          <div class="farmer-details">

            <p>
              <strong>Product:</strong>
              ${farmer.product_name ?? ""}
            </p>

            <p>
              <strong>Available Quantity:</strong>
              ${farmer.quantity ?? ""}
            </p>

            <p>
              <strong>Location:</strong>
              ${farmer.location ?? ""}
            </p>

            <p>
              <strong>Contact:</strong>
              ${farmer.contact ?? ""}
            </p>

          </div>

          <div class="form-actions">

            <button
              class="btn-primary"
              data-farmer-id="${farmer.farmer_id ?? ""}"
            >
              Contact Farmer
            </button>

          </div>
        `;

        resultsContainer.appendChild(card);

      });

    } catch (error) {

      resultsContainer.innerHTML = "";

    }

  }


  const profileForm = document.getElementById("profileForm");

  if (profileForm) {

    profileForm.addEventListener("submit", async event => {

      event.preventDefault();

      const profileData = {

        name: document.getElementById("profName").value,
        phone: document.getElementById("profPhone").value,
        business: document.getElementById("profBusiness").value,
        location: document.getElementById("profLoc").value

      };

      try {

        await fetch("api/update_buyer_profile.php", {

          method: "POST",

          headers: {
            "Content-Type": "application/json"
          },

          body: JSON.stringify(profileData)

        });

      } catch (error) {

      }

    });

  }


  const logoutButton = document.getElementById("logoutBtn");

  if (logoutButton) {

    logoutButton.addEventListener("click", async () => {

      try {

        await fetch("logout.php");

      } finally {

        window.location.href = "login.php";

      }

    });

  }

});