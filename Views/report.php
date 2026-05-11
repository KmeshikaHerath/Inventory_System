<!-- DEPENDENCIES -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>

<!-- HEADER -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Filter Products</h1>

    <button onclick="openFilterModal()"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        Filter Report
    </button>
</div>

<!-- TABLE -->
<div class="bg-white shadow rounded overflow-x-auto">
    <table id="productsTable" class="w-full text-sm">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-3">ID</th>
                <th class="p-3">Name</th>
                <th class="p-3">Category</th>
                <th class="p-3">SKU</th>
                <th class="p-3">Price</th>
                <th class="p-3">Qty</th>
                <th class="p-3">Status</th>
            </tr>
        </thead>
    </table>
</div>

<!-- FILTER MODAL -->
<div id="filterModal"
    class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50">

    <div class="bg-white p-6 rounded-lg w-96 shadow-lg">

        <h2 class="text-lg font-bold mb-4">Filter Report</h2>

        <form id="filterForm">

            <!-- CATEGORY -->
            <div class="mb-4">

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Category
                </label>

                <select id="category_id"
                    name="category_id"
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400 outline-none">

                    <option value="">All Categories</option>

                    <?php if (!empty($categories)): ?>

                        <?php foreach ($categories as $category): ?>

                            <option value="<?= htmlspecialchars($category['id']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </select>

            </div>

            <!-- SKU -->
            <div class="mb-4">

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    SKU
                </label>

                <input type="text"
                    id="sku"
                    name="sku"
                    placeholder="Enter SKU"
                    class="w-full border p-2 rounded">

            </div>

            <!-- STATUS -->
            <div class="mb-4">

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Status
                </label>

                <select id="status"
                    name="status"
                    class="w-full border p-2 rounded">

                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>

                </select>

            </div>

            <!-- BUTTONS -->
            <div class="flex flex-wrap gap-2 justify-between mt-6">

                <!-- CLOSE -->
                <button onclick="closeFilterModal()"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Close
                </button>


                <button type="submit"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Apply Filter
                </button>

                <button type="button"
                    onclick="downloadCSV()"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    CSV
                </button>

                <button type="button"
                    onclick="downloadPDF()"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    PDF
                </button>

            </div>

        </form>

    </div>
</div>

<script>
    let table;

    $(document).ready(function() {

        /* ================= DATATABLE ================= */

        table = $('#productsTable').DataTable({

            processing: true,
            serverSide: true,

            ajax: {
                url: "/report/filter",
                type: "GET",

                data: function(d) {

                    d.category_id = $('#category_id').val();
                    d.sku = $('#sku').val();
                    d.status = $('#status').val();

                }
            },

            columns: [

                {
                    data: 'id'
                },

                {
                    data: 'name'
                },

                {
                    data: 'category_name',

                    render: function(data) {

                        return data ?
                            data :
                            '<span class="text-gray-400">No Category</span>';

                    }
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

                    render: function(data) {

                        if (data === 'active') {

                            return '<span class="text-green-600 font-semibold">Active</span>';

                        } else if (data === 'inactive') {

                            return '<span class="text-red-600 font-semibold">Inactive</span>';

                        }

                        return data;
                    }
                }

            ],

            order: [
                [0, 'desc']
            ],

            language: {
                emptyTable: "No products found"
            }

        });

        /* ================= AUTO FILTER ================= */

        $('#category_id, #sku, #status').on('keyup change', function() {

            table.ajax.reload();

        });

        /* ================= FORM SUBMIT ================= */

        $('#filterForm').on('submit', function(e) {

            e.preventDefault();

            table.ajax.reload();

            closeFilterModal();

        });

    });

    /* ================= MODAL ================= */

    function openFilterModal() {

        $('#filterModal').removeClass('hidden');

    }

    function closeFilterModal() {

        $('#filterModal').addClass('hidden');

    }

    /* ================= EXPORT CSV ================= */

    function downloadCSV() {

        let category_id = $('#category_id').val();
        let sku = $('#sku').val();
        let status = $('#status').val();

        const url =
            `/report/export/csv?category_id=${encodeURIComponent(category_id)}&sku=${encodeURIComponent(sku)}&status=${encodeURIComponent(status)}`;

        // Create download link
        const link = document.createElement('a');
        link.href = url;
        link.download = 'report.csv';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /* ================= EXPORT PDF ================= */

    function downloadPDF() {

        let category_id = $('#category_id').val();
        let sku = $('#sku').val();
        let status = $('#status').val();

        const url =
            `/report/export/pdf?category_id=${encodeURIComponent(category_id)}&sku=${encodeURIComponent(sku)}&status=${encodeURIComponent(status)}`;

        // Create temporary download button/link
        const link = document.createElement('a');

        link.href = url;
        link.download = 'report.pdf';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>