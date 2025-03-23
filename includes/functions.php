<?php
require_once 'db.php';

// Function to sanitize user input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to get all categories
function getAllCategories() {
    $conn = getDbConnection();
    $sql = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($sql);
    
    $categories = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    $conn->close();
    return $categories;
}

// Function to get category name by ID
function getCategoryName($categoryId) {
    $conn = getDbConnection();
    $sql = "SELECT name FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categoryName = "Uncategorized";
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $categoryName = $row['name'];
    }
    
    $stmt->close();
    $conn->close();
    return $categoryName;
}

// Function to count total expenses
function countTotalExpenses($search = '', $categoryFilter = 0, $startDate = '', $endDate = '') {
    $conn = getDbConnection();
    
    $sql = "SELECT COUNT(*) as total FROM expenses WHERE 1=1";
    $params = [];
    $types = "";
    
    // Add search condition if provided
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    // Add category filter if provided
    if ($categoryFilter > 0) {
        $sql .= " AND category_id = ?";
        $params[] = $categoryFilter;
        $types .= "i";
    }
    
    // Add date range filter if provided
    if (!empty($startDate) && !empty($endDate)) {
        $sql .= " AND expense_date BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= "ss";
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $row['total'];
}

// Function to format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Function to calculate total expenses for a period
function getTotalExpensesForPeriod($period, $value) {
    $conn = getDbConnection();
    $sql = "";
    
    if ($period === 'month') {
        $sql = "SELECT SUM(amount) as total FROM expenses 
                WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?";
        $stmt = $conn->prepare($sql);
        
        $month = $value['month'];
        $year = $value['year'];
        $stmt->bind_param("ii", $month, $year);
    } else if ($period === 'year') {
        $sql = "SELECT SUM(amount) as total FROM expenses 
                WHERE YEAR(expense_date) = ?";
        $stmt = $conn->prepare($sql);
        
        $year = $value;
        $stmt->bind_param("i", $year);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $row['total'] ?: 0;
}
?>