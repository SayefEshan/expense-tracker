# Expense Tracker Application

## Project Overview

This Expense Tracker is a web-based application built with HTML, CSS, JavaScript, and PHP with MySQL for database management. It allows users to track, manage, and visualize their expenses efficiently.

## Features

1. **Expense Management**
   - Add new expenses with details (name, description, amount, category, date)
   - View all recorded expenses in a paginated table
   - Update existing expense information
   - Delete unwanted expenses

2. **Search & Filter**
   - Search expenses by name or description
   - Filter expenses by category
   - Filter expenses by specific date range

3. **Statistical Analysis**
   - View total expenses for a month or year
   - Visualize spending patterns with interactive charts
   - Analyze expenses by category with percentage breakdown

4. **User Interface**
   - Responsive design that works on desktop and mobile devices
   - Intuitive user interface with Bootstrap styling
   - Clear visual presentation of data

## Technologies Used

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP (Raw PHP without frameworks)
- **Database**: MySQL
- **Charts**: Chart.js for data visualization

## Installation and Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Installation Steps

1. **Clone or download the repository**
   ```
   git clone https://github.com/yourusername/expense-tracker.git
   ```
   or download and extract the ZIP file

2. **Set up the database**
   - Create a new MySQL database named `expense_tracker`
   - Import the database structure from `database.sql`
   ```
   mysql -u username -p expense_tracker < database.sql
   ```

3. **Configure database connection**
   - Open `includes/config.php`
   - Update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'expense_tracker');
   ```

4. **Deploy to web server**
   - Move all files to your web server's document root or a subdirectory
   - Make sure the web server has read/write permissions to the project directory

5. **Access the application**
   - Open your web browser and navigate to the project URL
   - For local development: `http://localhost/expense-tracker/`

## Project Structure

```
expense-tracker/
├── css/
│   └── style.css
├── js/
│   └── script.js
├── includes/
│   ├── config.php
│   ├── db.php
│   └── functions.php
├── index.php
├── add-expense.php
├── update-expense.php
├── delete-expense.php
├── view-expense.php
├── stats.php
├── database.sql
└── README.md
```

## Usage Guide

### Adding Expenses
1. Fill out the expense form at the top of the main page
2. Enter required details (name, amount, date)
3. Add optional information (description, category)
4. Click "Save Expense"

### Managing Expenses
- View all expenses in the table on the main page
- Click the eye icon to view complete expense details
- Click the edit icon to modify expense information
- Click the trash icon to delete an expense

### Filtering and Searching
- Use the search box to find expenses by name or description
- Select a category from the dropdown to filter by category
- Use the date range pickers to filter expenses by date

### Viewing Statistics
1. Click "View Statistics" button on the main page
2. Select a month and year to view specific period statistics
3. Analyze the charts and tables showing expense distribution

## Customization

- **Categories**: Add or modify expense categories in the database
- **Pagination**: Change the number of items per page in `includes/config.php`
- **Styling**: Modify the CSS in `css/style.css` to change the appearance

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [Bootstrap](https://getbootstrap.com/) - Frontend framework
- [Chart.js](https://www.chartjs.org/) - JavaScript charting library
- [Font Awesome](https://fontawesome.com/) - Icons

## Author

Developed by SayefEshan  
Last Updated: 2023-03-23