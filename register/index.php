<?php
include "../includes/db-config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $contact  = $_POST['contact'];
    $role     = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO user (name, email, password, contact, role)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $name, $email, $password, $contact, $role);
    $stmt->execute();

    echo "Registration successful";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<body>
<h2>Register</h2>
<form method="post">
    <input type="text" name="name" placeholder="Full Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="text" name="contact" placeholder="Contact" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
    </select><br><br>
    <button type="submit">Register</button>
</form>
</body>
</html>
