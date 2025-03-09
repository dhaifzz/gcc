<?php
require_once '../../font/font.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Super Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="superadmin.css">
</head>
<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <img src="logo.png" alt="Logo" class="logo">
            <h1>Guidance and Counseling</h1>
        </header>
        <nav>
            <button class="menu-icon">☰</button>
        </nav>
        <main>
            <section class="accounts">
                <h2>Accounts</h2>
                <button class="add-account">Add Account</button>
                <div class="filter">
                    <label for="account-type">Account Type:</label>
                    <select id="account-type">
                        <option value="all">All Account</option>
                        <option value="staff">Staff</option>
                        <option value="student">Student</option>
                        <option value="director">Director</option>
                    </select>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Account Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Candido, Ralph Chester M</td>
                            <td>chex@gmail.com</td>
                            <td>qqwertyu</td>
                            <td>STAFF</td>
                            <td class="actions">
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Labang, Anojay </td>
                            <td>anojay@gmail.com</td>
                            <td>qqwertyu</td>
                            <td>STAFF</td>
                            <td class="actions">
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Divinagracia, Bianca Camille I</td>
                            <td>camille@gmail.com</td>
                            <td>qqwertyu</td>
                            <td>GF of the programmer</td>
                            <td class="actions">
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Lim, Akemi</td>
                            <td>kem@gmail.com</td>
                            <td>qqwertyu</td>
                            <td>STAFF</td>
                            <td class="actions">
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Buanafe, Fini Joy </td>
                            <td>joy@gmail.com</td>
                            <td>qqwertyu</td>
                            <td>Director</td>
                            <td class="actions">
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Kayden, Anona Z</td>
                            <td>anona@gmail.com</td>
                            <td>qqwertyu</td>
                            <td>Super Admin</td>
                            <td class="actions">
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </section>
        </main>
        <footer>
            <p>Copyright © 2025 Western Mindanao State University. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
</body>
</html>

