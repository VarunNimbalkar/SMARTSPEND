<?php

require "config.php";
require "includes/auth.php";

$userId = $_SESSION["user_id"];


/* =========================
   GET ALL TRANSACTIONS
========================= */

$rows = [];

$stmt = $conn->prepare(
    "SELECT *
     FROM transactions
     WHERE user_id = ?
     ORDER BY trans_date DESC, id DESC"
);

$stmt->bind_param("i", $userId);

$stmt->execute();

$result = $stmt->get_result();


/* =========================
   CALCULATE TOTALS
========================= */

$totalIncome = 0;

$totalExpense = 0;

$highestExpense = 0;


while ($row = $result->fetch_assoc()) {

    $rows[] = $row;


    if ($row["type"] == "Income") {

        $totalIncome += $row["amount"];

    }


    else {

        $totalExpense += $row["amount"];


        if ($row["amount"] > $highestExpense) {

            $highestExpense = $row["amount"];

        }

    }

}


/* =========================
   TOTAL SAVINGS
========================= */

$totalSavings = $totalIncome - $totalExpense;


/* =========================
   HEADER
========================= */

require "includes/header.php";

?>


<h1>Financial Reports</h1>


<!-- =========================
     SUMMARY CARDS
========================= -->

<div class="cards">


    <!-- TOTAL INCOME -->

    <div class="summary green">

        <span>
            Total Income
        </span>

        <h2>

            ₹ <?= number_format(
                $totalIncome,
                2
            ) ?>

        </h2>

    </div>


    <!-- TOTAL EXPENSE -->

    <div class="summary red">

        <span>
            Total Expenses
        </span>

        <h2>

            ₹ <?= number_format(
                $totalExpense,
                2
            ) ?>

        </h2>

    </div>


    <!-- SAVINGS -->

    <div class="summary purple">

        <span>
            Total Savings
        </span>

        <h2>

            ₹ <?= number_format(
                $totalSavings,
                2
            ) ?>

        </h2>

    </div>


    <!-- HIGHEST EXPENSE -->

    <div class="summary orange">

        <span>
            Highest Expense
        </span>

        <h2>

            ₹ <?= number_format(
                $highestExpense,
                2
            ) ?>

        </h2>

    </div>


</div>


<!-- =========================
     PRINT BUTTON
========================= -->

<div class="panel">

    <button onclick="window.print()">

        Print / Save PDF

    </button>

</div>


<br>


<!-- =========================
     TRANSACTION REPORT
========================= -->

<div class="panel">

    <h3>
        Transaction Report
    </h3>


    <table>

        <tr>

            <th>
                Date
            </th>

            <th>
                Description
            </th>

            <th>
                Type
            </th>

            <th>
                Category
            </th>

            <th>
                Payment Method
            </th>

            <th>
                Amount
            </th>

        </tr>


        <?php if (count($rows) > 0): ?>


            <?php foreach ($rows as $row): ?>

                <tr>


                    <!-- DATE -->

                    <td>

                        <?= htmlspecialchars(
                            $row["trans_date"]
                        ) ?>

                    </td>


                    <!-- DESCRIPTION -->

                    <td>

                        <?= htmlspecialchars(
                            $row["title"]
                        ) ?>

                    </td>


                    <!-- TYPE -->

                    <td>

                        <?= htmlspecialchars(
                            $row["type"]
                        ) ?>

                    </td>


                    <!-- CATEGORY -->

                    <td>

                        <?= htmlspecialchars(
                            $row["category"]
                        ) ?>

                    </td>


                    <!-- PAYMENT METHOD -->

                    <td>

                        <?= htmlspecialchars(
                            $row["payment_method"]
                        ) ?>

                    </td>


                    <!-- AMOUNT -->

                    <td class="<?=
                        $row["type"] == "Income"
                        ? "income-text"
                        : "expense-text"
                    ?>">

                        ₹ <?= number_format(
                            $row["amount"],
                            2
                        ) ?>

                    </td>


                </tr>

            <?php endforeach; ?>


        <?php else: ?>


            <tr>

                <td
                    colspan="6"
                    class="center"
                >

                    No transactions available.

                </td>

            </tr>


        <?php endif; ?>


    </table>

</div>


<?php

require "includes/footer.php";

?>