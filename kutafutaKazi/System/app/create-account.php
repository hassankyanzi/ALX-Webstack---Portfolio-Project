<?php
// Set the timezone
date_default_timezone_set('Africa/Dar_es_salaam');

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
        // Import necessary constants and functions
        require '../constants/db_config.php';
        require '../constants/uniques.php';

        // Collect data from POST
        $role = 'employer';
        $last_login = date('d-m-Y h:i A [T P]');
        $comp_no = 'CM'.get_rand_numbers(9);  // Unique company number
        $cname = ucwords($_POST['company']); // Company name from form
        $ctype = ucwords($_POST['type']);    // Company type from form
        $email = $_POST['email'];            // Email from form
        $login = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hash

        // Debugging: Check if form data is being received correctly
        if (empty($cname) || empty($ctype) || empty($email) || empty($_POST['password'])) {
            // If any required fields are missing, redirect with error
            header("location:../register.php?p=Employer&r=4568&message=All fields are required");
            exit;
        }

        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare SQL query for employer registration
        $stmt = $conn->prepare("INSERT INTO tbl_users (company_name, company_type, email, last_login, login, role, member_no) 
                                VALUES (:company, :type, :email, :lastlogin, :login, :role, :memberno)");

        // Bind the form data to the SQL query
        $stmt->bindParam(':company', $cname);
        $stmt->bindParam(':type', $ctype);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':lastlogin', $last_login);
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':memberno', $comp_no);

        // Execute the query to insert data into the database
        $stmt->execute();

        // Debugging: Check if the query was successful
        if ($stmt->rowCount() > 0) {
            // Redirect with success notification
            header("location:../register.php?p=Employer&r=1123&message=You are now registered as an Employer");
        } else {
            // Redirect with failure message if no rows were inserted
            header("location:../register.php?p=Employer&r=4568&message=Registration failed");
        }

    } catch (PDOException $e) {
        // Handle exceptions and redirect with an error message
        header("location:../register.php?p=Employer&r=4568&message=Error: ".$e->getMessage());
    }
}


// Display the notification on the registration page
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    echo "<div class='notification'>$message</div>";
}
?>
