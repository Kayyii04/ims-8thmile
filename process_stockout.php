<?php
session_start();
include('config.php');

if (isset($_POST['save_stockout'])) {
    $trans_id = mysqli_real_escape_string($conn, $_POST['transaction_id']);
    $client_id = (int)$_POST['client_id'];
    $holder_name = mysqli_real_escape_string($conn, $_POST['holder_name']);
    $holder_id = mysqli_real_escape_string($conn, $_POST['holder_id_number']);
    // ADDED: Capture the project name from the form securely
    $project_name = mysqli_real_escape_string($conn, $_POST['project_name']); 
    $date_out = date('Y-m-d H:i:s');
    
    // Arrays from the form
    $product_ids = $_POST['product_id']; 
    $quantities = $_POST['quantity'];

    mysqli_begin_transaction($conn);
    try {
        // Loop through each item in the list
        for ($i = 0; $i < count($product_ids); $i++) {
            $pid = (int)$product_ids[$i];
            $qty = (int)$quantities[$i];

            if ($pid > 0 && $qty > 0) {
                // 1. Insert Record (UPDATED: Added project_name to columns and values)
                $sql = "INSERT INTO stock_out (transaction_id, product_id, ClientID, holder_name, holder_id_number, project_name, quantity, date_out) 
                        VALUES ('$trans_id', '$pid', '$client_id', '$holder_name', '$holder_id', '$project_name', '$qty', '$date_out')";
                if (!mysqli_query($conn, $sql)) {
                    throw new Exception(mysqli_error($conn));
                }

                // 2. Deduct Stock
                $update_sql = "UPDATE products SET quantity = quantity - $qty WHERE productID = $pid";
                if (!mysqli_query($conn, $update_sql)) {
                    throw new Exception(mysqli_error($conn));
                }
            }
        }

        mysqli_commit($conn);
        header("Location: stock_out.php?msg=success");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("Transaction Failed: " . $e->getMessage());
    }
}
?>