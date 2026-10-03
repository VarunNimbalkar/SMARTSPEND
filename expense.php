<?php

require "config.php";
require "includes/auth.php";

$userId = $_SESSION["user_id"];

$message = "";


/* =========================
   SAVE EXPENSE
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $amount = floatval($_POST["amount"]);
    $category = trim($_POST["category"]);
    $method = trim($_POST["method"]);
    $date = $_POST["date"];
    $description = trim($_POST["description"]);


    if ($title != "" && $amount > 0 && $date != "") {

        $stmt = $conn->prepare(
            "INSERT INTO transactions
            (
                user_id,
                title,
                amount,
                category,
                payment_method,
                type,
                trans_date,
                description
            )
            VALUES (?, ?, ?, ?, ?, 'Expense', ?, ?)"
        );


        $stmt->bind_param(
            "isdssss",
            $userId,
            $title,
            $amount,
            $category,
            $method,
            $date,
            $description
        );


        if ($stmt->execute()) {

            $message = "Expense saved successfully.";

        } else {

            $message = "Unable to save expense.";

        }

    } else {

        $message = "Please fill all required fields.";

    }
}


require "includes/header.php";

?>


<h1>Expense Entry</h1>


<?php if ($message): ?>

    <div class="success">

        <?= htmlspecialchars($message) ?>

    </div>

<?php endif; ?>


<div class="form-panel">


    <form method="POST">


        <div class="two-col">


            <!-- EXPENSE TITLE -->

            <div>

                <label>
                    Expense Title
                </label>

                <input
                    type="text"
                    name="title"
                    placeholder="Enter expense title"
                    required
                >

            </div>


            <!-- AMOUNT -->

            <div>

                <label>
                    Amount
                </label>

                <input
                    type="number"
                    name="amount"
                    step="0.01"
                    placeholder="Enter amount"
                    required
                >

            </div>


            <!-- CATEGORY -->

            <div>

                <label>
                    Expense Category
                </label>

                <select name="category">

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


            <!-- PAYMENT METHOD -->

            <div>

                <label>
                    Payment Method
                </label>

                <select name="method">

                    <option value="Cash">
                        Cash
                    </option>

                    <option value="UPI">
                        UPI
                    </option>

                    <option value="Bank">
                        Bank
                    </option>

                    <option value="Card">
                        Card
                    </option>

                </select>

            </div>


            <!-- DATE -->

            <div>

                <label>
                    Date
                </label>

                <input
                    type="date"
                    name="date"
                    required
                >

            </div>


        </div>


        <!-- DESCRIPTION -->

        <label>
            Description
        </label>

        <textarea
            name="description"
            placeholder="Enter description"
        ></textarea>


        <br>


        <button type="submit">
            Save Expense
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