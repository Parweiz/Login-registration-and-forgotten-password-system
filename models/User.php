<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Function to signup an user with
    public function SignUp($data)
    {
        // If any of the fields are empty then the user should be redirected back to signup page
        if (empty($data['UserName']) || empty($data['Email']) || empty($data['Password']) || empty($data['RepeatPassword'])) {
            header("Location: ../signup.php?error=emptyfields&username=" . $data['UserName'] . "&mail=" . $data['Email']);
            exit();
        }
        // Checks whether the mail is validated as an actual email AND the username matches the search pattern that is being checked for
        elseif (!filter_var($data['Email'], FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z0-9]*$/", $data['UserName'])) {
            header("Location: ../signup.php?error=invalidmailAndusername");
            exit();
        }
        // Look at line 19 for explanation - Basically ONLY checking if the mail is validated or not
        elseif (!filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
            header("Location: ../signup.php?error=invalidmail&username=" . $data['UserName']);
            exit();
        }
        // Look at line 19 for explanation - Basically ONLY checking if the username matches the search pattern that is being checked for
        elseif (!preg_match("/^[a-zA-Z0-9]*$/", $data['UserName'])) {  // We are basically searching for what we have inside [].
            header("Location: ../signup.php?error=invalidusername&mail=" . $data['Email']);
            exit();
        } else if ($data['Password'] !== $data['RepeatPassword']) {
            header("Location: ../signup.php?error=passwordcheckfailed&username=" . $data['UserName'] . "&mail=" . $data['Email']);
            exit();
        } else {

            // Prepare the query. Trying to see if there is already an existing username in the database
            $this->db->query('SELECT UserName FROM users WHERE UserName=:UserName');

            // Binding the actual values for our named parameters before execute
            $this->db->bind(':UserName', $data['UserName']);
            $this->db->execute();

            // Checks how many rows or results we get from the db
            $resultCheck = $this->db->rowCount();
            if ($resultCheck > 0) {
                header("Location: ../signup.php?error=usernametaken&mail=" . $data['Email']);
                exit();
            } else {
                // Prepare the query
                $this->db->query('INSERT INTO users (UserName, FullName, Email, Pwd) VALUES (:UserName,:FullName,:Email, :Pwd)');

                $hashedPwd = password_hash($data['Password'], PASSWORD_DEFAULT);

                // Bind the values for our named parameters
                $this->db->bind(':UserName', $data['UserName']);
                $this->db->bind(':FullName', $data['FullName']);
                $this->db->bind(':Email', $data['Email']);
                $this->db->bind(':Pwd', $hashedPwd);

                $execute = $this->db->execute();

                if ($execute) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        // Think this is the proper way to close connection in PDO
        $this->db->closeConnection();
    }

    // Function to signin with
    public function SignIn($data)
    {
        if (empty($data['UserName']) || empty($data['Password'])) {
            header("Location: ../signin.php?error=emptyfields");
            exit();
        } else {
            // Prepare the query - Trying to see if there's already an User object in DB
            $this->db->query('SELECT * FROM users WHERE UserName=:UserName');
            $this->db->bind(':UserName', $data['UserName']);

            // If there's an User object in DB with that specific username then grab the object
            $result = $this->db->getSingle();
            // Converting from stdClass to array
            $array = json_decode(json_encode($result), true);

            if (!$array) {
                header("Location: ../signin.php?error=nouser");
                exit();
            } else {
                $pwdCheck = password_verify($data['Password'], $array['Pwd']);
                if ($pwdCheck === false) {
                    header("Location: ../signin.php?error=wrongpwd");
                    exit();
                } elseif ($pwdCheck == true) {

                    /* What we need to do in order to let the user to login is to start a session. The reason behind this 
                    is that the way loginsystem works is that we create a global variable that has the info about the user, when
                    he/she is signed in the website. In that matter we simply check inside the website check whether the global 
                    variable is available or not. This is where "session" comes into play, because the type of variable we'd like
                    to store globally is going to be a "session" variable. 
                    */
                    session_start();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $array['UserID'];
                    $_SESSION['username'] = $array['UserName'];
                    $_SESSION['fullname'] = $array['FullName'];

                    header("Location: ../index.php?login=success");
                } else {
                    header("Location: ../signin.php?error=wrongpwd");
                    exit();
                }
            }
        }
    }

    // With this function we can request to reset our pwd and enter new pwd for user object
    public function ResetPwdRequest($data)
    {
        // Prepare the query - Delete any existing entries of a token inside db to make sure that there's no existing token from same user in db
        $this->db->query('DELETE FROM pwdReset WHERE pwdResetEmail=:pwdResetEmail');
        $this->db->bind(':pwdResetEmail', $data['email']);
        $this->db->execute();

        $this->db->query('SELECT * FROM users WHERE Email=:Email');
        $this->db->bind(':Email', $data['email']);

        $result = $this->db->getSingle();
        $array = json_decode(json_encode($result), true);

        if ($array === false) {
            header("Location: ../reset_password.php?error=emaildoesntexist");
            exit();
        } else {

            // Prepare new query
            $this->db->query('INSERT INTO pwdReset (PwdResetEmail, PwdResetSelector, PwdResetToken, PwdResetExpireTime) 
            VALUES (:pwdResetEmail,:pwdResetSelector,:pwdResetToken,:pwdResetExpireTime)');

            $hashedToken = password_hash($data['token'], PASSWORD_DEFAULT);

            $this->db->bind(':pwdResetEmail', $data['email']);
            $this->db->bind(':pwdResetSelector', $data['selector']);
            $this->db->bind(':pwdResetToken', $hashedToken);
            $this->db->bind(':pwdResetExpireTime', $data['expireTime']);

            $execute = $this->db->execute();

            if ($execute) {
                return true;
            } else {
                return false;
            }
        }

        $this->db->closeConnection();
    }

    // Actual function that resets password and changes it
    public function ResetPassword($data)
    {
        // Preparing the query - Select all data from pwdSelector and pwdResetExpireTime columns
        $this->db->query('SELECT * FROM pwdReset WHERE PwdResetSelector=:pwdResetSelector AND PwdResetExpireTime >= :pwdResetExpireTime');

        $this->db->bind(':pwdResetSelector', $data['selector']);
        $this->db->bind(':pwdResetExpireTime', $data['currentDate']);

        // If there's an object in DB containing value for its "selector" and "currentDate" columns then grab the object
        $result = $this->db->getSingle();
        $array = json_decode(json_encode($result), true);

        if (!$array) {
            header("Location: ../index.php?error=couldnotgetobject");
            exit();
        } else {
            $tokenToBinary = hex2bin($data['validator']);

            $tokenCheck = password_verify($tokenToBinary, $array['PwdResetToken']);

            if ($tokenCheck === false) {
                header("Location: ../index.php?error=tokenverifyfailed");
                exit();
            } elseif ($tokenCheck === true) {
                $tokenEmail = $array['PwdResetEmail'];

                // Select all data from emailUsers column from
                $this->db->query('SELECT * FROM users WHERE Email=:Email');

                $this->db->bind(':Email', $tokenEmail);
                $result = $this->db->getSingle();
                $row = json_decode(json_encode($result), true);

                if (!$array = $row) {
                    header("Location: ../index.php?error=usernamedoesnotexist");
                    exit();
                } else {
                    // Modifies pwdUsers and emailUsers
                    $this->db->query('UPDATE users SET Pwd=:Pwd WHERE Email=:Email');

                    $hashedNewPwd = password_hash($data['password'], PASSWORD_DEFAULT);

                    $this->db->bind(':Pwd', $hashedNewPwd);
                    $this->db->bind(':Email', $tokenEmail);

                    $this->db->execute();

                    // Deletes the specific object from pwdReset table after password has been updated
                    $this->db->query('DELETE FROM pwdReset WHERE pwdResetEmail=:pwdResetEmail');
                    $this->db->bind(':pwdResetEmail', $tokenEmail);
                    $this->db->execute();
                    header("Location: ../signin.php?newpwd=passwordupdated");
                }
            } else {
                // ERROR HANDLING
            }
        }
    }
}