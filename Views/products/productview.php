<!-- DEPENDENCIES -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>

<?php $permissions = $_SESSION['permissions'] ?? []; ?>

<script>
const permissions = <?= json_encode($permissions ?? []) ?>;

let table;
let showDeleted = false;
let onlyActive = false;
</script>

<div class="max-w-7xl mx-auto p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Products</h1>

        <div class="flex gap-2">

            <?php if (in_array('products_create', $permissions)): ?>
                <button onclick="openCreateModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                    + Add Product
                </button>
            <?php endif; ?>

        </div>
    </div>

    <!-- FILTERS -->
    <div class="bg-white p-4 rounded shadow mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">

        <input type="text" id="search" placeholder="Search product..." class="border p-2 rounded">

        <input type="number" id="minPrice" placeholder="Min Price" class="border p-2 rounded">

        <input type="number" id="maxPrice" placeholder="Max Price" class="border p-2 rounded">

        <select id="status" class="border p-2 rounded">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>

    </div>

    <!-- TABLE -->
    <div class="bg-white shadow rounded overflow-x-auto">
        <table id="productsTable" class="w-full text-sm text-left">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">Image</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">SKU</th>
                    <th class="p-3">Price</th>
                    <th class="p-3">Qty</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- MODAL -->
<div id="productModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">

    <div class="bg-white w-full max-w-lg p-6 rounded shadow-lg">

        <h2 id="modalTitle" class="text-xl font-bold mb-4">Add Product</h2>

        <form id="productForm" enctype="multipart/form-data">

            <input type="hidden" id="productId" name="id">

            <input type="text" id="name" name="name" placeholder="Name"
                class="w-full border p-2 mb-2 rounded">

            <input type="number" id="price" name="price" placeholder="Price"
                class="w-full border p-2 mb-2 rounded">

            <input type="number" id="quantity" name="quantity" placeholder="Quantity"
                class="w-full border p-2 mb-2 rounded">

            <input type="text" id="sku" name="sku" placeholder="SKU"
                class="w-full border p-2 mb-2 rounded">

            <select id="statusSelect" name="status"
                class="w-full border p-2 mb-2 rounded">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <input type="file" name="image" class="w-full border p-2 mb-2">

            <textarea id="description" name="description"
                class="w-full border p-2 mb-2 rounded"
                placeholder="Description"></textarea>

            <button class="bg-blue-600 text-white w-full py-2 rounded">
                Save
            </button>
        </form>

        <button onclick="closeModal()" class="mt-3 w-full text-red-500">
            Close
        </button>

    </div>
</div>

<script>

/* ================= DATA TABLE ================= */
$(document).ready(function () {

    table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,

        ajax: {
            url: "/products/paginate",
            data: function (d) {
                d.search_custom = $('#search').val();
                d.min_price = $('#minPrice').val();
                d.max_price = $('#maxPrice').val();
                d.status = $('#status').val();
                d.deleted = showDeleted ? 1 : 0;
                d.only_active = onlyActive ? 1 : 0;
            }
        },

        columns: [
            { data: 'id' },

            {
                data: 'image',
                render: d => `<img src="${d || 'placeholder.jpg'}" class="h-10 w-10 rounded">`
            },

            { data: 'name' },
            { data: 'sku' },
            { data: 'price' },
            { data: 'quantity' },

            {
                data: 'status',
                render: d =>
                    d === 'active'
                        ? `<span class="text-green-600">Active</span>`
                        : `<span class="text-red-600">Inactive</span>`
            },

            {
                data: null,
                render: function (data, type, row) {

                    let html = '';

                    if (row.deleted_at) {
                        html += `<button onclick="restoreProduct(${row.id})" class="text-green-600">Restore</button>`;
                    } else {

                        if (permissions.includes('products_update')) {
                            html += `<button onclick="editProduct(${row.id})" class="text-blue-600 mr-2">Edit</button>`;
                        }

                        if (permissions.includes('products_delete')) {
                            html += `<button onclick="deleteProduct(${row.id})" class="text-red-600">Delete</button>`;
                        }
                    }

                    return html;
                }
            }
        ]
    });

    $('#search,#minPrice,#maxPrice,#status').on('keyup change', function () {
        table.ajax.reload();
    });
});

/* ================= MODAL ================= */
function openCreateModal() {
    $('#productForm')[0].reset();
    $('#productId').val('');
    $('#modalTitle').text('Add Product');

    const modal = document.getElementById('productModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('productModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/* close on background click */
document.getElementById('productModal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});

/* ================= DELETE ================= */
function deleteProduct(id) {
    Swal.fire({
        title: "Delete Product?",
        icon: "warning",
        showCancelButton: true
    }).then(r => {
        if (!r.isConfirmed) return;

        fetch('/products/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id })
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                table.ajax.reload();
                Swal.fire("Deleted", "", "success");
            }
        });
    });
}

/* ================= RESTORE ================= */
function restoreProduct(id) {
    fetch('/products/restore', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            table.ajax.reload();
            Swal.fire("Restored", "", "success");
        }
    });
}

/* ================= EDIT ================= */
function editProduct(id) {
    fetch('/products/get?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const p = res.data;

                $('#modalTitle').text('Edit Product');
                $('#productId').val(p.id);
                $('#name').val(p.name);
                $('#price').val(p.price);
                $('#quantity').val(p.quantity);
                $('#sku').val(p.sku);
                $('#description').val(p.description);
                $('#statusSelect').val(p.status);

                openCreateModal();
            }
        });
}

/* ================= SAVE ================= */
$('#productForm').on('submit', function (e) {
    e.preventDefault();

    const id = $('#productId').val();
    const url = id ? '/products/update' : '/products/store';

    fetch(url, {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            closeModal();
            table.ajax.reload();
            Swal.fire("Success", "Saved", "success");
        } else {
            Swal.fire("Error", res.message, "error");
        }
    });
});

</script>