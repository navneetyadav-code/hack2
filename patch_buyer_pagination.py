import re

with open('pages/buyer.php', 'r', encoding='utf-8') as f:
    html = f.read()

old_html = r"""          <div class="panel search-results-panel mt-4">
            <div class="panel-header">
              <h2>Available Produce from Farmers</h2>
            </div>
            <div id="searchResults" class="data-grid">
                <p style="padding:20px; color:#6b7280;">Start typing to see live results...</p>
            </div>
          </div>"""

new_html = r"""          <div class="panel search-results-panel mt-4">
            <div class="panel-header">
              <h2>Available Produce from Farmers</h2>
            </div>
            <div id="searchResults" class="data-grid">
                <p style="padding:20px; color:#6b7280;">Start typing to see live results...</p>
            </div>
            <div id="paginationControls" style="display:flex; justify-content:center; gap:10px; margin-top:20px; align-items:center;">
              <!-- Pagination buttons injected here -->
            </div>
          </div>"""

html = html.replace(old_html, new_html)

with open('pages/buyer.php', 'w', encoding='utf-8') as f:
    f.write(html)
