<?php
session_start();
include('config.php');
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$cat_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : "";
if (empty($cat_id)) { header("Location: categories.php"); exit(); }
$cat_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM categories WHERE category_id = '$cat_id'"));
$category_name = $cat_info['name'] ?? "Unknown Category";
$result = mysqli_query($conn, "SELECT * FROM products WHERE category_id = '$cat_id' ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>8th Mile IMS | <?php echo htmlspecialchars($category_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* ADDED: Responsive Main Content adapted to Sidebar */
        :root {
            --sidebar-width: 280px;
        }
        
        #main-content { 
            transition: margin-left 0.3s; 
            width: 100%;
            overflow-x: hidden;
            position: relative; /* ADDED: To help contain the watermark */
        }
        
        @media (min-width: 992px) { 
            #main-content { 
                margin-left: var(--sidebar-width); 
                width: calc(100% - var(--sidebar-width)); 
            } 
        }
        
        @media (max-width: 991.98px) { 
            #main-content { 
                margin-left: 0; 
            } 
        }

        /* ADDED: Watermark hidden by default on the screen */
        .watermark-logo {
            display: none; 
        }

        @media print {
            body { background-color: white; }
            .d-print-none { display: none !important; }
            #main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            
            /* UPDATED: Added transparent backgrounds so they don't block the watermark */
            .card { border: none !important; box-shadow: none !important; background: transparent !important; }
            table { background: transparent !important; }
            
            .badge { border: 1px solid #000; color: #000 !important; }

            /* ADDED: Make the watermark visible and positioned during print */
            .watermark-logo {
                display: block !important;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 400px;
                opacity: 0.1;
                z-index: -1;
                pointer-events: none;
            }
        }
    </style>
</head>
<body>
    <div class="d-print-none">
        <?php include('sidebar.php'); ?>
    </div>
    
    <main id="main-content" class="p-3 p-md-4">
        <img src="8thmile_logo.png" class="watermark-logo" alt="Watermark Background">

        <button class="btn btn-primary d-lg-none mb-3 animate-fade-up d-print-none" onclick="toggleSidebar()"><i class="bi bi-list"></i> Menu</button>
        <div class="d-flex justify-content-between align-items-center mb-4 animate-slide-in">
            <div>
                <a href="categories.php" class="text-decoration-none text-muted small d-print-none"><i class="bi bi-arrow-left"></i> Back to Categories</a>
                <h3 class="fw-bold text-secondary mt-1"><?php echo htmlspecialchars($category_name); ?></h3>
            </div>
            <div>
                <span class="badge bg-primary fs-6"><?php echo mysqli_num_rows($result); ?> Items</span>
                <button onclick="window.print()" class="btn btn-outline-primary ms-2 d-print-none"><i class="bi bi-printer"></i> Print</button>
            </div>
        </div>
        <div class="card border-0 shadow-sm animate-fade-up delay-1">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Code</th>
                            <th>Item Name</th>
                            <th>Supplier</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        while($row = mysqli_fetch_assoc($result)): 
                            $item_total = $row['quantity'] * $row['unit_price'];
                            $grand_total += $item_total;
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($row['sku']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td class="fw-bold">₱<?php echo number_format($row['unit_price'], 2); ?></td>
                            <td class="fw-bold text-success">₱<?php echo number_format($item_total, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end pe-4">Total Inventory Value:</td>
                            <td class="text-success fs-6">₱<?php echo number_format($grand_total, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>