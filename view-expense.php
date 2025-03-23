<?php
require_once 'includes/functions.php';

// Initialize variables
$expense = null;
$errors = [];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get expense details with category name
    $conn = getDbConnection();
    $sql = "SELECT e.*, c.name as category_name 
            FROM expenses e 
            LEFT JOIN categories c ON e.category_id = c.id 
            WHERE e.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $expense = $result->fetch_assoc();
    } else {
        $errors[] = "Expense not found";
    }
    
    $stmt->close();
    $conn->close();
} else {
    $errors[] = "Invalid request";
}

// If no expense is found, redirect to the main page
if ($expense === null) {
    header("Location: index.php?error=" . urlencode(implode(", ", $errors)));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Expense - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container py-4">
        <header class="pb-3 mb-4 border-bottom">
            <h1 class="display-5 fw-bold text-primary">Expense Tracker</h1>
            <p class="lead">View Expense Details</p>
        </header>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-eye me-2"></i>Expense Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h4 class="text-primary"><?php echo htmlspecialchars($expense['name']); ?></h4>
                                <p class="text-muted mb-0">
                                    <strong>Date:</strong> <?php echo date('F d, Y', strtotime($expense['expense_date'])); ?>
                                </p>
                                <p class="text-muted mb-0">
                                    <strong>Category:</strong> <?php echo htmlspecialchars($expense['category_name'] ?? 'Uncategorized'); ?>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h3 class="text-success"><?php echo formatCurrency($expense['amount']); ?></h3>
                                <p class="text-muted mb-0">
                                    <small>Added on <?php echo date('M d, Y', strtotime($expense['created_at'])); ?></small>
                                </p>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Description</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($expense['description'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($expense['description'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted">No description provided</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                            <div>
                                <a href="update-expense.php?id=<?php echo $expense['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <a href="delete-expense.php?id=<?php echo $expense['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this expense?')">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="pt-3 mt-4 text-muted border-top text-center">
            &copy; <?php echo date('Y'); ?> Expense Tracker | Developed by SayefEshan
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>