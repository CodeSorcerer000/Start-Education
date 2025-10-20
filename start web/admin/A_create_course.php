<?php
// Database connection
$server = "localhost";
$user = "root";
$password = "";
$database = "start_education";

// Create connection
$conn = mysqli_connect($server, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$successMessage = "";
$errorMessage = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $courseName = mysqli_real_escape_string($conn, $_POST['courseName']);
    $courseDescription = mysqli_real_escape_string($conn, $_POST['courseDescription']);
    $instructor = mysqli_real_escape_string($conn, $_POST['instructor']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $duration = (int)$_POST['duration'];
    $courseType = mysqli_real_escape_string($conn, $_POST['courseType']);
    $price = ($courseType === 'free') ? 0 : (float)$_POST['price'];
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $startTime = mysqli_real_escape_string($conn, $_POST['startTime']);
    $zoomLink = isset($_POST['zoomLink']) ? mysqli_real_escape_string($conn, $_POST['zoomLink']) : '';
    
    // Handle file upload
    $courseImage = '';
    if (isset($_FILES['courseImage']) && $_FILES['courseImage']['error'] == 0) {
        $targetDir = "uploads/";
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['courseImage']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'webp');
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $targetFile = $targetDir . $newFileName;
            
            if (move_uploaded_file($_FILES['courseImage']['tmp_name'], $targetFile)) {
                $courseImage = $targetFile;
            }
        }
    }
    
    // Insert query
    $query = "INSERT INTO courses (course_name, course_description, instructor, category, duration, 
              course_type, price, start_date, start_time, course_image, zoom_link, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "ssssisdssss", 
            $courseName, 
            $courseDescription, 
            $instructor, 
            $category, 
            $duration, 
            $courseType, 
            $price, 
            $startDate, 
            $startTime, 
            $courseImage, 
            $zoomLink
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $successMessage = "Course created successfully!";
        } else {
            $errorMessage = "Error: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $errorMessage = "Error preparing statement: " . mysqli_error($conn);
    }
}

// Fetch all courses to display
$courses = [];
$query = "SELECT * FROM courses ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - Start Education</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #F2F3F4 0%, #ffffff 100%);
            color: #1F1E26;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 20s infinite ease-in-out;
        }

        .orb1 {
            width: 400px;
            height: 400px;
            background: #03C03C;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .orb2 {
            width: 350px;
            height: 350px;
            background: #A2D43D;
            bottom: -100px;
            right: -100px;
            animation-delay: 5s;
        }

        .orb3 {
            width: 300px;
            height: 300px;
            background: #03C03C;
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 50px) scale(1.1); }
            50% { transform: translate(30px, -30px) scale(0.9); }
            75% { transform: translate(-30px, 30px) scale(1.05); }
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 20px 60px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(31, 30, 38, 0.1);
            transition: all 0.3s ease;
        }

        header.scrolled {
            padding: 15px 60px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .back-link {
            text-decoration: none;
            color: #1F1E26;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #03C03C;
        }

        /* Main Container */
        .container {
            max-width: 900px;
            margin: 120px auto 60px;
            padding: 0 40px;
            animation: fadeInUp 0.8s ease-out;
        }

        .form-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .form-header h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #1F1E26 0%, #4A4952 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-header .highlight {
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-header p {
            font-size: 18px;
            color: #4A4952;
            line-height: 1.6;
        }

        /* Alert Messages */
        .alert {
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            animation: slideDown 0.5s ease-out;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(3, 192, 60, 0.1), rgba(162, 212, 61, 0.1));
            border: 2px solid #03C03C;
            color: #03C03C;
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(255, 59, 48, 0.1), rgba(255, 149, 0, 0.1));
            border: 2px solid #FF3B30;
            color: #FF3B30;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 30px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(3, 192, 60, 0.1);
            position: relative;
            overflow: hidden;
            margin-bottom: 50px;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(3, 192, 60, 0.03), transparent);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .form-content {
            position: relative;
            z-index: 1;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease-out backwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #1F1E26;
            margin-bottom: 10px;
        }

        .required {
            color: #03C03C;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea,
        select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #F2F3F4;
            border-radius: 15px;
            font-size: 15px;
            font-family: inherit;
            color: #1F1E26;
            background: white;
            transition: all 0.3s;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus,
        textarea:focus,
        select:focus {
            border-color: #03C03C;
            box-shadow: 0 0 0 4px rgba(3, 192, 60, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* File Upload */
        .file-upload {
            position: relative;
            display: block;
            width: 100%;
            padding: 30px;
            border: 2px dashed #F2F3F4;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: rgba(3, 192, 60, 0.02);
        }

        .file-upload:hover {
            border-color: #03C03C;
            background: rgba(3, 192, 60, 0.05);
        }

        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            top: 0;
            left: 0;
        }

        .file-upload-content {
            pointer-events: none;
        }

        .upload-icon {
            font-size: 40px;
            margin-bottom: 10px;
            color: #03C03C;
        }

        .file-upload p {
            color: #4A4952;
            font-size: 14px;
        }

        .file-name {
            margin-top: 10px;
            color: #03C03C;
            font-weight: 600;
        }

        /* Submit Button */
        .submit-section {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            justify-content: center;
            animation: fadeInUp 0.8s ease-out 0.7s backwards;
        }

        .btn-submit {
            padding: 18px 50px;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(3, 192, 60, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(3, 192, 60, 0.4);
        }

        .btn-cancel {
            padding: 18px 50px;
            background: white;
            color: #1F1E26;
            border: 2px solid #F2F3F4;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .btn-cancel:hover {
            border-color: #03C03C;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Courses Display Section */
        .courses-section {
            margin-top: 60px;
        }

        .section-title {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 30px;
            text-align: center;
            background: linear-gradient(135deg, #1F1E26 0%, #4A4952 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .course-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(3, 192, 60, 0.1);
            transition: all 0.3s;
            animation: fadeInUp 0.6s ease-out backwards;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .course-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
        }

        .course-title {
            font-size: 22px;
            font-weight: 700;
            color: #1F1E26;
            margin-bottom: 10px;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .meta-badge {
            padding: 5px 12px;
            background: rgba(3, 192, 60, 0.1);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #03C03C;
        }

        .course-description {
            color: #4A4952;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #F2F3F4;
        }

        .course-price {
            font-size: 24px;
            font-weight: 700;
            color: #03C03C;
        }

        .course-instructor {
            font-size: 14px;
            color: #4A4952;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
            }

            .container {
                padding: 0 20px;
                margin-top: 100px;
            }

            .form-header h1 {
                font-size: 36px;
            }

            .form-card {
                padding: 30px 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .submit-section {
                flex-direction: column;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="orb orb1"></div>
        <div class="orb orb2"></div>
        <div class="orb orb3"></div>
    </div>

    <header id="header">
        <div class="header-content">
            <div class="logo">Start Education</div>
            <nav>
            </nav>
            <div class="auth-buttons">
                <a href="admin.html" class="btn-signup">← Back to Courses</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="form-header">
            <h1>Create New <span class="highlight">Course</span></h1>
            <p>Fill in the details below to create an engaging learning experience for your students</p>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">✓ <?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">✗ <?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form id="courseForm" class="form-content" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="courseName">Course Name <span class="required">*</span></label>
                    <input type="text" id="courseName" name="courseName" placeholder="e.g., Advanced Web Development" required>
                </div>

                <div class="form-group">
                    <label for="courseDescription">Course Description <span class="required">*</span></label>
                    <textarea id="courseDescription" name="courseDescription" placeholder="Describe what students will learn in this course..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="instructor">Instructor Name <span class="required">*</span></label>
                        <input type="text" id="instructor" name="instructor" placeholder="John Doe" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="programming">Programming</option>
                            <option value="design">Design</option>
                            <option value="business">Business</option>
                            <option value="marketing">Marketing</option>
                            <option value="photography">Photography</option>
                            <option value="music">Music</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="duration">Duration (Hours) <span class="required">*</span></label>
                        <input type="number" id="duration" name="duration" min="1" placeholder="40" required>
                    </div>

                    <div class="form-group">
                        <label for="courseType">Course Type <span class="required">*</span></label>
                        <select id="courseType" name="courseType" required>
                            <option value="paid">Paid Course</option>
                            <option value="free">Free Course</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="priceGroup">
                    <label for="price">Price ($) <span class="required">*</span></label>
                    <input type="number" id="price" name="price" min="0" step="0.01" placeholder="99.99" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="startDate">Start Date <span class="required">*</span></label>
                        <input type="date" id="startDate" name="startDate" required>
                    </div>

                    <div class="form-group">
                        <label for="startTime">Start Time <span class="required">*</span></label>
                        <input type="time" id="startTime" name="startTime" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="courseImage">Course Thumbnail Image</label>
                    <label class="file-upload">
                        <input type="file" id="courseImage" name="courseImage" accept="image/*">
                        <div class="file-upload-content">
                            <div class="upload-icon">📁</div>
                            <p>Click to upload or drag and drop</p>
                            <p>style="font-size: 12px; margin-top: 5px; color: #A2D43D;">PNG, JPG or WEBP (max. 5MB)</p>
                            <div class="file-name" id="fileName"></div>
                        </div>
                    </label>
                </div>

                <div class="form-group">
                    <label for="zoomLink">Zoom Meeting Link</label>
                    <input type="text" id="zoomLink" name="zoomLink" placeholder="https://zoom.us/j/...">
                </div>

                <div class="submit-section">
                    <button type="button" class="btn-cancel" onclick="resetForm()">Cancel</button>
                    <button type="submit" class="btn-submit">Create Course</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Header scroll effect
        const header = document.getElementById('header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Course type handler (Free/Paid)
        const courseTypeSelect = document.getElementById('courseType');
        const priceGroup = document.getElementById('priceGroup');
        const priceInput = document.getElementById('price');

        courseTypeSelect.addEventListener('change', (e) => {
            if (e.target.value === 'free') {
                priceGroup.style.display = 'none';
                priceInput.removeAttribute('required');
                priceInput.value = '0';
            } else {
                priceGroup.style.display = 'block';
                priceInput.setAttribute('required', 'required');
                priceInput.value = '';
            }
        });

        // File upload handler
        const fileInput = document.getElementById('courseImage');
        const fileNameDisplay = document.getElementById('fileName');

        fileInput.addEventListener('change', (e) => {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                fileNameDisplay.textContent = `Selected: ${fileName}`;
            } else {
                fileNameDisplay.textContent = '';
            }
        });

        // Reset form function
        function resetForm() {
            document.getElementById('courseForm').reset();
            fileNameDisplay.textContent = '';
        }

        // Set minimum date to today
        const startDateInput = document.getElementById('startDate');
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;

        // Auto-scroll to success message if present
        <?php if ($successMessage): ?>
        window.scrollTo({ top: 0, behavior: 'smooth' });
        <?php endif; ?>
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>