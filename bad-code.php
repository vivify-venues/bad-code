<?php

function dbConnection()
{
    $conn = mysqli_connect("localhost", "root", "password", "my_database");
    if (! $conn) {
        die("Connection failed: ".mysqli_connect_error());
    }

    return $conn;
}

function getUserDetails($userId)
{
    $conn = dbConnection();

    $sql = "SELECT * FROM users WHERE id = $userId";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $user = $row;
        }
    } else {
        $user = null;
    }

    mysqli_close($conn);

    return $user;
}

function sendEmail($userId, $subject, $message)
{
    $user = getUserDetails($userId);
    if (! $user) {
        die("User not found.");
    }

    $to = $user['email'];
    $headers = "From: noreply@example.com";

    if (! mail($to, $subject, $message, $headers)) {
        echo "Error sending email";
    } else {
        echo "Email sent to ".$user['email'];
    }
}

function createUser($username, $email)
{
    $conn = dbConnection();

    $sql = "INSERT INTO users (username, email) VALUES ('$username', '$email')";

    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
        $last_id = mysqli_insert_id($conn);

        sendEmail($last_id, "Welcome", "Thank you for signing up!");

        $log = fopen("log.txt", "a");
        fwrite($log, "User created: $username, Email: $email\n");
        fclose($log);
    } else {
        echo "Error: ".$sql."<br>".mysqli_error($conn);
    }

    mysqli_close($conn);
}

function getUserCount()
{
    $conn = dbConnection();
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    mysqli_close($conn);

    return $row['total'];
}

function loginUser($username, $password)
{
    $conn = dbConnection();
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        session_start();
        $_SESSION['user'] = $username;

        echo "Login successful. Welcome, ".$username;

        $log = fopen("log.txt", "a");
        fwrite($log, "User logged in: $username\n");
        fclose($log);
    } else {
        echo "Invalid username or password.";
    }

    mysqli_close($conn);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'create') {
        createUser($_POST['username'], $_POST['email']);
    } elseif (isset($_POST['action']) && $_POST['action'] == 'login') {
        loginUser($_POST['username'], $_POST['password']);
    } elseif (isset($_POST['action']) && $_POST['action'] == 'count') {
        echo "Total users: ".getUserCount();
    }
}


