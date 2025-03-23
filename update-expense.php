<?php
require_once 'includes/functions.php';

// Get categories for the dropdown
$categories = getAllCategories();

// Initialize variables
$expense = null;
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get expense details
    $conn = getDbConnection();
    $sql = "SELECT * FROM expenses WHERE id = ?";
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
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Update expense
    $id = (int)$_POST['id'];
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $amount = floatval($_POST['amount']);
    $categoryId = (int)$_POST['category_id'];
    $expenseDate = sanitizeInput($_POST['expense_date']);
    
    // Validate input
    if (empty($name)) {
        $errors[] = "Expense name is required";
    }
    
    if ($amount <= 0) {
        $errors[] = "Amount must be greater than zero";
    }
    
    if (empty($expenseDate)) {
        $errors[] = "Date is required";
    }
    
    // If validation passes, update the database
    if (empty($errors)) {
        $conn = getDbConnection();
        
        $sql = "UPDATE expenses SET name = ?, description = ?, amount = ?, 
                category_id = ?, expense_date = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        
        // Use NULL for category_id if it's 0
        $categoryParam = ($categoryId > 0) ? $categoryId : NULL;
        
        $stmt->bind_param("ssdisi", $name, $description, $amount, $categoryParam, $expenseDate, $id);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
        $conn->close();
        
        if ($success) {
            // Redirect to the main page with a success message
            header("Location: index.php?success=2");
            exit();
        }
    }
    
    // If there are errors, keep the posted data as the current expense
    if (!empty($errors)) {
        $expense = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'amount' => $amount,
            'category_id' => $categoryId,
            'expense_date' => $expenseDate
        ];
    }
}

// If no expense is found and we have no form data, redirect to the main page
if ($expense === null && !isset($_POST['id'])) {
    header("Location: index.php?error=Expense not found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Expense - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container py-4">
        <header class="pb-3 mb-4 border-bottom">
            <h1 class="display-5 fw-bold text-primary">Expense Tracker</h1>
            <p class="lead">Update Expense</p>
        </header>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Update Expense
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Expense updated successfully!
                            </div>
                        <?php endif; ?>

                        <form action="update-expense.php" method="post">
                            <input type="hidden" name="id" value="<?php echo $expense['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Expense Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($expense['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount ($) *</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo $expense['amount']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="0">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($expense['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo $expense['expense_date']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($expense['description']); ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Update Expense
                                </button>
                            </div>
                        </form>
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