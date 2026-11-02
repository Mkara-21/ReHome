document.addEventListener('DOMContentLoaded', () => {

  // ========================================================
  // 1. Client-Side Form Validation (create-listing.php)
  // ========================================================
  const listingForm = document.getElementById('listingForm');

  if (listingForm) {
    listingForm.addEventListener('submit', (e) => {
      let isValid = true;

      const title = document.getElementById('title');
      const price = document.getElementById('price');
      const category = document.getElementById('category');

      // Reset errors
      document.querySelectorAll('.error-text').forEach(el => el.textContent = '');

      if (!title.value.trim()) {
        showError(title, 'Please enter an item title.');
        isValid = false;
      }

      if (!price.value || parseFloat(price.value) <= 0) {
        showError(price, 'Please enter a valid price in KSh.');
        isValid = false;
      }

      if (!category.value) {
        showError(category, 'Please select a category.');
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault(); // Stop PHP submission if JS validation fails
      }
    });
  }

  function showError(inputElement, message) {
    const errorSpan = document.getElementById(`${inputElement.id}-error`);
    if (errorSpan) {
      errorSpan.textContent = message;
    }
  }

  // ========================================================
  // 2. Meaningful Interaction #1: Live Listing Preview
  // ========================================================
  const titleInput = document.getElementById('title');
  const priceInput = document.getElementById('price');
  const conditionInput = document.getElementById('condition');

  const previewTitle = document.getElementById('preview-title');
  const previewPrice = document.getElementById('preview-price');
  const previewCondition = document.getElementById('preview-condition');

  if (titleInput && previewTitle) {
    titleInput.addEventListener('input', (e) => {
      previewTitle.textContent = e.target.value.trim() || 'Item Name Placeholder';
    });
  }

  if (priceInput && previewPrice) {
    priceInput.addEventListener('input', (e) => {
      const val = parseFloat(e.target.value);
      previewPrice.textContent = !isNaN(val) ? `KSh ${val.toLocaleString()}` : 'KSh 0';
    });
  }

  if (conditionInput && previewCondition) {
    conditionInput.addEventListener('change', (e) => {
      previewCondition.textContent = e.target.value || 'Select Condition';
    });
  }

  // ========================================================
  // 3. Meaningful Interaction #2: Dynamic Moving Sale Summary
  // ========================================================
  const bundleItems = [];
  const addItemBtn = document.getElementById('add-to-bundle-btn');

  if (addItemBtn) {
    addItemBtn.addEventListener('click', () => {
      const itemTitle = document.getElementById('bundle-item-name').value;
      const itemPrice = parseFloat(document.getElementById('bundle-item-price').value);
      const itemCategory = document.getElementById('bundle-item-category').value;

      if (itemTitle && !isNaN(itemPrice) && itemCategory) {
        bundleItems.push({ title: itemTitle, price: itemPrice, category: itemCategory });
        updateBundleSummary();
      }
    });
  }

  function updateBundleSummary() {
    const itemCountEl = document.getElementById('summary-count');
    const totalValueEl = document.getElementById('summary-total');

    const totalCount = bundleItems.length;
    const totalValue = bundleItems.reduce((sum, item) => sum + item.price, 0);

    if (itemCountEl) itemCountEl.textContent = `${totalCount} items`;
    if (totalValueEl) totalValueEl.textContent = `KSh ${totalValue.toLocaleString()}`;
  }
});