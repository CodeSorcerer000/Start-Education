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

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $deleteQuery = "DELETE FROM contact_messages WHERE id = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        $message = "Message deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Error deleting message: " . mysqli_error($conn);
        $messageType = "error";
    }
}

// Handle status update
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $updateQuery = "UPDATE contact_messages SET status = 'read' WHERE id = $id";
    mysqli_query($conn, $updateQuery);
    header("Location: view_contact_us.php");
    exit();
}

if (isset($_GET['mark_replied'])) {
    $id = intval($_GET['mark_replied']);
    $updateQuery = "UPDATE contact_messages SET status = 'replied' WHERE id = $id";
    mysqli_query($conn, $updateQuery);
    header("Location: view_contact_us.php");
    exit();
}

// Fetch all contact messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Get statistics
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) as unread,
    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as `read`,
    SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied
FROM contact_messages";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact Messages - Start Education</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 100px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-card.total {
            border-left: 5px solid #03C03C;
        }

        .stat-card.unread {
            border-left: 5px solid #FF6B6B;
        }

        .stat-card.read {
            border-left: 5px solid #4ECDC4;
        }

        .stat-card.replied {
            border-left: 5px solid #A2D43D;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #6C757D;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            animation: slideDown 0.3s ease-out;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 30px;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            color: white;
        }

        .table-header h2 {
            font-size: 20px;
            font-weight: 700;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: #F8F9FA;
        }

        .data-table th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 700;
            color: #1F1E26;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #E9ECEF;
        }

        .data-table td {
            padding: 18px 20px;
            border-bottom: 1px solid #E9ECEF;
            color: #4A4952;
            font-size: 14px;
        }

        .data-table tbody tr {
            transition: all 0.3s;
        }

        .data-table tbody tr:hover {
            background: #F8F9FA;
            transform: scale(1.01);
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-unread {
            background: #FFE5E5;
            color: #FF6B6B;
        }

        .status-read {
            background: #E0F7F7;
            color: #4ECDC4;
        }

        .status-replied {
            background: #E8F5E0;
            color: #A2D43D;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .btn-view {
            background: #4ECDC4;
            color: white;
        }

        .btn-view:hover {
            background: #3CBDB4;
            transform: translateY(-2px);
        }

        .btn-read {
            background: #FFA726;
            color: white;
        }

        .btn-read:hover {
            background: #FB8C00;
            transform: translateY(-2px);
        }

        .btn-replied {
            background: #A2D43D;
            color: white;
        }

        .btn-replied:hover {
            background: #8BC234;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #FF6B6B;
            color: white;
        }

        .btn-delete:hover {
            background: #FF5252;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6C757D;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease-out;
        }

        .modal-header {
            padding: 25px 30px;
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            color: white;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 22px;
            font-weight: 700;
        }

        .close {
            color: white;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .close:hover {
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .modal-field {
            margin-bottom: 20px;
        }

        .modal-field label {
            font-weight: 700;
            color: #1F1E26;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal-field p {
            color: #4A4952;
            line-height: 1.6;
            background: #F8F9FA;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 3px solid #03C03C;
        }

        /* Animations */
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

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table-container {
                overflow-x: auto;
            }

            .data-table {
                min-width: 800px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #F8F9FA;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #03C03C, #A2D43D);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #A2D43D, #03C03C);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
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

        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            <div class="stat-card unread">
                <div class="stat-number"><?php echo $stats['unread']; ?></div>
                <div class="stat-label">Unread</div>
            </div>
            <div class="stat-card read">
                <div class="stat-number"><?php echo $stats['read']; ?></div>
                <div class="stat-label">Read</div>
            </div>
            <div class="stat-card replied">
                <div class="stat-number"><?php echo $stats['replied']; ?></div>
                <div class="stat-label">Replied</div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>All Contact Messages</h2>
            </div>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-view" onclick="viewMessage(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['name'])); ?>', '<?php echo htmlspecialchars(addslashes($row['email'])); ?>', '<?php echo htmlspecialchars(addslashes($row['phone'])); ?>', '<?php echo htmlspecialchars(addslashes($row['subject'])); ?>', '<?php echo htmlspecialchars(addslashes($row['message'])); ?>', '<?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?>')">
                                            👁️ View
                                        </button>
                                        <?php if ($row['status'] == 'unread'): ?>
                                            <a href="?mark_read=<?php echo $row['id']; ?>" class="btn btn-read">
                                                ✓ Mark Read
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($row['status'] != 'replied'): ?>
                                            <a href="?mark_replied=<?php echo $row['id']; ?>" class="btn btn-replied">
                                                ✉️ Replied
                                            </a>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this message?')">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No Messages Yet</h3>
                    <p>Contact messages will appear here once submitted.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Message Details</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-field">
                    <label>Message ID</label>
                    <p id="modal-id"></p>
                </div>
                <div class="modal-field">
                    <label>Full Name</label>
                    <p id="modal-name"></p>
                </div>
                <div class="modal-field">
                    <label>Email Address</label>
                    <p id="modal-email"></p>
                </div>
                <div class="modal-field">
                    <label>Phone Number</label>
                    <p id="modal-phone"></p>
                </div>
                <div class="modal-field">
                    <label>Subject</label>
                    <p id="modal-subject"></p>
                </div>
                <div class="modal-field">
                    <label>Message</label>
                    <p id="modal-message"></p>
                </div>
                <div class="modal-field">
                    <label>Submitted Date</label>
                    <p id="modal-date"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewMessage(id, name, email, phone, subject, message, date) {
            document.getElementById('modal-id').textContent = '#' + id;
            document.getElementById('modal-name').textContent = name;
            document.getElementById('modal-email').textContent = email;
            document.getElementById('modal-phone').textContent = phone || 'N/A';
            document.getElementById('modal-subject').textContent = subject;
            document.getElementById('modal-message').textContent = message;
            document.getElementById('modal-date').textContent = date;
            
            document.getElementById('messageModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>