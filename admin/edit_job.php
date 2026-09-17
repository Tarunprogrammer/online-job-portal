<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Job ID is missing.");
}

$job_id = intval($_GET['id']);

// Using PDO
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Job not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/deep-theme.css">
    <style>
        body {
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: rgba(18, 18, 30, 0.35);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(100, 100, 160, 0.15);
            animation: fadeInUp 0.6s ease forwards;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--text-color);
            font-family: var(--heading-font);
            font-weight: 700;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        h2::after {
            content: '';
            position: absolute;
            width: 60%;
            height: 3px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.7), transparent);
            bottom: -10px;
            left: 20%;
            border-radius: 4px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--label-color);
            font-weight: 500;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text-color);
            font-family: var(--primary-font);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button {
            margin-top: 20px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="header-actions">
            <a href="dashboard.php" class="btn back-btn">
                <i class='bx bx-arrow-back'></i> Back to Dashboard
            </a>
            <h2>Edit Job Posting</h2>
        </div>

        <form action="update_job.php" method="POST">
            <input type="hidden" name="id" value="<?= $job['id']; ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($job['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company" value="<?= htmlspecialchars($job['company']); ?>" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($job['location']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Salary (₹)</label>
                    <input type="number" name="salary" value="<?= htmlspecialchars($job['salary']); ?>" min="0" step="1000">
                </div>
            </div>

            <div class="form-group">
                <label>Qualification</label>
                <select name="qualification" required>
                    <option value="SSC" <?= $job['qualification'] == 'SSC' ? 'selected' : '' ?>>SSC</option>
                    <option value="Intermediate" <?= $job['qualification'] == 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="Graduation" <?= $job['qualification'] == 'Graduation' ? 'selected' : '' ?>>Graduation</option>
                    <option value="MBBS" <?= $job['qualification'] == 'MBBS' ? 'selected' : '' ?>>MBBS</option>
                    <option value="B.Tech" <?= $job['qualification'] == 'B.Tech' ? 'selected' : '' ?>>B.Tech</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required><?= htmlspecialchars($job['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Requirements</label>
                <textarea name="requirements"><?= htmlspecialchars($job['requirements']); ?></textarea>
            </div>

            <button type="submit">
                <i class='bx bx-save'></i> Update Job
            </button>
        </form>
    </div>
</body>
</html>
