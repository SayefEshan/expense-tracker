<?php
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $amount = floatval($_POST['amount']);
    $categoryId = (int)$_POST['category_id'];
    $expenseDate = sanitizeInput($_POST['expense_date']);
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Expense name is required";
    }
    
    if ($amount <= 0) {
        $errors[] = "Amount must be greater than zero";
    }
    
    if (empty($expenseDate)) {
        $errors[] = "Date is required";
    }
    
    // If validation passes, save to database
    if (empty($errors)) {
        $conn = getDbConnection();
        
        $sql = "INSERT INTO expenses (name, description, amount, category_id, expense_date) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        // Use NULL for category_id if it's 0
        $categoryParam = ($categoryId > 0) ? $categoryId : NULL;
        
        $stmt->bind_param("ssdis", $name, $description, $amount, $categoryParam, $expenseDate);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            
            // Redirect to the main page with a success message
            header("Location: index.php?success=1");
            exit();
        } else {
            $errors[] = "Error: " . $stmt->error;
            $stmt->close();
            $conn->close();
        }
    }
}

// If there are errors, redirect back with error messages
if (!empty($errors)) {
    $errorStr = urlencode(implode(", ", $errors));
    header("Location: index.php?error=" . $errorStr);
    exit();
}

// Redirect to the main page if accessed directly
header("Location: index.php");
exit();
?>