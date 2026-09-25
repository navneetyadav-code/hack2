<?php
session_start();
// Use dirname(__DIR__) to correctly locate db_conn.php since we are inside 'actions' folder
require dirname(__DIR__) . '/db_conn.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            if (empty($username)) {
                header("Location: ../index.php?error=User Name is Required");
                exit();
            } else if (empty($password)) {
                header("Location: ../index.php?error=Password is Required");
                exit();
            }

            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username=?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                $stored_password = $row['password'];
                $password_valid = false;
                $needs_rehash = false;

                if (strlen($stored_password) === 32 && ctype_xdigit($stored_password)) {
                    if (md5($password) === $stored_password) {
                        $password_valid = true;
                        $needs_rehash = true;
                    }
                } else {
                    if (password_verify($password, $stored_password)) {
                        $password_valid = true;
                    }
                }

                if ($password_valid) {
                    session_regenerate_id(true);
                    $_SESSION['name'] = $row['name'];
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['mobile'] = $row['mobile'];

                    if ($needs_rehash) {
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        $update_stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
                        mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $row['id']);
                        mysqli_stmt_execute($update_stmt);
                    }

                    header("Location: ../redirect.php");
                    exit();
                } else {
                    header("Location: ../index.php?error=Incorrect User name or password");
                    exit();
                }
            } else {
                header("Location: ../index.php?error=Incorrect User name or password");
                exit();
            }
        }
        break;

    case 'signup':
        if (isset($_POST['name'], $_POST['username'], $_POST['mobile'], $_POST['role'], $_POST['password'])) {
            $name = trim($_POST['name']);
            $username = trim($_POST['username']);
            $mobile = trim($_POST['mobile']);
            $role = $_POST['role'];
            $password = $_POST['password'];
            
            $user_data = 'name=' . urlencode($name) . '&username=' . urlencode($username) . '&mobile=' . urlencode($mobile) . '&role=' . urlencode($role);

            if (empty($name) || empty($username) || empty($mobile) || empty($role) || empty($password)) {
                header("Location: ../signup.php?error=All fields are required&$user_data");
                exit();
            }

            if (!in_array($role, ['farmer', 'transporter', 'buyer'])) {
                header("Location: ../signup.php?error=Valid role is required&$user_data");
                exit();
            }

            $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username=?");
            mysqli_stmt_bind_param($stmt_check, "s", $username);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                header("Location: ../signup.php?error=The username is already taken. Please choose another.&$user_data");
                exit();
            }
            mysqli_stmt_close($stmt_check);

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = mysqli_prepare($conn, "INSERT INTO users(name, username, mobile, role, password) VALUES(?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "sssss", $name, $username, $mobile, $role, $hashed_password);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                header("Location: ../signup.php?success=Your account has been created successfully! You can now sign in.");
                exit();
            } else {
                header("Location: ../signup.php?error=An unknown error occurred. Please try again.&$user_data");
                exit();
            }
        }
        break;

    case 'google_signup':
        if (!isset($_SESSION['temp_google_id'])) {
            header("Location: ../index.php");
            exit();
        }
        if (isset($_POST['username'], $_POST['mobile'], $_POST['role'])) {
            $username = trim($_POST['username']);
            $mobile = trim($_POST['mobile']);
            $role = $_POST['role'];
            
            $googleId = $_SESSION['temp_google_id'];
            $email = $_SESSION['temp_email'];
            $name = $_SESSION['temp_name'];

            if (empty($username) || empty($mobile) || empty($role) || !in_array($role, ['farmer', 'transporter', 'buyer'])) {
                header("Location: ../google-signup.php?error=All fields are required and must be valid.");
                exit();
            }

            $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username=?");
            mysqli_stmt_bind_param($stmt_check, "s", $username);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                header("Location: ../google-signup.php?error=The username is already taken. Please choose another.");
                exit();
            }
            mysqli_stmt_close($stmt_check);

            $random_password = bin2hex(random_bytes(16));
            $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);

            $stmt_insert = mysqli_prepare($conn, "INSERT INTO users(name, username, email, google_id, mobile, role, password) VALUES(?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_insert, "sssssss", $name, $username, $email, $googleId, $mobile, $role, $hashed_password);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $inserted_id = mysqli_insert_id($conn);
                
                unset($_SESSION['temp_google_id'], $_SESSION['temp_email'], $_SESSION['temp_name']);
                
                session_regenerate_id(true);
                $_SESSION['name'] = $name;
                $_SESSION['id'] = $inserted_id;
                $_SESSION['role'] = $role;
                $_SESSION['username'] = $username;
                $_SESSION['mobile'] = $mobile;
                
                header("Location: ../redirect.php");
                exit();
            } else {
                header("Location: ../google-signup.php?error=An unknown error occurred. Please try again.");
                exit();
            }
        }
        break;

    case 'logout':
        session_unset();
        session_destroy();
        header("Location: ../index.php");
        exit();
        break;

    default:
        header("Location: ../index.php");
        exit();
}
