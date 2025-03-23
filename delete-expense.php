<?php
require_once 'includes/functions.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $conn = getDbConnection();
    $sql = "DELETE FROM expenses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: index.php?success=3");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: index.php?error=Failed to delete expense");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>