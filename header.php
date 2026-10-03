<?php

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit;
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

    <title>SMARTSPEND</title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>


<body>


<!-- =========================
     LEFT SIDEBAR
========================= -->

<aside class="sidebar">


    <!-- LOGO -->

    <div class="logo">

        💰 SMARTSPEND

    </div>


    <!-- DASHBOARD -->

    <a href="dashboard.php">

        🏠 Dashboard

    </a>


    <!-- INCOME -->

    <a href="income.php">

        💵 Income

    </a>


    <!-- EXPENSE -->

    <a href="expense.php">

        💸 Expenses

    </a>


    <!-- BUDGET -->

    <a href="budget.php">

        📊 Budget

    </a>


    <!-- CATEGORIES -->

    <a href="categories.php">

        📁 Categories

    </a>


    <!-- REPORTS -->

    <a href="reports.php">

        📄 Reports

    </a>


    <!-- ADMIN -->

    <a href="admin.php">

        ⚙️ Admin Panel

    </a>


    <!-- LOGOUT -->

    <a
        class="logout"
        href="logout.php"
    >

        ↪ Logout

    </a>


</aside>


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="content">