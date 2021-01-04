<?php
$page = 'contact';
require "header.php"
?>

<main>

    <div class="contactpage">
        <div class="wrap-contactpage">

            <span class="title">
                Send Us A Message
            </span>
            <form action="includes/contactpage.inc.php" method="POST">
                <div class="wrap-input">
                    <input class="input" type="text" name="name" placeholder="Full Name">
                    <span class="focus-input"></span>
                </div>

                <div class="wrap-input">
                    <input class="input" type="text" name="email" placeholder="E-mail">
                    <span class="focus-input"></span>
                </div>

                <div class="wrap-input">
                    <input class="input" type="text" name="subject" placeholder="Subject">
                    <span class="focus-input"></span>
                </div>

                <div class="wrap-input">
                    <textarea class="input" name="message" placeholder="Your Message"></textarea>
                    <span class="focus-input"></span>
                </div>

                <div class="container-contact-btn">
                    <button class="btn" type="submit" name="submit">


                        Send Mail
                    </button>
                </div>

            </form>

            <?php

            if (isset($_GET["mailsent"])) {
                if ($_GET["mailsent"] == "success") {
                    echo '<p class="mailsent"><br /><br /> Thank you for contacting us! We will get back to you soon.</p>';
                } else if ($_GET['mailsent'] == "failed") {
                    echo '<p class="mailnotsent"><br /><br /> Sorry, your email was not sent. Please try again in a few minutes.</p>';
                }
            } elseif (isset($_GET['error'])) {
                if ($_GET['error'] == "emptyfields") {
                    echo '<p class="contacterror" style="text-align:center"><br /> Fill in all fields!</p>';
                } else if ($_GET['error'] == "invalidmail") {
                    echo '<p class="contacterror" style="text-align:center"><br /> Invalid email!</p>';
                }
            }
            ?>
        </div>

    </div>


</main>

<?php

require "footer.php"
?>