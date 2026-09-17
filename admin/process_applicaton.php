<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "online_job_portal";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action'];
$applicant_id = $_POST['applicant_id'];

if ($action === 'approve') {
    $user_id = $_POST['user_id'];

    // 1. Update applicant status to approved
    $updateProfile = $conn->query("UPDATE profile SET jobs = 'approved' WHERE id = $applicant_id");

    // 2. Update jobs table status to approved (assuming 1 job per applicant for now)
    $updateJob = $conn->query("UPDATE jobs SET status = 'approved' WHERE id IN (
        SELECT job_id FROM applicants WHERE user_id = $user_id
    )");

    header("Location: applicants.php?msg=Applicant approved and job status updated.");
    exit;
}

if ($action === 'reject') {
    // 1. Update applicant status to rejected
    $updateProfile = $conn->query("UPDATE profile SET jobs = 'rejected' WHERE id = $applicant_id");

    // 2. Update jobs table status to rejected
    $updateJob = $conn->query("UPDATE jobs SET status = 'rejected' WHERE id IN (
        SELECT job_id FROM applicants WHERE user_id = (
            SELECT user_id FROM profile WHERE id = $applicant_id
        )
    )");

    header("Location: applicants.php?msg=Applicant rejected and job status updated.");
    exit;
}

$conn->close();
?>
