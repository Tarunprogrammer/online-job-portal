<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_stmt = $conn->prepare("SELECT qualification FROM profile WHERE user_id = ?");
$profile_stmt->execute([$user_id]);
$profile = $profile_stmt->fetch(PDO::FETCH_ASSOC);
$qualification = strtolower($profile['qualification'] ?? '');

$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query
$baseQuery = "SELECT *, 
              CASE 
                  WHEN DATEDIFF(NOW(), posted_date) <= 2 THEN 1 
                  ELSE 0 
              END AS is_new";

// Filter based on search keyword
if (!empty($search_keyword)) {
    $baseQuery .= " FROM jobs WHERE title LIKE :search";
    $params = [':search' => "%$search_keyword%"];
} else {
    $baseQuery .= " FROM jobs WHERE 1";
    $params = [];
}

// Apply qualification filters
if ($qualification === 'graduation') {
    // For graduation, exclude MBBS and B.Tech jobs
    $baseQuery .= " AND qualification NOT IN ('MBBS', 'B.Tech')";
    // Order: graduation, intermediate, SSC
    $baseQuery .= " ORDER BY 
                    CASE 
                        WHEN qualification = 'Graduation' THEN 1 
                        WHEN qualification = 'Intermediate' THEN 2 
                        WHEN qualification = 'SSC' THEN 3 
                        ELSE 4 
                    END, 
                    posted_date DESC";
} elseif ($qualification === 'intermediate') {
    // For intermediate, ONLY show intermediate and SSC jobs
    $baseQuery .= " AND qualification IN ('Intermediate', 'SSC')";
    // Order: intermediate, SSC
    $baseQuery .= " ORDER BY 
                    CASE 
                        WHEN qualification = 'Intermediate' THEN 1 
                        WHEN qualification = 'SSC' THEN 2 
                        ELSE 3 
                    END, 
                    posted_date DESC";
} elseif ($qualification === 'ssc') {
    // For SSC, ONLY show SSC jobs
    $baseQuery .= " AND qualification = 'SSC'";
    // Sort by newest first
    $baseQuery .= " ORDER BY posted_date DESC";
} elseif ($qualification === 'mbbs') {
    // For MBBS, ONLY show MBBS jobs
    $baseQuery .= " AND qualification = 'MBBS'";
    // Sort by newest first
    $baseQuery .= " ORDER BY posted_date DESC";
} else {
    // Default sorting by newest first
    $baseQuery .= " ORDER BY posted_date DESC";
}

$job_stmt = $conn->prepare($baseQuery);
$job_stmt->execute($params);
$jobs = $job_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1a0533, #4a0e57, #7a1f6d);
            --secondary-gradient: linear-gradient(135deg, #1e2761, #3e31c4);
            --card-bg: rgba(30, 30, 45, 0.25);
            --header-gradient: linear-gradient(135deg, #0f0422, #36174a);
            --button-gradient: linear-gradient(135deg, #4a154b, #341948);
            --dark-gradient: linear-gradient(135deg, #0a0a1a, #141e30);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            --primary-font: 'Poppins', sans-serif;
            --heading-font: 'Montserrat', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--primary-font);
            margin: 0;
            padding: 0;
            background: var(--primary-gradient);
            color: #e0e0e0;
            animation: fadeInBody 1s ease-in;
            transition: all 0.5s ease;
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            overflow-x: hidden;
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

        .dark-theme {
            --primary-gradient: var(--dark-gradient);
            --card-bg: rgba(15, 15, 25, 0.8);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            background: var(--dark-gradient);
            color: #e0e0e0;
        }

        .dark-theme header {
            background: linear-gradient(135deg, #000428, #03052e);
        }

        .dark-theme footer {
            background: #000428;
        }

        .dark-theme .product {
            background: rgba(10, 10, 20, 0.8);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        header {
            background: var(--header-gradient);
            color: white;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            flex-wrap: wrap;
            animation: slideDown 0.8s ease-out;
        }

        .header-container img {
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            margin-right: 15px;
            transition: transform 0.3s ease;
        }

        .header-container img:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 2.5em;
            font-family: var(--heading-font);
            font-weight: 700;
            letter-spacing: 1px;
            animation: slideDown 0.8s ease-out;
            background: linear-gradient(90deg, #fff, #9d4edd, #fff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shine 3s linear infinite;
        }

        @keyframes shine {
            to {
                background-position: 200% center;
            }
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        nav {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .nav-button {
            font-family: var(--primary-font);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 50px;
            background: rgba(40, 40, 80, 0.3);
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(100, 100, 160, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .nav-button span {
            margin-right: 8px;
            font-size: 1.2em;
        }

        .nav-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: rgba(80, 80, 120, 0.3);
            transition: all 0.3s ease;
            z-index: -1;
            transform: skewX(-15deg);
        }

        .nav-button:hover::before {
            width: 100%;
        }

        .nav-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .dark-toggle {
            background: #1f1f3d;
            color: #e0e0e0;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: 500;
            font-family: var(--primary-font);
            border: 1px solid rgba(100, 100, 160, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dark-toggle:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.25);
            background: #2d2d4d;
        }

        .main-container {
            padding: 30px 15px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            perspective: 1000px;
        }

        .quotation-container {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .quotation {
            position: absolute;
            width: 100%;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
            pointer-events: none;
        }

        .quotation.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        main h2 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 10px;
            color: white;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
            position: relative;
            display: inline-block;
            font-family: var(--heading-font);
            font-weight: 700;
        }

        .quote-text {
            font-size: 1.2em;
            color: rgba(255, 255, 255, 0.9);
            font-style: italic;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        .quote-author {
            margin-top: 10px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
        }

        main h2::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 4px;
            background: linear-gradient(to right, transparent, white, transparent);
            bottom: -10px;
            left: 25%;
            border-radius: 4px;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            width: 100%;
        }

        .product {
            background: rgba(18, 18, 30, 0.35);
            padding: 25px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            width: 300px;
            text-align: left;
            transition: all 0.4s ease;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(100, 100, 160, 0.15);
            animation: fadeInUp 0.6s ease forwards;
            transform-style: preserve-3d;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .product::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(100,100,200,0.05) 0%, rgba(50,50,100,0) 70%);
            transform: rotate(45deg);
            pointer-events: none;
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(40px) scale(0.9); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        .product:hover {
            transform: translateY(-15px) rotateY(5deg);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .icon-title {
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5);
        }
        
        .icon-title i {
            font-size: 1.2em;
            background: rgba(60, 60, 100, 0.3);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .product:hover .icon-title i {
            transform: scale(1.1) rotate(10deg);
            background: rgba(80, 80, 120, 0.4);
        }

        .icon-info {
            font-size: 1.1em;
            margin: 12px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s ease;
        }
        
        .icon-info i {
            font-size: 1.1em;
            color: rgba(255, 255, 255, 0.9);
            background: rgba(50, 50, 90, 0.25);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .icon-info:hover {
            transform: translateX(5px);
        }
        
        .icon-info:hover i {
            background: rgba(70, 70, 120, 0.35);
            transform: scale(1.1);
        }

        .product-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 12px;
            margin: 15px 0;
            transition: all 0.5s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            filter: brightness(0.9);
        }

        .product:hover .product-image {
            transform: scale(1.08) translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
            filter: brightness(1.1) contrast(1.1);
        }

        .add-to-cart-button {
            background: var(--button-gradient);
            color: white;
            padding: 12px 22px;
            border: none;
            border-radius: 30px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .add-to-cart-button::before {
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

        .add-to-cart-button:hover::before {
            left: 100%;
        }

        .add-to-cart-button:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.25);
        }

        footer {
            background: linear-gradient(to right, #0a0a1a, #0f142a, #151630);
            color: white;
            text-align: center;
            padding: 30px;
            margin-top: 50px;
            font-size: 1em;
            box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        footer a {
            transition: all 0.3s ease;
            display: inline-block;
        }

        footer a:hover {
            transform: translateY(-5px) scale(1.2);
            color: #a5f3fc !important;
        }

        .search-form {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeIn 1s ease;
            position: relative;
            width: 70%;
            max-width: 400px;
            margin: 0 auto 40px;
        }

        .search-form input {
            padding: 10px 20px;
            width: 100%;
            border-radius: 50px;
            border: none;
            background: rgba(30, 30, 60, 0.3);
            backdrop-filter: blur(12px);
            color: white;
            font-size: 0.9em;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .search-form input::placeholder {
            color: rgba(200, 200, 220, 0.6);
        }

        .search-form input:focus {
            outline: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            transform: scale(1.02);
            background: rgba(40, 40, 70, 0.4);
        }

        .search-form button {
            position: absolute;
            right: 5px;
            top: 5px;
            padding: 5px 15px;
            border-radius: 50px;
            background: linear-gradient(135deg, #2e1065, #4c1d95);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .search-form button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
            background: linear-gradient(135deg, #4c1d95, #5b21b6);
        }

        .no-jobs {
            text-align: center;
            font-size: 1.2em;
            color: white;
            margin: 50px 0;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease;
            background: rgba(20, 20, 40, 0.4);
            padding: 20px 30px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            
            nav {
                margin-top: 15px;
                justify-content: center;
            }
            
            .product {
                width: 100%;
                max-width: 350px;
            }
            
            .search-form {
                width: 85%;
                max-width: 300px;
            }

            .quotation-container {
                height: 150px;
            }
        }

        /* Job Search Banner */
        .job-search-banner {
            width: 100%;
            padding: 20px 30px;
            margin: 15px 0 5px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .job-search-text {
            text-align: center;
            color: white;
        }

        .job-search-text h2 {
            font-size: 1.6em;
            margin: 0 0 5px;
            font-weight: 600;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
        }

        .job-search-text p {
            font-size: 0.9em;
            margin: 0;
            opacity: 0.8;
        }

        .quick-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .quick-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .quick-link:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .quick-link i {
            font-size: 1.1em;
        }

        @media (max-width: 768px) {
            .job-search-banner {
                padding: 15px;
            }
            
            .job-search-text h2 {
                font-size: 1.3em;
            }
            
            .job-search-text p {
                font-size: 0.8em;
            }
            
            .quick-links {
                gap: 8px;
            }
            
            .quick-link {
                padding: 6px 12px;
                font-size: 0.8em;
            }
        }

        /* New tag style */
        .new-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #ff4e50, #f9d423);
            color: white;
            font-size: 0.8em;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            animation: pulse 1.5s infinite;
            z-index: 2;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <div class="logo-container">
            <img src="images/logo.png" alt="Company Logo">
            <h1>GRAB IT!</h1>
        </div>
        <nav>
            <a href="pages/user_info.php?id=<?= $user_id ?>" class="nav-button"><span><i class='bx bx-user-circle'></i></span> My Profile</a>
            <a href="pages/applications.php" class="nav-button"><span><i class='bx bx-file'></i></span> Applications</a>
            <a href="pages/login.php" class="nav-button"><span><i class='bx bx-log-out'></i></span> Logout</a>
            <button class="dark-toggle" onclick="document.body.classList.toggle('dark-theme')"><span><i class='bx bx-moon'></i></span> Theme</button>
        </nav>
    </div>
</header>

<div class="main-container">
    <main>
        <div class="quotation-container">
            <h2></h2>
            
            <div class="quotation active">
                <p class="quote-text">"Choose a job you love, and you will never have to work a day in your life."</p>
                <p class="quote-author">— Confucius</p>
            </div>
            
            <div class="quotation">
                <p class="quote-text">"The only way to do great work is to love what you do. If you haven't found it yet, keep looking. Don't settle."</p>
                <p class="quote-author">— Steve Jobs</p>
            </div>
            
            <div class="quotation">
                <p class="quote-text">"Your work is going to fill a large part of your life, and the only way to be truly satisfied is to do what you believe is great work."</p>
                <p class="quote-author">— Steve Jobs</p>
            </div>
            
            <div class="quotation">
                <p class="quote-text">"Opportunities don't happen. You create them."</p>
                <p class="quote-author">— Chris Grosser</p>
            </div>
            
            <div class="quotation">
                <p class="quote-text">"The future depends on what you do today."</p>
                <p class="quote-author">— Mahatma Gandhi</p>
            </div>
        </div>

        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search for your dream job..." value="<?= htmlspecialchars($search_keyword) ?>">
            <button type="submit"><i class='bx bx-search'></i></button>
        </form>

        <div class="product-list">
            <?php if (empty($jobs)) : ?>
                <p class="no-jobs"><i class='bx bx-error-circle' style="font-size: 1.5em; margin-right: 10px;"></i> No job opportunities available<?= !empty($search_keyword) ? " for '<strong>" . htmlspecialchars($search_keyword) . "</strong>'" : "" ?>. Check back later or try a different search.</p>
            <?php else : ?>
                <?php foreach ($jobs as $job) : ?>
                    <div class="product">
                        <?php if ($job['is_new']) : ?>
                            <div class="new-tag">New</div>
                        <?php endif; ?>
                        <div class="icon-title"><i class='bx bx-briefcase-alt-2'></i> <?= htmlspecialchars($job['title']); ?></div>
                        <div class="icon-info"><i class='bx bx-rupee'></i> ₹<?= number_format($job['salary'], 2); ?></div>
                        <div class="icon-info"><i class='bx bx-certification'></i> <?= htmlspecialchars($job['qualification']); ?></div>
                        <div class="icon-info"><i class='bx bx-map'></i> <?= htmlspecialchars($job['location']); ?></div>
                        <?php if (!empty($job['image'])) : ?>
                            <img src="images/<?= htmlspecialchars($job['image']); ?>" alt="<?= htmlspecialchars($job['title']); ?>" class="product-image">
                        <?php endif; ?>
                        <a href="pages/job_details.php?id=<?= $job['id'] ?>" class="add-to-cart-button">View Details <i class='bx bx-right-arrow-alt' style="vertical-align: middle; margin-left: 5px;"></i></a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<footer>
    &copy; <?= date("Y"); ?> Grab It!! All rights reserved.
    <div style="margin-top: 15px;">
        <a href="#" style="margin: 0 15px; color: white;"><i class="fab fa-facebook"></i></a>
        <a href="#" style="margin: 0 15px; color: white;"><i class="fab fa-twitter"></i></a>
        <a href="#" style="margin: 0 15px; color: white;"><i class='bx bx-linkedin'></i></a>
    </div>
</footer>

<script>
    // Add animation to cards when they come into view
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = 1;
                    entry.target.style.transform = 'translateY(0) scale(1)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.product').forEach(card => {
            observer.observe(card);
            card.style.opacity = 0;
            card.style.transform = 'translateY(40px) scale(0.9)';
        });
        
        // Rotate quotes every 5 seconds
        const quotes = document.querySelectorAll('.quotation');
        let currentQuote = 0;
        
        function rotateQuotes() {
            quotes.forEach(quote => quote.classList.remove('active'));
            currentQuote = (currentQuote + 1) % quotes.length;
            quotes[currentQuote].classList.add('active');
        }
        
        setInterval(rotateQuotes, 5000);
    });
</script>

</body>
</html>
