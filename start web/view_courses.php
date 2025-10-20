<?php
// Database connection
$server = "localhost";
$user = "root";
$password = "";
$database = "start_education";

$conn = mysqli_connect($server, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get filter and search parameters
$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$typeFilter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

// Build query with filters
$query = "SELECT * FROM courses WHERE 1=1";

if ($searchTerm) {
    $query .= " AND (course_name LIKE '%$searchTerm%' OR course_description LIKE '%$searchTerm%' OR instructor LIKE '%$searchTerm%')";
}

if ($categoryFilter) {
    $query .= " AND category = '$categoryFilter'";
}

if ($typeFilter) {
    $query .= " AND course_type = '$typeFilter'";
}

$query .= " ORDER BY start_date DESC, start_time DESC";

$result = mysqli_query($conn, $query);
$courses = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

// Get statistics
$statsQuery = "SELECT 
    COUNT(*) as total_courses,
    COUNT(CASE WHEN course_type = 'free' THEN 1 END) as free_courses,
    COUNT(CASE WHEN course_type = 'paid' THEN 1 END) as paid_courses,
    SUM(CASE WHEN course_type = 'paid' THEN price ELSE 0 END) as total_revenue
FROM courses";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses - Start Education</title>
    <link rel="stylesheet" href="css/style.css">
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
        }

        .orb2 {
            width: 350px;
            height: 350px;
            background: #A2D43D;
            bottom: -100px;
            right: -100px;
            animation-delay: 5s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, 50px) scale(1.1); }
            50% { transform: translate(30px, -30px) scale(0.9); }
            75% { transform: translate(-30px, 30px) scale(1.05); }
        }
        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            color: white;
            box-shadow: 0 5px 20px rgba(3, 192, 60, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(3, 192, 60, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #1F1E26;
            border: 2px solid #F2F3F4;
        }

        .btn-secondary:hover {
            border-color: #03C03C;
        }

        .btn-edit {
            background: linear-gradient(135deg, #A2D43D, #6B8E23);
            color: white;
            box-shadow: 0 5px 20px rgba(162, 212, 61, 0.3);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(162, 212, 61, 0.4);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 60px;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 40px;
            animation: fadeInUp 0.6s ease-out;
        }

        .page-title {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #1F1E26 0%, #4A4952 100%);
            -webkit-text-fill-color: transparent;
        }

        .page-subtitle {
            font-size: 18px;
            color: #4A4952;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            animation: fadeInUp 0.6s ease-out 0.2s backwards;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(3, 192, 60, 0.1);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 800;
            color: #03C03C;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #4A4952;
            font-weight: 600;
        }

        /* Filters Section */
        .filters-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-top: 100px;
            margin-bottom: 40px;
            animation: fadeInUp 0.6s ease-out 0.4s backwards;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .filter-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1F1E26;
            margin-bottom: 8px;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #F2F3F4;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.3s;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #03C03C;
            box-shadow: 0 0 0 4px rgba(3, 192, 60, 0.1);
        }

        .btn-filter {
            padding: 12px 25px;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(3, 192, 60, 0.3);
        }

        /* Courses Grid */
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .course-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            animation: fadeInUp 0.6s ease-out backwards;
            border: 1px solid rgba(3, 192, 60, 0.1);
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .course-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
        }

        .course-content {
            padding: 25px;
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .course-title {
            font-size: 22px;
            font-weight: 700;
            color: #1F1E26;
            margin-bottom: 8px;
            line-height: 1.3;
            flex: 1;
        }

        .course-price {
            font-size: 24px;
            font-weight: 800;
            color: #03C03C;
            white-space: nowrap;
            margin-left: 15px;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-category {
            background: rgba(3, 192, 60, 0.1);
            color: #03C03C;
        }

        .badge-duration {
            background: rgba(162, 212, 61, 0.2);
            color: #6B8E23;
        }

        .badge-type {
            background: rgba(74, 73, 82, 0.1);
            color: #1F1E26;
        }

        .course-description {
            color: #4A4952;
            line-height: 1.6;
            font-size: 14px;
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

        .course-instructor {
            font-size: 13px;
            color: #4A4952;
        }

        .course-instructor strong {
            color: #1F1E26;
            font-weight: 600;
        }

        .course-date {
            font-size: 12px;
            color: #4A4952;
            text-align: right;
        }

        .course-links {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #F2F3F4;
        }

        .video-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #FF0000;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 15px;
            background: rgba(255, 0, 0, 0.05);
            border-radius: 10px;
            transition: all 0.3s;
            margin-bottom: 10px;
        }

        .video-link:hover {
            background: rgba(255, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .zoom-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #03C03C;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 15px;
            background: rgba(3, 192, 60, 0.05);
            border-radius: 10px;
            transition: all 0.3s;
            margin-bottom: 10px;
        }

        .zoom-link:hover {
            background: rgba(3, 192, 60, 0.1);
            transform: translateX(5px);
        }

        /* View Records Section */
        .view-records-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #6B4CE6;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 15px;
            background: rgba(107, 76, 230, 0.05);
            border-radius: 10px;
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .view-records-btn:hover {
            background: rgba(107, 76, 230, 0.1);
            border-color: rgba(107, 76, 230, 0.2);
        }

        .records-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #F2F3F4;
            display: none;
        }

        .records-section.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 500px;
            }
        }

        .records-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .records-title {
            font-size: 14px;
            font-weight: 700;
            color: #1F1E26;
        }

        .record-count {
            font-size: 12px;
            color: #6B4CE6;
            background: rgba(107, 76, 230, 0.1);
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
        }

        .record-item {
            background: #F9FAFB;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 3px solid #6B4CE6;
            transition: all 0.3s;
        }

        .record-item:hover {
            background: #F3F4F6;
            transform: translateX(3px);
        }

        .record-date-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            color: #6B4CE6;
            background: white;
            padding: 4px 8px;
            border-radius: 8px;
            margin-bottom: 6px;
        }

        .record-title {
            font-size: 13px;
            font-weight: 600;
            color: #1F1E26;
            margin-bottom: 4px;
        }

        .record-description {
            font-size: 12px;
            color: #4A4952;
            margin-bottom: 8px;
        }

        .record-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #FF0000;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            background: white;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .record-link:hover {
            background: rgba(255, 0, 0, 0.05);
            transform: translateX(3px);
        }

        .no-records {
            text-align: center;
            padding: 20px;
            color: #4A4952;
            font-size: 13px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            animation: fadeInUp 0.6s ease-out;
        }

        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 28px;
            font-weight: 700;
            color: #1F1E26;
            margin-bottom: 10px;
        }

        .empty-text {
            font-size: 16px;
            color: #4A4952;
            margin-bottom: 30px;
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

        /* Responsive */
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
            }

            .container {
                padding: 20px;
            }

            .page-title {
                font-size: 32px;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }

            .header-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
  <div class="container">
    <div class="bg-animation">
        <div class="orb orb1"></div>
        <div class="orb orb2"></div>
    </div>

    <header id="header">
        <div class="header-content">
            <a href="index.html">
            <div class="logo">Start Education</div>
            </a>
            <nav>
                <a href="index.php">home</a>
                <a href="view_courses.php">courses</a>
                <a href="about_us.php">about us</a>
                <a href="contact_section.php">contact us</a>
                <a href="#lectures">lectures</a>
            </nav>
        </div>
    </header>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="search">Search Courses</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, instructor..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="programming" <?php echo $categoryFilter === 'programming' ? 'selected' : ''; ?>>Programming</option>
                            <option value="design" <?php echo $categoryFilter === 'design' ? 'selected' : ''; ?>>Design</option>
                            <option value="business" <?php echo $categoryFilter === 'business' ? 'selected' : ''; ?>>Business</option>
                            <option value="marketing" <?php echo $categoryFilter === 'marketing' ? 'selected' : ''; ?>>Marketing</option>
                            <option value="photography" <?php echo $categoryFilter === 'photography' ? 'selected' : ''; ?>>Photography</option>
                            <option value="music" <?php echo $categoryFilter === 'music' ? 'selected' : ''; ?>>Music</option>
                            <option value="other" <?php echo $categoryFilter === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="type">Course Type</label>
                        <select id="type" name="type">
                            <option value="">All Types</option>
                            <option value="free" <?php echo $typeFilter === 'free' ? 'selected' : ''; ?>>Free</option>
                            <option value="paid" <?php echo $typeFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter">🔍 Filter</button>
                </div>
            </form>
        </div>

        <!-- Courses Grid -->
        <?php if (!empty($courses)): ?>
            <div class="courses-grid">
                <?php foreach ($courses as $index => $course): ?>
                <?php
                    // Get course records
                    $courseId = $course['id'];
                    $recordsQuery = "SELECT * FROM course_records WHERE course_id = $courseId ORDER BY record_date DESC";
                    $recordsResult = mysqli_query($conn, $recordsQuery);
                    $records = [];
                    if ($recordsResult) {
                        while ($record = mysqli_fetch_assoc($recordsResult)) {
                            $records[] = $record;
                        }
                    }
                ?>
                <div class="course-card" style="animation-delay: <?php echo $index * 0.05; ?>s;">
                    <?php if ($course['course_image'] && file_exists($course['course_image'])): ?>
                        <img src="<?php echo htmlspecialchars($course['course_image']); ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>" class="course-image">
                    <?php else: ?>
                        <div class="course-image"></div>
                    <?php endif; ?>
                    
                    <div class="course-content">
                        <div class="course-header">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            <div class="course-price">
                                <?php echo $course['course_type'] === 'free' ? 'FREE' : '$' . number_format($course['price'], 2); ?>
                            </div>
                        </div>
                        
                        <div class="course-meta">
                            <span class="badge badge-category"><?php echo htmlspecialchars($course['category']); ?></span>
                            <span class="badge badge-duration"><?php echo htmlspecialchars($course['duration']); ?> hrs</span>
                            <span class="badge badge-type"><?php echo htmlspecialchars($course['course_type']); ?></span>
                        </div>
                        
                        <p class="course-description"><?php echo htmlspecialchars($course['course_description']); ?></p>
                        
                        <div class="course-footer">
                            <div class="course-instructor">
                                <strong>Instructor:</strong><br>
                                <?php echo htmlspecialchars($course['instructor']); ?>
                            </div>
                            <div class="course-date">
                                📅 <?php echo date('M d, Y', strtotime($course['start_date'])); ?><br>
                                🕐 <?php echo date('g:i A', strtotime($course['start_time'])); ?>
                            </div>
                        </div>

                        <div class="course-links">
                            <?php if (!empty($course['video_link'])): ?>
                            <a href="<?php echo htmlspecialchars($course['video_link']); ?>" target="_blank" class="video-link">
                                ▶️ Watch Course Video
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($course['zoom_link'])): ?>
                            <a href="<?php echo htmlspecialchars($course['zoom_link']); ?>" target="_blank" class="zoom-link">
                                🎥 Join Zoom Meeting
                            </a>
                            <?php endif; ?>

                            <!-- View Records Button -->
                            <div class="view-records-btn" onclick="toggleRecords(<?php echo $courseId; ?>)">
                                📼 View Records (<?php echo count($records); ?>)
                            </div>
                        </div>

                        <!-- Records Section -->
                        <div class="records-section" id="records-<?php echo $courseId; ?>">
                            <div class="records-header">
                                <span class="records-title">📚 Course Recordings</span>
                                <span class="record-count"><?php echo count($records); ?> recordings</span>
                            </div>

                            <?php if (!empty($records)): ?>
                                <?php foreach ($records as $record): ?>
                                <div class="record-item">
                                    <div class="record-date-badge">
                                        📅 <?php echo date('M d, Y', strtotime($record['record_date'])); ?>
                                    </div>
                                    <div class="record-title"><?php echo htmlspecialchars($record['record_title']); ?></div>
                                    <?php if (!empty($record['description'])): ?>
                                    <div class="record-description"><?php echo htmlspecialchars($record['description']); ?></div>
                                    <?php endif; ?>
                                    <a href="<?php echo htmlspecialchars($record['youtube_link']); ?>" target="_blank" class="record-link">
                                        ▶️ Watch Recording
                                        <?php if (!empty($record['duration'])): ?>
                                        <span>(<?php echo htmlspecialchars($record['duration']); ?>)</span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-records">
                                    📹 No recordings available yet
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h2 class="empty-title">No Courses Found</h2>
                <p class="empty-text">Start creating amazing courses for your students!</p>
                <a href="A_create_course.php" class="btn btn-primary">+ Create Your First Course</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleRecords(courseId) {
            const recordsSection = document.getElementById('records-' + courseId);
            recordsSection.classList.toggle('active');
        }
    </script>
        <!-- Footer Section -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <!-- About Section -->
                <div class="footer-section footer-about">
                    <h3>Start Education</h3>
                    <p>Empowering learners worldwide with quality education. Access live Zoom sessions, recorded lectures, and comprehensive course materials all in one place.</p>
                    <div class="footer-social">
                        <a href="#" class="social-link" title="Facebook">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="social-link" title="Twitter">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="#" class="social-link" title="LinkedIn">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="#" class="social-link" title="Instagram">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="view_courses.php">All Courses</a></li>
                        <li><a href="about_us.php">About Us</a></li>
                        <li><a href="contact_section.php">Contact</a></li>
                        <li><a href="#lectures">Lectures</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="footer-section">
                    <h3>Categories</h3>
                    <ul class="footer-links">
                        <li><a href="#">Web Development</a></li>
                        <li><a href="#">Data Science</a></li>
                        <li><a href="#">Business</a></li>
                        <li><a href="#">Design</a></li>
                        <li><a href="#">Marketing</a></li>
                    </ul>
                </div>

                <!-- Contact & Newsletter -->
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul class="footer-contact-info">
                        <li>support@starteducation.com</li>
                        <li>+1 (555) 123-4567</li>
                        <li>123 Education St, Learning City</li>
                    </ul>
                    <form class="newsletter-form" onsubmit="return false;">
                        <input type="email" placeholder="Your email" class="newsletter-input" required>
                        <button type="submit" class="newsletter-btn">Subscribe</button>
                    </form>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="copyright">&copy; 2025 Start Education. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
<?php mysqli_close($conn); ?>