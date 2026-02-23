<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$auto_trans_id = "OUT-" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));

// Fetch Data
$query = "SELECT s.*, p.name AS product_name, p.sku, c.client_name 
          FROM stock_out s 
          LEFT JOIN products p ON s.product_id = p.productID 
          LEFT JOIN clients c ON s.ClientID = c.id 
          ORDER BY s.date_out DESC";
$result = mysqli_query($conn, $query);

// Fetch Products for Dropdown
$prod_query = mysqli_query($conn, "SELECT * FROM products WHERE quantity > 0 ORDER BY name ASC");
$products = [];
while ($row = mysqli_fetch_assoc($prod_query)) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>8th Mile IMS | Stock Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        #main-content { transition: margin-left 0.3s; padding: 30px; }
        @media (min-width: 992px) { #main-content { margin-left: 260px; } }
        @media (max-width: 991.98px) { #main-content { margin-left: 0; } }
    </style>
</head>
<body>

    <?php include('sidebar.php'); ?>

    <main id="main-content">
        <button class="btn btn-primary d-lg-none mb-3" onclick="toggleSidebar()"><i class="bi bi-list"></i> Menu</button>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-secondary">Stock Issuance</h3>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#stockOutModal">
                <i class="bi bi-cart-plus me-2"></i>Issue Items
            </button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Trans ID</th>
                                <th>Client / Holder</th>
                                <th>Project Name</th>
                                <th>Item Issued</th>
                                <th>Date</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border fw-bold"><?php echo $row['transaction_id']; ?></span>
                                </td>
                                
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['client_name']); ?></div>
                                    <div class="small text-muted"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($row['holder_name']); ?></div>
                                </td>

                                <td>
                                    <div class="text-secondary fw-semibold"><?php echo htmlspecialchars($row['project_name'] ?? 'N/A'); ?></div>
                                </td>

                                <td>
                                    <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                    <small class="text-muted">SKU: <?php echo htmlspecialchars($row['sku']); ?></small>
                                </td>

                                <td class="text-muted small">
                                    <?php echo date('M d, Y', strtotime($row['date_out'])); ?>
                                </td>

                                <td class="text-center fw-bold fs-5"><?php echo $row['quantity']; ?></td>

                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary me-1" onclick="printSlip('<?php echo $row['transaction_id']; ?>')" title="Print Slip">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <a href="delete_stockout.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Return stock and delete record?');" title="Delete">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="stockOutModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Issue Items</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form action="process_stockout.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-4 p-3 bg-light rounded">
                            <div class="col-md-4">
                                <label class="small fw-bold text-uppercase text-muted">Transaction ID</label>
                                <input type="text" name="transaction_id" class="form-control fw-bold" value="<?php echo $auto_trans_id; ?>" readonly>
                            </div>
                            <div class="col-md-8">
                                <label class="small fw-bold text-uppercase text-muted">Client / Site</label>
                                <div class="input-group">
                                    <select name="client_id" class="form-select" required>
                                        <option value="" disabled selected hidden>Select Client </option>
                                        <?php 
                                        $clients = mysqli_query($conn, "SELECT * FROM clients");
                                        while($c = mysqli_fetch_assoc($clients)) {
                                            echo "<option value='{$c['id']}'>{$c['client_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <button class="btn btn-light border btn-search" type="button" title="Search Clients"><i class="bi bi-search"></i></button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-uppercase text-muted">Receiver Name</label>
                                <input type="text" name="holder_name" class="form-control" placeholder="Full Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-uppercase text-muted">ID Number</label>
                                <input type="text" name="holder_id_number" class="form-control" placeholder="ID No." required>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold text-uppercase text-muted">Project Name</label>
                                <input type="text" name="project_name" class="form-control" placeholder="e.g. Building A Renovation" required>
                            </div>
                        </div>

                        <label class="fw-bold mb-2">Items to Issue</label>
                        <div id="items-container">
                            <div class="row g-2 mb-2 item-row">
                                <div class="col-8">
                                    <div class="input-group">
                                        <select name="product_id[]" class="form-select" required>
                                            <option value="" disabled selected hidden>Select Product</option>
                                            <?php foreach($products as $p): ?>
                                                <option value="<?php echo $p['productID']; ?>">
                                                    <?php echo htmlspecialchars($p['name']); ?> (Stock: <?php echo $p['quantity']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-light border btn-search" type="button" title="Search Products"><i class="bi bi-search"></i></button>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <input type="number" name="quantity[]" class="form-control" placeholder="Qty" min="1" value="1" required>
                                </div>
                                <div class="col-1 text-end">
                                    <button type="button" class="btn btn-light text-danger border" disabled><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addItemRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Item
                        </button>
                    </div>

                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_stockout" class="btn btn-primary px-4">Confirm Issuance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dynamicSearchModal" tabindex="-1" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content shadow">
                <div class="modal-header py-2 bg-light">
                    <h6 class="modal-title fw-bold text-secondary mb-0"><i class="bi bi-search me-2"></i>Search & Select</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <input type="text" id="dynamicSearchInput" class="form-control mb-2" placeholder="Type to filter...">
                    <div class="list-group" id="dynamicSearchResults" style="max-height: 250px; overflow-y: auto;">
                        </div>
                </div>
            </div>
        </div>
    </div>

    <iframe id="print_frame" style="display:none;"></iframe>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Print Function using Transaction ID
        function printSlip(transId) {
            var iframe = document.getElementById('print_frame');
            iframe.src = 'print_slip.php?transaction_id=' + transId;
            iframe.onload = function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            };
        }

        // Dynamic Rows for Modal
        function addItemRow() {
            const container = document.getElementById('items-container');
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 item-row animate-slide-in';
            row.innerHTML = `
                <div class="col-8">
                    <div class="input-group">
                        <select name="product_id[]" class="form-select" required>
                            <option value="">Select Product...</option>
                            <?php foreach($products as $p): ?>
                            <option value="<?php echo $p['productID']; ?>">
                                <?php echo addslashes($p['name']); ?> (Stock: <?php echo $p['quantity']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-light border btn-search" type="button" title="Search Products"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-3">
                    <input type="number" name="quantity[]" class="form-control" placeholder="Qty" min="1" value="1" required>
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-outline-danger" onclick="this.closest('.item-row').remove()"><i class="bi bi-trash"></i></button>
                </div>
            `;
            container.appendChild(row);
        }

        // Search Functionality Logic
        document.addEventListener('DOMContentLoaded', function() {
            let activeSelectElement = null;
            const searchModalElement = document.getElementById('dynamicSearchModal');
            let searchModal;
            
            // Initialize Bootstrap Modal if available
            if (typeof bootstrap !== 'undefined') {
                searchModal = new bootstrap.Modal(searchModalElement);
            }

            const searchInput = document.getElementById('dynamicSearchInput');
            const searchResults = document.getElementById('dynamicSearchResults');

            // Event delegation to catch clicks on dynamically generated search buttons
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-search');
                if (btn) {
                    activeSelectElement = btn.previousElementSibling;
                    if (activeSelectElement && activeSelectElement.tagName === 'SELECT') {
                        openSearch(activeSelectElement);
                    }
                }
            });

            function openSearch(selectElement) {
                searchInput.value = ''; // Clear previous input
                renderList(selectElement, ''); // Render all options initially
                searchModal.show();
            }

            // Focus on the input field when the modal finishes opening
            searchModalElement.addEventListener('shown.bs.modal', function () {
                searchInput.focus();
            });

            // Filter results as the user types
            searchInput.addEventListener('input', function() {
                if (activeSelectElement) {
                    renderList(activeSelectElement, this.value);
                }
            });

            function renderList(selectElement, filterText) {
                searchResults.innerHTML = '';
                const options = Array.from(selectElement.options);
                const query = filterText.toLowerCase().trim();
                let matchCount = 0;

                options.forEach(option => {
                    // Skip the empty default placeholder
                    if (option.value === "") return;

                    const text = option.text;
                    if (text.toLowerCase().includes(query)) {
                        matchCount++;
                        const itemBtn = document.createElement('button');
                        itemBtn.type = 'button';
                        
                        // Using d-block and padding to prevent text truncation
                        itemBtn.className = 'list-group-item list-group-item-action border-0 border-bottom px-3 py-2'; 
                        
                        itemBtn.textContent = text;
                        
                        // Handle selection
                        itemBtn.onclick = function() {
                            selectElement.value = option.value; // Update the hidden dropdown
                            searchModal.hide(); // Close modal
                        };
                        searchResults.appendChild(itemBtn);
                    }
                });

                if (matchCount === 0) {
                    searchResults.innerHTML = '<div class="p-3 text-center text-muted small">No matches found.</div>';
                }
            }
        });
    </script>
</body>
</html>