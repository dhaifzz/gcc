<?php
require_once '../../font/font.php';
require_once('../../database/database.php');


session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

$user_email = $_SESSION['email'];
$message = '';

// Create carousel directory if it doesn't exist
$dir = "../../img/carousel-img/carousel";
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

// Create services directory if it doesn't exist
$services_dir = "../../img/services";
if (!file_exists($services_dir)) {
    mkdir($services_dir, 0777, true);
}

try {
    $stmt = $pdo->prepare("SELECT first_name FROM users WHERE email = :email");
    $stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
    $stmt->execute();
    $first_name = $stmt->fetchColumn();

    if (!$first_name) {
        $first_name = "User"; 
    }

    $text = "Welcome to GCC Admin, $first_name!";
    $text_length = strlen($text);
    $name_length = strlen($first_name) + 17; 

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Store message in session if needed
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying
}

// Handle carousel image upload
if (isset($_POST['upload_carousel'])) {
    $target_dir = "../../img/carousel-img/carousel/";
    
    $file_extension = strtolower(pathinfo($_FILES["carousel_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["carousel_image"]["tmp_name"]);
    if($check !== false) {
        // Check file size (5MB limit)
        if ($_FILES["carousel_image"]["size"] > 5000000) {
            $_SESSION['message'] = "Sorry, your file is too large. Maximum size is 5MB.";
        } else {
            // Allow certain file formats
            if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
                $_SESSION['message'] = "Sorry, only JPG, JPEG & PNG files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["carousel_image"]["tmp_name"], $target_file)) {
                    $_SESSION['message'] = "The image has been uploaded successfully.";
                } else {
                    $_SESSION['message'] = "Sorry, there was an error uploading your file.";
                }
            }
        }
    } else {
        $_SESSION['message'] = "File is not an image.";
    }
    
    // Redirect after processing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle service image upload and creation
if (isset($_POST['add_service'])) {
    $target_dir = "../../img/services/";
    
    $file_extension = strtolower(pathinfo($_FILES["service_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    $upload_success = false;
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["service_image"]["tmp_name"]);
    if($check !== false) {
        // Check file size (5MB limit)
        if ($_FILES["service_image"]["size"] > 5000000) {
            $_SESSION['message'] = "Sorry, your file is too large. Maximum size is 5MB.";
        } else {
            // Allow certain file formats
            if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
                $_SESSION['message'] = "Sorry, only JPG, JPEG & PNG files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
                    $upload_success = true;
                } else {
                    $_SESSION['message'] = "Sorry, there was an error uploading your file.";
                }
            }
        }
    } else {
        $_SESSION['message'] = "File is not an image.";
    }
    
    if ($upload_success) {
        try {
            // Get the current maximum display_order
            $stmt = $pdo->query("SELECT MAX(display_order) FROM services");
            $max_order = $stmt->fetchColumn();
            $new_order = $max_order ? $max_order + 1 : 1;
            
            // Insert new service
            $stmt = $pdo->prepare("INSERT INTO services (title, description, image_path, link, display_order) VALUES (:title, :description, :image_path, :link, :display_order)");
            
            // Get the link from the form
            $service_link = !empty($_POST['service_link']) ? $_POST['service_link'] : '#';
            
            $image_path = "/gcc/img/services/" . $new_filename;
            $stmt->execute([
                'title' => $_POST['service_title'],
                'description' => $_POST['service_description'],
                'image_path' => $image_path,
                'link' => $service_link,
                'display_order' => $new_order
            ]);
            
            $_SESSION['message'] = "Service added successfully.";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error adding service: " . $e->getMessage();
            // Delete uploaded image if database insert fails
            if (file_exists($target_file)) {
                unlink($target_file);
            }
        }
    }
    
    // Redirect after processing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle service deletion
if (isset($_POST['delete_service'])) {
    try {
        // Get the image path before deleting the record
        $stmt = $pdo->prepare("SELECT image_path FROM services WHERE id = :id");
        $stmt->execute(['id' => $_POST['service_id']]);
        $image_path = $stmt->fetchColumn();
        
        // Delete the record
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = :id");
        $stmt->execute(['id' => $_POST['service_id']]);
        
        // Delete the image file
        $file_path = "../../" . ltrim($image_path, '/gcc/');
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        $_SESSION['message'] = "Service deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting service: " . $e->getMessage();
    }
    
    // Redirect after processing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle service update
if (isset($_POST['update_service'])) {
    try {
        $stmt = $pdo->prepare("UPDATE services SET title = :title, description = :description, link = :link WHERE id = :id");
        $stmt->execute([
            'id' => $_POST['service_id'],
            'title' => $_POST['service_title'],
            'description' => $_POST['service_description'],
            'link' => $_POST['service_link']
        ]);
        
        $_SESSION['message'] = "Service updated successfully.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error updating service: " . $e->getMessage();
    }
    
    // Redirect after processing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle service image update
if (isset($_POST['update_service_image'])) {
    $target_dir = "../../img/services/";
    $service_id = $_POST['service_id'];
    
    // Check if file was uploaded
    if (isset($_FILES["service_image"]) && $_FILES["service_image"]["error"] == 0) {
        $file_extension = strtolower(pathinfo($_FILES["service_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        $upload_success = false;
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["service_image"]["tmp_name"]);
        if($check !== false) {
            // Check file size (5MB limit)
            if ($_FILES["service_image"]["size"] > 5000000) {
                $_SESSION['message'] = "Sorry, your file is too large. Maximum size is 5MB.";
            } else {
                // Allow certain file formats
                if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
                    $_SESSION['message'] = "Sorry, only JPG, JPEG & PNG files are allowed.";
                } else {
                    // Get current image path
                    $stmt = $pdo->prepare("SELECT image_path FROM services WHERE id = :id");
                    $stmt->execute(['id' => $service_id]);
                    $old_image_path = $stmt->fetchColumn();
                    
                    if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
                        // Update database with new image path
                        $new_path = "/gcc/img/services/" . $new_filename;
                        $stmt = $pdo->prepare("UPDATE services SET image_path = :image_path WHERE id = :id");
                        $stmt->execute([
                            'id' => $service_id,
                            'image_path' => $new_path
                        ]);
                        
                        // Delete old image if it exists
                        if ($old_image_path) {
                            $old_file = "../../" . ltrim($old_image_path, '/gcc/');
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }
                        
                        $_SESSION['message'] = "Service image updated successfully.";
                    } else {
                        $_SESSION['message'] = "Sorry, there was an error uploading your file.";
                    }
                }
            }
        } else {
            $_SESSION['message'] = "File is not an image.";
        }
    } else {
        $_SESSION['message'] = "No image file selected.";
    }
    
    // Redirect after processing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle image deletion
if (isset($_POST['delete_image']) && isset($_POST['image_name'])) {
    $image_to_delete = $_POST['image_name'];
    $file_path = "../../img/carousel-img/carousel/" . $image_to_delete;
    
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            $_SESSION['message'] = "Image deleted successfully.";
        } else {
            $_SESSION['message'] = "Error deleting image.";
        }
    } else {
        $_SESSION['message'] = "Image file not found.";
    }
    
    // Redirect after processing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get list of carousel images
$carousel_images = array_diff(scandir("../../img/carousel-img/carousel"), array('.', '..'));

// Get list of services
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY display_order ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $services = [];
    $_SESSION['message'] = "Error fetching services: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/content.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        .content-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            width: 100%;
            max-width: 1200px;
        }

        .section-title {
            color: #236641;
            font-size: 1.5rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .carousel-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .carousel-image-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .carousel-image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .carousel-image-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #dee2e6;
        }

        .carousel-image-card .image-actions {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }

        .upload-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #dee2e6;
            margin-bottom: 30px;
        }

        .upload-form h3 {
            color: #236641;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }

        .upload-form input[type="file"] {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: white;
        }

        .upload-btn {
            background: linear-gradient(145deg, #236641, #1a4d31);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background: linear-gradient(145deg, #1a4d31, #236641);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(35, 102, 65, 0.3);
        }

        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            font-size: 1rem;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .delete-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
        }

        .edit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 5px;
        }

        .edit-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
        }

        .service-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .service-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .service-content {
            padding: 20px;
        }

        .service-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #236641;
            margin-bottom: 10px;
        }

        .service-description {
            color: #6c757d;
            margin-bottom: 15px;
        }

        .service-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .service-form input[type="text"],
        .service-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .service-form textarea {
            height: 100px;
            resize: vertical;
        }

        /* Fixed sidebar and container positioning */
        body {
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            background-color: #f4f6f9;
        }

        .sidebar {
            position: fixed;
            width: 13.75rem;
            height: 100vh;
            background-color: #236641;
            z-index: 1000;
        }

        .container {
            flex: 1;
            margin-left: 13.75rem;
            padding: 2rem 2rem 2rem 50px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        h1 {
            margin-bottom: 2rem;
            color: #236641;
            font-size: 2rem;
        }

        .file-name {
            color: #495057;
            font-size: 0.9rem;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .carousel-section {
            margin-bottom: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .carousel-section h3 {
            color: #236641;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .no-images {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }

        .services-section {
            margin-top: 40px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
            <a href="admin.php"><i class="fa-solid fa-users"></i> Manage Users</a>
            <a href="content.php" style=" background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-solid fa-chart-bar"></i> Content </a>
            <!-- <a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a> -->
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="sidebar-footer">
        <small>© 2025 WMSU </small>
        <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
    </div>
    </div>

    <div class="container">
        <!-- <h1>Content Management System</h1>
        
        <div class="content-section">
            <h2 class="section-title">Carousel Images Management</h2>
            
            <?php if (!empty($message)): ?>
                <div class="message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="carousel-section">
                <h3>Carousel Management</h3>
                <div class="upload-form">
                    <h4>Upload New Image</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" name="carousel_image" required accept="image/png, image/jpeg, image/jpg">
                        <button type="submit" name="upload_carousel" class="upload-btn">
                            <i class="fas fa-upload"></i>
                            Upload Image
                        </button>
                    </form>
                </div>

                <div class="carousel-images">
                    <?php 
                    if (!empty($carousel_images)):
                        foreach($carousel_images as $image): 
                    ?>
                        <div class="carousel-image-card">
                            <img src="/gcc/img/carousel-img/carousel/<?php echo $image; ?>" alt="Carousel Image">
                            <div class="image-actions">
                                <span class="file-name"><?php echo $image; ?></span>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="image_name" value="<?php echo $image; ?>">
                                    <button type="submit" name="delete_image" class="delete-btn" onclick="return confirm('Are you sure you want to delete this image?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <p class="no-images">No images uploaded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div> -->

        <div class="content-section">
            <h2 class="section-title">Services Management</h2>
            
            <div class="upload-form">
                <h4>Add New Service</h4>
                <form method="POST" enctype="multipart/form-data" class="service-form">
                    <input type="text" name="service_title" required placeholder="Service Title">
                    <input type="text" name="service_link" placeholder="Service Link (e.g., /gcc/services/name.php or leave empty)">
                    <textarea name="service_description" required placeholder="Service Description"></textarea>
                    <input type="file" name="service_image" required accept="image/png, image/jpeg, image/jpg">
                    <button type="submit" name="add_service" class="upload-btn">
                        <i class="fas fa-plus"></i>
                        Add Service
                    </button>
                </form>
            </div>

            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <img src="<?php echo htmlspecialchars($service['image_path']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>">
                        <div class="service-content">
                            <h3 class="service-title"><?php echo htmlspecialchars($service['title']); ?></h3>
                            <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                            <?php if (!empty($service['link']) && $service['link'] != '#'): ?>
                            <p class="service-link"><strong>Link:</strong> <?php echo htmlspecialchars($service['link']); ?></p>
                            <?php endif; ?>
                            <div class="service-actions">
                                <button class="edit-btn" onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                    <i class="fas fa-edit"></i>
                                    Edit Service
                                </button>
                                <button class="edit-btn" onclick="editServiceImage(<?php echo $service['id']; ?>)">
                                    <i class="fas fa-image"></i>
                                    Change Image
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <button type="submit" name="delete_service" class="delete-btn" onclick="return confirm('Are you sure you want to delete this service?')">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="content-section">
            <h2 class="section-title">Team Management</h2>
            <div class="team-management-section" style="text-align: center; padding: 40px 20px;">
                <div style="max-width: 600px; margin: 0 auto;">
                    <i class="fas fa-users" style="font-size: 48px; color: #236641; margin-bottom: 20px;"></i>
                    <h3 style="color: #236641; margin-bottom: 15px; font-size: 1.5rem;">Manage Our Team</h3>
                    <p style="color: #6c757d; margin-bottom: 25px;">Add, edit, or remove team members and manage their information through our dedicated team management interface.</p>
                    <a href="manage-team.php" class="upload-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-user-cog"></i>
                        Manage Team Members
                    </a>
                </div>
            </div>
        </div>

        <div class="content-section">
            <h2 class="section-title">Contact Information</h2>
            <div class="team-management-section" style="text-align: center; padding: 40px 20px;">
                <div style="max-width: 600px; margin: 0 auto;">
                    <i class="fas fa-address-book" style="font-size: 48px; color: #236641; margin-bottom: 20px;"></i>
                    <h3 style="color: #236641; margin-bottom: 15px; font-size: 1.5rem;">Manage Contact Information</h3>
                    <p style="color: #6c757d; margin-bottom: 25px;">Update contact details, social media links, and other contact information displayed on the Contact Us page.</p>
                    <a href="manage-contact.php" class="upload-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-edit"></i>
                        Manage Contact Info
                    </a>
                </div>
            </div>
        </div>

        <div class="content-section">
            <h2 class="section-title">About Us Content</h2>
            <div class="team-management-section" style="text-align: center; padding: 40px 20px;">
                <div style="max-width: 600px; margin: 0 auto;">
                    <i class="fas fa-info-circle" style="font-size: 48px; color: #236641; margin-bottom: 20px;"></i>
                    <h3 style="color: #236641; margin-bottom: 15px; font-size: 1.5rem;">Manage About Us Content</h3>
                    <p style="color: #6c757d; margin-bottom: 25px;">Update the About Us page content including description, vision, mission, and quality policy.</p>
                    <a href="manage-about.php" class="upload-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-edit"></i>
                        Manage About Content
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div id="editServiceModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: white; margin: 15% auto; padding: 20px; border-radius: 8px; width: 80%; max-width: 500px;">
            <span class="close" style="float: right; cursor: pointer; font-size: 28px;">&times;</span>
            <h2>Edit Service</h2>
            <form method="POST" class="service-form">
                <input type="hidden" name="service_id" id="edit_service_id">
                <input type="text" name="service_title" id="edit_service_title" required placeholder="Service Title">
                <input type="text" name="service_link" id="edit_service_link" placeholder="Service Link (e.g., /gcc/services/name.php or leave empty)">
                <textarea name="service_description" id="edit_service_description" required placeholder="Service Description" style="width: 100%; height: 150px; margin-bottom: 20px;"></textarea>
                <button type="submit" name="update_service" class="upload-btn">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </form>
        </div>
    </div>
    
    <!-- Edit Service Image Modal -->
    <div id="editImageModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: white; margin: 15% auto; padding: 20px; border-radius: 8px; width: 80%; max-width: 500px;">
            <span class="close" style="float: right; cursor: pointer; font-size: 28px;">&times;</span>
            <h2>Change Service Image</h2>
            <form method="POST" enctype="multipart/form-data" class="service-form">
                <input type="hidden" name="service_id" id="image_service_id">
                <div style="margin-bottom: 20px;">
                    <label for="service_image">Select New Image:</label>
                    <input type="file" name="service_image" id="service_image" required accept="image/png, image/jpeg, image/jpg">
                </div>
                <button type="submit" name="update_service_image" class="upload-btn">
                    <i class="fas fa-upload"></i>
                    Upload New Image
                </button>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const serviceModal = document.getElementById("editServiceModal");
        const imageModal = document.getElementById("editImageModal");
        const spans = document.getElementsByClassName("close");

        // Close button for service modal
        spans[0].onclick = function() {
            serviceModal.style.display = "none";
        }
        
        // Close button for image modal
        spans[1].onclick = function() {
            imageModal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == serviceModal) {
                serviceModal.style.display = "none";
            }
            if (event.target == imageModal) {
                imageModal.style.display = "none";
            }
        }

        function editService(service) {
            document.getElementById("edit_service_id").value = service.id;
            document.getElementById("edit_service_title").value = service.title;
            document.getElementById("edit_service_description").value = service.description;
            document.getElementById("edit_service_link").value = service.link !== '#' ? service.link : '';
            serviceModal.style.display = "block";
        }
        
        function editServiceImage(serviceId) {
            document.getElementById("image_service_id").value = serviceId;
            imageModal.style.display = "block";
        }
    </script>
</body>
</html>