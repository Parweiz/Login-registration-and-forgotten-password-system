<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <link rel="icon" type="image/png" href="img/fcb.png" />

    <link rel="stylesheet" type="text/css" href="dist/css/style.css" />
</head>

<body style="background-image: url(img/coffee.jpg); background-size: 100%;">

    <header>
        <nav>
            <ul class=" topnav">

                <li><a class="<?php if ($page == 'index') {
                                    echo 'active';
                                } ?>" href="index.php">Home</a></li>

                <li><a href="#">Portfolio</a></li>
                <li><a href="#">About</a> </li>

                <li><a class="<?php if ($page == 'contact') {
                                    echo 'active';
                                } ?>" href="contact.php">Contact</a></li>

                <?php

                if (isset($_SESSION['user_id'])) {
                    echo '<form action="includes/logout.inc.php" method="post">
                    <input type="submit" style="float:right; width:200px; height: 50px" name="logout_submit" value="Logout">
                </form>';
                } else {
                ?>

                <li style="float:right"><a class="<?php if ($page == 'register') {
                                                            echo 'active';
                                                        } ?>" href="signup.php">Sign Up</a> </li>

                <li style="float:right"><a class="<?php if ($page == 'login') {
                                                            echo 'active';
                                                        } ?>" href="signin.php">Login</a> </li>

                <?php
                }

                ?>
            </ul>
            <div>


            </div>
        </nav>
    </header>

</body>