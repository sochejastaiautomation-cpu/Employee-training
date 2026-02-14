<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Product.php';

$product_obj = new Product($conn);
$products = $product_obj->getAll();

$message = '';
$error = '';

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
            <!-- Add Product Form - Always Visible -->
            <div class="section form-section" style="display: block;">
                <h2>➕ Add Product</h2>

                <form method="POST" class="form" id="addProductForm">
                    <input type="hidden" name="action" value="create">

                    <div class="form-group">
                        <label for="product_name">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" placeholder="Enter product name" required>
                    </div>

                    <div class="form-group">
                        <label for="product_type">Category</label>
                        <input type="text" id="product_type" name="product_type" placeholder="e.g., Bag, Cosmetics, Accessories">
                    </div>

                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" id="brand" name="brand" placeholder="e.g., Nike, Adidas">
                    </div>

                    <div class="form-group">
                        <label for="material">Material</label>
                        <input type="text" id="material" name="material" placeholder="e.g., Cotton, Leather, Plastic">
                    </div>

                    <div class="form-group">
                        <label for="price">Price (Rs.) *</label>
                        <input type="number" id="price" name="price" step="0.01" placeholder="Enter price" required>
                    </div>

                    <div class="form-group">
                        <label for="colors">Available Colors</label>
                        <input type="text" id="colors" name="colors" placeholder="e.g., Red, Blue, Black (comma separated)">
                    </div>

                    <div class="form-group">
                        <label for="features_list">Features (one per line)</label>
                        <textarea id="features_list" name="features_list" rows="3" placeholder="Waterproof&#10;Lightweight&#10;Durable"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" style="flex:1;">
                            Add Product
                        </button>
                        <button type="reset" class="btn btn-secondary" style="flex:1;">
                            Clear
                        </button>
                    </div>
                </form>
            </div>

            <!-- Products Section -->
            <div class="section products-section">
                <div class="products-header">
                    <h2>📋 All Products (<span id="productCount"><?php echo count($products); ?></span>)</h2>
                    <div class="search-sort-controls">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Search products by name or category..." />
                            <button class="btn btn-primary btn-sm" onclick="searchProducts()">🔍 Search</button>
                        </div>
                        <select id="sortSelect" class="sort-select" onchange="sortProducts()">
                            <option value="">Sort By...</option>
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                            <option value="price-asc">Price (Low to High)</option>
                            <option value="price-desc">Price (High to Low)</option>
                        </select>
                    </div>
                </div>

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
                                    <button class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($prod)); ?>)">Edit</button>
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

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Edit Product</h2>
                <button class="close-btn" onclick="closeModal('editModal')">×</button>
            </div>
            <form method="POST" class="form" id="editProductForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" id="editProductId">

                <div class="form-group">
                    <label for="edit_product_name">Product Name *</label>
                    <input type="text" id="edit_product_name" name="product_name" required>
                </div>

                <div class="form-group">
                    <label for="edit_product_type">Category</label>
                    <input type="text" id="edit_product_type" name="product_type">
                </div>

                <div class="form-group">
                    <label for="edit_brand">Brand</label>
                    <input type="text" id="edit_brand" name="brand">
                </div>

                <div class="form-group">
                    <label for="edit_material">Material</label>
                    <input type="text" id="edit_material" name="material">
                </div>

                <div class="form-group">
                    <label for="edit_price">Price (Rs.) *</label>
                    <input type="number" id="edit_price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="edit_colors">Available Colors</label>
                    <input type="text" id="edit_colors" name="colors">
                </div>

                <div class="form-group">
                    <label for="edit_features_list">Features (one per line)</label>
                    <textarea id="edit_features_list" name="features_list" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="flex:1;">
                        Update Product
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')" style="flex:1;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/algorithms.js"></script>
    <script>
        // Store original products data
        let allProducts = <?php echo json_encode($products); ?>;

        // Search products using KMP
        function searchProducts() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            
            if (!query) {
                displayProducts(allProducts);
                return;
            }

            const filtered = allProducts.filter(product => {
                const name = (product.product_name || '').toLowerCase();
                const category = (product.product_type || '').toLowerCase();
                const brand = (product.brand || '').toLowerCase();
                
                return kmpSearch(name, query) || 
                       kmpSearch(category, query) || 
                       kmpSearch(brand, query);
            });

            displayProducts(filtered);
        }

        // Sort products using Merge Sort
        function sortProducts() {
            const sortValue = document.getElementById('sortSelect').value;
            
            if (!sortValue) {
                displayProducts(allProducts);
                return;
            }

            let compareFn;

            if (sortValue === 'name-asc') {
                compareFn = (a, b) => (a.product_name || '').localeCompare(b.product_name || '');
            } else if (sortValue === 'name-desc') {
                compareFn = (a, b) => (b.product_name || '').localeCompare(a.product_name || '');
            } else if (sortValue === 'price-asc') {
                compareFn = (a, b) => parseFloat(a.price || 0) - parseFloat(b.price || 0);
            } else if (sortValue === 'price-desc') {
                compareFn = (a, b) => parseFloat(b.price || 0) - parseFloat(a.price || 0);
            }

            const sorted = mergeSort([...allProducts], compareFn);
            displayProducts(sorted);
        }

        // Display products on page
        function displayProducts(products) {
            const container = document.querySelector('.product-grid');
            document.getElementById('productCount').textContent = products.length;

            if (products.length === 0) {
                container.innerHTML = '<div class="no-data-message" style="grid-column: 1/-1;"><p>No products found.</p></div>';
                return;
            }

            container.innerHTML = products.map(prod => `
                <div class="product-card">
                    <div class="product-card-title">${escapeHtml(prod.product_name)}</div>
                    <div class="product-card-meta">
                        ${prod.brand ? `<strong>${escapeHtml(prod.brand)}</strong><br>` : ''}
                        ${prod.product_type ? `Category: ${escapeHtml(prod.product_type)}<br>` : ''}
                        ${prod.material ? `Material: ${escapeHtml(prod.material)}` : ''}
                    </div>
                    <div class="product-card-price">Rs. ${parseFloat(prod.price || 0).toFixed(2)}</div>
                    <div class="product-card-actions">
                        <button class="btn btn-info btn-sm" onclick='showDetails(${JSON.stringify(prod)})'>View</button>
                        <button class="btn btn-warning btn-sm" onclick='openEditModal(${JSON.stringify(prod)})'>Edit</button>
                        <a href="?delete=${prod.product_id}" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
                    </div>
                </div>
            `).join('');
        }

        // Allow Enter key in search
        document.getElementById('searchInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });

        function openEditModal(product) {
            document.getElementById('editProductId').value = product.product_id;
            document.getElementById('edit_product_name').value = product.product_name || '';
            document.getElementById('edit_product_type').value = product.product_type || '';
            document.getElementById('edit_brand').value = product.brand || '';
            document.getElementById('edit_material').value = product.material || '';
            document.getElementById('edit_price').value = product.price || '';
            
            // Extract colors from variants JSON
            const variants = JSON.parse(product.variants || '{}');
            document.getElementById('edit_colors').value = (variants.colors && variants.colors.length > 0) ? variants.colors.join(', ') : '';
            
            // Extract features from JSON
            const features = JSON.parse(product.features || '[]');
            document.getElementById('edit_features_list').value = (features.length > 0) ? features.join('\n') : '';
            
            const modal = document.getElementById('editModal');
            modal.classList.add('active');
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
            html += '<button class="btn btn-warning" style="flex:1;" onclick="closeModal(\'detailsModal\'); openEditModal(' + JSON.stringify(product).split("'").join("&#39;") + ')">Edit</button>';
            html += '<a href="?delete=' + product.product_id + '" class="btn btn-danger" style="flex:1;text-align:center;" onclick="return confirm(\'Delete this product?\')">Delete</a>';
            html += '</div>';

            content.innerHTML = html;
            modal.classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        window.onclick = function(event) {
            const detailsModal = document.getElementById('detailsModal');
            const editModal = document.getElementById('editModal');
            if (event.target === detailsModal) {
                detailsModal.classList.remove('active');
            }
            if (event.target === editModal) {
                editModal.classList.remove('active');
            }
        };
    </script>
</body>
</html>
