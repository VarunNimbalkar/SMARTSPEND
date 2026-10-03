<?php
require "config.php";

$message = "";

$full_name = "";
$username = "";
$email = "";
$mobileInput = "";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobileInput = trim($_POST["mobile"] ?? "");

    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    if ($full_name == "") {

        $message = "Please enter your full name.";

    } elseif (!preg_match("/^[A-Za-z ]+$/", $full_name)) {

        $message = "Full name can contain only letters and spaces.";

    } elseif ($username == "") {

        $message = "Please enter a username.";

    } elseif (strlen($username) < 5) {

        $message = "Username must contain at least 5 characters.";

    } elseif (!preg_match("/^[A-Za-z0-9]+$/", $username)) {

        $message = "Username can contain only letters and numbers.";

    } elseif (!preg_match("/[A-Za-z]/", $username) ||
              !preg_match("/[0-9]/", $username)) {

        $message = "Username must contain both letters and numbers.";

    } elseif ($email == "") {

        $message = "Please enter your email.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif ($mobileInput == "") {

        $message = "Please enter your mobile number.";

    } elseif (!preg_match("/^[6-9][0-9]{9}$/", $mobileInput)) {

        $message = "Please enter a valid 10-digit Indian mobile number.";

    } else {

        $mobile = "+91" . $mobileInput;

        if (strlen($password) < 8) {

            $message = "Password must contain at least 8 characters.";

        } elseif (!preg_match("/[A-Z]/", $password)) {

            $message = "Password must contain at least one capital letter.";

        } elseif (!preg_match("/[a-z]/", $password)) {

            $message = "Password must contain at least one small letter.";

        } elseif (!preg_match("/[0-9]/", $password)) {

            $message = "Password must contain at least one number.";

        } elseif (!preg_match("/[^A-Za-z0-9]/", $password)) {

            $message = "Password must contain at least one special character.";

        } elseif ($password !== $confirm_password) {

            $message = "Passwords do not match.";

        }

    }


    if ($message == "") {

        $check = $conn->prepare(
            "SELECT id FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        $check->bind_param(
            "ss",
            $username,
            $email
        );

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Username or email already exists.";

        }

    }


    if ($message == "") {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $email_verified = 1;
        $phone_verified = 1;
        $role = "user";


        $stmt = $conn->prepare(
            "INSERT INTO users
            (full_name, username, email, mobile, password,
             email_verified, phone_verified, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "sssssiis",
            $full_name,
            $username,
            $email,
            $mobile,
            $hashedPassword,
            $email_verified,
            $phone_verified,
            $role
        );


        if ($stmt->execute()) {

            $userId = $stmt->insert_id;

            session_regenerate_id(true);

            $_SESSION["user_id"] = $userId;

            header("Location: dashboard.php");

            exit;

        } else {

            $message = "Unable to create account. Please try again.";

        }

    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Create Account | SMARTSPEND</title>

<link rel="preconnect"
      href="https://fonts.googleapis.com">

<link rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap"
      rel="stylesheet">


<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


:root {

    --purple: #5D57F6;

    --purple-dark: #4D47E8;

    --purple-light: #7772FF;

    --text: #24233D;

    --muted: #85859A;

    --border: #E8E7F0;

}


body {

    min-height: 100vh;

    background: #F7F7FB;

    font-family: "DM Sans", sans-serif;

    color: var(--text);

    padding: 30px;

}


.auth-container {

    width: min(1355px, 100%);

    min-height: 780px;

    margin: auto;

    display: grid;

    grid-template-columns: 48% 52%;

    background: white;

    border-radius: 38px;

    overflow: hidden;

    box-shadow:
        0 25px 70px rgba(42, 38, 110, 0.12);

}


/* ==================================================
   LEFT PURPLE PANEL
================================================== */

.left-panel {

    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            145deg,
            #615BFF 0%,
            #514BE9 55%,
            #443ED5 100%
        );

    color: white;

    padding: 65px 58px;

    display: flex;

    flex-direction: column;

    justify-content: center;

}


/* TOP RIGHT CURVE */

.left-panel::before {

    content: "";

    position: absolute;

    width: 440px;

    height: 440px;

    border-radius: 50%;

    background: rgba(255,255,255,0.10);

    top: -185px;

    right: -150px;

}


/* BOTTOM LEFT CURVE */

.left-panel::after {

    content: "";

    position: absolute;

    width: 360px;

    height: 360px;

    border-radius: 50%;

    background: rgba(255,255,255,0.08);

    bottom: -210px;

    left: -170px;

}


/* SMALL EXTRA CURVE */

.curve-small {

    position: absolute;

    width: 190px;

    height: 190px;

    border-radius: 50%;

    background: rgba(255,255,255,0.055);

    right: -90px;

    bottom: 60px;

}


/* CONTENT */

.left-content {

    position: relative;

    z-index: 5;

}


.logo {

    display: flex;

    align-items: center;

    gap: 11px;

    font-family: "Space Grotesk", sans-serif;

    font-size: 23px;

    font-weight: 700;

    margin-bottom: 95px;

}


.logo-money {

    font-size: 23px;

}


.eyebrow {

    font-size: 13px;

    font-weight: 700;

    letter-spacing: 2px;

    margin-bottom: 24px;

}


.left-title {

    font-family: "Space Grotesk", sans-serif;

    font-size: 52px;

    line-height: 1.05;

    letter-spacing: -2px;

    font-weight: 700;

}


.left-description {

    max-width: 570px;

    margin-top: 26px;

    font-size: 17px;

    line-height: 1.65;

    color: rgba(255,255,255,0.93);

}


.feature-pills {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

    margin-top: 38px;

}


.pill {

    padding: 10px 17px;

    border-radius: 30px;

    border: 1px solid rgba(255,255,255,0.30);

    background: rgba(255,255,255,0.08);

    font-size: 12px;

    color: white;

}


/* ==================================================
   RIGHT FORM PANEL
================================================== */

.right-panel {

    background: #FFFFFF;

    padding: 55px 75px;

    display: flex;

    align-items: center;

}


.form-wrapper {

    width: 100%;

    max-width: 680px;

    margin: auto;

}


.form-eyebrow {

    color: var(--purple);

    font-size: 13px;

    font-weight: 700;

    letter-spacing: 2px;

    margin-bottom: 13px;

}


.form-title {

    font-family: "Space Grotesk", sans-serif;

    font-size: 38px;

    line-height: 1.1;

    letter-spacing: -1.2px;

    font-weight: 700;

}


.form-subtitle {

    color: var(--muted);

    font-size: 15px;

    margin-top: 10px;

    margin-bottom: 28px;

}


.message {

    background: #FFF0F0;

    border: 1px solid #FFD5D5;

    color: #C93E3E;

    padding: 12px 14px;

    border-radius: 9px;

    font-size: 13px;

    margin-bottom: 18px;

}


.form-group {

    margin-bottom: 17px;

}


label {

    display: block;

    font-size: 13px;

    font-weight: 700;

    color: #4E4E61;

    margin-bottom: 8px;

}


input {

    width: 100%;

    height: 58px;

    border: 1px solid var(--border);

    border-radius: 15px;

    padding: 0 18px;

    outline: none;

    background: #FBFBFD;

    font-family: "DM Sans", sans-serif;

    font-size: 14px;

    color: var(--text);

    transition: 0.2s;

}


input:focus {

    border-color: var(--purple);

    background: white;

    box-shadow:
        0 0 0 4px rgba(93,87,246,0.08);

}


input::placeholder {

    color: #A5A5B6;

}


.help-text {

    font-size: 11px;

    color: #9999AA;

    margin-top: 6px;

}


.mobile-box {

    display: flex;

}


.country-code {

    height: 58px;

    min-width: 67px;

    display: flex;

    align-items: center;

    justify-content: center;

    border: 1px solid var(--border);

    border-right: none;

    border-radius: 15px 0 0 15px;

    background: #F9F9FC;

    color: #5D5D6D;

    font-size: 14px;

}


.mobile-box input {

    border-radius: 0 15px 15px 0;

}


.password-wrapper {

    position: relative;

}


.password-wrapper input {

    padding-right: 50px;

}


.password-toggle {

    position: absolute;

    right: 18px;

    top: 50%;

    transform: translateY(-50%);

    cursor: pointer;

    color: #777789;

    font-size: 17px;

}


.submit-btn {

    width: 100%;

    height: 58px;

    border: none;

    border-radius: 15px;

    background: var(--purple);

    color: white;

    font-family: "DM Sans", sans-serif;

    font-size: 15px;

    font-weight: 700;

    cursor: pointer;

    margin-top: 8px;

    box-shadow:
        0 10px 25px rgba(93,87,246,0.22);

    transition: 0.2s;

}


.submit-btn:hover {

    background: var(--purple-dark);

    transform: translateY(-1px);

}


.login-text {

    text-align: center;

    border-top: 1px solid #ECEBF2;

    margin-top: 22px;

    padding-top: 20px;

    color: #7E7E90;

    font-size: 13px;

}


.login-text a {

    color: var(--purple);

    font-weight: 700;

}


@media(max-width: 1000px) {

    body {
        padding: 15px;
    }

    .auth-container {
        grid-template-columns: 1fr;
    }

    .left-panel {
        min-height: 430px;
        padding: 45px;
    }

    .logo {
        margin-bottom: 55px;
    }

    .right-panel {
        padding: 45px;
    }

}


@media(max-width: 600px) {

    body {
        padding: 0;
    }

    .auth-container {
        min-height: 100vh;
        border-radius: 0;
    }

    .left-panel {
        min-height: 390px;
        padding: 35px 28px;
    }

    .left-title {
        font-size: 40px;
    }

    .left-description {
        font-size: 15px;
    }

    .right-panel {
        padding: 35px 25px;
    }

    .form-title {
        font-size: 31px;
    }

}

</style>

</head>


<body>


<div class="auth-container">


    <!-- ============================================
         LEFT SIDE
    ============================================= -->

    <section class="left-panel">

        <div class="curve-small"></div>


        <div class="left-content">


            <div class="logo">

                <span class="logo-money">
                    💰
                </span>

                <span>
                    SMARTSPEND
                </span>

            </div>


            <div class="eyebrow">
                TAKE CONTROL OF YOUR MONEY
            </div>


            <h1 class="left-title">

                Start tracking.

                <br>

                Start saving.

            </h1>


            <p class="left-description">

                Create your account and keep your income,
                expenses and budgets organized — all in one place.

            </p>


            <div class="feature-pills">

                <div class="pill">
                    ✦ Smart Tracking
                </div>

                <div class="pill">
                    ✦ Easy Budgets
                </div>

                <div class="pill">
                    ✦ Clear Insights
                </div>

            </div>


        </div>

    </section>



    <!-- ============================================
         RIGHT SIDE
    ============================================= -->

    <section class="right-panel">

        <div class="form-wrapper">


            <div class="form-eyebrow">
                GET STARTED
            </div>


            <h2 class="form-title">
                Create your account
            </h2>


            <p class="form-subtitle">

                Join SMARTSPEND and take control of your money.

            </p>


            <?php if ($message): ?>

                <div class="message">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- FULL NAME -->

                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        placeholder="Enter your full name"
                        value="<?= htmlspecialchars($full_name) ?>"
                        required
                    >

                </div>


                <!-- USERNAME -->

                <div class="form-group">

                    <label>
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        placeholder="Example: varun123"
                        value="<?= htmlspecialchars($username) ?>"
                        required
                    >

                    <div class="help-text">
                        5+ characters • Letters + numbers
                    </div>

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars($email) ?>"
                        required
                    >

                </div>


                <!-- MOBILE -->

                <div class="form-group">

                    <label>
                        Mobile Number
                    </label>

                    <div class="mobile-box">

                        <div class="country-code">
                            +91
                        </div>

                        <input
                            type="text"
                            name="mobile"
                            placeholder="10-digit mobile number"
                            maxlength="10"
                            inputmode="numeric"
                            value="<?= htmlspecialchars($mobileInput) ?>"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label>
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Create a strong password"
                            required
                        >

                        <span
                            class="password-toggle"
                            onclick="togglePassword('password', this)"
                        >
                            ◉
                        </span>

                    </div>

                    <div class="help-text">

                        8+ characters • Uppercase • Lowercase • Number • Special character

                    </div>

                </div>


                <!-- CONFIRM PASSWORD -->

                <div class="form-group">

                    <label>
                        Confirm Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            placeholder="Confirm your password"
                            required
                        >

                        <span
                            class="password-toggle"
                            onclick="togglePassword('confirm_password', this)"
                        >
                            ◉
                        </span>

                    </div>

                </div>


                <button
                    type="submit"
                    class="submit-btn"
                >

                    Create Account →

                </button>


            </form>


            <div class="login-text">

                Already have an account?

                <a href="login.php">
                    Login
                </a>

            </div>


        </div>

    </section>


</div>


<script>

function togglePassword(id, icon) {

    const input = document.getElementById(id);

    if (input.type === "password") {

        input.type = "text";

        icon.textContent = "◉";

    } else {

        input.type = "password";

        icon.textContent = "◉";

    }

}

</script>


</body>

</html>