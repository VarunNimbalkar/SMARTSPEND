<?php

require "config.php";
require "includes/auth.php";

$userId = $_SESSION["user_id"];

$message = "";


/* =========================
   ADD CATEGORY
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $type = $_POST["type"];
    $description = trim($_POST["description"]);


    if ($name != "") {

        $stmt = $conn->prepare(
            "INSERT INTO categories
            (
                user_id,
                name,
                type,
                description
            )
            VALUES (?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "isss",
            $userId,
            $name,
            $type,
            $description
        );


        if ($stmt->execute()) {

            $message = "Category added successfully.";

        } else {

            $message = "Unable to add category.";

        }

    } else {

        $message = "Please enter a category name.";

    }
}


/* =========================
   GET CATEGORIES
========================= */

$stmt = $conn->prepare(
    "SELECT *
     FROM categories
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param("i", $userId);

$stmt->execute();

$rows = $stmt->get_result();


/* =========================
   HEADER
========================= */

require "includes/header.php";

?>


<h1>Category Management</h1>


<?php if ($message): ?>

    <div class="success">

        <?= htmlspecialchars($message) ?>

    </div>

<?php endif; ?>


<!-- =========================
     ADD CATEGORY
========================= -->

<div class="form-panel">

    <form method="POST">


        <label>
            Category Name
        </label>

        <input
            type="text"
            name="name"
            placeholder="Example: Gym"
            required
        >


        <label>
            Category Type
        </label>

        <select name="type">

            <option value="Expense">
                Expense
            </option>

            <option value="Income">
                Income
            </option>

        </select>


        <label>
            Description
        </label>

        <textarea
            name="description"
            placeholder="Enter category description"
        ></textarea>


        <button type="submit">
            Save Category
        </button>


        <button
            type="reset"
            class="light-button"
        >
            Reset
        </button>


    </form>

</div>


<br>


<!-- =========================
     CATEGORY LIST
========================= -->

<div class="panel">

    <h3>
        My Categories
    </h3>


    <table>

        <tr>

            <th>
                Name
            </th>

            <th>
                Type
            </th>

            <th>
                Description
            </th>

        </tr>


        <?php if ($rows->num_rows > 0): ?>


            <?php while ($row = $rows->fetch_assoc()): ?>

                <tr>

                    <td>

                        <?= htmlspecialchars(
                            $row["name"]
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $row["type"]
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $row["description"]
                        ) ?>

                    </td>

                </tr>

            <?php endwhile; ?>


        <?php else: ?>

            <tr>

                <td
                    colspan="3"
                    class="center"
                >

                    No categories created yet.

                </td>

            </tr>

        <?php endif; ?>


    </table>

</div>


<?php

require "includes/footer.php";

?>