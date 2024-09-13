<?php
date_default_timezone_set('Africa/Dar_es_salaam');
$last_login = date('d-m-Y h:i A [T P]'); // Fix incorrect format
require '../constants/db_config.php';
$myemail = $_POST['email'];
$mypass = md5($_POST['password']); // Consider using password_hash for security

try {
    // Establish database connection once
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL statement for user authentication
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = :myemail AND login = :mypassword");
    $stmt->bindParam(':myemail', $myemail);
    $stmt->bindParam(':mypassword', $mypass);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $rec = count($result);

    if ($rec == 0) {
        // Redirect if credentials don't match
        header("location:../login.php?r=0346");
    } else {
        // Process user login
        session_start();
        foreach ($result as $row) {
            $role = $row['role'];
            $_SESSION['logged'] = true;
            $_SESSION['myid'] = $row['member_no'];
            $_SESSION['myemail'] = $row['email'];
            $_SESSION['myphone'] = $row['phone'];
            $_SESSION['mycity'] = $row['city'];
            $_SESSION['mystreet'] = $row['street'];
            $_SESSION['myzip'] = $row['zip'];
            $_SESSION['mycountry'] = $row['country'];
            $_SESSION['mydesc'] = $row['about'];
            $_SESSION['avatar'] = $row['avatar'];
            $_SESSION['lastlogin'] = $row['last_login'];
            $_SESSION['role'] = $role;

            if ($role == "employee") {
                $_SESSION['myfname'] = $row['first_name'];
                $_SESSION['mylname'] = $row['last_name'];
                $_SESSION['mydate'] = $row['bdate'];
                $_SESSION['mymonth'] = $row['bmonth'];
                $_SESSION['myyear'] = $row['byear'];
                $_SESSION['myedu'] = $row['education'];
                $_SESSION['mytitle'] = $row['title'];
            } else {
                $_SESSION['compname'] = $row['first_name'];
                $_SESSION['established'] = $row['byear'];
                $_SESSION['comptype'] = $row['title'];
                $_SESSION['myserv'] = $row['services'];
                $_SESSION['myexp'] = $row['expertise'];
                $_SESSION['website'] = $row['website'];
                $_SESSION['people'] = $row['people'];
            }

            // Update last login time
            $updateStmt = $conn->prepare("UPDATE tbl_users SET last_login = :lastlogin WHERE email= :email");
            $updateStmt->bindParam(':lastlogin', $last_login);
            $updateStmt->bindParam(':email', $myemail);
            $updateStmt->execute();

            // Redirect to the user role page
            header("location:../$role");
        }
    }
} catch (PDOException $e) {
    // Log or display error
    error_log("Database error: " . $e->getMessage());
    // Consider redirecting or showing a user-friendly error page
}
?>
