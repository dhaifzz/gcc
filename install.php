<?php
// Installation script to set up the CMS system tables
$pageTitle = 'GCC Content Management System Installation';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle; ?></title>
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        h1 {
            color: #236641;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .step {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #236641;
        }
        .step-title {
            font-weight: bold;
            color: #236641;
            margin-bottom: 10px;
        }
        .status {
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            background: #236641;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $pageTitle; ?></h1>
        
        <div class="step">
            <div class="step-title">Step 1: Creating CMS Tables</div>
            <?php
            try {
                require_once 'database/create_cms_tables.php';
                echo '<div class="status success">CMS tables created successfully!</div>';
            } catch (Exception $e) {
                echo '<div class="status error">Error creating CMS tables: ' . $e->getMessage() . '</div>';
            }
            ?>
        </div>
        
        <div class="step">
            <div class="step-title">Step 2: Ensuring Required Directories Exist</div>
            <?php
            $directories = [
                'img/team-gcc',
                'img/services'
            ];
            
            $allCreated = true;
            
            foreach ($directories as $dir) {
                $fullPath = __DIR__ . '/' . $dir;
                if (!file_exists($fullPath)) {
                    if (mkdir($fullPath, 0777, true)) {
                        echo '<div class="status success">Directory created: ' . $dir . '</div>';
                    } else {
                        echo '<div class="status error">Failed to create directory: ' . $dir . '</div>';
                        $allCreated = false;
                    }
                } else {
                    echo '<div class="status success">Directory already exists: ' . $dir . '</div>';
                }
            }
            
            if ($allCreated) {
                echo '<div class="status success">All required directories are in place.</div>';
            }
            ?>
        </div>
        
        <a href="/gcc/users/admin/content.php" class="btn">Go to CMS Admin</a>
    </div>
</body>
</html> 