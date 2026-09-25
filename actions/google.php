<?php
session_start();
require dirname(__DIR__) . '/db_conn.php';

// Handle Google OAuth Callback
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $tokenData = json_decode($response, true);
    curl_close($ch);

    if (isset($tokenData['error'])) {
        header('Location: ../index.php?error=Google Login Failed: ' . htmlspecialchars($tokenData['error_description']));
        exit();
    }

    if (isset($tokenData['access_token'])) {
        $accessToken = $tokenData['access_token'];
        
        $infoUrl = "https://www.googleapis.com/oauth2/v2/userinfo?access_token=$accessToken";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $infoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $userResponse = curl_exec($ch);
        $userData = json_decode($userResponse, true);
        curl_close($ch);

        if (isset($userData['id'])) {
            $googleId = $userData['id'];
            $email = $userData['email'];
            $name = $userData['name'];

            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE google_id=? OR email=?");
            mysqli_stmt_bind_param($stmt, "ss", $googleId, $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                if (empty($row['google_id'])) {
                    $updateStmt = mysqli_prepare($conn, "UPDATE users SET google_id=? WHERE id=?");
                    mysqli_stmt_bind_param($updateStmt, "si", $googleId, $row['id']);
                    mysqli_stmt_execute($updateStmt);
                }

                session_regenerate_id(true);
                $_SESSION['name'] = $row['name'];
                $_SESSION['id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['mobile'] = $row['mobile'];
                
                header("Location: ../redirect.php");
                exit();
            } else {
                $_SESSION['temp_google_id'] = $googleId;
                $_SESSION['temp_email'] = $email;
                $_SESSION['temp_name'] = $name;
                
                header("Location: ../google-signup.php");
                exit();
            }
        }
    }
    
    header('Location: ../index.php?error=Google login was cancelled or failed.');
    exit();
}

// Otherwise, initiate the Google OAuth flow
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth";
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'prompt' => 'select_account'
];

$login_url = $authUrl . '?' . http_build_query($params);
header("Location: $login_url");
exit();
?>
