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

$successMessage = "";
$errorMessage = "";

// Handle AJAX Delete Request
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $courseId = (int)$_POST['course_id'];
    
    // Get image path before deleting
    $imgQuery = "SELECT course_image FROM courses WHERE id = ?";
    $imgStmt = mysqli_prepare($conn, $imgQuery);
    mysqli_stmt_bind_param($imgStmt, "i", $courseId);
    mysqli_stmt_execute($imgStmt);
    $imgResult = mysqli_stmt_get_result($imgStmt);
    $imgData = mysqli_fetch_assoc($imgResult);
    
    // Delete from database
    $deleteQuery = "DELETE FROM courses WHERE id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $courseId);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete image file if exists
        if ($imgData && $imgData['course_image'] && file_exists($imgData['course_image'])) {
            unlink($imgData['course_image']);
        }
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting course']);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// Handle AJAX Update Request
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $courseId = (int)$_POST['course_id'];
    $courseName = mysqli_real_escape_string($conn, $_POST['courseName']);
    $courseDescription = mysqli_real_escape_string($conn, $_POST['courseDescription']);
    $instructor = mysqli_real_escape_string($conn, $_POST['instructor']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $duration = (int)$_POST['duration'];
    $courseType = mysqli_real_escape_string($conn, $_POST['courseType']);
    $price = ($courseType === 'free') ? 0 : (float)$_POST['price'];
    $startDate = mysqli_real_escape_string($conn, $_POST['startDate']);
    $startTime = mysqli_real_escape_string($conn, $_POST['startTime']);
    $zoomLink = mysqli_real_escape_string($conn, $_POST['zoomLink']);
    
    $updateQuery = "UPDATE courses SET 
                    course_name = ?, 
                    course_description = ?, 
                    instructor = ?, 
                    category = ?, 
                    duration = ?, 
                    course_type = ?, 
                    price = ?, 
                    start_date = ?, 
                    start_time = ?, 
                    zoom_link = ?
                    WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ssssidssssi", 
        $courseName, $courseDescription, $instructor, $category, 
        $duration, $courseType, $price, $startDate, $startTime, $zoomLink, $courseId
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating course']);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// Fetch all courses
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
    <title>Manage Courses - Start Education</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
    
        /* Header */

        /* Alert Messages */
        .alert {
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            display: none;
            animation: slideDown 0.5s ease-out;
        }

        .alert.show {
            display: block;
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

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            margin-top: 100px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(3, 192, 60, 0.1);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .table-title {
            font-size: 24px;
            font-weight: 700;
            color: #1F1E26;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #F2F3F4;
            border-radius: 30px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }

        .search-input:focus {
            border-color: #03C03C;
            box-shadow: 0 0 0 4px rgba(3, 192, 60, 0.1);
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        thead {
            background: linear-gradient(135deg, rgba(3, 192, 60, 0.1), rgba(162, 212, 61, 0.1));
        }

        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 700;
            color: #1F1E26;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #03C03C;
        }

        td {
            padding: 20px 15px;
            border-bottom: 1px solid #F2F3F4;
            color: #4A4952;
            font-size: 14px;
        }

        tbody tr {
            transition: all 0.3s;
        }

        tbody tr:hover {
            background: rgba(3, 192, 60, 0.02);
        }

        .course-name-cell {
            font-weight: 600;
            color: #1F1E26;
            max-width: 250px;
        }

        .course-thumb {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-free {
            background: rgba(3, 192, 60, 0.1);
            color: #03C03C;
        }

        .badge-paid {
            background: rgba(162, 212, 61, 0.2);
            color: #7BA900;
        }

        .price-cell {
            font-weight: 700;
            color: #03C03C;
            font-size: 16px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-edit {
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            color: white;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(3, 192, 60, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #FF3B30, #FF6B6B);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 59, 48, 0.3);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            animation: fadeIn 0.3s;
        }

        .modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .modal-title {
            font-size: 28px;
            font-weight: 700;
            color: #1F1E26;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #4A4952;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #FF3B30;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1F1E26;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #F2F3F4;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #1F1E26;
            transition: all 0.3s;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #03C03C;
            box-shadow: 0 0 0 4px rgba(3, 192, 60, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-save {
            flex: 1;
            padding: 14px;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(3, 192, 60, 0.3);
        }

        .btn-cancel {
            flex: 1;
            padding: 14px;
            background: white;
            color: #1F1E26;
            border: 2px solid #F2F3F4;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            border-color: #03C03C;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #4A4952;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
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

        @media (max-width: 768px) {
            .table-container {
                padding: 20px 10px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                padding: 25px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div id="alertContainer">
    
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

    

        <div class="table-container">
            <div class="table-header">
                <div class="table-title">All Courses (<?php echo count($courses); ?>)</div>
                <div class="search-box">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search courses...">
                </div>
            </div>

            <?php if (empty($courses)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <h3>No courses found</h3>
                    <p>Start by creating your first course!</p>
                </div>
            <?php else: ?>
                <table id="coursesTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Course Name</th>
                            <th>Instructor</th>
                            <th>Category</th>
                            <th>Duration</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Start Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr data-course-id="<?php echo $course['id']; ?>">
                            <td>
                                <?php if ($course['course_image']): ?>
                                    <img src="<?php echo htmlspecialchars($course['course_image']); ?>" alt="Course" class="course-thumb">
                                <?php else: ?>
                                    <div class="course-thumb"></div>
                                <?php endif; ?>
                            </td>
                            <td class="course-name-cell"><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($course['category'])); ?></td>
                            <td><?php echo htmlspecialchars($course['duration']); ?> hrs</td>
                            <td>
                                <span class="badge badge-<?php echo $course['course_type']; ?>">
                                    <?php echo ucfirst(htmlspecialchars($course['course_type'])); ?>
                                </span>
                            </td>
                            <td class="price-cell">
                                <?php echo $course['course_type'] === 'free' ? 'FREE' : '$' . number_format($course['price'], 2); ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($course['start_date'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-edit" onclick="editCourse(<?php echo $course['id']; ?>)">
                                        ✏️ Edit
                                    </button>
                                    <button class="btn btn-delete" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Course</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form id="editForm">
                <input type="hidden" id="editCourseId">
                
                <div class="form-group">
                    <label for="editCourseName">Course Name</label>
                    <input type="text" id="editCourseName" required>
                </div>

                <div class="form-group">
                    <label for="editCourseDescription">Description</label>
                    <textarea id="editCourseDescription" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editInstructor">Instructor</label>
                        <input type="text" id="editInstructor" required>
                    </div>
                    <div class="form-group">
                        <label for="editCategory">Category</label>
                        <select id="editCategory" required>
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
                        <label for="editDuration">Duration (Hours)</label>
                        <input type="number" id="editDuration" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="editCourseType">Course Type</label>
                        <select id="editCourseType" required>
                            <option value="paid">Paid Course</option>
                            <option value="free">Free Course</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="editPriceGroup">
                    <label for="editPrice">Price ($)</label>
                    <input type="number" id="editPrice" min="0" step="0.01">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editStartDate">Start Date</label>
                        <input type="date" id="editStartDate" required>
                    </div>
                    <div class="form-group">
                        <label for="editStartTime">Start Time</label>
                        <input type="time" id="editStartTime" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editZoomLink">Zoom Link</label>
                    <input type="text" id="editZoomLink">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Course data stored for quick access
        const coursesData = <?php echo json_encode($courses); ?>;

        // Show alert message
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Open edit modal
        function editCourse(courseId) {
            const course = coursesData.find(c => c.id == courseId);
            if (!course) return;

            document.getElementById('editCourseId').value = course.id;
            document.getElementById('editCourseName').value = course.course_name;
            document.getElementById('editCourseDescription').value = course.course_description;
            document.getElementById('editInstructor').value = course.instructor;
            document.getElementById('editCategory').value = course.category;
            document.getElementById('editDuration').value = course.duration;
            document.getElementById('editCourseType').value = course.course_type;
            document.getElementById('editPrice').value = course.price;
            document.getElementById('editStartDate').value = course.start_date;
            document.getElementById('editStartTime').value = course.start_time;
            document.getElementById('editZoomLink').value = course.zoom_link || '';

            // Handle price field visibility
            togglePriceField();

            document.getElementById('editModal').classList.add('active');
        }

        // Close modal
        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        // Toggle price field based on course type
        function togglePriceField() {
            const courseType = document.getElementById('editCourseType').value;
            const priceGroup = document.getElementById('editPriceGroup');
            const priceInput = document.getElementById('editPrice');

            if (courseType === 'free') {
                priceGroup.style.display = 'none';
                priceInput.value = '0';
            } else {
                priceGroup.style.display = 'block';
            }
        }

        document.getElementById('editCourseType').addEventListener('change', togglePriceField);

        // Handle edit form submission
        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('course_id', document.getElementById('editCourseId').value);
            formData.append('courseName', document.getElementById('editCourseName').value);
            formData.append('courseDescription', document.getElementById('editCourseDescription').value);
            formData.append('instructor', document.getElementById('editInstructor').value);
            formData.append('category', document.getElementById('editCategory').value);
            formData.append('duration', document.getElementById('editDuration').value);
            formData.append('courseType', document.getElementById('editCourseType').value);
            formData.append('price', document.getElementById('editPrice').value);
            formData.append('startDate', document.getElementById('editStartDate').value);
            formData.append('startTime', document.getElementById('editStartTime').value);
            formData.append('zoomLink', document.getElementById('editZoomLink').value);

            try {
                const response = await fetch('manage_courses.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred', 'error');
            }
        });

        // Delete course
        async function deleteCourse(courseId) {
            if (!confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('course_id', courseId);

            try {
                const response = await fetch('manage_courses.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    
                    // Remove row from table
                    const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            row.remove();
                            // Check if table is empty
                            const tbody = document.querySelector('#coursesTable tbody');
                            if (tbody.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred', 'error');
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#coursesTable tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Close modal on outside click
        document.getElementById('editModal').addEventListener('click', (e) => {
            if (e.target.id === 'editModal') {
                closeModal();
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>