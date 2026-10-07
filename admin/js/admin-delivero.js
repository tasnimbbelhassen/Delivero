

document.addEventListener("DOMContentLoaded", function () {

  const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltips.forEach((tooltip) => {
    new bootstrap.Tooltip(tooltip);
  });

 
  const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
  popovers.forEach((popover) => {
    new bootstrap.Popover(popover);
  });

  
  const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
  alerts.forEach((alert) => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  
  const deleteButtons = document.querySelectorAll('a[href*="delete"]');
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (!confirm("Êtes-vous sûr de vouloir effectuer cette action ?")) {
        e.preventDefault();
      }
    });
  });

  
  const forms = document.querySelectorAll("form[data-validate]");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      this.classList.add("was-validated");
    });
  });

 
  const filterSelects = document.querySelectorAll("select[data-auto-filter]");
  filterSelects.forEach((select) => {
    select.addEventListener("change", function () {
      this.form.submit();
    });
  });

  
  const searchInputs = document.querySelectorAll("input[data-delayed-search]");
  searchInputs.forEach((input) => {
    let timeout = null;
    input.addEventListener("input", function () {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        this.form.submit();
      }, 500);
    });
  });

  
  const toggleSwitches = document.querySelectorAll(
    ".form-check-input[data-toggle-action]",
  );
  toggleSwitches.forEach((switchEl) => {
    switchEl.addEventListener("change", function () {
      const action = this.getAttribute("data-toggle-action");
      const id = this.getAttribute("data-id");
      const newStatus = this.checked ? 1 : 0;

     
      const originalHTML = this.parentElement.innerHTML;
      this.parentElement.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span>';

      fetch(`toggle_${action}.php?id=${id}&status=${newStatus}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            
            this.parentElement.innerHTML = originalHTML;
            
            this.parentElement
              .querySelector(".form-check-input")
              .addEventListener("change", arguments.callee);
          } else {
            alert("Erreur: " + data.message);
            location.reload();
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Une erreur est survenue");
          location.reload();
        });
    });
  });

  
  const quantityInputs = document.querySelectorAll(
    "input[data-calculate-subtotal]",
  );
  quantityInputs.forEach((input) => {
    input.addEventListener("input", function () {
      const price = parseFloat(this.getAttribute("data-price")) || 0;
      const quantity = parseInt(this.value) || 0;
      const subtotal = price * quantity;

      const subtotalElement = document.getElementById(
        this.getAttribute("data-subtotal-id"),
      );
      if (subtotalElement) {
        subtotalElement.textContent = subtotal.toFixed(2) + " €";
      }

      
      updateTotal();
    });
  });

  
  function updateTotal() {
    let total = 0;
    document.querySelectorAll("[data-item-subtotal]").forEach((element) => {
      const subtotal = parseFloat(element.textContent) || 0;
      total += subtotal;
    });

    const totalElement = document.getElementById("totalAmount");
    if (totalElement) {
      totalElement.textContent = total.toFixed(2) + " €";
    }
  }


  const dropZones = document.querySelectorAll(".drop-zone");
  dropZones.forEach((zone) => {
    zone.addEventListener("dragover", function (e) {
      e.preventDefault();
      this.classList.add("dragover");
    });

    zone.addEventListener("dragleave", function () {
      this.classList.remove("dragover");
    });

    zone.addEventListener("drop", function (e) {
      e.preventDefault();
      this.classList.remove("dragover");

      const files = e.dataTransfer.files;
      if (files.length > 0) {
        const fileInput = this.querySelector('input[type="file"]');
        if (fileInput) {
          fileInput.files = files;

          
          const event = new Event("change", { bubbles: true });
          fileInput.dispatchEvent(event);
        }
      }
    });
  });

  
  const imageInputs = document.querySelectorAll(
    'input[type="file"][data-preview]',
  );
  imageInputs.forEach((input) => {
    input.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        const previewId = this.getAttribute("data-preview");
        const previewElement = document.getElementById(previewId);

        reader.onload = function (e) {
          if (previewElement) {
            previewElement.innerHTML = `
                            <img src="${e.target.result}" 
                                 class="img-fluid rounded" 
                                 style="max-height: 200px; object-fit: cover;">
                        `;
          }
        };
        reader.readAsDataURL(file);
      }
    });
  });


  const searchAutocomplete = document.querySelectorAll(
    "input[data-autocomplete]",
  );
  searchAutocomplete.forEach((input) => {
    let timeout = null;
    input.addEventListener("input", function () {
      clearTimeout(timeout);

      const query = this.value.trim();
      if (query.length < 2) return;

      timeout = setTimeout(() => {
        const source = this.getAttribute("data-autocomplete");
        fetch(
          `autocomplete.php?source=${source}&q=${encodeURIComponent(query)}`,
        )
          .then((response) => response.json())
          .then((data) => {
            
            showAutocompleteSuggestions(this, data);
          })
          .catch(console.error);
      }, 300);
    });

    
    document.addEventListener("click", function (e) {
      if (!e.target.closest(".autocomplete-container")) {
        hideAutocompleteSuggestions(input);
      }
    });
  });

  
  function showAutocompleteSuggestions(input, suggestions) {
    let container = input.parentElement.querySelector(
      ".autocomplete-container",
    );
    if (!container) {
      container = document.createElement("div");
      container.className = "autocomplete-container position-absolute w-100";
      container.style.zIndex = "1000";
      input.parentElement.style.position = "relative";
      input.parentElement.appendChild(container);
    }

    if (suggestions.length === 0) {
      container.innerHTML =
        '<div class="autocomplete-item">Aucun résultat</div>';
    } else {
      container.innerHTML = suggestions
        .map(
          (item) => `
                <div class="autocomplete-item" data-value="${item.value}">
                    ${item.label}
                </div>
            `,
        )
        .join("");

      
      container.querySelectorAll(".autocomplete-item").forEach((item) => {
        item.addEventListener("click", function () {
          input.value = this.getAttribute("data-value");
          hideAutocompleteSuggestions(input);
          
          if (input.form) {
            input.form.submit();
          }
        });
      });
    }

    container.style.display = "block";
  }

  function hideAutocompleteSuggestions(input) {
    const container = input.parentElement.querySelector(
      ".autocomplete-container",
    );
    if (container) {
      container.style.display = "none";
    }
  }

 
  const textareasWithCounter = document.querySelectorAll(
    "textarea[data-maxlength]",
  );
  textareasWithCounter.forEach((textarea) => {
    const maxLength = parseInt(textarea.getAttribute("data-maxlength")) || 500;
    const counterId = textarea.id + "-counter";

   
    const counter = document.createElement("div");
    counter.id = counterId;
    counter.className = "form-text text-end";
    counter.textContent = `0/${maxLength}`;

    textarea.parentElement.appendChild(counter);

    
    textarea.addEventListener("input", function () {
      const length = this.value.length;
      counter.textContent = `${length}/${maxLength}`;

      if (length > maxLength) {
        counter.classList.add("text-danger");
      } else {
        counter.classList.remove("text-danger");
      }
    });

    
    textarea.dispatchEvent(new Event("input"));
  });

  
  const sortableHeaders = document.querySelectorAll("th[data-sortable]");
  sortableHeaders.forEach((header) => {
    header.style.cursor = "pointer";
    header.addEventListener("click", function () {
      const table = this.closest("table");
      const columnIndex = Array.from(this.parentElement.children).indexOf(this);
      const sortDirection = this.getAttribute("data-sort-direction") || "asc";

      
      sortableHeaders.forEach((h) => {
        if (h !== this) {
          h.removeAttribute("data-sort-direction");
          h.classList.remove("sorted-asc", "sorted-desc");
        }
      });

      
      const newDirection = sortDirection === "asc" ? "desc" : "asc";
      this.setAttribute("data-sort-direction", newDirection);
      this.classList.toggle("sorted-asc", newDirection === "asc");
      this.classList.toggle("sorted-desc", newDirection === "desc");

      
      const tbody = table.querySelector("tbody");
      const rows = Array.from(tbody.querySelectorAll("tr"));

      rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();

        
        const aNum = parseFloat(aValue.replace(",", "."));
        const bNum = parseFloat(bValue.replace(",", "."));

        if (!isNaN(aNum) && !isNaN(bNum)) {
          return newDirection === "asc" ? aNum - bNum : bNum - aNum;
        } else {
         
          return newDirection === "asc"
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
        }
      });

      
      rows.forEach((row) => tbody.appendChild(row));
    });
  });
});


function formatPrice(price) {
  return parseFloat(price).toFixed(2).replace(".", ",") + " €";
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("fr-FR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function showLoading(element) {
  const originalHTML = element.innerHTML;
  element.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
  element.disabled = true;
  return originalHTML;
}

function hideLoading(element, originalHTML) {
  element.innerHTML = originalHTML;
  element.disabled = false;
}

function showToast(message, type = "success") {
  const toastContainer =
    document.getElementById("toast-container") || createToastContainer();
  const toastId = "toast-" + Date.now();

  const toast = document.createElement("div");
  toast.id = toastId;
  toast.className = `toast align-items-center text-bg-${type} border-0`;
  toast.setAttribute("role", "alert");

  toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

  toastContainer.appendChild(toast);
  const bsToast = new bootstrap.Toast(toast);
  bsToast.show();

 
  toast.addEventListener("hidden.bs.toast", function () {
    this.remove();
  });
}

function createToastContainer() {
  const container = document.createElement("div");
  container.id = "toast-container";
  container.className = "toast-container position-fixed bottom-0 end-0 p-3";
  container.style.zIndex = "9999";
  document.body.appendChild(container);
  return container;
}


window.DeliveroAdmin = {
  formatPrice,
  formatDate,
  showLoading,
  hideLoading,
  showToast,
};
