<?php
session_start();
include('../includes/db.php'); // Adjust path if needed

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if profile is already filled
$query = "SELECT * FROM profile WHERE user_id = :id";
$stmt = $conn->prepare($query);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if already filled
if (
    !empty($user['full_name']) || !empty($user['email']) || 
    !empty($user['address']) || !empty($user['dob'])
) {
    echo "<script>alert('Profile already completed.'); window.location.href='../dashboard.php';</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];
    $skills = $_POST['skills'];

    // Handle image upload
    $img_name = '';
    if (isset($_FILES['resume_image']) && $_FILES['resume_image']['error'] === UPLOAD_ERR_OK) {
        $img_name = time() . '_' . basename($_FILES['resume_image']['name']);
        $target_dir = "../images/";
        move_uploaded_file($_FILES['resume_image']['tmp_name'], $target_dir . $img_name);
    }

    // Insert into profile table
$insert = "INSERT INTO profile (
    user_id, full_name, email, phone, address, dob, gender, qualification, experience, skills, resume_image
) VALUES (
    :user_id, :full_name, :email, :phone, :address, :dob, :gender, :qualification, :experience, :skills, :resume_image
)";

$stmt = $conn->prepare($insert);
$stmt->execute([
    'user_id' => $user_id,
    'full_name' => $full_name,
    'email'  => $email,
    'phone'  => $phone,
    'address' => $address,
    'dob' => $dob,
    'gender' => $gender,
    'qualification' => $qualification,
    'experience'  => $experience,
    'skills' => $skills,
    'resume_image' => $img_name ]);

    echo "<script>alert('Profile completed successfully!'); window.location.href='../index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="../css/deep-theme.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(18, 18, 30, 0.35);
            border-radius: 20px;
            backdrop-filter: blur(12px);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(100, 100, 160, 0.15);
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        .profile-icon {
            width: 100px;
            height: 100px;
            background: rgba(80, 80, 120, 0.4);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            color: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(100, 100, 160, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); }
            50% { transform: scale(1.05); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3); }
            100% { transform: scale(1); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); }
        }
        
        .profile-header h2 {
            font-size: 2.2em;
            color: var(--text-color);
            margin-bottom: 10px;
            font-family: var(--heading-font);
            font-weight: 700;
        }
        
        .profile-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1em;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 5px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--label-color);
            font-size: 0.95em;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(30, 30, 60, 0.3);
            border: 1px solid rgba(100, 100, 160, 0.2);
            border-radius: 10px;
            color: var(--text-color);
            font-family: var(--primary-font);
            font-size: 1em;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            border-color: rgba(150, 150, 255, 0.4);
            box-shadow: 0 0 0 2px rgba(150, 150, 255, 0.2);
            background: rgba(40, 40, 70, 0.4);
            outline: none;
            transform: translateY(-2px);
        }
        
        .full-width {
            grid-column: span 2;
        }
        
        .submit-btn {
            background: var(--button-gradient);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1em;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 30px auto 0;
            text-align: center;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.2), rgba(255,255,255,0.1));
            transition: all 0.4s ease;
            z-index: 1;
        }
        
        .submit-btn:hover::before {
            left: 100%;
        }
        
        .submit-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.25);
            background: var(--button-hover-gradient);
        }
        
        .file-input-container {
            position: relative;
            margin-top: 15px;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(30, 30, 60, 0.3);
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px dashed rgba(100, 100, 160, 0.3);
        }
        
        .file-input-label:hover {
            background: rgba(40, 40, 70, 0.4);
        }
        
        .file-input-label i {
            font-size: 1.5em;
        }
        
        input[type="file"] {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 25px;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            transform: translateX(-5px);
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 30px 20px;
                margin: 20px 15px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .full-width {
                grid-column: span 1;
            }
            
            .profile-header h2 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-icon">
                <i class='bx bx-user'></i>
            </div>
            <h2>Complete Your Profile</h2>
            <p>Tell us a little about yourself to help us personalize your job search experience</p>
        </div>
        
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="" disabled selected>Select your gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="qualification">Highest Qualification</label>
                    <select id="qualification" name="qualification" required>
                        <option value="" disabled selected>Select your qualification</option>
                        <option value="SSC">SSC</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Graduation">Graduation</option>
                        <option value="MBBS">MBBS</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="experience">Years of Experience</label>
                    <input type="text" id="experience" name="experience" placeholder="e.g. 2 years" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="address">Your Address</label>
                    <textarea id="address" name="address" rows="3" required></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label for="skills">Skills & Expertise</label>
                    <textarea id="skills" name="skills" rows="3" placeholder="List your skills separated by commas"></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label>Resume/CV (Optional)</label>
                    <div class="file-input-container">
                        <label for="resume_image" class="file-input-label">
                            <i class='bx bx-upload'></i>
                            <span>Choose a file</span>
                        </label>
                        <input type="file" id="resume_image" name="resume_image" accept="image/*">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="submit-btn">
                <i class='bx bx-check-circle'></i> Complete Profile
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="../index.php" class="back-link">
                <i class='bx bx-arrow-back'></i> Back to Home
            </a>
        </div>
    </div>
    
    <script>
        // Display filename when file is selected
        document.getElementById('resume_image').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            this.nextElementSibling = fileName;
            const label = document.querySelector('.file-input-label span');
            label.textContent = fileName;
        });
    </script>
</body>
</html>
