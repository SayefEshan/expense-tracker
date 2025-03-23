<?php
require_once 'includes/functions.php';

// Initialize variables
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : '';

// Get categories for the dropdown
$categories = getAllCategories();

// Calculate pagination
$totalExpenses = countTotalExpenses($search, $categoryFilter, $startDate, $endDate);
$totalPages = ceil($totalExpenses / ITEMS_PER_PAGE);
$offset = ($currentPage - 1) * ITEMS_PER_PAGE;

// Get expenses for the current page
$conn = getDbConnection();

$sql = "SELECT e.*, c.name as category_name 
        FROM expenses e 
        LEFT JOIN categories c ON e.category_id = c.id 
        WHERE 1=1";

$params = [];
$types = "";

// Add search condition if provided
if (!empty($search)) {
    $sql .= " AND (e.name LIKE ? OR e.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Add category filter if provided
if ($categoryFilter > 0) {
    $sql .= " AND e.category_id = ?";
    $params[] = $categoryFilter;
    $types .= "i";
}

// Add date range filter if provided
if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND e.expense_date BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

$sql .= " ORDER BY e.expense_date DESC LIMIT ? OFFSET ?";
$params[] = ITEMS_PER_PAGE;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$expenses = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container py-4">
        <header class="pb-3 mb-4 border-bottom">
            <h1 class="display-5 fw-bold text-primary">Expense Tracker</h1>
            <p class="lead">Manage and track your expenses easily</p>
        </header>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Add New Expense
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="add-expense.php" method="post">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="name" class="form-label">Expense Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="amount" class="form-label">Amount ($) *</label>
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="expense_date" class="form-label">Date *</label>
                                    <input type="date" class="form-control" id="expense_date" name="expense_date" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category_id">
                                        <option value="0">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="1"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Expense
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Expense List
                        </h5>
                        <div>
                            <a href="stats.php" class="btn btn-light btn-sm">
                                <i class="fas fa-chart-bar me-1"></i>View Statistics
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search & Filter Form -->
                        <form action="index.php" method="get" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Search expense..." value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="0">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($categoryFilter == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="start_date" placeholder="Start Date" value="<?php echo $startDate; ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="end_date" placeholder="End Date" value="<?php echo $endDate; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                            </div>
                        </form>

                        <!-- Expenses Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($expenses)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3">No expenses found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($expenses as $expense): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($expense['name']); ?></td>
                                                <td><?php echo formatCurrency($expense['amount']); ?></td>
                                                <td><?php echo htmlspecialchars($expense['category_name'] ?? 'Uncategorized'); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                                                <td>
                                                    <a href="view-expense.php?id=<?php echo $expense['id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="update-expense.php?id=<?php echo $expense['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete-expense.php?id=<?php echo $expense['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this expense?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
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