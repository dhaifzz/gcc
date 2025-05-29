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
            case 'update':
                $id = $_POST['contact_id'];
                $value = $_POST['contact_value'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE contact_info SET value = :value WHERE id = :id");
                    if ($stmt->execute(['value' => $value, 'id' => $id])) {
                        $message = "Contact information updated successfully!";
                    }
                } catch (PDOException $e) {
                    $error = "Error updating contact information: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch all contact information
try {
    $stmt = $pdo->query("SELECT * FROM contact_info ORDER BY display_order ASC");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching contact information: " . $e->getMessage();
    $contacts = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Contact Information - GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        .contact-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #236641;
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .contact-form {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .save-btn {
            background-color: #236641;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }

        .save-btn:hover {
            background-color: #1a4d31;
        }

        .notification {
            padding: 15px;
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

        .preview {
            margin-top: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .preview i {
            font-size: 20px;
            margin-right: 10px;
            color: #236641;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage Contact Information</h1>
            <div class="header-buttons">
                <a href="/gcc/users/admin/content.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Content
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="notification success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="notification error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php foreach ($contacts as $contact): ?>
            <div class="contact-section">
                <h2 class="section-title"><?php echo htmlspecialchars($contact['title']); ?></h2>
                <form method="POST" class="contact-form">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                    
                    <div class="form-group">
                        <?php if ($contact['type'] === 'description'): ?>
                            <label for="value_<?php echo $contact['id']; ?>">Content:</label>
                            <textarea name="contact_value" id="value_<?php echo $contact['id']; ?>" required><?php echo htmlspecialchars($contact['value']); ?></textarea>
                        <?php else: ?>
                            <label for="value_<?php echo $contact['id']; ?>">Value:</label>
                            <input type="text" name="contact_value" id="value_<?php echo $contact['id']; ?>" value="<?php echo htmlspecialchars($contact['value']); ?>" required>
                        <?php endif; ?>
                    </div>

                    <?php if ($contact['type'] !== 'description'): ?>
                        <div class="preview">
                            <i class="<?php echo $contact['icon']; ?>"></i>
                            <span><?php echo htmlspecialchars($contact['value']); ?></span>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="save-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html> 