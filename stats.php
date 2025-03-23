<?php
require_once 'includes/functions.php';

// Get month and year for filtering
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Get total expenses for the current month and year
$monthlyTotal = getTotalExpensesForPeriod('month', ['month' => $currentMonth, 'year' => $currentYear]);
$yearlyTotal = getTotalExpensesForPeriod('year', $currentYear);

// Get expenses by category for the current month
$conn = getDbConnection();
$sql = "SELECT c.name as category_name, SUM(e.amount) as total 
        FROM expenses e 
        LEFT JOIN categories c ON e.category_id = c.id 
        WHERE MONTH(e.expense_date) = ? AND YEAR(e.expense_date) = ? 
        GROUP BY e.category_id 
        ORDER BY total DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $currentMonth, $currentYear);
$stmt->execute();
$result = $stmt->get_result();

$categoryExpenses = [];
while ($row = $result->fetch_assoc()) {
    $categoryExpenses[] = $row;
}
$stmt->close();

// Get expenses by date for the current month (for chart)
$sql = "SELECT expense_date, SUM(amount) as daily_total 
        FROM expenses 
        WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ? 
        GROUP BY expense_date 
        ORDER BY expense_date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $currentMonth, $currentYear);
$stmt->execute();
$result = $stmt->get_result();

$dailyExpenses = [];
while ($row = $result->fetch_assoc()) {
    $dailyExpenses[] = $row;
}
$stmt->close();
$conn->close();

// Prepare chart data
$dates = [];
$amounts = [];
foreach ($dailyExpenses as $daily) {
    $dates[] = date('d', strtotime($daily['expense_date']));
    $amounts[] = $daily['daily_total'];
}

// Month names for the dropdown
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Statistics - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container py-4">
        <header class="pb-3 mb-4 border-bottom">
            <h1 class="display-5 fw-bold text-primary">Expense Tracker</h1>
            <p class="lead">Expense Statistics</p>
        </header>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Expense Statistics
                        </h5>
                        <div>
                            <a href="index.php" class="btn btn-light btn-sm">
                                <i class="fas fa-list me-1"></i>Back to Expenses
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Period Selector -->
                        <form action="stats.php" method="get" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="month" class="form-label">Month</label>
                                <select class="form-select" id="month" name="month">
                                    <?php foreach ($months as $num => $name): ?>
                                        <option value="<?php echo $num; ?>" <?php echo ($currentMonth == $num) ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="year" class="form-label">Year</label>
                                <select class="form-select" id="year" name="year">
                                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($currentYear == $y) ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-1"></i>Apply Filter
                                </button>
                            </div>
                        </form>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Monthly Total (<?php echo $months[$currentMonth] . ' ' . $currentYear; ?>)</h5>
                                        <h2 class="display-6"><?php echo formatCurrency($monthlyTotal); ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Yearly Total (<?php echo $currentYear; ?>)</h5>
                                        <h2 class="display-6"><?php echo formatCurrency($yearlyTotal); ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Daily Expenses</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($dailyExpenses)): ?>
                                            <canvas id="dailyExpensesChart"></canvas>
                                        <?php else: ?>
                                            <p class="text-center text-muted py-4">No data available for this period</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Expenses by Category</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($categoryExpenses)): ?>
                                            <canvas id="categoryExpensesChart"></canvas>
                                        <?php else: ?>
                                            <p class="text-center text-muted py-4">No data available for this period</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Breakdown Table -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Category Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($categoryExpenses)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Amount</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($categoryExpenses as $category): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($category['category_name'] ?? 'Uncategorized'); ?></td>
                                                        <td><?php echo formatCurrency($category['total']); ?></td>
                                                        <td>
                                                            <?php 
                                                                $percentage = ($monthlyTotal > 0) ? ($category['total'] / $monthlyTotal) * 100 : 0;
                                                                echo number_format($percentage, 1) . '%';
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-primary">
                                                    <th>Total</th>
                                                    <th><?php echo formatCurrency($monthlyTotal); ?></th>
                                                    <th>100%</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center text-muted py-4">No data available for this period</p>
                                <?php endif; ?>
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
    <script>
        // Daily Expenses Chart
        <?php if (!empty($dailyExpenses)): ?>
        const dailyCtx = document.getElementById('dailyExpensesChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Daily Expenses',
                    data: <?php echo json_encode($amounts); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Category Expenses Chart
        <?php if (!empty($categoryExpenses)): ?>
        const categoryLabels = [];
        const categoryData = [];
        const categoryColors = [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(199, 199, 199, 0.7)',
            'rgba(83, 102, 255, 0.7)',
            'rgba(40, 159, 64, 0.7)',
            'rgba(210, 199, 199, 0.7)'
        ];

        <?php foreach ($categoryExpenses as $index => $category): ?>
            categoryLabels.push('<?php echo addslashes($category['category_name'] ?? 'Uncategorized'); ?>');
            categoryData.push(<?php echo $category['total']; ?>);
        <?php endforeach; ?>

        const categoryCtx = document.getElementById('categoryExpensesChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: categoryColors.slice(0, categoryData.length),
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw;
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: $${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>