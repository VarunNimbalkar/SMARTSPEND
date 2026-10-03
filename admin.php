<?php

require "config.php";
require "includes/auth.php";


$userId = $_SESSION["user_id"];


$stmt = $conn->prepare(
    "SELECT role
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $userId);

$stmt->execute();

$currentUser = $stmt->get_result()->fetch_assoc();


if ($currentUser["role"] != "Admin") {

    require "includes/header.php";

    echo '

        <div class="message">

            Only administrators can open this page.

        </div>

    ';

    require "includes/footer.php";

    exit;
}



$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users"
);

$totalUsers = $result->fetch_assoc()["total"];


$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM transactions
     WHERE type = 'Income'"
);

$totalIncome = $result->fetch_assoc()["total"];


$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM transactions
     WHERE type = 'Expense'"
);

$totalExpense = $result->fetch_assoc()["total"];



$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM categories"
);

$totalCategories = $result->fetch_assoc()["total"];



$users = $conn->query(
    "SELECT
        full_name,
        username,
        email,
        mobile,
        role,
        email_verified,
        phone_verified
     FROM users
     ORDER BY id DESC"
);



require "includes/header.php";

?>


<h1>
    Admin Panel
</h1>



<div class="cards">


    <!-- USERS -->

    <div class="blue">

        <span>
            Total Users
        </span>

        <h2>

            <?= $totalUsers ?>

        </h2>

    </div>


    <!-- INCOME RECORDS -->

    <div class="summary green">

        <span>
            Income Records
        </span>

        <h2>

            <?= $totalIncome ?>

        </h2>

    </div>


    <!-- EXPENSE RECORDS -->

    <div class="summary red">

        <span>
            Expense Records
        </span>

        <h2>

            <?= $totalExpense ?>

        </h2>

    </div>


    <!-- CATEGORIES -->

    <div class="summary orange">

        <span>
            Categories
        </span>

        <h2>

            <?= $totalCategories ?>

        </h2>

    </div>


</div>



<div class="panel">


    <h3>
        User Management
    </h3>


    <table>


        <tr>

            <th>
                Name
            </th>

            <th>
                Username
            </th>

            <th>
                Email
            </th>

            <th>
                Mobile
            </th>

            <th>
                Role
            </th>

            <th>
                Verified
            </th>

        </tr>


        <?php while ($row = $users->fetch_assoc()): ?>


            <tr>


                <!-- NAME -->

                <td>

                    <?= htmlspecialchars(
                        $row["full_name"]
                    ) ?>

                </td>


                <!-- USERNAME -->

                <td>

                    <?= htmlspecialchars(
                        $row["username"]
                    ) ?>

                </td>


                <!-- EMAIL -->

                <td>

                    <?= htmlspecialchars(
                        $row["email"]
                    ) ?>

                </td>


                <!-- MOBILE -->

                <td>

                    <?= htmlspecialchars(
                        $row["mobile"]
                    ) ?>

                </td>


                <!-- ROLE -->

                <td>

                    <?= htmlspecialchars(
                        $row["role"]
                    ) ?>

                </td>


                <!-- VERIFICATION -->

                <td>

                    <?php

                    if (
                        $row["email_verified"] &&
                        $row["phone_verified"]
                    ) {

                        echo "✓ Yes";

                    } else {

                        echo "No";

                    }

                    ?>

                </td>


            </tr>


        <?php endwhile; ?>


    </table>


</div>


<?php

require "includes/footer.php";

?>