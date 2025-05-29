<?php
require_once '../../font/font.php';
require_once '../../database/database.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $status = $_POST['status'];
                $title = $_POST['title'];
                $campus = $_POST['campus'];
                $category = $_POST['category'];
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "../../img/team-gcc/";
                    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_path = $new_filename;
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                }
                
                if (empty($error)) {
                    $stmt = $pdo->prepare("INSERT INTO team_members (name, image_path, status, title, campus, category) 
                                         VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$name, $image_path, $status, $title, $campus, $category])) {
                        $message = "Team member added successfully!";
                    } else {
                        $error = "Error adding team member.";
                    }
                }
                break;
                
            case 'edit':
                $id = $_POST['member_id'];
                $name = $_POST['name'];
                $status = $_POST['status'];
                $title = $_POST['title'];
                $campus = $_POST['campus'];
                $category = $_POST['category'];
                
                // Handle image upload for edit
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "../../img/team-gcc/";
                    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        // Get old image to delete
                        $stmt = $pdo->prepare("SELECT image_path FROM team_members WHERE id = ?");
                        $stmt->execute([$id]);
                        $old_image = $stmt->fetchColumn();
                        
                        // Delete old image if it exists
                        if ($old_image && file_exists("../../img/team-gcc/" . $old_image)) {
                            unlink("../../img/team-gcc/" . $old_image);
                        }
                        
                        // Update with new image
                        $stmt = $pdo->prepare("UPDATE team_members SET name = ?, image_path = ?, status = ?, title = ?, campus = ?, category = ? WHERE id = ?");
                        $stmt->execute([$name, $new_filename, $status, $title, $campus, $category, $id]);
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    // Update without changing image
                    $stmt = $pdo->prepare("UPDATE team_members SET name = ?, status = ?, title = ?, campus = ?, category = ? WHERE id = ?");
                    $stmt->execute([$name, $status, $title, $campus, $category, $id]);
                }
                
                if (empty($error)) {
                    $message = "Team member updated successfully!";
                }
                break;
                
            case 'delete':
                $id = $_POST['member_id'];
                
                // Get image path before deleting
                $stmt = $pdo->prepare("SELECT image_path FROM team_members WHERE id = ?");
                $stmt->execute([$id]);
                $image_path = $stmt->fetchColumn();
                
                // Delete the member
                $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
                if ($stmt->execute([$id])) {
                    // Delete associated image
                    if ($image_path && file_exists("../../img/team-gcc/" . $image_path)) {
                        unlink("../../img/team-gcc/" . $image_path);
                    }
                    $message = "Team member deleted successfully!";
                } else {
                    $error = "Error deleting team member.";
                }
                break;
        }
    }
}

// Fetch all team members
$stmt = $pdo->query("SELECT * FROM team_members ORDER BY campus, category, display_order");
$team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Team Members - GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add your CSS styles here */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 0;
        }
        
        .header-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .back-button {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
        
        .add-button {
            background-color: #236641;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .add-button:hover {
            background-color: #1a4d31;
        }

        .add-button i, .back-button i {
            font-size: 16px;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .team-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        
        .team-card:hover {
            transform: translateY(-5px);
        }
        
        .team-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .team-info {
            padding: 15px;
        }
        
        .team-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 10px;
        }
        
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .edit-btn {
            background-color: #ffc107;
            color: black;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
        }
        
        .modal-content {
            background-color: white;
            position: relative;
            margin: 1.75rem auto;
            padding: 30px;
            width: 95%;
            max-width: 550px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            cursor: pointer;
            font-size: 24px;
            color: #666;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #000;
        }

        #modalTitle {
            margin-top: 0;
            margin-bottom: 20px;
            padding-right: 30px;
            color: #236641;
        }
        
        .form-group {
            margin-bottom: 20px;
            max-width: 100%;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input, 
        .form-group select,
        .form-group textarea {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            height: 42px;
            line-height: 1.5;
        }

        .form-group input[type="file"] {
            padding: 8px;
            height: auto;
            line-height: 1;
            border: 1px dashed #ddd;
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .form-group input[type="file"]:hover {
            background-color: #f0f1f2;
            border-color: #236641;
        }

        .form-group input:focus, 
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #236641;
            outline: none;
            box-shadow: 0 0 5px rgba(35, 102, 65, 0.2);
        }

        /* Add button styling update */
        .add-button {
            width: 100%;
            height: 42px;
            margin-top: 10px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }

        .add-button:hover {
            background-color: #1a4d31;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .modal-content {
                padding: 20px;
                margin: 1rem auto;
            }
            
            .form-group input, 
            .form-group select,
            .form-group textarea {
                height: 38px;
                padding: 8px 10px;
            }
            
            .add-button {
                height: 38px;
            }
        }
        
        .notification {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage Team Members</h1>
            <div class="header-buttons">
                <a href="/gcc/users/admin/content.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Content
                </a>
                <button class="add-button" onclick="showAddModal()">
                    <i class="fas fa-plus"></i> Add New Member
                </button>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="notification success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="notification error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="team-grid">
            <?php foreach ($team_members as $member): ?>
                <div class="team-card">
                    <img src="../../img/team-gcc/<?php echo htmlspecialchars($member['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($member['name']); ?>" 
                         class="team-image" style="border: 1px solid #ccc; border-radius: 10px;">
                    <div class="team-info">
                        <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                        <?php if ($member['status']): ?>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($member['status']); ?></p>
                        <?php endif; ?>
                        <?php if ($member['title']): ?>
                            <p><strong>Title:</strong> <?php echo htmlspecialchars($member['title']); ?></p>
                        <?php endif; ?>
                        <p><strong>Campus:</strong> <?php echo ucfirst(htmlspecialchars($member['campus'])); ?></p>
                    </div>
                    <div class="team-actions">
                        <button class="edit-btn" onclick="showEditModal(<?php echo htmlspecialchars(json_encode($member)); ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="delete-btn" onclick="confirmDelete(<?php echo $member['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="memberModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Team Member</h2>
            <form id="memberForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="member_id" id="memberId">
                
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="image">Profile Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="status">Status (Optional):</label>
                    <input type="text" id="status" name="status">
                </div>
                
                <div class="form-group">
                    <label for="title">Title/Department (Optional):</label>
                    <input type="text" id="title" name="title">
                </div>
                
                <div class="form-group">
                    <label for="campus">Campus:</label>
                    <select id="campus" name="campus" required>
                        <option value="main">Main Campus</option>
                        <option value="esu">ESU Campus</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="director">Director</option>
                        <option value="counselor">Guidance Counselor</option>
                        <option value="staff">Guidance Staff</option>
                        <option value="coordinator">Coordinator</option>
                    </select>
                </div>
                
                <button type="submit" class="add-button">
                    <i class="fas fa-save"></i> Save Member
                </button>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Team Member';
            document.getElementById('formAction').value = 'add';
            document.getElementById('memberForm').reset();
            document.getElementById('memberId').value = '';
            document.getElementById('memberModal').style.display = 'block';
        }
        
        function showEditModal(member) {
            document.getElementById('modalTitle').textContent = 'Edit Team Member';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('memberId').value = member.id;
            document.getElementById('name').value = member.name;
            document.getElementById('status').value = member.status || '';
            document.getElementById('title').value = member.title || '';
            document.getElementById('campus').value = member.campus;
            document.getElementById('category').value = member.category;
            document.getElementById('memberModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('memberModal').style.display = 'none';
        }
        
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this team member?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="member_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('memberModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html> 