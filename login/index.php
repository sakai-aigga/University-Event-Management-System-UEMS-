<?php
include "../includes/db-config.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Form</title>
</head>

<body>
    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required>
        <br><br>
        <input type="text" name="password" placeholder="Password" required>
        <br><br>
        <input type="submit" name="submit" value="Submit">
    </form>
</body>
</html>
