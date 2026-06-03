<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 8px;
        }

        table th {
            background: #f2f2f2;
        }
    </style>
</head>

<body>

    <h2>Product Report</h2>

    <table>

        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

            <?php
            if (!isset($products) || !is_array($products)) {
                $products = [];
            }
            foreach ($products as $product): ?>

                <tr>

                    <td><?= $product['id'] ?></td>

                    <td><?= htmlspecialchars($product['name']) ?></td>

                    <td><?= htmlspecialchars($product['category_name'] ?? 'No Category') ?></td>

                    <td><?= htmlspecialchars($product['sku']) ?></td>

                    <td><?= $product['price'] ?></td>

                    <td><?= $product['quantity'] ?></td>

                    <td><?= $product['status'] ?></td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</body>

</html>