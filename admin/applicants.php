<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get filters from URL
$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query parts
$select_fields = "
    a.job_id,
    a.user_id, 
    u.full_name, 
    u.email, 
    p.phone, 
    p.address, 
    p.dob, 
    p.gender, 
    p.qualification, 
    p.experience, 
    p.skills, 
    p.resume_image, 
    a.applied_date AS application_date,
    j.title AS job_title, 
    j.company AS job_company,
    j.location AS job_location,
    j.salary AS job_salary
";

$from_clause = "
    FROM applicants a
    JOIN users u ON a.user_id = u.id
    JOIN profile p ON u.id = p.user_id
    JOIN jobs j ON a.job_id = j.id
";

// Build where clause based on filters
$where_clause = "WHERE 1=1";
$params = [];

if ($job_id) {
    $where_clause .= " AND a.job_id = ?";
    $params[] = $job_id;
}

if ($user_id) {
    $where_clause .= " AND a.user_id = ?";
    $params[] = $user_id;
}

if ($search_term) {
    $where_clause .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR j.title LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$order_clause = "ORDER BY a.applied_date DESC";

// Get job title if job_id is provided
$job_title = '';
if ($job_id) {
    $jobStmt = $conn->prepare("SELECT title, company FROM jobs WHERE id = ?");
    $jobStmt->execute([$job_id]);
    $jobDetails = $jobStmt->fetch(PDO::FETCH_ASSOC);
    $job_title = $jobDetails ? $jobDetails['title'] . " at " . $jobDetails['company'] : '';
}

// Get user name if user_id is provided
$user_name = '';
if ($user_id) {
    $userStmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $userStmt->execute([$user_id]);
    $userDetails = $userStmt->fetch(PDO::FETCH_ASSOC);
    $user_name = $userDetails ? $userDetails['full_name'] : '';
}

// Build page title
if ($job_id && $user_id) {
    $pageTitle = "Applications from $user_name for $job_title";
} elseif ($job_id) {
    $pageTitle = "All Applicants for $job_title";
} elseif ($user_id) {
    $pageTitle = "All Applications from $user_name";
} elseif ($search_term) {
    $pageTitle = "Search Results for: $search_term";
} else {
    $pageTitle = "All Applicants";
}

// Get jobs for dropdown
$jobsStmt = $conn->query("SELECT id, title, company FROM jobs ORDER BY title");
$jobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get users for dropdown
$usersStmt = $conn->query("SELECT u.id, u.full_name, u.email FROM users u JOIN applicants a ON u.id = a.user_id GROUP BY u.id ORDER BY u.full_name");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Execute the final query
$query = "SELECT $select_fields $from_clause $where_clause $order_clause";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Admin Dashboard</title>
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

        .container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 20px;
        }

        .header h1 {
            font-family: var(--heading-font);
            font-weight: 700;
            margin: 0;
            background: linear-gradient(90deg, #fff, #9d4edd, #fff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shine 3s linear infinite;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .filter-container {
            background: rgba(18, 18, 30, 0.35);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(100, 100, 160, 0.15);
            padding: 20px;
            margin-bottom: 30px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 8px;
            color: var(--label-color);
            font-weight: 500;
        }

        .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .reset-btn {
            background: rgba(108, 117, 125, 0.3);
        }

        .reset-btn:hover {
            background: rgba(108, 117, 125, 0.5);
        }

        .applicants-table {
            width: 100%;
            overflow-x: auto;
            background: rgba(18, 18, 30, 0.35);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(100, 100, 160, 0.15);
            padding: 5px;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: rgba(40, 40, 80, 0.5);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        td {
            border-bottom: 1px solid rgba(100, 100, 160, 0.15);
        }

        tr:hover {
            background: rgba(40, 40, 80, 0.2);
        }

        .user-profile-link {
            color: var(--text-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .user-profile-link:hover {
            transform: translateY(-2px);
            color: #9d4edd;
        }

        img.resume-thumb {
            max-width: 80px;
            height: auto;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        img.resume-thumb:hover {
            transform: scale(1.1);
        }

        .contact-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin-bottom: 5px;
        }

        .email-link {
            background: rgba(0, 123, 255, 0.2);
        }

        .email-link:hover {
            background: rgba(0, 123, 255, 0.4);
        }

        .whatsapp-link {
            background: rgba(37, 211, 102, 0.2);
        }

        .whatsapp-link:hover {
            background: rgba(37, 211, 102, 0.4);
        }

        .view-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--text-color);
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            background: rgba(140, 120, 255, 0.2);
            transition: all 0.3s ease;
        }

        .view-link:hover {
            background: rgba(140, 120, 255, 0.4);
            transform: translateY(-2px);
        }

        .applicant-info {
            font-weight: 500;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            background: rgba(18, 18, 30, 0.25);
            border-radius: 16px;
            margin: 30px 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.2em;
        }

        /* Modal for resume */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .modal-img {
            max-width: 90%;
            max-height: 90vh;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            cursor: pointer;
            transition: 0.3s;
        }

        .close:hover {
            color: #bbb;
        }

        .job-link {
            color: var(--text-color);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .job-link:hover {
            color: #9d4edd;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
            background: rgba(40, 40, 80, 0.4);
            margin-left: 5px;
            vertical-align: middle;
        }
        
        .active-filter {
            background: rgba(140, 120, 255, 0.4);
            padding: 8px 12px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .active-filter a {
            color: white;
            text-decoration: none;
            margin-left: 5px;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $pageTitle ?></h1>
            <a href="dashboard.php" class="btn back-btn">
                <i class='bx bx-arrow-back'></i> Back to Dashboard
            </a>
        </div>

        <div class="filter-container">
            <form method="GET" action="">
                <div class="filters">
                    <div class="filter-group">
                        <label for="job_id">Select Job</label>
                        <select name="job_id" id="job_id">
                            <option value="">All Jobs</option>
                            <?php foreach ($jobs as $job): ?>
                                <option value="<?= $job['id'] ?>" <?= $job_id == $job['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($job['title']) ?> (<?= htmlspecialchars($job['company']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="user_id">Select Applicant</label>
                        <select name="user_id" id="user_id">
                            <option value="">All Applicants</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $user_id == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" placeholder="Search by name, email, job title..." value="<?= htmlspecialchars($search_term) ?>">
                    </div>
                </div>
                
                <div class="filter-actions">
                    <a href="applicants.php" class="btn reset-btn">
                        <i class='bx bx-reset'></i> Reset
                    </a>
                    <button type="submit" class="btn">
                        <i class='bx bx-filter-alt'></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <?php if ($job_id || $user_id || $search_term): ?>
            <div class="active-filters">
                <?php if ($job_id): ?>
                    <div class="active-filter">
                        <i class='bx bx-briefcase'></i> Job: <?= htmlspecialchars($job_title) ?>
                        <a href="?<?= $user_id ? 'user_id='.$user_id : '' ?><?= $search_term ? '&search='.$search_term : '' ?>" title="Remove filter">×</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($user_id): ?>
                    <div class="active-filter">
                        <i class='bx bx-user'></i> Applicant: <?= htmlspecialchars($user_name) ?>
                        <a href="?<?= $job_id ? 'job_id='.$job_id : '' ?><?= $search_term ? '&search='.$search_term : '' ?>" title="Remove filter">×</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($search_term): ?>
                    <div class="active-filter">
                        <i class='bx bx-search'></i> Search: "<?= htmlspecialchars($search_term) ?>"
                        <a href="?<?= $job_id ? 'job_id='.$job_id : '' ?><?= $user_id ? '&user_id='.$user_id : '' ?>" title="Remove filter">×</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (count($applicants) > 0): ?>
            <div class="applicants-table">
                <table>
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Contact</th>
                            <th>Qualification</th>
                            <th>Job Details</th>
                            <th>Resume</th>
                            <th>Applied On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applicants as $row): 
                            $email = htmlspecialchars($row['email']);
                            $name = urlencode($row['full_name']);
                            $jobTitle = htmlspecialchars($row['job_title']);
                            $subject = "Regarding Your Application for " . $jobTitle;
                            $body = "Hello $name,%0D%0A%0D%0AThank you for applying for the position of $jobTitle. We will review your application and contact you shortly.%0D%0A%0D%0ARegards,%0D%0AHR Team";
                            $gmailLink = "https://mail.google.com/mail/?view=cm&fs=1&to={$email}&su=" . urlencode($subject) . "&body={$body}";
                            $phone = preg_replace('/[^0-9]/', '', $row['phone']);
                        ?>
                        <tr>
                            <td>
                                <a href="?user_id=<?= $row['user_id'] ?>" class="user-profile-link">
                                    <i class='bx bx-user-circle'></i>
                                    <div class="applicant-info">
                                        <strong><?= htmlspecialchars($row['full_name']) ?></strong>
                                    </div>
                                </a>
                                <div><?= $row['gender'] ?></div>
                                <div><?= htmlspecialchars($row['address']) ?></div>
                                <div>DOB: <?= $row['dob'] ?></div>
                            </td>
                            <td>
                                <a href="<?= $gmailLink ?>" target="_blank" class="contact-link email-link">
                                    <i class='bx bx-envelope'></i> <?= $email ?>
                                </a>
                                <br>
                                <a href="https://wa.me/91<?= $phone ?>" target="_blank" class="contact-link whatsapp-link">
                                    <i class='bx bxl-whatsapp'></i> <?= htmlspecialchars($row['phone']) ?>
                                </a>
                            </td>
                            <td>
                                <div><strong><?= htmlspecialchars($row['qualification']) ?></strong></div>
                                <div><strong>Experience:</strong> <?= htmlspecialchars($row['experience']) ?></div>
                                <div><strong>Skills:</strong> <?= htmlspecialchars($row['skills']) ?></div>
                            </td>
                            <td>
                                <a href="?job_id=<?= $row['job_id'] ?>" class="job-link">
                                    <i class='bx bx-briefcase'></i>
                                    <div><strong><?= $jobTitle ?></strong></div>
                                </a>
                                <div><strong>Company:</strong> <?= htmlspecialchars($row['job_company']) ?></div>
                                <div><strong>Location:</strong> <?= htmlspecialchars($row['job_location']) ?></div>
                                <div><strong>Salary:</strong> ₹<?= number_format($row['job_salary'], 2) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($row['resume_image']) && file_exists("../images/" . $row['resume_image'])): ?>
                                    <a href="#" class="view-link" onclick="openModal('../images/<?= $row['resume_image']; ?>')">
                                        <i class='bx bx-file'></i> View Resume
                                    </a>
                                    <br><br>
                                    <img src="../images/<?= $row['resume_image']; ?>" alt="Resume" class="resume-thumb" onclick="openModal('../images/<?= $row['resume_image']; ?>')">
                                <?php else: ?>
                                    <span>No resume uploaded</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($row['application_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-message">
                <?php if ($job_id || $user_id || $search_term): ?>
                    No applicants found matching your search criteria.
                <?php else: ?>
                    No applicants found in the system.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for resume preview -->
    <div id="resumeModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modalImg" class="modal-img" src="" alt="Resume">
        </div>
    </div>

    <script>
        // Modal functionality
        function openModal(imgSrc) {
            document.getElementById('resumeModal').style.display = 'block';
            document.getElementById('modalImg').src = imgSrc;
        }

        function closeModal() {
            document.getElementById('resumeModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('resumeModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
