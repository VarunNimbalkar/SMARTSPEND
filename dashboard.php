<?php

require "config.php";

/* =========================================================
   AUTHENTICATION
========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


/* =========================================================
   VARIABLES
========================================================= */

$profile_message = "";
$profile_error = "";
$open_profile_popup = false;


/* =========================================================
   GET USER
========================================================= */

$stmt = $conn->prepare(
    "SELECT full_name, profile_image
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

$name = $user["full_name"] ?? "User";
$profile_image = $user["profile_image"] ?? "";


/* =========================================================
   UPLOAD PROFILE PHOTO
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["upload_profile_photo"])
) {

    if (
        !isset($_FILES["profile_image"])
        || $_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK
    ) {

        $profile_error = "Please select an image.";
        $open_profile_popup = true;

    } else {

        $file = $_FILES["profile_image"];

        /* Maximum file size = 5 MB */

        if ($file["size"] > 5 * 1024 * 1024) {

            $profile_error = "Image must be smaller than 5 MB.";
            $open_profile_popup = true;

        } else {

            /* Allowed MIME types */

            $allowed_types = [
                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/webp" => "webp"
            ];

            $mime_type = mime_content_type($file["tmp_name"]);


            if (!isset($allowed_types[$mime_type])) {

                $profile_error =
                    "Only JPG, PNG and WEBP images are allowed.";

                $open_profile_popup = true;

            } else {

                $extension = $allowed_types[$mime_type];


                /* Create unique filename */

                $new_filename =
                    "profile_" .
                    $user_id .
                    "_" .
                    time() .
                    "." .
                    $extension;


                /* Upload directory */

                $upload_dir = __DIR__ . "/uploads/";


                /* Create directory if it doesn't exist */

                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0755,
                        true
                    );
                }


                $new_file_path =
                    $upload_dir . $new_filename;


                $database_path =
                    "uploads/" . $new_filename;


                /* Move uploaded file */

                if (
                    move_uploaded_file(
                        $file["tmp_name"],
                        $new_file_path
                    )
                ) {

                    /*
                     * Delete old profile photo
                     */

                    if (!empty($profile_image)) {

                        $old_file =
                            __DIR__ . "/" . $profile_image;

                        if (
                            file_exists($old_file)
                            && is_file($old_file)
                        ) {

                            unlink($old_file);
                        }
                    }


                    /*
                     * Save new image path
                     */

                    $update_photo = $conn->prepare(
                        "UPDATE users
                         SET profile_image = ?
                         WHERE id = ?"
                    );

                    $update_photo->bind_param(
                        "si",
                        $database_path,
                        $user_id
                    );

                    $update_photo->execute();


                    $profile_image = $database_path;

                    $profile_message =
                        "Profile photo updated successfully.";

                } else {

                    $profile_error =
                        "Unable to upload the image.";

                    $open_profile_popup = true;
                }
            }
        }
    }
}


/* =========================================================
   REMOVE PROFILE PHOTO
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["remove_profile_photo"])
) {

    if (!empty($profile_image)) {

        $old_file =
            __DIR__ . "/" . $profile_image;

        if (
            file_exists($old_file)
            && is_file($old_file)
        ) {

            unlink($old_file);
        }
    }


    $remove_photo = $conn->prepare(
        "UPDATE users
         SET profile_image = NULL
         WHERE id = ?"
    );

    $remove_photo->bind_param(
        "i",
        $user_id
    );

    $remove_photo->execute();


    $profile_image = "";

    header("Location: dashboard.php");
    exit;
}


/* =========================================================
   RESET ALL FINANCIAL DATA
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["reset_all_data"])
) {

    /*
     * Delete every transaction belonging
     * to the currently logged-in user.
     */

    $reset_transactions = $conn->prepare(
        "DELETE FROM transactions
         WHERE user_id = ?"
    );

    $reset_transactions->bind_param(
        "i",
        $user_id
    );

    $reset_transactions->execute();


    /*
     * If you have a separate budgets table,
     * you can add its DELETE query here.
     *
     * Example:
     *
     * DELETE FROM budgets WHERE user_id = ?
     *
     * Do not add it unless that table exists.
     */


    header("Location: dashboard.php");
    exit;
}


/* =========================================================
   GET TRANSACTION TOTALS
========================================================= */

$total_income = 0;
$total_expense = 0;


/*
 * Get income
 */

$income_stmt = $conn->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE user_id = ?
     AND type = 'income'"
);

$income_stmt->bind_param(
    "i",
    $user_id
);

$income_stmt->execute();

$income_result = $income_stmt->get_result();
$income_row = $income_result->fetch_assoc();

$total_income =
    (float)($income_row["total"] ?? 0);


/*
 * Get expenses
 */

$expense_stmt = $conn->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM transactions
     WHERE user_id = ?
     AND type = 'expense'"
);

$expense_stmt->bind_param(
    "i",
    $user_id
);

$expense_stmt->execute();

$expense_result = $expense_stmt->get_result();
$expense_row = $expense_result->fetch_assoc();

$total_expense =
    (float)($expense_row["total"] ?? 0);


/*
 * Calculate balance
 */

$total_balance =
    $total_income - $total_expense;


/* =========================================================
   MONTHLY BUDGET
========================================================= */

/*
 * Change this value if your project has a budget table.
 *
 * For now this keeps the same ₹30,000 budget
 * shown in your original dashboard.
 */

$budget = 30000;


/*
 * Budget percentage
 */

$budget_used = $budget > 0
    ? ($total_expense / $budget) * 100
    : 0;


/*
 * Prevent progress bar from exceeding 100%
 */

$budget_progress =
    min(100, max(0, $budget_used));


/* =========================================================
   RECENT TRANSACTIONS
========================================================= */

$transactions = [];


$transaction_stmt = $conn->prepare(
    "SELECT
        description,
        amount,
        type,
        trans_date
     FROM transactions
     WHERE user_id = ?
     ORDER BY trans_date DESC
     LIMIT 5"
);

$transaction_stmt->bind_param(
    "i",
    $user_id
);

$transaction_stmt->execute();

$transaction_result =
    $transaction_stmt->get_result();


while (
    $row =
    $transaction_result->fetch_assoc()
) {

    $transactions[] = $row;
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Dashboard | SMARTSPEND</title>


<script src="https://cdn.tailwindcss.com"></script>


<link rel="preconnect"
      href="https://fonts.googleapis.com">


<link rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin>


<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>


<script>

tailwind.config = {

    theme: {

        extend: {

            colors: {

                primary: '#0056B3',

                navy: '#17324D',

                surface: '#F5F7FA',

                income: '#287D4F',

                expense: '#C94C4C'

            },

            fontFamily: {

                inter: ['Inter', 'sans-serif']

            }

        }

    }

}

</script>


<style>

body {
    font-family: 'Inter', sans-serif;
}

.soft-shadow {
    box-shadow:
        0 3px 14px rgba(23, 50, 77, 0.07);
}


/* Profile dropdown animation */

.profile-menu {
    transform-origin: top right;
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}


/* Popup */

.profile-popup {
    backdrop-filter: blur(3px);
}

</style>

</head>


<body class="bg-surface text-navy">


<!-- =====================================================
     NAVBAR
===================================================== -->

<header
    class="bg-white
           border-b border-gray-200
           sticky top-0
           z-50"
>

    <div
        class="max-w-[1400px]
               mx-auto
               px-5 lg:px-8"
    >

        <div
            class="h-20
                   flex
                   items-center
                   gap-6"
        >


            <!-- LOGO -->

            <a
                href="dashboard.php"
                class="flex
                       items-center
                       gap-2
                       shrink-0"
            >

                <div
                    class="w-9 h-9
                           bg-primary
                           text-white
                           flex
                           items-center
                           justify-center
                           font-bold"
                >

                    ₹

                </div>


                <span
                    class="font-extrabold
                           text-xl"
                >

                    SMARTSPEND

                </span>

            </a>



            <!-- EXPLORE -->

            <button
                type="button"
                class="hidden md:flex
                       items-center
                       gap-2
                       px-4 py-3
                       text-sm
                       font-semibold
                       hover:bg-gray-100"
            >

                Explore Categories

                <span>⌄</span>

            </button>



            <!-- SEARCH -->

            <div
                class="hidden
                       lg:block
                       flex-1
                       max-w-xl"
            >

                <div class="relative">

                    <span
                        class="absolute
                               left-4
                               top-1/2
                               -translate-y-1/2"
                    >
                        🔍
                    </span>


                    <input
                        type="text"
                        placeholder="Search for transactions, categories, or tags..."
                        class="w-full
                               h-12
                               border
                               border-gray-300
                               pl-11
                               pr-4
                               text-sm
                               focus:outline-none
                               focus:ring-2
                               focus:ring-primary"
                    >

                </div>

            </div>



            <!-- RIGHT SIDE -->

            <div
                class="ml-auto
                       flex
                       items-center
                       gap-5"
            >


                <!-- ANALYTICS -->

                <a
                    href="reports.php"
                    class="hidden
                           xl:block
                           text-sm
                           font-semibold
                           hover:text-primary"
                >

                    Analytics

                </a>



                <!-- BUDGETS -->

                <a
                    href="budget.php"
                    class="hidden
                           xl:block
                           text-sm
                           font-semibold
                           hover:text-primary"
                >

                    Budgets

                </a>



                <!-- NOTIFICATION -->

                <button
                    type="button"
                    class="text-xl"
                >

                    🔔

                </button>



                <!-- =================================================
                     PROFILE CONTAINER
                ================================================= -->

                <div
                    id="profileContainer"
                    class="relative"
                >


                    <!-- PROFILE BUTTON -->

                    <button
                        type="button"
                        id="profileButton"
                        onclick="toggleProfileMenu(event)"
                        class="w-10
                               h-10
                               rounded-full
                               overflow-hidden
                               focus:outline-none
                               focus:ring-2
                               focus:ring-primary"
                    >

                        <?php if (!empty($profile_image)): ?>

                            <img
                                src="<?= htmlspecialchars($profile_image) ?>"
                                alt="Profile Photo"
                                class="w-full
                                       h-full
                                       object-cover"
                            >

                        <?php else: ?>

                            <div
                                class="w-full
                                       h-full
                                       bg-primary
                                       text-white
                                       flex
                                       items-center
                                       justify-center
                                       font-bold"
                            >

                                <?= strtoupper(
                                    substr($name, 0, 1)
                                ) ?>

                            </div>

                        <?php endif; ?>

                    </button>



                    <!-- =================================================
                         PROFILE DROPDOWN
                    ================================================= -->

                    <div
                        id="profileMenu"
                        class="profile-menu
                               hidden
                               absolute
                               right-0
                               top-12
                               w-60
                               bg-white
                               border
                               border-gray-200
                               shadow-xl
                               z-[200]"
                    >


                        <!-- UPDATE PHOTO -->

                        <button
                            type="button"
                            onclick="openProfilePopup()"
                            class="w-full
                                   flex
                                   items-center
                                   gap-3
                                   px-5
                                   py-4
                                   text-sm
                                   text-left
                                   hover:bg-gray-50"
                        >

                            <span class="text-lg">
                            </span>

                            <span>
                                Update New Photo
                            </span>

                        </button>



                        <!-- REMOVE PHOTO -->

                        <form method="POST">

                            <button
                                type="submit"
                                name="remove_profile_photo"
                                onclick="
                                    return confirm(
                                        'Are you sure you want to remove your profile photo?'
                                    );
                                "
                                class="w-full
                                       flex
                                       items-center
                                       gap-3
                                       px-5
                                       py-4
                                       text-sm
                                       text-left
                                       hover:bg-gray-50"
                            >

                                <span class="text-lg">
                                </span>

                                <span>
                                    Remove Photo
                                </span>

                            </button>

                        </form>



                        <!-- RESET DATA -->

                        <form method="POST">

                            <button
                                type="submit"
                                name="reset_all_data"
                                onclick="
                                    return confirm(
                                        'WARNING: This will permanently delete all your financial transactions. Are you sure you want to continue?'
                                    );
                                "
                                class="w-full
                                       flex
                                       items-center
                                       gap-3
                                       px-5
                                       py-4
                                       text-sm
                                       text-left
                                       text-red-600
                                       hover:bg-red-50"
                            >

                                <span class="text-lg">
                                </span>

                                <span>
                                    Reset Data
                                </span>

                            </button>

                        </form>



                        <!-- SEPARATOR -->

                        <div
                            class="border-t
                                   border-gray-200"
                        ></div>



                        <!-- LOGOUT -->

                        <a
                            href="logout.php"
                            class="w-full
                                   flex
                                   items-center
                                   gap-3
                                   px-5
                                   py-4
                                   text-sm
                                   text-left
                                   text-red-600
                                   hover:bg-red-50"
                        >

                            <span class="text-lg">
                            </span>

                            <span>
                                Logout
                            </span>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</header>



<!-- =====================================================
     MAIN
===================================================== -->

<main
    class="max-w-[1400px]
           mx-auto
           px-5 lg:px-8
           py-10"
>


<!-- =====================================================
     WELCOME HERO
===================================================== -->

<section
    class="bg-white
           border
           border-gray-200
           soft-shadow
           p-8 lg:p-10
           mb-8"
>

    <div
        class="grid
               lg:grid-cols-2
               gap-10
               items-center"
    >


        <!-- LEFT -->

        <div>

            <p
                class="text-primary
                       text-xs
                       uppercase
                       tracking-widest
                       font-bold
                       mb-3"
            >

                MY FINANCIAL DASHBOARD

            </p>


            <h1
                class="text-3xl
                       md:text-4xl
                       font-extrabold
                       tracking-tight"
            >

                Welcome back,
                <?= htmlspecialchars($name) ?>.

            </h1>


            <p
                class="text-lg
                       text-gray-600
                       mt-4
                       max-w-xl"
            >

                What are we tracking today?

                Keep your income, expenses and
                budgets organized in one place.

            </p>


            <div
                class="flex
                       flex-wrap
                       gap-3
                       mt-7"
            >


                <a
                    href="income.php"
                    class="bg-primary
                           text-white
                           px-5 py-3
                           text-sm
                           font-bold
                           hover:opacity-90"
                >

                    + Add Transaction

                </a>


                <a
                    href="reports.php"
                    class="border-2
                           border-primary
                           text-primary
                           px-5 py-2.5
                           text-sm
                           font-bold
                           hover:bg-blue-50"
                >

                    View Reports

                </a>

            </div>

        </div>



        <!-- RIGHT PROFILE IMAGE -->

        <div
            class="hidden
                   lg:flex
                   justify-center"
        >

            <?php if (!empty($profile_image)): ?>

                <img
                    src="<?= htmlspecialchars($profile_image) ?>"
                    alt="Profile Photo"
                    class="w-44
                           h-44
                           rounded-full
                           object-cover
                           border-4
                           border-white
                           shadow-lg"
                >

            <?php else: ?>

                <div
                    class="w-44
                           h-44
                           rounded-full
                           bg-primary
                           text-white
                           flex
                           items-center
                           justify-center
                           text-5xl
                           font-bold
                           shadow-lg"
                >

                    <?= strtoupper(
                        substr($name, 0, 1)
                    ) ?>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>



<!-- =====================================================
     TABS
===================================================== -->

<div
    class="bg-white
           border
           border-gray-200
           mb-8"
>

    <div
        class="flex
               overflow-x-auto"
    >

        <a
            href="dashboard.php"
            class="px-7
                   py-5
                   border-b-4
                   border-primary
                   text-primary
                   font-bold
                   text-sm
                   whitespace-nowrap"
        >

            Overview

        </a>


        <a
            href="income.php"
            class="px-7
                   py-5
                   text-gray-600
                   hover:text-primary
                   font-semibold
                   text-sm
                   whitespace-nowrap"
        >

            Income

        </a>


        <a
            href="expense.php"
            class="px-7
                   py-5
                   text-gray-600
                   hover:text-primary
                   font-semibold
                   text-sm
                   whitespace-nowrap"
        >

            Expenses

        </a>


        <a
            href="budget.php"
            class="px-7
                   py-5
                   text-gray-600
                   hover:text-primary
                   font-semibold
                   text-sm
                   whitespace-nowrap"
        >

            Budgets

        </a>

    </div>

</div>



<!-- =====================================================
     SUMMARY CARDS
===================================================== -->

<section
    class="grid
           sm:grid-cols-2
           xl:grid-cols-4
           gap-5
           mb-8"
>


    <!-- BALANCE -->

    <div
        class="bg-white
               border
               border-gray-200
               p-6
               soft-shadow"
    >

        <div
            class="flex
                   justify-between"
        >

            <div>

                <p
                    class="text-xs
                           uppercase
                           tracking-wider
                           text-gray-500
                           font-semibold"
                >

                    Total Balance

                </p>


                <h2
                    class="text-2xl
                           font-extrabold
                           mt-3"
                >

                    ₹<?= number_format(
                        $total_balance,
                        2
                    ) ?>

                </h2>

            </div>


            <span class="text-2xl">
                💳
            </span>

        </div>


        <p
            class="text-xs
                   text-income
                   font-semibold
                   mt-4"
        >

            Your current balance

        </p>

    </div>



    <!-- INCOME -->

    <div
        class="bg-white
               border
               border-gray-200
               p-6
               soft-shadow"
    >

        <div
            class="flex
                   justify-between"
        >

            <div>

                <p
                    class="text-xs
                           uppercase
                           tracking-wider
                           text-gray-500
                           font-semibold"
                >

                    Income

                </p>


                <h2
                    class="text-2xl
                           font-extrabold
                           text-income
                           mt-3"
                >

                    ₹<?= number_format(
                        $total_income,
                        2
                    ) ?>

                </h2>

            </div>


            <span class="text-2xl">
                ↗
            </span>

        </div>


        <p
            class="text-xs
                   text-gray-500
                   mt-4"
        >

            Total income

        </p>

    </div>



    <!-- EXPENSE -->

    <div
        class="bg-white
               border
               border-gray-200
               p-6
               soft-shadow"
    >

        <div
            class="flex
                   justify-between"
        >

            <div>

                <p
                    class="text-xs
                           uppercase
                           tracking-wider
                           text-gray-500
                           font-semibold"
                >

                    Expenses

                </p>


                <h2
                    class="text-2xl
                           font-extrabold
                           text-expense
                           mt-3"
                >

                    ₹<?= number_format(
                        $total_expense,
                        2
                    ) ?>

                </h2>

            </div>


            <span class="text-2xl">
                ↘
            </span>

        </div>


        <p
            class="text-xs
                   text-gray-500
                   mt-4"
        >

            Total expenses

        </p>

    </div>



    <!-- BUDGET -->

    <div
        class="bg-white
               border
               border-gray-200
               p-6
               soft-shadow"
    >

        <div
            class="flex
                   justify-between"
        >

            <div>

                <p
                    class="text-xs
                           uppercase
                           tracking-wider
                           text-gray-500
                           font-semibold"
                >

                    Budget Used

                </p>


                <h2
                    class="text-2xl
                           font-extrabold
                           mt-3"
                >

                    <?= round($budget_used) ?>%

                </h2>

            </div>


            <span class="text-2xl">
                🎯
            </span>

        </div>


        <div
            class="w-full
                   h-2
                   bg-gray-200
                   mt-5"
        >

            <div
                class="h-2
                       <?= $budget_used >= 90
                           ? 'bg-expense'
                           : 'bg-primary' ?>"
                style="
                    width:
                    <?= $budget_progress ?>%
                "
            ></div>

        </div>

    </div>

</section>



<!-- =====================================================
     MAIN GRID
===================================================== -->

<section
    class="grid
           lg:grid-cols-3
           gap-8"
>


    <!-- LEFT -->

    <div
        class="lg:col-span-2
               space-y-8"
    >


        <!-- BUDGET -->

        <div
            class="bg-white
                   border
                   border-gray-200
                   p-7
                   soft-shadow"
        >

            <div
                class="flex
                       justify-between
                       items-center
                       mb-6"
            >

                <div>

                    <h2
                        class="font-bold
                               text-lg"
                    >

                        Budget Overview

                    </h2>


                    <p
                        class="text-sm
                               text-gray-500
                               mt-1"
                    >

                        Track your monthly spending

                    </p>

                </div>


                <a
                    href="budget.php"
                    class="text-primary
                           text-sm
                           font-bold"
                >

                    Manage →

                </a>

            </div>


            <div>

                <div
                    class="flex
                           justify-between
                           text-sm
                           mb-2"
                >

                    <span>
                        Monthly expenses
                    </span>


                    <span class="font-bold">

                        ₹<?= number_format(
                            $total_expense,
                            2
                        ) ?>

                        /

                        ₹<?= number_format(
                            $budget,
                            2
                        ) ?>

                    </span>

                </div>


                <div
                    class="h-3
                           bg-gray-200"
                >

                    <div
                        class="h-3
                               <?= $budget_used >= 90
                                   ? 'bg-expense'
                                   : 'bg-primary' ?>"
                        style="
                            width:
                            <?= $budget_progress ?>%
                        "
                    ></div>

                </div>


                <div
                    class="flex
                           justify-between
                           mt-2"
                >

                    <span
                        class="text-xs
                               text-gray-500"
                    >

                        <?= round($budget_used) ?>% used

                    </span>


                    <span
                        class="text-xs
                               font-semibold
                               <?= $budget_used > 100
                                   ? 'text-expense'
                                   : 'text-income' ?>"
                    >

                        <?= $budget_used > 100
                            ? 'Over Budget'
                            : 'On Track' ?>

                    </span>

                </div>

            </div>

        </div>



        <!-- RECENT TRANSACTIONS -->

        <div
            class="bg-white
                   border
                   border-gray-200
                   soft-shadow"
        >

            <div
                class="p-7
                       border-b
                       border-gray-200
                       flex
                       justify-between"
            >

                <div>

                    <h2
                        class="font-bold
                               text-lg"
                    >

                        Recent Transactions

                    </h2>


                    <p
                        class="text-sm
                               text-gray-500
                               mt-1"
                    >

                        Your latest financial activity

                    </p>

                </div>


                <a
                    href="reports.php"
                    class="text-primary
                           text-sm
                           font-bold"
                >

                    View All →

                </a>

            </div>



            <?php if (count($transactions) > 0): ?>


                <?php foreach (
                    $transactions
                    as $transaction
                ): ?>


                    <div
                        class="px-7
                               py-5
                               border-b
                               border-gray-100
                               flex
                               items-center
                               justify-between
                               hover:bg-gray-50"
                    >

                        <div
                            class="flex
                                   items-center
                                   gap-4"
                        >

                            <div
                                class="w-11
                                       h-11
                                       bg-gray-100
                                       flex
                                       items-center
                                       justify-center"
                            >

                                <?= $transaction["type"] === "income"
                                    ? "↗"
                                    : "↘" ?>

                            </div>


                            <div>

                                <p
                                    class="font-semibold
                                           text-sm"
                                >

                                    <?= htmlspecialchars(
                                        $transaction["description"]
                                    ) ?>

                                </p>


                                <p
                                    class="text-xs
                                           text-gray-500
                                           mt-1"
                                >

                                    <?= htmlspecialchars(
                                        $transaction["trans_date"]
                                    ) ?>

                                </p>

                            </div>

                        </div>


                        <p
                            class="font-bold
                                   text-sm
                                   <?= $transaction["type"] === "income"
                                       ? "text-income"
                                       : "text-expense" ?>"
                        >

                            <?= $transaction["type"] === "income"
                                ? "+"
                                : "-" ?>

                            ₹<?= number_format(
                                $transaction["amount"],
                                2
                            ) ?>

                        </p>

                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div
                    class="p-10
                           text-center
                           text-gray-500"
                >

                    No transactions yet.

                </div>


            <?php endif; ?>

        </div>

    </div>



    <!-- =================================================
         RIGHT SIDEBAR
    ================================================= -->

    <aside
        class="space-y-6"
    >


        <!-- FINANCIAL INSIGHTS -->

        <div
            class="bg-white
                   border
                   border-gray-200
                   p-6
                   soft-shadow"
        >

            <div
                class="flex
                       items-center
                       gap-3
                       mb-5"
            >

                <div
                    class="w-9
                           h-9
                           bg-purple-50
                           flex
                           items-center
                           justify-center"
                >

                    💡

                </div>


                <h2 class="font-bold">

                    Financial Insights

                </h2>

            </div>


            <div
                class="border-l-4
                       border-primary
                       bg-purple-50
                       p-4"
            >

                <p
                    class="font-semibold
                           text-sm"
                >

                    Keep tracking your spending.

                </p>


                <p
                    class="text-xs
                           text-gray-600
                           mt-2
                           leading-relaxed"
                >

                    Reviewing your transactions regularly
                    can help you understand where your
                    money is going.

                </p>

            </div>


            <div
                class="border-l-4
                       border-income
                       bg-green-50
                       p-4
                       mt-4"
            >

                <p
                    class="font-semibold
                           text-sm"
                >

                    Current Balance

                </p>


                <p
                    class="text-xs
                           text-gray-600
                           mt-2
                           leading-relaxed"
                >

                    Your current balance is
                    ₹<?= number_format(
                        $total_balance,
                        2
                    ) ?>.

                </p>

            </div>

        </div>



        <!-- QUICK ACTIONS -->

        <div
            class="bg-white
                   border
                   border-gray-200
                   p-6
                   soft-shadow"
        >

            <h2
                class="font-bold
                       mb-5"
            >

                Quick Actions

            </h2>


            <div class="space-y-2">


                <a
                    href="income.php"
                    class="flex
                           items-center
                           justify-between
                           border
                           border-gray-200
                           p-4
                           text-sm
                           font-semibold
                           hover:border-primary
                           hover:text-primary"
                >

                    Add Income

                    <span>→</span>

                </a>


                <a
                    href="expense.php"
                    class="flex
                           items-center
                           justify-between
                           border
                           border-gray-200
                           p-4
                           text-sm
                           font-semibold
                           hover:border-primary
                           hover:text-primary"
                >

                    Add Expense

                    <span>→</span>

                </a>


                <a
                    href="budget.php"
                    class="flex
                           items-center
                           justify-between
                           border
                           border-gray-200
                           p-4
                           text-sm
                           font-semibold
                           hover:border-primary
                           hover:text-primary"
                >

                    Manage Budget

                    <span>→</span>

                </a>


                <a
                    href="categories.php"
                    class="flex
                           items-center
                           justify-between
                           border
                           border-gray-200
                           p-4
                           text-sm
                           font-semibold
                           hover:border-primary
                           hover:text-primary"
                >

                    Categories

                    <span>→</span>

                </a>

            </div>

        </div>

    </aside>

</section>

</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer
    class="bg-navy
           text-white
           mt-12"
>

    <div
        class="max-w-[1400px]
               mx-auto
               px-5 lg:px-8
               py-8"
    >

        <div
            class="flex
                   flex-col
                   md:flex-row
                   justify-between
                   gap-4"
        >

            <p class="font-bold">

                SMARTSPEND

            </p>


            <p
                class="text-gray-400
                       text-sm"
            >

                Your money. Your control.

            </p>

        </div>

    </div>

</footer>



<!-- =====================================================
     PROFILE UPLOAD POPUP
===================================================== -->

<div
    id="profilePopup"
    class="profile-popup
           fixed
           inset-0
           bg-black/40
           hidden
           items-center
           justify-center
           z-[300]"
>


    <div
        class="bg-white
               w-[90%]
               max-w-md
               shadow-xl
               p-8"
    >


        <!-- HEADER -->

        <div
            class="flex
                   items-center
                   justify-between
                   mb-6"
        >

            <h2
                class="text-xl
                       font-bold"
            >

                Update Profile Photo

            </h2>


            <button
                type="button"
                onclick="closeProfilePopup()"
                class="text-gray-500
                       hover:text-black
                       text-2xl"
            >

                ×

            </button>

        </div>



        <!-- CURRENT PHOTO -->

        <div
            class="flex
                   flex-col
                   items-center"
        >

            <?php if (!empty($profile_image)): ?>

                <img
                    src="<?= htmlspecialchars($profile_image) ?>"
                    alt="Current Profile Photo"
                    class="w-28
                           h-28
                           rounded-full
                           object-cover
                           border-4
                           border-gray-100"
                >

            <?php else: ?>

                <div
                    class="w-28
                           h-28
                           rounded-full
                           bg-primary
                           text-white
                           flex
                           items-center
                           justify-center
                           text-4xl
                           font-bold"
                >

                    <?= strtoupper(
                        substr($name, 0, 1)
                    ) ?>

                </div>

            <?php endif; ?>


            <h3
                class="font-bold
                       text-lg
                       mt-4"
            >

                <?= htmlspecialchars($name) ?>

            </h3>

        </div>



        <!-- SUCCESS MESSAGE -->

        <?php if (!empty($profile_message)): ?>

            <div
                class="mt-5
                       p-3
                       bg-green-50
                       border
                       border-green-200
                       text-green-700
                       text-sm"
            >

                <?= htmlspecialchars(
                    $profile_message
                ) ?>

            </div>

        <?php endif; ?>



        <!-- ERROR MESSAGE -->

        <?php if (!empty($profile_error)): ?>

            <div
                class="mt-5
                       p-3
                       bg-red-50
                       border
                       border-red-200
                       text-red-700
                       text-sm"
            >

                <?= htmlspecialchars(
                    $profile_error
                ) ?>

            </div>

        <?php endif; ?>



        <!-- UPLOAD FORM -->

        <form
            method="POST"
            enctype="multipart/form-data"
            class="mt-6"
        >


            <label
                class="block
                       text-sm
                       font-semibold
                       mb-2"
            >

                Choose New Photo

            </label>


            <!-- FILE BROWSER -->

            <input
                type="file"
                name="profile_image"
                accept="image/jpeg,image/png,image/webp"
                required
                class="w-full
                       border
                       border-gray-300
                       p-3
                       text-sm
                       cursor-pointer"
            >


            <p
                class="text-xs
                       text-gray-500
                       mt-2"
            >

                JPG, PNG or WEBP — Maximum 5 MB

            </p>


            <!-- UPLOAD BUTTON -->

            <button
                type="submit"
                name="upload_profile_photo"
                class="w-full
                       mt-5
                       bg-primary
                       text-white
                       py-3
                       font-bold
                       hover:opacity-90"
            >

                Upload Photo

            </button>

        </form>

    </div>

</div>



<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>


/* =========================================================
   PROFILE DROPDOWN
========================================================= */

function toggleProfileMenu(event)
{

    event.stopPropagation();

    const menu =
        document.getElementById("profileMenu");


    menu.classList.toggle("hidden");

}


/* =========================================================
   CLOSE PROFILE MENU
========================================================= */

function closeProfileMenu()
{

    const menu =
        document.getElementById("profileMenu");


    if (menu) {

        menu.classList.add("hidden");

    }

}


/* =========================================================
   OPEN PROFILE POPUP
========================================================= */

function openProfilePopup()
{

    closeProfileMenu();


    const popup =
        document.getElementById("profilePopup");


    popup.classList.remove("hidden");

    popup.classList.add("flex");

}


/* =========================================================
   CLOSE PROFILE POPUP
========================================================= */

function closeProfilePopup()
{

    const popup =
        document.getElementById("profilePopup");


    popup.classList.remove("flex");

    popup.classList.add("hidden");

}


/* =========================================================
   CLICK OUTSIDE DROPDOWN
========================================================= */

document.addEventListener(
    "click",
    function(event)
    {

        const container =
            document.getElementById(
                "profileContainer"
            );


        if (
            container &&
            !container.contains(event.target)
        ) {

            closeProfileMenu();

        }

    }
);


/* =========================================================
   CLICK OUTSIDE POPUP
========================================================= */

const profilePopup =
    document.getElementById(
        "profilePopup"
    );


if (profilePopup) {

    profilePopup.addEventListener(
        "click",
        function(event)
        {

            if (event.target === this) {

                closeProfilePopup();

            }

        }
    );

}


/* =========================================================
   ESCAPE KEY
========================================================= */

document.addEventListener(
    "keydown",
    function(event)
    {

        if (event.key === "Escape") {

            closeProfileMenu();

            closeProfilePopup();

        }

    }
);


/* =========================================================
   REOPEN POPUP IF THERE WAS AN UPLOAD ERROR
========================================================= */

<?php if ($open_profile_popup): ?>

document.addEventListener(
    "DOMContentLoaded",
    function()
    {

        openProfilePopup();

    }
);

<?php endif; ?>


</script>


</body>

</html>