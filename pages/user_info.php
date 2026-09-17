<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    echo "<script>alert('Profile not found.'); window.location.href='dashboard.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg,rgb(19, 16, 50),rgb(30, 9, 44));
            --card-bg: rgba(255, 255, 255, 0.15);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            --primary-font: 'Poppins', sans-serif;
            --heading-font: 'Montserrat', sans-serif;
            --button-gradient: linear-gradient(135deg, #3a7bd5, #5a8dd6);
            --button-hover-gradient: linear-gradient(135deg, #5a8dd6, #3a7bd5);
            --text-color: #f8f9fa;
            --secondary-text: rgba(255, 255, 255, 0.85);
            --label-color: rgba(255, 255, 255, 0.9);
            --section-bg: rgba(255, 255, 255, 0.08);
            --section-bg-hover: rgba(255, 255, 255, 0.12);
        }

        body {
            font-family: var(--primary-font);
            margin: 0;
            padding: 0;
            background: var(--primary-gradient);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: var(--text-color);
            min-height: 100vh;
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

        .portfolio {
            max-width: 900px;
            margin: 60px auto;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            animation: fadeInUp 0.8s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .portfolio::before {
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

        h2 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 30px;
            color: var(--text-color);
            font-family: var(--heading-font);
            font-weight: 700;
            position: relative;
            display: inline-block;
            width: 100%;
        }

        h2::after {
            content: '';
            position: absolute;
            width: 60%;
            height: 3px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.6), transparent);
            bottom: -10px;
            left: 20%;
            border-radius: 4px;
        }

        .section {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 16px;
            background: var(--section-bg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
        }

        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: var(--section-bg-hover);
        }

        .section h3 {
            color: var(--label-color);
            margin-bottom: 15px;
            font-size: 1.5em;
            font-family: var(--heading-font);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section p {
            font-size: 1.1em;
            line-height: 1.7;
            color: var(--secondary-text);
            margin: 10px 0;
        }

        .section p strong {
            color: var(--text-color);
            font-weight: 600;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 25px;
            background: var(--button-gradient);
            color: var(--text-color);
            padding: 14px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .back-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.2), rgba(255,255,255,0.1));
            transition: all 0.4s ease;
            z-index: -1;
        }

        .back-link:hover::before {
            left: 100%;
        }

        .back-link:hover {
            background: var(--button-hover-gradient);
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .experience-content, .education-content {
            white-space: pre-line;
        }
    </style>
</head>
<body>

<div class="portfolio">
    <h2><?= htmlspecialchars($profile['full_name']) ?>'s Info</h2>

    <div class="section">
        <h3>📧 Contact</h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($profile['phone']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($profile['address']) ?></p>
    </div>

    <div class="section">
        <h3>🎓 Education</h3>
        <p class="education-content"><?= nl2br(htmlspecialchars($profile['qualification'])) ?></p>
    </div>

    <div class="section">
        <h3>💼 Experience</h3>
        <p class="experience-content"><?= nl2br(htmlspecialchars($profile['experience'])) ?></p>
    </div>

    <a href="../index.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>
