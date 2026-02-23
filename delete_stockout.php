<?php
session_start();
include('config.php');

// 1. Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 2. Fetch details to restore inventory
    $fetch = mysqli_query($conn, "SELECT product_id, quantity FROM stock_out WHERE id = $id LIMIT 1");
    
    if ($data = mysqli_fetch_assoc($fetch)) {
        $prod_id = $data['product_id'];
        $qty = $data['quantity'];

        mysqli_begin_transaction($conn);
        try {
            // 3. Restore stock (Using productID per your database rename)
            $update_query = "UPDATE products SET quantity = quantity + $qty WHERE productID = $prod_id";
            if (!mysqli_query($conn, $update_query)) {
                throw new Exception(mysqli_error($conn));
            }

            // 4. Delete the issuance record
            $delete_query = "DELETE FROM stock_out WHERE id = $id LIMIT 1";
            if (!mysqli_query($conn, $delete_query)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);
            header("Location: stock_out.php?msg=deleted");
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conn);
            // This will show a message if the SQL itself fails
            die("Database Error: " . $e->getMessage());
        }
    } else {
        header("Location: stock_out.php?msg=error");
        exit();
    }
} else {
    header("Location: stock_out.php");
    exit();
}
?>