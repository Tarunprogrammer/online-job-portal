<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_now'])) {
    $user_id = $_SESSION['user_id'];
    $job_id = $_POST['job_id'];

    // Check if already applied
    $check = $conn->prepare("SELECT * FROM applicants WHERE user_id = ? AND job_id = ?");
    $check->execute([$user_id, $job_id]);

    if ($check->rowCount() === 0) {
        $stmt = $conn->prepare("INSERT INTO applicants (user_id, job_id, applied_date) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $job_id]);
    }

    // Redirect to confirmation
    header("Location: confirm.php");
    exit();
} else {
    echo "<script>alert('Invalid Request'); window.location.href='dashboard.php';</script>";
    exit();
}
?>
