<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

// Get form data
$id = intval($_POST['id']);
$title = $_POST['title'];
$company = $_POST['company'];
$location = $_POST['location'];
$salary = $_POST['salary'];
$description = $_POST['description'];
$requirements = $_POST['requirements'];
$qualification = $_POST['qualification'];

try {
    // Update query using PDO
    $stmt = $conn->prepare("UPDATE jobs SET 
                            title = ?, 
                            company = ?, 
                            location = ?, 
                            salary = ?, 
                            description = ?, 
                            requirements = ?, 
                            qualification = ?
                        WHERE id = ?");
    
    $result = $stmt->execute([
        $title, 
        $company, 
        $location, 
        $salary, 
        $description, 
        $requirements, 
        $qualification, 
        $id
    ]);

    if ($result) {
        header("Location: dashboard.php?updated=success");
        exit;
    } else {
        throw new Exception("Failed to update job");
    }
} catch (Exception $e) {
    // Store error in session and redirect
    $_SESSION['error_message'] = "Error updating job: " . $e->getMessage();
    header("Location: edit_job.php?id=" . $id);
    exit;
}
?>
