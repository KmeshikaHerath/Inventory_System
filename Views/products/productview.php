<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<?php

$permissions = $_SESSION['permissions'] ?? [];
?>

<!-- Pass permissions to JS -->
<script>
     // function hasPermission(name) {
    //     return permissions.includes(name);
    // }
</script>

<div class="max-w-7xl mx-auto p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Products</h1>

        <!-- ADD BUTTON (permission controlled) -->
        <?php if (in_array('products_create', $permissions)): ?>
            <button onclick="openCreateModal()"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                + Add Product
            </button>
        <?php endif; ?>
    </div>

    <!-- SEARCH -->
    <div class="bg-white p-4 rounded-lg shadow mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" id="search" placeholder="Search product..."
            class="border p-2 rounded w-full">

        <input type="number" id="minPrice" placeholder="Min Price"
            class="border p-2 rounded w-full">

        <input type="number" id="maxPrice" placeholder="Max Price"
            class="border p-2 rounded w-full">

        <button onclick="searchProducts()"
            class="bg-gray-800 text-white rounded px-4 py-2 hover:bg-gray-900 transition">
            Search
        </button>
    </div>

    <!-- TABLE -->
    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">SKU</th>
                    <th class="p-3">Price</th>
                    <th class="p-3">Qty</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody id="productTable"></tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div id="pagination" class="flex justify-center mt-6 gap-2"></div>
</div>

<!-- MODAL -->
<div id="productModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-lg p-6 rounded shadow-xl">

        <h2 id="modalTitle" class="text-xl font-bold mb-4">Add Product</h2>

        <form id="productForm">

            <input type="hidden" name="id" id="productId">

            <input type="text" name="name" id="name" placeholder="Name" required
                class="w-full border p-2 mb-2 rounded">

            <input type="number" name="price" id="price" placeholder="Price" required step="0.01"
                class="w-full border p-2 mb-2 rounded">

            <input type="number" name="quantity" id="quantity" placeholder="Quantity" required
                class="w-full border p-2 mb-2 rounded">

            <input type="text" name="sku" id="sku" placeholder="SKU" required
                class="w-full border p-2 mb-2 rounded">

            <textarea name="description" id="description" placeholder="Description"
                class="w-full border p-2 mb-2 rounded" rows="3"></textarea>

            <button type="submit"
                class="bg-blue-600 text-white w-full py-2 rounded hover:bg-blue-700 transition">
                Save Product
            </button>
        </form>

        <button onclick="closeModal()"
            class="mt-3 text-red-500 hover:text-red-700 transition w-full">
            Close
        </button>
    </div>
</div>

<script>
    const permissions = <?= json_encode($permissions ?? []) ?>;
    console.log("PERMISSIONS:", permissions);
    let currentPage = 1;
    let isLoading = false;

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = "Add Product";
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = "";
        openModal();
    }

    function openModal() {
        document.getElementById('productModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('productModal').classList.add('hidden');
    }

    function loadPage(page = 1) {
        if (isLoading) return;
        isLoading = true;

        currentPage = page;

        const search = encodeURIComponent(document.getElementById('search').value);
        const minPrice = encodeURIComponent(document.getElementById('minPrice').value);
        const maxPrice = encodeURIComponent(document.getElementById('maxPrice').value);

        fetch(`/products/paginate?page=${page}&search=${search}&minPrice=${minPrice}&maxPrice=${maxPrice}`)
            .then(res => res.json())
            .then(data => {
                renderTable(data.data || []);
                renderPagination(data.totalPages || 1, data.currentPage || 1);
            })
            .finally(() => isLoading = false);
    }

    function renderTable(products) {
        const table = document.getElementById('productTable');
        table.innerHTML = "";

        if (!products.length) {
            table.innerHTML = `<tr><td colspan="6" class="text-center p-6">No products found</td></tr>`;
            return;
        }

        products.forEach(p => {

            let actions = "";

            // DEBUG (optional)
            // console.log(p.id, permissions);

            if (permissions.includes('products_update')) {
                actions += `
        <button onclick="editProduct(${p.id})"
            class="bg-yellow-500 px-2 py-1 text-white rounded mr-1">
            Edit
        </button>`;
            }

            if (permissions.includes('products_delete')) {
                actions += `
        <button onclick="deleteProduct(${p.id})"
            class="bg-red-500 px-2 py-1 text-white rounded">
            Delete
        </button>`;
            }
            table.innerHTML += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">${escapeHtml(p.id)}</td>
                    <td class="p-3">${escapeHtml(p.name)}</td>
                    <td class="p-3">${escapeHtml(p.sku)}</td>
                    <td class="p-3">$${parseFloat(p.price).toFixed(2)}</td>
                    <td class="p-3">${parseInt(p.quantity)}</td>
                    <td class="p-3">${actions}</td>
                </tr>`;
        });
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderPagination(totalPages, currentPage) {
        const container = document.getElementById('pagination');
        container.innerHTML = "";

        for (let i = 1; i <= totalPages; i++) {
            container.innerHTML += `
                <button onclick="loadPage(${i})"
                    class="${i === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200'} px-2 py-1 rounded">
                    ${i}
                </button>`;
        }
    }

    function searchProducts() {
        loadPage(1);
    }

    document.getElementById('search').addEventListener('keyup', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(searchProducts, 300);
    });

    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('productId').value;
        const url = id ? '/products/update' : '/products/store';

        fetch(url, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal();
                    loadPage(currentPage);
                    Swal.fire("Success", "Product saved", "success");
                } else {
                    Swal.fire("Error", data.message, "error");
                }
            });
    });

    function deleteProduct(id) {
        Swal.fire({
            title: "Are you sure?",
            icon: "warning",
            showCancelButton: true
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch('/products/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + id
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadPage(currentPage);
                        Swal.fire("Deleted!", "", "success");
                    } else {
                        Swal.fire("Error", data.message, "error");
                    }
                });
        });
    }

    function editProduct(id) {
        fetch('/products/get?id=' + id)
            .then(res => res.json())
            .then(p => {
                document.getElementById('modalTitle').innerText = "Edit Product";
                document.getElementById('productId').value = p.id;
                document.getElementById('name').value = p.name || '';
                document.getElementById('price').value = p.price || '';
                document.getElementById('quantity').value = p.quantity || '';
                document.getElementById('sku').value = p.sku || '';
                document.getElementById('description').value = p.description || '';
                openModal();
            });
    }

    window.onload = () => loadPage(1);
</script>