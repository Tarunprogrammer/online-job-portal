<?php
session_start();
include '../includes/db.php'; // DB connection

// Optional: Redirect if not logged in (e.g., only admins/employers can post)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $qualification = $_POST['qualification'];
    $posted_date = date('Y-m-d'); // Current date

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO jobs (title, company, location, salary, description, requirements, qualification, posted_date)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$title, $company, $location, $salary, $description, $requirements, $qualification, $posted_date]);

    if ($result) {
        $message = "Job posted successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to post the job.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job - Admin Dashboard</title>
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

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.2);
            color: #fff;
            border-left: 3px solid #28a745;
        }

        .error-message {
            background: rgba(220, 20, 60, 0.2);
            color: #fff;
            border-left: 3px solid crimson;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="header-actions">
            <a href="dashboard.php" class="btn back-btn">
                <i class='bx bx-arrow-back'></i> Back to Dashboard
            </a>
            <h2>Post a New Job</h2>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $message_type === 'success' ? 'success-message' : 'error-message' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" required>
                </div>

                <div class="form-group">
                    <label>Salary (₹)</label>
                    <input type="number" name="salary" min="0" step="1000">
                </div>
            </div>

            <div class="form-group">
                <label>Qualification</label>
                <select name="qualification" required>
                    <option value="" disabled selected>Select Qualification</option>
                    <option value="SSC">SSC</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Graduation">Graduation</option>
                    <option value="MBBS">MBBS</option>
                    <option value="B.Tech">B.Tech</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required></textarea>
            </div>

            <div class="form-group">
                <label>Requirements</label>
                <textarea name="requirements"></textarea>
            </div>

            <button type="submit">
                <i class='bx bx-plus-circle'></i> Post Job
            </button>
        </form>
    </div>
</body>
</html>
