<?php

require "config.php";
require "includes/auth.php";

$userId = $_SESSION["user_id"];

$message = "";


/* =========================
   SAVE BUDGET
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $budgetName = trim($_POST["budget_name"]);
    $category = trim($_POST["category"]);
    $amount = floatval($_POST["amount"]);
    $startDate = $_POST["start_date"];
    $endDate = $_POST["end_date"];
    $alertLimit = intval($_POST["alert_limit"]);


    if ($budgetName != "" && $amount > 0) {

        $stmt = $conn->prepare(
            "INSERT INTO budgets
            (
                user_id,
                budget_name,
                category,
                amount,
                start_date,
                end_date,
                alert_limit
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "issdssi",
            $userId,
            $budgetName,
            $category,
            $amount,
            $startDate,
            $endDate,
            $alertLimit
        );


        if ($stmt->execute()) {

            $message = "Budget saved successfully.";

        } else {

            $message = "Unable to save budget.";

        }

    } else {

        $message = "Please enter a budget name and valid amount.";

    }
}


require "includes/header.php";

?>


<h1>Budget Management</h1>


<?php if ($message): ?>

    <div class="success">

        <?= htmlspecialchars($message) ?>

    </div>

<?php endif; ?>


<div class="form-panel">


    <form method="POST">


        <div class="two-col">


            <!-- BUDGET NAME -->

            <div>

                <label>
                    Budget Name
                </label>

                <input
                    type="text"
                    name="budget_name"
                    placeholder="Example: Monthly Food Budget"
                    required
                >

            </div>


            <!-- CATEGORY -->

            <div>

                <label>
                    Category
                </label>

                <select name="category">

                    <option value="All Categories">
                        All Categories
                    </option>

                    <option value="Food">
                        Food
                    </option>

                    <option value="Transport">
                        Transport
                    </option>

                    <option value="Shopping">
                        Shopping
                    </option>

                    <option value="Bills">
                        Bills
                    </option>

                    <option value="Education">
                        Education
                    </option>

                    <option value="Entertainment">
                        Entertainment
                    </option>

                    <option value="Health">
                        Health
                    </option>

                    <option value="Other">
                        Other
                    </option>

                </select>

            </div>


            <!-- AMOUNT -->

            <div>

                <label>
                    Budget Amount
                </label>

                <input
                    type="number"
                    name="amount"
                    step="0.01"
                    placeholder="Enter budget amount"
                    required
                >

            </div>


            <!-- START DATE -->

            <div>

                <label>
                    Start Date
                </label>

                <input
                    type="date"
                    name="start_date"
                >

            </div>


            <!-- END DATE -->

            <div>

                <label>
                    End Date
                </label>

                <input
                    type="date"
                    name="end_date"
                >

            </div>


            <!-- ALERT LIMIT -->

            <div>

                <label>
                    Alert Limit (%)
                </label>

                <input
                    type="number"
                    name="alert_limit"
                    value="80"
                    min="1"
                    max="100"
                >

            </div>


        </div>


        <button type="submit">
            Save Budget
        </button>


        <button
            type="reset"
            class="light-button"
        >
            Reset
        </button>


    </form>


</div>


<?php

require "includes/footer.php";

?>