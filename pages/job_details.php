<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get job ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid job ID.'); window.location.href='dashboard.php';</script>";
    exit();
}

$job_id = $_GET['id'];

// Fetch job details
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo "<script>alert('Job not found.'); window.location.href='dashboard.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> - Job Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/deep-theme.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1a0533, #4a0e57, #7a1f6d);
            --card-bg: rgba(255, 255, 255, 0.15);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            --primary-font: 'Poppins', sans-serif;
            --heading-font: 'Montserrat', sans-serif;
            --button-gradient: linear-gradient(135deg, #ff6b6b, #556270);
            --button-hover-gradient: linear-gradient(135deg, #556270, #ff6b6b);
            --text-color: #f8f9fa;
            --secondary-text: rgba(255, 255, 255, 0.85);
            --label-color: rgba(255, 255, 255, 0.9);
            --card-bg-hover: rgba(255, 255, 255, 0.18);
            --detail-bg: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: var(--primary-font);
            background: var(--primary-gradient);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            padding: 30px;
            margin: 0;
            color: var(--text-color);
            min-height: 100vh;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .job-container {
            background: var(--card-bg);
            max-width: 850px;
            margin: 20px auto;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            animation: fadeInUp 0.6s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .job-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 60%);
            z-index: -1;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .job-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            background: rgba(18, 18, 30, 0.35);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(100, 100, 160, 0.15);
            box-shadow: var(--card-shadow);
        }

        .job-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5em;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
            50% { transform: scale(1.05); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2); }
            100% { transform: scale(1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
        }

        h2 {
            margin-top: 0;
            color: var(--text-color);
            font-size: 2.2em;
            font-family: var(--heading-font);
            font-weight: 700;
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 1.2em;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            margin-bottom: 20px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .detail {
            margin-bottom: 10px;
            padding: 15px 20px;
            background: var(--detail-bg);
            border-radius: 12px;
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .detail:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: var(--card-bg-hover);
        }

        .detail-icon {
            background: rgba(255, 255, 255, 0.15);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4em;
            transition: all 0.3s ease;
        }

        .detail:hover .detail-icon {
            transform: scale(1.1) rotate(10deg);
            background: rgba(255, 255, 255, 0.25);
        }

        .detail-content {
            flex: 1;
        }

        .label {
            font-weight: 600;
            color: var(--label-color);
            display: block;
            margin-bottom: 5px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .value {
            font-size: 1.05em;
            line-height: 1.6;
            color: var(--secondary-text);
        }

        .section-title {
            font-size: 1.4em;
            margin: 30px 0 15px;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            font-size: 1.2em;
            opacity: 0.9;
        }

        .content-box {
            background: var(--detail-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
            line-height: 1.7;
            white-space: pre-line;
        }

        .content-box:hover {
            background: var(--card-bg-hover);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .apply-button {
            background: var(--button-gradient);
            color: var(--text-color);
            border: none;
            padding: 15px 30px;
            font-size: 1.1em;
            font-weight: 600;
            font-family: var(--primary-font);
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .apply-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(80,80,150,0.1), rgba(100,100,180,0.3), rgba(80,80,150,0.1));
            transition: all 0.4s ease;
            z-index: -1;
        }

        .apply-button:hover::before {
            left: 100%;
        }

        .apply-button:hover {
            background: var(--button-hover-gradient);
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
            text-decoration: none;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }

        .tag {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.9em;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .tag:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .requirements-box, .description-box {
            white-space: pre-line;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .job-details-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .company-logo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            margin-right: 25px;
        }
        
        .apply-section {
            margin-top: 30px;
            text-align: center;
        }
        
        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(40, 40, 80, 0.3);
            padding: 10px 15px;
            border-radius: 30px;
        }
        
        .meta-item i {
            font-size: 1.2em;
        }
        
        .job-description {
            margin-top: 30px;
            line-height: 1.8;
        }
    </style>
</head>
<body>

<a href="../index.php" class="back-button"><i class='bx bx-arrow-back'></i> Back to Jobs</a>

<div class="job-container">
    <div class="job-header">
        <div class="job-logo">
            <i class='bx bx-briefcase'></i>
        </div>
        <h2><?= htmlspecialchars($job['title']) ?></h2>
        <div class="company-name"><?= htmlspecialchars($job['company']) ?></div>
        
        <div class="tags">
            <div class="tag"><i class='bx bx-calendar'></i> <?= htmlspecialchars(date('d M Y', strtotime($job['posted_date']))) ?></div>
            <div class="tag"><i class='bx bx-user-check'></i> <?= htmlspecialchars($job['qualification']) ?></div>
        </div>
    </div>
    
    <div class="detail-grid">
        <div class="detail">
            <div class="detail-icon">
                <i class='bx bx-map'></i>
            </div>
            <div class="detail-content">
                <span class="label">Location</span>
                <div class="value"><?= htmlspecialchars($job['location']) ?></div>
            </div>
        </div>
        
        <div class="detail">
            <div class="detail-icon">
                <i class='bx bx-rupee'></i>
            </div>
            <div class="detail-content">
                <span class="label">Salary</span>
                <div class="value">₹<?= number_format($job['salary']) ?></div>
            </div>
        </div>
        
        <div class="detail">
            <div class="detail-icon">
                <i class='bx bx-time'></i>
            </div>
            <div class="detail-content">
                <span class="label">Posted On</span>
                <div class="value"><?= htmlspecialchars(date('d M, Y', strtotime($job['posted_date']))) ?></div>
            </div>
        </div>
        
        <div class="detail">
            <div class="detail-icon">
                <i class='bx bx-certification'></i>
            </div>
            <div class="detail-content">
                <span class="label">Required Qualification</span>
                <div class="value"><?= htmlspecialchars($job['qualification']) ?></div>
            </div>
        </div>
    </div>
    
    <div class="section-title">
        <i class='bx bx-info-circle'></i> Job Description
    </div>
    <div class="content-box description-box">
        <?= nl2br(htmlspecialchars($job['description'])) ?>
    </div>
    
    <div class="section-title">
        <i class='bx bx-list-check'></i> Requirements
    </div>
    <div class="content-box requirements-box">
        <?= nl2br(htmlspecialchars($job['requirements'])) ?>
    </div>
    
    <div class="action-buttons">
        <a href="confirm.php?job_id=<?= $job['id'] ?>" class="apply-button"><i class='bx bx-send'></i> Apply Now</a>
        <a href="../index.php" class="back-button"><i class='bx bx-home'></i> Back to Home</a>
    </div>
</div>

</body>
</html>
