<!-- DEPENDENCIES -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>

<?php
$permissions = $_SESSION['permissions'] ?? [];
?>

<script>
    // SAFE permissions (IMPORTANT FIX)
    const permissions = <?= json_encode($permissions ?? []) ?> || [];

    let table;
    let showDeleted = false;
</script>

<div class="max-w-7xl mx-auto p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Products</h1>

        <?php if (in_array('products_create', $permissions)): ?>
            <button onclick="openCreateModal()"
                class="bg-blue-600 text-white px-4 py-2 rounded">
                + Add Product
            </button>
        <?php endif; ?>
    </div>

    <!-- FILTERS -->
    <div class="bg-white p-4 rounded shadow mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">

        <input type="text" id="search" placeholder="Search..." class="border p-2 rounded">

        <input type="number" id="minPrice" placeholder="Min Price" class="border p-2 rounded">

        <input type="number" id="maxPrice" placeholder="Max Price" class="border p-2 rounded">

        </select>

    </div>

    <!-- TABLE -->
    <div class="bg-white shadow rounded overflow-x-auto">
        <table id="productsTable" class="w-full text-sm">
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

            <input type="hidden" name="status" id="statusHidden" value="active">

            <div class="flex items-center gap-2 mb-2">
                <label class="font-medium">Status</label>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input id="statusToggle" type="checkbox"
                        class="peer appearance-none w-10 h-5 bg-slate-100 rounded-full
       checked:bg-blue-600 cursor-pointer transition-colors duration-300" checked>

                    <label for="statusToggle"
                        class="absolute top-0 left-0 w-5 h-5 bg-white rounded-full border border-slate-300 shadow-sm
       transition-transform duration-300 peer-checked:translate-x-3 peer-checked:border-slate-800 cursor-pointer">
                    </label>
                </label>
            </div>

            <input type="file" name="image" class="w-full border p-2 mb-2">

            <textarea id="description" name="description"
                class="w-full border p-2 mb-2 rounded"
                placeholder="Description"></textarea>

            <div class="flex justify-end gap-2 mt-4">

                <!-- Close Button -->
                <button
                    type="button"
                    onclick="closeModal()"
                    class="px-4 py-2 text-red-500 border border-red-500 rounded hover:bg-red-50">
                    Close
                </button>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save
                </button>

            </div>

        </form>

    </div>
</div>

<script>
    /* ================= DATA TABLE ================= */

    $(document).ready(function() {


        $('#statusToggle').on('change', function() {
            $('#statusHidden').val(
                $(this).is(':checked') ? 'active' : 'inactive'
            );
        });
        table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,

            ajax: {
                url: "/products/paginate",
                type: "GET",
                data: function(d) {
                    d.search_custom = $('#search').val();
                    d.min_price = $('#minPrice').val();
                    d.max_price = $('#maxPrice').val();
                    d.status = $('#status').val();
                    d.deleted = showDeleted ? 1 : 0;
                }
            },

            columns: [{
                    data: 'id'
                },

                {
                    data: 'image_path',
                    render: function(d, type, row) {

                        const img = d || `/Images/uploads/products/products${row.id}.png`;

                        return `<img src="${img}" class="h-10 w-10 rounded">`;
                    }
                },

                {
                    data: 'name'
                },
                {
                    data: 'sku'
                },
                {
                    data: 'price'
                },
                {
                    data: 'quantity'
                },

                {
                    data: 'status',
                    render: function(d, type, row) {
                        return `
                        <button onclick="toggleStatus(${row.id})"
                            class="${d === 'active' ? 'text-green-600' : 'text-red-600'} font-bold">
                            ${d}
                        </button>
                    `;
                    }
                },

                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {

                        let html = '';

                        const canUpdate = Array.isArray(permissions) && permissions.includes('products_update');
                        const canDelete = Array.isArray(permissions) && permissions.includes('products_delete');

                        if (canUpdate) {
                            html += `
                            <button onclick="editProduct(${row.id})"
                                class="text-blue-600 mr-2">
                                Edit
                            </button>
                        `;
                        }

                        if (canDelete) {
                            html += `
                            <button onclick="deleteProduct(${row.id})"
                                class="text-red-600">
                                Delete
                            </button>
                        `;
                        }

                        return html;
                    }
                }
            ]
        });

        $('#search,#minPrice,#maxPrice,#status').on('keyup change', function() {
            table.ajax.reload();
        });

    });

    /* ================= MODAL ================= */

    function openCreateModal() {
        $('#productForm')[0].reset();
        $('#productId').val('');
        $('#modalTitle').text('Add Product');
        $('#statusToggle').prop('checked', true);
        $('#statusHidden').val('active');

        $('#productModal').removeClass('hidden').addClass('flex');
        openModal();
    }

    function closeModal() {
        const modal = document.getElementById('productModal');

        // hide modal
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // reset form
        $('#productForm')[0].reset();
        $('#productId').val('');
        $('#modalTitle').text('Add Product');

        $('input[type="file"]').val('');
    }

    /* ================= SAVE ================= */

    $('#productForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        const id = $('#productId').val();
        const url = id ? '/products/update' : '/products/store';

        fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {

                if (res.status === 'success') {
                    closeModal();

                    table.ajax.reload();
                    Swal.fire("Success", "Saved successfully", "success");
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            });
    });

    /* ================= EDIT ================= */

    function editProduct(id) {

        fetch('/products/get?id=' + id)
            .then(async res => {
                const text = await res.text();
                return JSON.parse(text);
            })
            .then(res => {

                console.log("EDIT RESPONSE:", res);

                if (res.status !== 'success') {
                    Swal.fire("Error", res.message || "Failed", "error");
                    return;
                }

                const p = res.data;

                $('#productId').val(p.id);
                $('#name').val(p.name);
                $('#price').val(p.price);
                $('#quantity').val(p.quantity);
                $('#sku').val(p.sku);
                $('#description').val(p.description);

                const status = (p.status || '').toLowerCase();

                $('#statusToggle').prop('checked', status === 'active');
                $('#statusHidden').val(status === 'active' ? 'active' : 'inactive');

                $('#modalTitle').text('Edit Product');
                $('#productModal').removeClass('hidden').addClass('flex');
            })
            .catch(err => {
                console.error("EDIT ERROR:", err);
                Swal.fire("Error", "Server error", "error");
            });
    }

    /* ================= DELETE ================= */

    function deleteProduct(id) {

        Swal.fire({
            title: "Delete product?",
            icon: "warning",
            showCancelButton: true
        }).then(r => {

            if (!r.isConfirmed) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('/products/delete', {
                    method: 'POST',
                    body: formData
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
</script>