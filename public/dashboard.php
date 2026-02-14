<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Product.php';

$product_obj = new Product($conn);
$products = $product_obj->getAll();

$message = '';
$error = '';
$edit_mode = false;
$edit_product = null;

// Handle edit via GET first, before calculating show_form
if (isset($_GET['edit'])) {
    $edit_product = $product_obj->getById($_GET['edit']);
    if ($edit_product) {
        $edit_mode = true;
    }
}

$show_form = isset($_GET['show_form']) ? $_GET['show_form'] : ($edit_mode ? true : false);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Convert simple fields to JSON for database storage
    $colors = !empty($_POST['colors']) ? explode(',', $_POST['colors']) : [];
    $colors = array_map('trim', $colors);
    
    $features = !empty($_POST['features_list']) ? explode('\n', $_POST['features_list']) : [];
    $features = array_map('trim', array_filter($features));

    $data = [
        'product_name' => $_POST['product_name'] ?? '',
        'product_type' => $_POST['product_type'] ?? '',
        'brand' => $_POST['brand'] ?? '',
        'material' => $_POST['material'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'delivery' => json_encode(['standard' => true]),
        'variants' => json_encode(['colors' => $colors]),
        'features' => json_encode($features),
        'faqs' => json_encode([])
    ];

    if ($action === 'create') {
        $result = $product_obj->create($data);
        if ($result['success']) {
            $message = "Product created successfully!";
            $_POST = []; // Clear form
            $products = $product_obj->getAll(); // Refresh list
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'update') {
        $id = $_POST['product_id'] ?? 0;
        $result = $product_obj->update($id, $data);
        if ($result['success']) {
            $message = "Product updated successfully!";
            $_POST = []; // Clear form
            $edit_mode = false;
            $products = $product_obj->getAll(); // Refresh list
        } else {
            $error = $result['message'];
        }
    }
}

// Handle delete via GET
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $product_obj->delete($id);
    if ($result['success']) {
        $message = "Product deleted successfully!";
        $products = $product_obj->getAll(); // Refresh list
    } else {
        $error = $result['message'];
    }
}

function extractColors($variants_json) {
    $data = json_decode($variants_json, true);
    if (isset($data['colors']) && is_array($data['colors'])) {
        return implode(', ', $data['colors']);
    }
    return '';
}

function extractFeatures($features_json) {
    $data = json_decode($features_json, true);
    if (is_array($data)) {
        return implode("\n", $data);
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - CRUD Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📦 Products Management</h1>
            <button class="btn btn-primary" onclick="toggleForm()" id="addProductBtn">➕ Add New Product</button>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success">
                ✓ <?php echo htmlspecialchars($message); ?>
                <button onclick="this.parentElement.style.display='none'" style="background:none;border:none;color:inherit;cursor:pointer;font-size:18px;float:right;">×</button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                ✗ <?php echo htmlspecialchars($error); ?>
                <button onclick="this.parentElement.style.display='none'" style="background:none;border:none;color:inherit;cursor:pointer;font-size:18px;float:right;">×</button>
            </div>
        <?php endif; ?>

        <div class="dashboard">
            <!-- Form Section - Toggleable -->
            <div class="section form-section" id="formSection" style="<?php echo $show_form ? '' : 'display: none;'; ?>">
                <h2><?php echo $edit_mode ? '✏️ Edit Product' : '➕ Add Product'; ?></h2>

                <form method="POST" class="form">
                    <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($edit_product['product_id']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="product_name">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($_POST['product_name'] ?? ($edit_product['product_name'] ?? '')); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="product_type">Category</label>
                        <input type="text" id="product_type" name="product_type" placeholder="e.g., Bag, Cosmetics, Accessories" value="<?php echo htmlspecialchars($_POST['product_type'] ?? ($edit_product['product_type'] ?? '')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" placeholder="e.g., Nike, Adidas" value="<?php echo htmlspecialchars($_POST['brand'] ?? ($edit_product['brand'] ?? '')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="material">Material</label>
                        <input type="text" id="material" name="material" placeholder="e.g., Cotton, Leather, Plastic" value="<?php echo htmlspecialchars($_POST['material'] ?? ($edit_product['material'] ?? '')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="price">Price (Rs.) *</label>
                        <input type="number" id="price" name="price" step="0.01" placeholder="Enter price" value="<?php echo htmlspecialchars($_POST['price'] ?? ($edit_product['price'] ?? '')); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="colors">Available Colors</label>
                        <input type="text" id="colors" name="colors" placeholder="e.g., Red, Blue, Black (comma separated)" value="<?php echo htmlspecialchars($_POST['colors'] ?? extractColors($edit_product['variants'] ?? '{}')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="features_list">Features (one per line)</label>
                        <textarea id="features_list" name="features_list" rows="3" placeholder="Waterproof&#10;Lightweight&#10;Durable"><?php echo htmlspecialchars($_POST['features_list'] ?? extractFeatures($edit_product['features'] ?? '[]')); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" style="flex:1;">
                            <?php echo $edit_mode ? 'Update Product' : 'Add Product'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeForm()" style="flex:1;">
                            <?php echo $edit_mode ? 'Cancel Edit' : 'Cancel'; ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Products Section - Main Focus -->
            <div class="section products-section">
                <h2>📋 All Products (<?php echo count($products); ?>)</h2>

                <?php if (empty($products)): ?>
                    <div class="no-data-message">
                        <p>No products yet. Add your first product above!</p>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $prod): ?>
                            <div class="product-card">
                                <div class="product-card-title"><?php echo htmlspecialchars($prod['product_name']); ?></div>
                                <div class="product-card-meta">
                                    <?php if ($prod['brand']): ?>
                                        <strong><?php echo htmlspecialchars($prod['brand']); ?></strong><br>
                                    <?php endif; ?>
                                    <?php if ($prod['product_type']): ?>
                                        Category: <?php echo htmlspecialchars($prod['product_type']); ?><br>
                                    <?php endif; ?>
                                    <?php if ($prod['material']): ?>
                                        Material: <?php echo htmlspecialchars($prod['material']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="product-card-price">Rs. <?php echo htmlspecialchars(number_format($prod['price'] ?? 0, 2)); ?></div>
                                <div class="product-card-actions">
                                    <button class="btn btn-info btn-sm" onclick="showDetails(<?php echo htmlspecialchars(json_encode($prod)); ?>)">View</button>
                                    <a href="?edit=<?php echo $prod['product_id']; ?>&show_form=1" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?delete=<?php echo $prod['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Product Details</h2>
                <button class="close-btn" onclick="closeModal('detailsModal')">×</button>
            </div>
            <div id="detailsContent"></div>
        </div>
    </div>

    <script>
        function toggleForm() {
            const formSection = document.getElementById('formSection');
            const addBtn = document.getElementById('addProductBtn');
            
            if (formSection.style.display === 'none') {
                formSection.style.display = 'block';
                addBtn.textContent = '✖ Close Form';
                addBtn.classList.add('active');
                document.getElementById('product_name').focus();
            } else {
                formSection.style.display = 'none';
                addBtn.textContent = '➕ Add New Product';
                addBtn.classList.remove('active');
            }
        }

        function closeForm() {
            const formSection = document.getElementById('formSection');
            const addBtn = document.getElementById('addProductBtn');
            
            formSection.style.display = 'none';
            addBtn.textContent = '➕ Add New Product';
            addBtn.classList.remove('active');
        }

        function showDetails(product) {
            const modal = document.getElementById('detailsModal');
            const title = document.getElementById('modalTitle');
            const content = document.getElementById('detailsContent');

            title.textContent = product.product_name;

            let html = '<div class="details-grid">';
            html += '<div class="detail-item"><div class="detail-item-label">Category</div><div class="detail-item-value">' + (product.product_type || 'N/A') + '</div></div>';
            html += '<div class="detail-item"><div class="detail-item-label">Brand</div><div class="detail-item-value">' + (product.brand || 'N/A') + '</div></div>';
            html += '<div class="detail-item"><div class="detail-item-label">Material</div><div class="detail-item-value">' + (product.material || 'N/A') + '</div></div>';
            html += '<div class="detail-item"><div class="detail-item-label">Price</div><div class="detail-item-value">Rs. ' + parseFloat(product.price || 0).toFixed(2) + '</div></div>';

            const variants = JSON.parse(product.variants || '{}');
            if (variants.colors && variants.colors.length > 0) {
                html += '<div class="detail-section"><strong>Available Colors:</strong><div class="detail-list"><ul>';
                variants.colors.forEach(color => {
                    html += '<li>' + color + '</li>';
                });
                html += '</ul></div></div>';
            }

            const features = JSON.parse(product.features || '[]');
            if (features.length > 0) {
                html += '<div class="detail-section"><strong>Features:</strong><div class="detail-list"><ul>';
                features.forEach(feature => {
                    html += '<li>' + feature + '</li>';
                });
                html += '</ul></div></div>';
            }

            html += '<div class="detail-section" style="margin-top: 20px; display: flex; gap: 10px;">';
            html += '<a href="?edit=' + product.product_id + '&show_form=1" class="btn btn-warning" style="flex:1;text-align:center;">Edit</a>';
            html += '<a href="?delete=' + product.product_id + '" class="btn btn-danger" style="flex:1;text-align:center;" onclick="return confirm(\'Delete this product?\')">Delete</a>';
            html += '</div>';

            content.innerHTML = html;
            modal.classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        };
    </script>
</body>
</html>
