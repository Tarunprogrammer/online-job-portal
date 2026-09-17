<?php
session_start();
include('../includes/db.php');

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch applications of the logged-in user
$sql = "SELECT a.*, j.title, j.company, j.location 
        FROM applicants a 
        JOIN jobs j ON a.job_id = j.id 
        WHERE a.user_id = :user_id 
        ORDER BY a.applied_on DESC";

$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/deep-theme.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg,rgb(6, 24, 59),rgb(28, 9, 39));
            --card-bg: rgba(255, 255, 255, 0.15);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            --primary-font: 'Poppins', sans-serif;
            --heading-font: 'Montserrat', sans-serif;
            --button-gradient: linear-gradient(135deg, #3a7bd5, #5a8dd6);
            --button-hover-gradient: linear-gradient(135deg, #5a8dd6, #3a7bd5);
            --text-color: #f8f9fa;
            --secondary-text: rgba(255, 255, 255, 0.85);
            --accent-color: #b3e0ff;
            --container-bg: rgba(255, 255, 255, 0.05);
            --card-bg: rgba(255, 255, 255, 0.1);
            --card-bg-hover: rgba(255, 255, 255, 0.15);
        }

        body {
            font-family: var(--primary-font);
            background: var(--primary-gradient);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            padding: 40px 20px;
            color: var(--text-color);
            min-height: 100vh;
            position: relative;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fadeInBody {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        h1 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5em;
            color: var(--text-color);
            font-family: var(--heading-font);
            font-weight: 700;
            position: relative;
            display: inline-block;
            width: 100%;
        }

        h1::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 3px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.6), transparent);
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 4px;
        }

        .application-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 20px;
            background: var(--container-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }

        .application-card {
            background: var(--card-bg);
            padding: 25px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 16px;
            backdrop-filter: blur(12px);
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease forwards;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .application-card::before {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .application-card:hover::before {
            opacity: 1;
            transform: translate(100%, 100%);
        }

        .application-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
            background: var(--card-bg-hover);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .application-card h2 {
            margin-top: 0;
            font-size: 1.6em;
            color: var(--text-color);
            font-family: var(--heading-font);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .application-card p {
            font-size: 1.05em;
            color: var(--secondary-text);
            margin: 12px 0;
        }

        .application-card p strong {
            color: var(--text-color);
            font-weight: 600;
        }

        .status {
            font-weight: 600;
            color: var(--accent-color);
            background: rgba(255, 255, 255, 0.08);
            padding: 6px 15px;
            border-radius: 30px;
            display: inline-block;
            margin-top: 10px;
            backdrop-filter: blur(8px);
        }

        .no-applications {
            text-align: center;
            font-size: 1.3em;
            color: var(--text-color);
            margin: 50px 0;
            line-height: 1.6;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1);
            background: var(--card-bg);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(8px);
            max-width: 600px;
            margin: 50px auto;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
            text-decoration: none;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 20px 0;
            backdrop-filter: blur(8px);
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Page specific styles */
        .applications-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .status-pending {
            background: rgba(255, 191, 0, 0.2);
            color: #ffbf00;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .status-approved {
            background: rgba(72, 187, 120, 0.2);
            color: #48bb78;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .status-rejected {
            background: rgba(220, 38, 38, 0.2);
            color: #dc2626;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .status-scheduled {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .application-card {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .application-date {
            font-size: 0.9em;
            opacity: 0.8;
        }
        
        .no-applications {
            text-align: center;
            padding: 40px 20px;
            font-size: 1.2em;
            color: var(--text-color);
        }
    </style>
</head>
<body>

<a href="../index.php" class="back-button">← Back to Jobs</a>

<h1>My Job Applications</h1>

<div class="application-container">
    <?php if (count($applications) > 0): ?>
        <?php foreach ($applications as $app): ?>
            <div class="application-card">
                <h2><?= htmlspecialchars($app['title']) ?> @ <?= htmlspecialchars($app['company']) ?></h2>
                <p><strong>Location:</strong> <?= htmlspecialchars($app['location']) ?></p>
                <p><strong>Applied On:</strong> <?= date('d M Y', strtotime($app['applied_on'])) ?></p>
                <span class="status">Application Submitted</span>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-applications">You haven't applied for any jobs yet. Browse through our available positions and find your perfect match!</p>
    <?php endif; ?>
</div>

</body>
</html>
