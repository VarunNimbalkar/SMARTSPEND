<?php

require "config.php";

if (isset($_SESSION["user_id"])) {

    header("Location: dashboard.php");

    exit;

}

$login = "";

$message = "";

$wrongPassword = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $login =
        trim($_POST["login"] ?? "");


    $password =
        $_POST["password"] ?? "";

    if ($login == "") {

        $message =
            "Please enter your username or email.";

    }

    elseif ($password == "") {

        $message =
            "Please enter your password.";

    }

    else {
        $stmt = $conn->prepare(
            "SELECT
                id,
                password,
                email_verified,
                phone_verified
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


        $result =
            $stmt->get_result();


        /* =========================
           USER NOT FOUND
        ========================= */

        if ($result->num_rows == 0) {

            $message =
                "Account not found. Please create an account.";

        }

        else {


            $user =
                $result->fetch_assoc();


            /* =========================
               PASSWORD
            ========================= */

            if (!password_verify(
                $password,
                $user["password"]
            )) {

                $message =
                    "Incorrect password. Please try again.";

                $wrongPassword = true;

            }


            /* =========================
               VERIFICATION
            ========================= */

            elseif (
                $user["email_verified"] == 0 ||
                $user["phone_verified"] == 0
            ) {

                $message =
                    "Please verify your email and mobile number first.";

            }


            /* =========================
               SUCCESS
            ========================= */

            else {

                session_regenerate_id(true);


                $_SESSION["user_id"] =
                    $user["id"];


                header(
                    "Location: dashboard.php"
                );


                exit;

            }

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>
        Login | SMARTSPEND
    </title>


    <link
        rel="stylesheet"
        href="assets/style.css"
    >


    <style>


        /* =================================================
           LOGIN PAGE
        ================================================= */

        .login-page {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 25px;

        }


        .login-container {

            width: 100%;

            max-width: 1050px;

            min-height: 620px;

            display: grid;

            grid-template-columns: 1fr 1fr;

            overflow: hidden;

            border-radius: 28px;

            background: white;

            box-shadow:
                0 30px 90px
                rgba(30, 30, 55, 0.13);

        }


        /* =================================================
           LEFT SIDE
        ================================================= */

        .login-visual {

            position: relative;

            overflow: hidden;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

            padding: 45px;

            color: white;

            background:
                linear-gradient(
                    145deg,
                    #635bff,
                    #5148e8 55%,
                    #3f37c9
                );

        }


        .login-visual::before {

            content: "";

            position: absolute;

            width: 330px;

            height: 330px;

            top: -130px;

            right: -120px;

            border-radius: 50%;

            background:
                rgba(255,255,255,0.10);

        }


        .login-visual::after {

            content: "";

            position: absolute;

            width: 250px;

            height: 250px;

            bottom: -100px;

            left: -90px;

            border-radius: 50%;

            background:
                rgba(255,255,255,0.08);

        }


        .visual-content {

            position: relative;

            z-index: 2;

        }


        .visual-logo {

            font-family:
                "Space Grotesk",
                Arial,
                sans-serif;

            font-size: 21px;

            font-weight: 700;

            margin-bottom: 70px;

        }


        .visual-content h1 {

            max-width: 400px;

            margin-bottom: 18px;

            color: white;

            font-size: 43px;

            line-height: 1.08;

        }


        .visual-content > p {

            max-width: 370px;

            color:
                rgba(255,255,255,0.78);

            font-size: 15px;

            line-height: 1.7;

        }


        /* =================================================
           FLOATING MONEY CARD
        ================================================= */

        .money-card {

            position: absolute;

            right: 35px;

            bottom: 50px;

            z-index: 3;

            width: 230px;

            padding: 20px;

            border:
                1px solid
                rgba(255,255,255,0.25);

            border-radius: 18px;

            background:
                rgba(255,255,255,0.13);

            box-shadow:
                0 18px 40px
                rgba(0,0,0,0.12);

            backdrop-filter:
                blur(15px);

        }


        .money-card span {

            display: block;

            margin-bottom: 8px;

            color:
                rgba(255,255,255,0.70);

            font-size: 11px;

        }


        .money-card strong {

            font-family:
                "Space Grotesk",
                sans-serif;

            font-size: 25px;

        }


        .money-card small {

            display: block;

            margin-top: 8px;

            color:
                rgba(255,255,255,0.70);

            font-size: 11px;

        }


        /* =================================================
           FEATURES
        ================================================= */

        .visual-features {

            position: relative;

            z-index: 2;

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

        }


        .visual-feature {

            padding: 8px 12px;

            border:
                1px solid
                rgba(255,255,255,0.18);

            border-radius: 30px;

            background:
                rgba(255,255,255,0.10);

            color:
                rgba(255,255,255,0.90);

            font-size: 11px;

        }


        /* =================================================
           RIGHT SIDE
        ================================================= */

        .login-form-side {

            display: flex;

            align-items: center;

            padding: 55px;

            background: white;

        }


        .login-form {

            width: 100%;

            max-width: 390px;

            margin: auto;

        }


        .login-form .welcome {

            margin-bottom: 8px;

            color: var(--primary);

            font-size: 12px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 1px;

        }


        .login-form h2 {

            margin-bottom: 7px;

            font-size: 32px;

        }


        .login-description {

            margin-bottom: 27px;

            color: var(--muted);

            font-size: 14px;

        }


        /* =================================================
           FORM GROUP
        ================================================= */

        .login-field {

            position: relative;

            margin-bottom: 18px;

        }


        .login-field label {

            margin-top: 0;

        }


        .login-field input {

            height: 50px;

            padding-right: 45px;

        }


        /* =================================================
           PASSWORD BUTTON
        ================================================= */

        .password-toggle {

            position: absolute;

            right: 8px;

            bottom: 5px;

            width: 38px;

            height: 38px;

            min-height: 38px;

            padding: 0;

            border-radius: 9px;

            background: transparent;

            color: #777986;

            box-shadow: none;

        }


        .password-toggle:hover {

            background: var(--surface-soft);

            color: var(--text);

            transform: none;

            box-shadow: none;

        }


        /* =================================================
           LOGIN BUTTON
        ================================================= */

        .login-submit {

            width: 100%;

            height: 51px;

            margin-top: 7px;

            border-radius: 13px;

            font-size: 14px;

        }


        /* =================================================
           CREATE ACCOUNT
        ================================================= */

        .register-box {

            margin-top: 22px;

            padding-top: 20px;

            border-top:
                1px solid
                var(--border);

            text-align: center;

            color: var(--muted);

            font-size: 13px;

        }


        .register-box a {

            margin-left: 4px;

        }


        /* =================================================
           ERROR
        ================================================= */

        .login-error {

            animation:
                errorShake 0.35s ease;

        }


        @keyframes errorShake {

            0% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            50% {
                transform: translateX(5px);
            }

            75% {
                transform: translateX(-3px);
            }

            100% {
                transform: translateX(0);
            }

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 850px) {

            .login-container {

                grid-template-columns: 1fr;

                max-width: 520px;

            }


            .login-visual {

                min-height: 300px;

                padding: 30px;

            }


            .visual-logo {

                margin-bottom: 35px;

            }


            .visual-content h1 {

                font-size: 32px;

            }


            .money-card {

                display: none;

            }


            .login-form-side {

                padding: 35px 28px;

            }

        }


        @media (max-width: 500px) {

            .login-page {

                padding: 12px;

            }


            .login-container {

                border-radius: 20px;

            }


            .login-visual {

                min-height: 270px;

                padding: 25px;

            }


            .visual-content h1 {

                font-size: 29px;

            }


            .visual-content > p {

                font-size: 13px;

            }


            .login-form-side {

                padding: 30px 20px;

            }


            .login-form h2 {

                font-size: 27px;

            }

        }


    </style>

</head>


<body>


<div class="login-page">


    <div class="login-container">


        <!-- =============================================
             LEFT VISUAL SECTION
        ============================================== -->


        <section class="login-visual">


            <div class="visual-content">


                <div class="visual-logo">

                    💰 SMARTSPEND

                </div>


                <div>

                    <h1>
                        Your money.
                        <br>

                        Your control.

                    </h1>


                    <p>

                        Track your spending,
                        understand your habits
                        and make smarter money
                        decisions — all in one place.

                    </p>

                </div>


            </div>

            <div class="visual-features">

                <span class="visual-feature">
                    ✦ Smart Tracking
                </span>

                <span class="visual-feature">
                    ✦ Easy Budgets
                </span>

                <span class="visual-feature">
                    ✦ Clear Insights
                </span>

            </div>


        </section>


        <!-- =============================================
             RIGHT LOGIN SECTION
        ============================================== -->


        <section class="login-form-side">


            <div class="login-form">


                <div class="welcome">

                    Welcome back

                </div>


                <h2>

                    Let's get you in.

                </h2>


                <p class="login-description">

                    Login to continue managing
                    your finances.

                </p>


                <!-- ERROR -->

                <?php if ($message): ?>

                    <div class="message login-error">

                        <?= htmlspecialchars($message) ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    action=""
                >


                    <!-- USERNAME / EMAIL -->

                    <div class="login-field">


                        <label>

                            Username or Email

                        </label>


                        <input
                            type="text"
                            name="login"
                            placeholder="Enter username or email"
                            value="<?= htmlspecialchars($login) ?>"
                            autocomplete="username"
                            required
                        >


                    </div>


                    <!-- PASSWORD -->

                    <div class="login-field">


                        <label>

                            Password

                        </label>


                        <input
                            type="password"
                            name="password"
                            id="loginPassword"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword()"
                            aria-label="Show password"
                        >

                            👁

                        </button>


                    </div>


                    <!-- LOGIN -->

                    <button
                        type="submit"
                        class="login-submit"
                    >

                        Login
                        <span>→</span>

                    </button>

                    <?php if ($wrongPassword): ?>

                        <div style="
                            text-align:center;
                            margin-top:14px;
                        ">

                            <a
                                href="forgot_password.php"
                                style="
                                    color:#e95353;
                                    font-size:13px;
                                    font-weight:700;
                                "
                            >

                                Forgot Password?

                            </a>

                        </div>

                    <?php endif; ?>

                </form>


                <!-- REGISTER -->

                <div class="register-box">

                    Don't have an account?

                    <a href="register.php">

                        Create account

                    </a>

                </div>


            </div>


        </section>


    </div>


</div>


<script>


/* =====================================================
   SHOW / HIDE PASSWORD
===================================================== */

function togglePassword() {


    const password =
        document.getElementById(
            "loginPassword"
        );


    const button =
        document.querySelector(
            ".password-toggle"
        );


    if (
        password.type === "password"
    ) {

        password.type = "text";

        button.textContent = "🙈";

        button.setAttribute(
            "aria-label",
            "Hide password"
        );

    }

    else {

        password.type = "password";

        button.textContent = "👁";

        button.setAttribute(
            "aria-label",
            "Show password"
        );

    }

}

</script>


</body>

</html>