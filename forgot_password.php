<?php

require "config.php";

$message = "";
$success = "";

$step = 1;


/* =====================================================
   STEP 1
   FIND ACCOUNT
===================================================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST["action"] ?? "";


    /* =================================================
       SEND OTP
    ================================================= */

    if ($action == "send_otp") {

        $login = trim($_POST["login"] ?? "");


        if ($login == "") {

            $message =
                "Please enter your username or email.";

        } else {

            $stmt = $conn->prepare(
                "SELECT id, full_name, email, mobile
                 FROM users
                 WHERE username = ?
                 OR email = ?
                 LIMIT 1"
            );

            $stmt->bind_param(
                "ss",
                $login,
                $login
            );

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows == 0) {

                $message =
                    "No account found with these details.";

            } else {

                $user = $result->fetch_assoc();


                /*
                   DUMMY OTP FOR LOCAL TESTING
                */

                $_SESSION["reset_user_id"] =
                    $user["id"];

                $_SESSION["reset_otp"] =
                    "123456";


                $_SESSION["reset_otp_time"] =
                    time();


                $step = 2;


                $success =
                    "OTP sent successfully. " .
                    "For testing use OTP: 123456";
            }
        }
    }


    /* =================================================
       VERIFY OTP
    ================================================= */

    elseif ($action == "verify_otp") {

        $otp =
            trim($_POST["otp"] ?? "");


        if (!isset($_SESSION["reset_user_id"])) {

            $message =
                "Your reset session has expired.";

            $step = 1;

        }

        elseif ($otp == "") {

            $message =
                "Please enter the OTP.";

            $step = 2;

        }

        elseif ($otp !== $_SESSION["reset_otp"]) {

            $message =
                "Incorrect OTP. Please try again.";

            $step = 2;

        }

        else {

            $_SESSION["reset_verified"] =
                true;

            $step = 3;

        }
    }


    /* =================================================
       RESET PASSWORD
    ================================================= */

    elseif ($action == "reset_password") {

        $password =
            $_POST["password"] ?? "";

        $confirmPassword =
            $_POST["confirm_password"] ?? "";


        $step = 3;


        if (
            !isset($_SESSION["reset_user_id"]) ||
            !isset($_SESSION["reset_verified"])
        ) {

            $message =
                "Please restart the password reset process.";

            $step = 1;

        }

        elseif (strlen($password) < 8) {

            $message =
                "Password must contain at least 8 characters.";

        }

        elseif (!preg_match(
            "/[A-Z]/",
            $password
        )) {

            $message =
                "Password must contain at least one capital letter.";

        }

        elseif (!preg_match(
            "/[a-z]/",
            $password
        )) {

            $message =
                "Password must contain at least one small letter.";

        }

        elseif (!preg_match(
            "/[0-9]/",
            $password
        )) {

            $message =
                "Password must contain at least one number.";

        }

        elseif (!preg_match(
            "/[^A-Za-z0-9]/",
            $password
        )) {

            $message =
                "Password must contain at least one special character.";

        }

        elseif ($password !== $confirmPassword) {

            $message =
                "Passwords do not match.";

        }

        else {


            $hash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            $userId =
                $_SESSION["reset_user_id"];


            $stmt = $conn->prepare(
                "UPDATE users
                 SET password = ?
                 WHERE id = ?"
            );


            $stmt->bind_param(
                "si",
                $hash,
                $userId
            );


            if ($stmt->execute()) {


                unset(
                    $_SESSION["reset_user_id"],
                    $_SESSION["reset_otp"],
                    $_SESSION["reset_otp_time"],
                    $_SESSION["reset_verified"]
                );


                $success =
                    "Password reset successfully.";

                $step = 4;

            } else {

                $message =
                    "Unable to reset password.";

            }

        }

    }

}

?>


<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Forgot Password | SMARTSPEND
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>


<body class="auth-body">


<div class="auth-box">


    <div class="brand">

        💰 SMARTSPEND

    </div>


    <div class="auth-card">


        <?php if ($step == 1): ?>


            <!-- =====================================
                 STEP 1
            ====================================== -->


            <p class="welcome">

                PASSWORD RESET

            </p>


            <h2>

                Forgot password?

            </h2>


            <p class="sub">

                Enter your username or email
                to reset your password.

            </p>


            <?php if ($message): ?>

                <div class="message">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <input
                    type="hidden"
                    name="action"
                    value="send_otp"
                >


                <label>

                    Username or Email

                </label>


                <input
                    type="text"
                    name="login"
                    placeholder="Enter username or email"
                    required
                >


                <button
                    type="submit"
                    style="width:100%;"
                >

                    Send OTP →

                </button>


            </form>


        <?php elseif ($step == 2): ?>


            <!-- =====================================
                 STEP 2
            ====================================== -->


            <p class="welcome">

                VERIFY OTP

            </p>


            <h2>

                Check your OTP 🔐

            </h2>


            <p class="sub">

                Enter the OTP sent to your
                registered mobile number.

            </p>


            <?php if ($message): ?>

                <div class="message">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <?php if ($success): ?>

                <div class="success">

                    <?= htmlspecialchars($success) ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <input
                    type="hidden"
                    name="action"
                    value="verify_otp"
                >


                <label>

                    Enter OTP

                </label>


                <input
                    type="text"
                    name="otp"
                    placeholder="123456"
                    maxlength="6"
                    inputmode="numeric"
                    required
                >


                <button
                    type="submit"
                    style="width:100%;"
                >

                    Verify OTP →

                </button>


            </form>


        <?php elseif ($step == 3): ?>


            <!-- =====================================
                 STEP 3
            ====================================== -->


            <p class="welcome">

                NEW PASSWORD

            </p>


            <h2>

                Create a new password

            </h2>


            <p class="sub">

                Make sure your new password
                is strong and secure.

            </p>


            <?php if ($message): ?>

                <div class="message">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <input
                    type="hidden"
                    name="action"
                    value="reset_password"
                >


                <label>

                    New Password

                </label>


                <input
                    type="password"
                    name="password"
                    placeholder="Enter new password"
                    required
                >


                <p class="small">

                    8+ characters • Uppercase •
                    Lowercase • Number • Special character

                </p>


                <label>

                    Confirm Password

                </label>


                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    required
                >


                <button
                    type="submit"
                    style="width:100%;"
                >

                    Reset Password ✓

                </button>


            </form>


        <?php else: ?>


            <!-- =====================================
                 SUCCESS
            ====================================== -->


            <div style="
                text-align:center;
                padding:20px 0;
            ">


                <div style="
                    font-size:50px;
                    margin-bottom:15px;
                ">

                    ✓

                </div>


                <h2>

                    Password updated!

                </h2>


                <p class="sub">

                    Your password has been
                    successfully changed.

                </p>


                <a
                    href="login.php"
                    class="button-link"
                    style="
                        width:100%;
                        margin-top:20px;
                    "
                >

                    Back to Login →

                </a>


            </div>


        <?php endif; ?>


        <?php if ($step != 4): ?>

            <p
                class="center"
                style="margin-top:20px;"
            >

                Remember your password?

                <a href="login.php">

                    Back to Login

                </a>

            </p>

        <?php endif; ?>


    </div>


</div>


</body>

</html>