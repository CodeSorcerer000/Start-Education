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

$message = '';
$messageType = '';

// Handle form submission for adding records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $courseId = mysqli_real_escape_string($conn, $_POST['course_id']);
    $recordDate = mysqli_real_escape_string($conn, $_POST['record_date']);
    $recordTitle = mysqli_real_escape_string($conn, $_POST['record_title']);
    $youtubeLink = mysqli_real_escape_string($conn, $_POST['youtube_link']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);

    $insertQuery = "INSERT INTO course_records (course_id, record_date, record_title, youtube_link, description, duration) 
                    VALUES ('$courseId', '$recordDate', '$recordTitle', '$youtubeLink', '$description', '$duration')";
    
    if (mysqli_query($conn, $insertQuery)) {
        $message = 'Course record added successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error: ' . mysqli_error($conn);
        $messageType = 'error';
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $deleteId = mysqli_real_escape_string($conn, $_GET['delete']);
    $deleteQuery = "DELETE FROM course_records WHERE id = '$deleteId'";
    
    if (mysqli_query($conn, $deleteQuery)) {
        $message = 'Record deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error deleting record: ' . mysqli_error($conn);
        $messageType = 'error';
    }
}

// Get all courses for dropdown
$coursesQuery = "SELECT id, course_name FROM courses ORDER BY course_name ASC";
$coursesResult = mysqli_query($conn, $coursesQuery);

// Get all records with course names
$recordsQuery = "SELECT cr.*, c.course_name 
                 FROM course_records cr 
                 JOIN courses c ON cr.course_id = c.id 
                 ORDER BY cr.record_date DESC";
$recordsResult = mysqli_query($conn, $recordsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course Records - Start Education</title>
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
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-top: 100px;
            margin-bottom: 50px;
        }

        .page-title {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #6B4CE6, #A2D43D);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: 18px;
            color: #4A4952;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #6B4CE6;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 30px;
            transition: all 0.3s;
        }

        .back-link:hover {
            transform: translateX(-5px);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            animation: slideDown 0.3s ease-out;
        }

        .alert-success {
            background: rgba(3, 192, 60, 0.1);
            color: #03C03C;
            border: 2px solid rgba(3, 192, 60, 0.3);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            border: 2px solid rgba(239, 68, 68, 0.3);
        }

        /* Form Section */
        .form-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
            border: 1px solid rgba(107, 76, 230, 0.1);
        }

        .form-title {
            font-size: 24px;
            font-weight: 700;
            color: #1F1E26;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-grid.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1F1E26;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #F2F3F4;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.3s;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #6B4CE6;
            box-shadow: 0 0 0 4px rgba(107, 76, 230, 0.1);
        }

        .btn {
            padding: 14px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6B4CE6, #A2D43D);
            color: white;
            box-shadow: 0 5px 20px rgba(107, 76, 230, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 76, 230, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            box-shadow: 0 5px 20px rgba(239, 68, 68, 0.3);
            padding: 8px 16px;
            font-size: 12px;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        /* Records Table */
        .records-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(107, 76, 230, 0.1);
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #1F1E26;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
        }

        .records-table thead {
            background: linear-gradient(135deg, #6B4CE6, #A2D43D);
            color: white;
        }

        .records-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }

        .records-table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .records-table th:last-child {
            border-radius: 0 10px 0 0;
        }

        .records-table td {
            padding: 15px;
            border-bottom: 1px solid #F2F3F4;
            font-size: 14px;
        }

        .records-table tbody tr {
            transition: all 0.3s;
        }

        .records-table tbody tr:hover {
            background: #F9FAFB;
        }

        .course-name-badge {
            display: inline-block;
            padding: 6px 12px;
            background: rgba(107, 76, 230, 0.1);
            color: #6B4CE6;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .date-badge {
            display: inline-block;
            padding: 6px 12px;
            background: rgba(3, 192, 60, 0.1);
            color: #03C03C;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .youtube-link {
            color: #FF0000;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .youtube-link:hover {
            text-decoration: underline;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #4A4952;
        }

        .empty-icon {
            font-size: 60px;
            margin-bottom: 15px;
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

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 32px;
            }

            .form-section,
            .records-section {
                padding: 20px;
            }

            .records-table {
                font-size: 12px;
            }

            .records-table th,
            .records-table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
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

        <div class="page-header">
            <h1 class="page-title">📼 Manage Course Records</h1>
            <p class="page-subtitle">Add and manage YouTube video recordings for your courses</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <!-- Add Record Form -->
        <div class="form-section">
            <h2 class="form-title">➕ Add New Record</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="course_id">Select Course *</label>
                        <select id="course_id" name="course_id" required>
                            <option value="">Choose a course...</option>
                            <?php 
                            mysqli_data_seek($coursesResult, 0);
                            while ($course = mysqli_fetch_assoc($coursesResult)): 
                            ?>
                            <option value="<?php echo $course['id']; ?>">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="record_date">Record Date *</label>
                        <input type="date" id="record_date" name="record_date" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="record_title">Record Title *</label>
                        <input type="text" id="record_title" name="record_title" placeholder="e.g., Introduction to Python" required>
                    </div>

                    <div class="form-group">
                        <label for="duration">Duration (optional)</label>
                        <input type="text" id="duration" name="duration" placeholder="e.g., 1:30 or 90 mins">
                    </div>
                </div>

                <div class="form-grid full">
                    <div class="form-group">
                        <label for="youtube_link">YouTube Link *</label>
                        <input type="url" id="youtube_link" name="youtube_link" placeholder="https://www.youtube.com/watch?v=..." required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description (optional)</label>
                        <textarea id="description" name="description" placeholder="Brief description of what's covered in this recording..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">📼 Add Record</button>
            </form>
        </div>

        <!-- Records List -->
        <div class="records-section">
            <h2 class="section-title">📚 All Records</h2>
            
            <?php if (mysqli_num_rows($recordsResult) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($record = mysqli_fetch_assoc($recordsResult)): ?>
                        <tr>
                            <td>
                                <span class="course-name-badge">
                                    <?php echo htmlspecialchars($record['course_name']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="date-badge">
                                    <?php echo date('M d, Y', strtotime($record['record_date'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($record['record_title']); ?></td>
                            <td><?php echo htmlspecialchars(substr($record['description'], 0, 50)) . (strlen($record['description']) > 50 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars($record['duration'] ?: '-'); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($record['youtube_link']); ?>" target="_blank" class="youtube-link">
                                    ▶️ Watch
                                </a>
                            </td>
                            <td>
                                <a href="?delete=<?php echo $record['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this record?')">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📹</div>
                <p>No records added yet. Start by adding your first course recording above!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>