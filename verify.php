<?php
require "config.php";

$message = "";
$success = "";

/* Check whether registration created a verification session */

if (!isset($_SESSION["verification_user_id"])) {
    die("Verification session expired. Please create your account again.");
}

$user_id = $_SESSION["verification_user_id"];


/* Get user */

$stmt = $conn->prepare(
    "SELECT id, full_name, email, mobile
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    unset($_SESSION["verification_user_id"]);
    die("User not found. Please create your account again.");
}

$user = $result->fetch_assoc();


/* Handle verification */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $otp = trim($_POST["otp"] ?? "");

    /* Dummy OTP */

    if ($otp == "") {

        $message = "Please enter the OTP.";

    } elseif ($otp !== "123456") {

        $message = "Incorrect OTP. Please try again.";

    } else {

        /*
         * For now we are using a dummy OTP.
         * Both email and mobile are marked verified.
         */

        $stmt = $conn->prepare(
            "UPDATE users
             SET email_verified = 1,
                 phone_verified = 1
             WHERE id = ?"
        );

        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {

            unset($_SESSION["verification_user_id"]);

            $success = "Account verified successfully.";

        } else {

            $message = "Verification failed. Please try again.";

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

<title>Verify Account | SMARTSPEND </title>

<link rel="preconnect"
      href="https://fonts.googleapis.com">

<link rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap"
      rel="stylesheet">


<style>

/* ================= RESET ================= */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {

    min-height: 100vh;

    font-family: "DM Sans", sans-serif;

    background:
        linear-gradient(
            135deg,
            #5d57f6,
            #403bd2
        );

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 25px;

}


/* ================= CARD ================= */

.verify-card {

    width: 100%;

    max-width: 450px;

    background: #ffffff;

    border-radius: 22px;

    padding: 42px;

    box-shadow:
        0 25px 60px rgba(35,30,120,0.25);

}


/* ================= BRAND ================= */

.brand {

    text-align: center;

    font-family: "Space Grotesk", sans-serif;

    font-size: 20px;

    font-weight: 700;

    margin-bottom: 35px;

}


/* ================= ICON ================= */

.verify-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 22px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f0efff;

    font-size: 30px;

}


/* ================= TEXT ================= */

.welcome {

    text-align: center;

    color: #5a55ed;

    font-size: 12px;

    font-weight: 700;

    letter-spacing: 1.7px;

    margin-bottom: 8px;

}

h1 {

    text-align: center;

    font-family: "Space Grotesk", sans-serif;

    font-size: 30px;

    margin-bottom: 10px;

}

.description {

    text-align: center;

    color: #858594;

    font-size: 14px;

    line-height: 1.6;

    margin-bottom: 25px;

}


/* ================= USER INFO ================= */

.user-info {

    background: #f7f7fc;

    border-radius: 12px;

    padding: 13px 15px;

    margin-bottom: 22px;

    text-align: center;

    font-size: 13px;

    color: #666678;

}


/* ================= MESSAGE ================= */

.message {

    background: #fff0f0;

    border: 1px solid #ffd2d2;

    color: #d84747;

    border-radius: 10px;

    padding: 11px 13px;

    font-size: 13px;

    margin-bottom: 18px;

}

.success {

    text-align: center;

    background: #effcf4;

    border: 1px solid #c9f0d8;

    color: #249451;

    border-radius: 12px;

    padding: 14px;

    font-size: 14px;

    margin-bottom: 20px;

}


/* ================= LABEL ================= */

label {

    display: block;

    font-size: 13px;

    font-weight: 700;

    color: #4f4f61;

    margin-bottom: 8px;

}


/* ================= OTP ================= */

.otp-input {

    width: 100%;

    height: 58px;

    border: 1px solid #e1e1ea;

    border-radius: 12px;

    outline: none;

    text-align: center;

    letter-spacing: 8px;

    font-size: 22px;

    font-weight: 700;

    font-family: "DM Sans", sans-serif;

}

.otp-input:focus {

    border-color: #5a55ed;

    box-shadow:
        0 0 0 4px rgba(90,85,237,0.10);

}


/* ================= DUMMY OTP ================= */

.demo-otp {

    margin: 14px 0 20px;

    padding: 12px;

    text-align: center;

    border-radius: 10px;

    background: #f0efff;

    color: #514be5;

    font-size: 13px;

}

.demo-otp strong {

    font-size: 16px;

    letter-spacing: 2px;

}


/* ================= BUTTON ================= */

button {

    width: 100%;

    height: 51px;

    border: none;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #5d57f6,
            #4842dc
        );

    color: #ffffff;

    font-family: "DM Sans", sans-serif;

    font-size: 14px;

    font-weight: 700;

    cursor: pointer;

    box-shadow:
        0 8px 20px rgba(79,73,225,0.22);

    transition: 0.2s ease;

}

button:hover {

    transform: translateY(-1px);

    box-shadow:
        0 11px 25px rgba(79,73,225,0.30);

}


/* ================= SUCCESS BUTTON ================= */

.login-button {

    display: block;

    width: 100%;

    height: 51px;

    line-height: 51px;

    text-align: center;

    text-decoration: none;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #5d57f6,
            #4842dc
        );

    color: white;

    font-size: 14px;

    font-weight: 700;

}


/* ================= FOOTER ================= */

.back-login {

    text-align: center;

    margin-top: 20px;

    font-size: 13px;

    color: #858594;

}

.back-login a {

    color: #514be5;

    font-weight: 700;

    text-decoration: none;

}


/* ================= MOBILE ================= */

@media (max-width: 500px) {

    .verify-card {

        padding: 30px 22px;

    }

    h1 {

        font-size: 26px;

    }

}

</style>

</head>


<body>


<div class="verify-card">


    <div class="brand">
        💰 SMARTSPEND
    </div>


    <?php if ($success): ?>

        <!-- SUCCESS -->

        <div class="verify-icon">
            ✓
        </div>

        <p class="welcome">
            ALL SET
        </p>

        <h1>
            Account verified!
        </h1>

        <p class="description">
            Your SMARTSPEND account has been successfully verified.
        </p>

        <div class="success">
            Your email and mobile number have been verified successfully.
        </div>

        <a href="login.php" class="login-button">
            Continue to Login →
        </a>


    <?php else: ?>

        <!-- VERIFICATION -->

        <div class="verify-icon">
            🔐
        </div>

        <p class="welcome">
            VERIFY ACCOUNT
        </p>

        <h1>
            Verify your account
        </h1>

        <p class="description">
            Welcome, <?= htmlspecialchars($user["full_name"]) ?>!
            Enter the OTP below to complete your registration.
        </p>


        <div class="user-info">

            <?= htmlspecialchars($user["email"]) ?>

            <br>

            <?= htmlspecialchars($user["mobile"]) ?>

        </div>


        <?php if ($message): ?>

            <div class="message">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <label>
                Enter OTP
            </label>


            <input
                type="text"
                name="otp"
                class="otp-input"
                placeholder="••••••"
                maxlength="6"
                inputmode="numeric"
                autocomplete="one-time-code"
                required
            >


            <div class="demo-otp">

                Demo OTP:

                <strong>
                    123456
                </strong>

            </div>


            <button type="submit">
                Verify Account ✓
            </button>


        </form>


        <div class="back-login">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>

    <?php endif; ?>


</div>


</body>

</html>