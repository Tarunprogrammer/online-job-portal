<?php
session_start();
include '../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch jobs
$stmt = $conn->query("SELECT * FROM jobs ORDER BY posted_date DESC");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total jobs
$total_jobs = count($jobs);

// Count total applications
$stmt = $conn->query("SELECT COUNT(*) as total FROM applicants");
$applications = $stmt->fetch(PDO::FETCH_ASSOC);
$total_applications = $applications['total'];

// Count total users
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$users = $stmt->fetch(PDO::FETCH_ASSOC);
$total_users = $users['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Job Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/deep-theme.css">
    <style>
        body {
            padding: 0;
            margin: 0;
            min-height: 100vh;
            background-attachment: fixed;
            overflow-x: hidden;
        }

        .dashboard-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(18, 18, 30, 0.5);
            color: white;
            padding: 20px 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(100, 100, 160, 0.15);
            box-shadow: var(--card-shadow);
        }

        .dashboard-header h1 {
            margin: 0;
            font-family: var(--heading-font);
            font-weight: 700;
            background: linear-gradient(90deg, #fff, #9d4edd, #fff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shine 3s linear infinite;
        }

        .dashboard-actions {
            display: flex;
            gap: 15px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(18, 18, 30, 0.35);
            padding: 25px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            text-align: center;
            transition: all 0.4s ease;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(100, 100, 160, 0.15);
            animation: fadeInUp 0.6s ease forwards;
            color: white;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .stat-number {
            font-size: 3em;
            font-weight: 700;
            margin: 10px 0;
            background: linear-gradient(90deg, #fff, #9d4edd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-title {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .stat-icon {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(18, 18, 30, 0.35);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(12px);
        }

        th {
            background: rgba(40, 40, 80, 0.5);
            color: white;
            padding: 15px;
            text-align: left;
            font-family: var(--heading-font);
            font-weight: 600;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid rgba(100, 100, 160, 0.15);
            color: var(--text-color);
        }

        tr:hover {
            background: rgba(40, 40, 80, 0.2);
        }

        .actions a {
            display: inline-flex;
            align-items: center;
            margin-right: 10px;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(40, 40, 80, 0.4);
            transition: all 0.3s ease;
            font-size: 0.9em;
        }

        .actions a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .edit-btn {
            background: rgba(0, 123, 255, 0.3) !important;
        }

        .edit-btn:hover {
            background: rgba(0, 123, 255, 0.5) !important;
        }

        .delete-btn {
            background: rgba(220, 53, 69, 0.3) !important;
        }

        .delete-btn:hover {
            background: rgba(220, 53, 69, 0.5) !important;
        }

        .check-btn {
            background: rgba(40, 167, 69, 0.3) !important;
        }

        .check-btn:hover {
            background: rgba(40, 167, 69, 0.5) !important;
        }

        .logout-btn {
            background: rgba(220, 53, 69, 0.2);
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <div class="dashboard-actions">
                <a href="add_job.php" class="btn">
                    <i class='bx bx-plus-circle'></i> Post New Job
                </a>
                <a href="../index.php" class="btn">
                    <i class='bx bx-home'></i> Main Site
                </a>
                <a href="logout.php" class="btn logout-btn">
                    <i class='bx bx-log-out'></i> Logout
                </a>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class='bx bx-briefcase'></i>
                </div>
                <div class="stat-number"><?= $total_jobs ?></div>
                <div class="stat-title">Active Jobs</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class='bx bx-file'></i>
                </div>
                <div class="stat-number"><?= $total_applications ?></div>
                <div class="stat-title">Total Applications</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class='bx bx-user'></i>
                </div>
                <div class="stat-number"><?= $total_users ?></div>
                <div class="stat-title">Registered Users</div>
            </div>
        </div>

        <h2>Job Listings</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Salary</th>
                    <th>Posted Date</th>
                    <th>Applicants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)) : ?>
                    <tr><td colspan="7" class="empty-message">No job postings available.</td></tr>
                <?php else : ?>
                    <?php foreach ($jobs as $job) : ?>
                        <tr>
                            <td><?= htmlspecialchars($job['title']); ?></td>
                            <td><?= htmlspecialchars($job['company']); ?></td>
                            <td><?= htmlspecialchars($job['location']); ?></td>
                            <td>₹<?= number_format($job['salary'], 2); ?></td>
                            <td><?= htmlspecialchars($job['posted_date']); ?></td>
                            <td>
                                <a href="applicants.php?job_id=<?= $job['id']; ?>" class="check-btn">
                                    <i class='bx bx-user-check'></i> View
                                </a>
                            </td>
                            <td class="actions">
                                <a href="edit_job.php?id=<?= $job['id']; ?>" class="edit-btn">
                                    <i class='bx bx-edit'></i> Edit
                                </a>
                                <a href="delete_job.php?id=<?= $job['id']; ?>" onclick="return confirm('Are you sure you want to delete this job?');" class="delete-btn">
                                    <i class='bx bx-trash'></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
