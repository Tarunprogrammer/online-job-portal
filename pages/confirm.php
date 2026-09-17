<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current job ID
$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : 0;

// Get the job details to find similar jobs
if ($job_id) {
    // Get current job details
    $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    $current_job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Register the application if not already done
    if ($current_job) {
        $user_id = $_SESSION['user_id'];
        
        // Check if already applied
        $check_stmt = $conn->prepare("SELECT * FROM applicants WHERE user_id = ? AND job_id = ?");
        $check_stmt->execute([$user_id, $job_id]);
        
        if (!$check_stmt->fetch(PDO::FETCH_ASSOC)) {
            // Record the application
            $apply_stmt = $conn->prepare("INSERT INTO applicants (user_id, job_id, applied_on) VALUES (?, ?, NOW())");
            $apply_stmt->execute([$user_id, $job_id]);
        }
        
        // Find similar jobs based on qualification or title
        $similar_jobs_stmt = $conn->prepare("
            SELECT * FROM jobs 
            WHERE id != ? 
            AND (qualification = ? OR title LIKE ? OR location = ?)
            LIMIT 3
        ");
        $title_search = "%" . strtolower(substr($current_job['title'], 0, 10)) . "%";
        $similar_jobs_stmt->execute([
            $job_id, 
            $current_job['qualification'], 
            $title_search,
            $current_job['location']
        ]);
        $similar_jobs = $similar_jobs_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/deep-theme.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 40px auto;
            perspective: 1000px;
        }
        
        .confirmation-card {
            background: rgba(18, 18, 30, 0.35);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(12px);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(100, 100, 160, 0.15);
            text-align: center;
            transform-style: preserve-3d;
            animation: cardEntrance 1s ease-out forwards;
        }
        
        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(-20px) rotateX(5deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0);
            }
        }
        
        .success-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 20px;
            background: rgba(72, 187, 120, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.8em;
            color: #48bb78;
            border: 2px solid rgba(72, 187, 120, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            animation: successPulse 2s infinite;
        }
        
        @keyframes successPulse {
            0% { transform: scale(1); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
            50% { transform: scale(1.05); box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3); }
            100% { transform: scale(1); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        }
        
        .confirmation-card h2 {
            font-size: 2em;
            margin: 20px 0;
            color: #48bb78;
            font-family: var(--heading-font);
            font-weight: 600;
            animation: fadeInUp 0.8s ease-out forwards;
            animation-delay: 0.3s;
            opacity: 0;
        }
        
        .confirmation-card p {
            font-size: 1.1em;
            color: var(--text-color);
            margin-bottom: 25px;
            animation: fadeInUp 0.8s ease-out forwards;
            animation-delay: 0.5s;
            opacity: 0;
            line-height: 1.6;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            background: rgba(40, 40, 80, 0.3);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(100, 100, 160, 0.2);
            animation: fadeInUp 0.8s ease-out forwards;
            animation-delay: 0.7s;
            opacity: 0;
        }
        
        .back-button:hover {
            background: rgba(60, 60, 100, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .similar-jobs {
            margin-top: 50px;
            animation: fadeInUp 0.8s ease-out forwards;
            animation-delay: 0.9s;
            opacity: 0;
        }
        
        .similar-jobs h3 {
            font-size: 1.4em;
            margin-bottom: 20px;
            color: var(--text-color);
            font-family: var(--heading-font);
            font-weight: 600;
            text-align: center;
        }
        
        .job-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .job-card {
            background: rgba(30, 30, 50, 0.4);
            padding: 20px;
            border-radius: 16px;
            border: 1px solid rgba(100, 100, 160, 0.15);
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            text-align: left;
        }
        
        .job-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            background: rgba(40, 40, 70, 0.5);
        }
        
        .job-title {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .job-company {
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
        }
        
        .job-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 0.85em;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .job-detail i {
            font-size: 1.1em;
        }
        
        .view-job-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            background: rgba(60, 60, 120, 0.3);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.85em;
            margin-top: 15px;
            transition: all 0.3s ease;
            border: 1px solid rgba(100, 100, 160, 0.2);
        }
        
        .view-job-btn:hover {
            background: rgba(80, 80, 140, 0.4);
            transform: translateY(-3px);
        }
        
        .no-jobs {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-style: italic;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .confirmation-container {
                margin: 30px 15px;
            }
            
            .confirmation-card {
                padding: 30px 20px;
            }
            
            .job-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class='bx bx-check'></i>
            </div>
            <h2>Application Successful!</h2>
            <p>Thank you for applying to this position. Your application has been received and is being processed. Our team will review your qualifications and reach out to you soon.</p>
            <a href="../index.php" class="back-button">
                <i class='bx bx-home'></i> Return to Dashboard
            </a>
            
            <?php if (isset($similar_jobs) && !empty($similar_jobs)): ?>
                <div class="similar-jobs">
                    <h3>You might also be interested in:</h3>
                    <div class="job-cards">
                        <?php foreach ($similar_jobs as $job): ?>
                            <div class="job-card">
                                <div class="job-title"><?= htmlspecialchars($job['title']) ?></div>
                                <div class="job-company"><?= htmlspecialchars($job['company']) ?></div>
                                
                                <div class="job-detail">
                                    <i class='bx bx-map'></i>
                                    <span><?= htmlspecialchars($job['location']) ?></span>
                                </div>
                                
                                <div class="job-detail">
                                    <i class='bx bx-certification'></i>
                                    <span><?= htmlspecialchars($job['qualification']) ?></span>
                                </div>
                                
                                <div class="job-detail">
                                    <i class='bx bx-rupee'></i>
                                    <span>₹<?= number_format($job['salary']) ?></span>
                                </div>
                                
                                <a href="job_details.php?id=<?= $job['id'] ?>" class="view-job-btn">
                                    <i class='bx bx-show'></i> View Details
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif (isset($similar_jobs)): ?>
                <div class="similar-jobs">
                    <h3>Looking for more opportunities?</h3>
                    <p class="no-jobs">Explore our job listings for other exciting opportunities!</p>
                    <a href="../index.php" class="view-job-btn" style="margin-top: 20px;">
                        <i class='bx bx-search'></i> Browse All Jobs
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
