<?php
session_start();
include '../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $job_id = $_GET['id'];

    try {
        // Begin transaction
        $conn->beginTransaction();

        // First delete related applications
        $stmt1 = $conn->prepare("DELETE FROM applicants WHERE job_id = ?");
        $stmt1->execute([$job_id]);

        // Then delete the job
        $stmt2 = $conn->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt2->execute([$job_id]);

        // Commit transaction
        $conn->commit();

        // Redirect to dashboard with success message
        header("Location: dashboard.php?deleted=success");
        exit;
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        
        // Store error in session and redirect
        $_SESSION['error_message'] = "Error deleting job: " . $e->getMessage();
        header("Location: dashboard.php?deleted=error");
        exit;
    }
} else {
    // Invalid request
    header("Location: dashboard.php?deleted=invalid");
    exit;
}
?>
