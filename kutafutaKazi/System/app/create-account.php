<?php
// Set the timezone
date_default_timezone_set('Africa/Kampala');

// Check if the form has been submitted
if (isset($_POST['reg_mode'])) {
    checkemail();	
} else {
    header("location:../");		
}

// Function to check if the email already exists
function checkemail() {
    try {
        require '../constants/db_config.php';
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $email = $_POST['email'];
        $account_type = $_POST['acctype']; // Check if the user is an Employee or Employer
        
        // Prepare a statement to check if email already exists
        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $records = count($result);
        
        // Determine the user role (Employee or Employer)
        if ($account_type == "101") {
            $role = "Employee";	
        } else {
            $role = "Employer";	
        }
        
        // If the email is already registered
        if ($records > 0) {
            header("location:../register.php?p=$role&r=0927&message=Email already exists"); // Email already exists
        } else {
            // Register based on account type
            if ($account_type == "101") {
                register_as_employee();
            } else {
                register_as_employer();
            }
        }
    } catch (PDOException $e) {
        header("location:../register.php?p=$role&r=4568&message=Registration failed due to an error");
    }
}

// Function to register an employee
function register_as_employee() {
    try {
        require '../constants/db_config.php';
        require '../constants/uniques.php';
        
        
        $role = 'employee';
        $last_login = date('d-m-Y h:i A [T P]');
        $member_no = 'EM'.get_rand_numbers(9).'';
        $fname = ucwords($_POST['fname']);
        $lname = ucwords($_POST['lname']);
        $email = $_POST['email'];
        $login = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
        
        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Insert employee data into the database
        $stmt = $conn->prepare("INSERT INTO tbl_users (first_name, last_name, email, last_login, login, role, member_no) 
        VALUES (:fname, :lname, :email, :lastlogin, :login, :role, :memberno)");
        
        // Bind parameters
        $stmt->bindParam(':fname', $fname);
        $stmt->bindParam(':lname', $lname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':lastlogin', $last_login);
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':memberno', $member_no);
        
        // Execute the query
        $stmt->execute();
        
        // Redirect with a success message
        header("location:../register.php?p=Employee&r=1123&message=You are now registered as an Employee");
    } catch (PDOException $e) {
        header("location:../register.php?p=Employee&r=4568&message=Registration failed");
    }
}

// Function to register an employer
function register_as_employer() {
    try {
        require '../constants/db_config.php';
        require '../constants/uniques.php';
        
        
        $role = 'employer';
        $last_login = date('d-m-Y h:i A [T P]');
        $member_no = 'EM'.get_rand_numbers(9).'';
        $cname = ucwords($_POST['cname']);
        $ctype = ucwords($_POST['ctype']);
        $email = $_POST['email'];
        $login = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
        
        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Insert employer data into the database
        $stmt = $conn->prepare("INSERT INTO tbl_users (company_name, company_type, email, last_login, login, role, member_no) 
        VALUES (:cname, :ctype, :email, :lastlogin, :login, :role, :memberno)");
        
        // Bind parameters
        $stmt->bindParam(':cname', $cname);
        $stmt->bindParam(':ctype', $ctype);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':lastlogin', $last_login);
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':memberno', $member_no);
        
        // Execute the query
        $stmt->execute();
        
        // Redirect with a success message
        header("location:../register.php?p=Employee&r=1123&message=You are now registered as an Employer");
    } catch (PDOException $e) {
        header("location:../register.php?p=Employee&r=4568&message=Registration failed");
    }
}



// Display the notification on the registration page
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    echo "<div class='notification'>$message</div>";
}
?>
