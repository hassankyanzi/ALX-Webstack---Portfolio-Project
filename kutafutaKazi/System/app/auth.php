<?php
// Set timezone to Kampala
date_default_timezone_set('Africa/Kampala');

// Get the current date and time for last login
$last_login = date('d-m-Y h:i A [T P]');

// Database connection
require '../constants/db_config.php';

// Get the form inputs
$myemail = $_POST['email'];
$mypass = $_POST['password']; // Raw password for password_verify()

try {
    // Establish database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL statement to fetch user by email
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = :myemail");
    $stmt->bindParam(':myemail', $myemail);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if the password is bcrypt-hashed or MD5-hashed
        if (password_verify($mypass, $user['login'])) {
            // Password verified using bcrypt
            echo "Password verified successfully (bcrypt)";
        } elseif (md5($mypass) == $user['login']) {
            // Password verified using MD5, now rehash with bcrypt for security
            echo "Password verified successfully (MD5)";
            
            // Rehash password using bcrypt for better security
            $new_password_hash = password_hash($mypass, PASSWORD_DEFAULT);
            
            // Update the password in the database with bcrypt hash
            $updateStmt = $conn->prepare("UPDATE tbl_users SET login = :newhash WHERE email = :email");
            $updateStmt->bindParam(':newhash', $new_password_hash);
            $updateStmt->bindParam(':email', $myemail);
            $updateStmt->execute();
            
            echo "Password rehashed using bcrypt";
        } else {
            // Incorrect password
            header("location:../login.php?r=0346"); // Redirect to login page with error
            exit();
        }

        // Start session and process login
        session_start();
        $_SESSION['logged'] = true;
        $_SESSION['myid'] = $user['member_no'];
        $_SESSION['myemail'] = $user['email'];
        $_SESSION['myphone'] = $user['phone'];
        $_SESSION['mycity'] = $user['city'];
        $_SESSION['mystreet'] = $user['street'];
        $_SESSION['myzip'] = $user['zip'];
        $_SESSION['mycountry'] = $user['country'];
        $_SESSION['mydesc'] = $user['about'];
        $_SESSION['avatar'] = $user['avatar'];
        $_SESSION['lastlogin'] = $user['last_login'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == "employee") {
            $_SESSION['myfname'] = $user['first_name'];
            $_SESSION['mylname'] = $user['last_name'];
            $_SESSION['mydate'] = $user['bdate'];
            $_SESSION['mymonth'] = $user['bmonth'];
            $_SESSION['myyear'] = $user['byear'];
            $_SESSION['myedu'] = $user['education'];
            $_SESSION['mytitle'] = $user['title'];
        } else { // employer
            $_SESSION['cname'] = $user['company_name'];
            $_SESSION['established'] = $user['byear'];
            $_SESSION['ctype'] = $user['company_type'];
            $_SESSION['myserv'] = $user['services'];
            $_SESSION['myexp'] = $user['expertise'];
            $_SESSION['website'] = $user['website'];
            $_SESSION['people'] = $user['people'];
        }

        // Update last login time
        $updateStmt = $conn->prepare("UPDATE tbl_users SET last_login = :lastlogin WHERE email = :email");
        $updateStmt->bindParam(':lastlogin', $last_login);
        $updateStmt->bindParam(':email', $myemail);
        $updateStmt->execute();

        // Redirect based on user role
        header("location:../" . $user['role']);
        exit();
    } else {
        // User not found
        header("location:../login.php?r=0346"); // Redirect with error
    }
} catch (PDOException $e) {
    // Log or display error
    error_log("Database error: " . $e->getMessage());
    // Redirect or show a user-friendly error page
}
?>
