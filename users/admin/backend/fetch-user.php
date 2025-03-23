<?php
require_once '../../../database/database.php'; 

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT id, first_name, middle_name, last_name, email, wmsu_id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo $user['id'] . "|" . $user['first_name'] . "|" . $user['middle_name'] . "|" . $user['last_name'] . "|" . $user['email'] . "|" . $user['wmsu_id'];
        } else {
            echo "Error|User not found";
        }
    } catch (PDOException $e) {
        echo "Error|Database error: " . $e->getMessage();
    }
} else {
    echo "Error|Invalid request";
}
?>
