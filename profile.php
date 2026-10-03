

<?php

session_start();
require "config.php";

/* =====================================================
   CHECK LOGIN
===================================================== */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


/* =====================================================
   GET USER
===================================================== */

$stmt = $conn->prepare(
    "SELECT full_name, profile_image
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$name = $user["full_name"] ?? "User";
$profile_image = $user["profile_image"] ?? null;


/* =====================================================
   UPLOAD PROFILE PHOTO
===================================================== */

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_FILES["profile_image"])) {
        $error = "Please select an image.";
    } else {

        $file = $_FILES["profile_image"];

        /* Check upload error */
        if ($file["error"] !== UPLOAD_ERR_OK) {
            $error = "There was a problem uploading your image.";
        }

        /* Maximum size: 5 MB */
        elseif ($file["size"] > 5 * 1024 * 1024) {
            $error = "Image size must be less than 5 MB.";
        }

        else {

            /* Get file extension */
            $extension = strtolower(
                pathinfo($file["name"], PATHINFO_EXTENSION)
            );

            /* Allowed extensions */
            $allowed_extensions = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            if (!in_array($extension, $allowed_extensions)) {

                $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

            } else {

                /* Check actual image */
                $image_info = getimagesize($file["tmp_name"]);

                if ($image_info === false) {

                    $error = "The uploaded file is not a valid image.";

                } else {

                    /* Create upload directory if it doesn't exist */
                    $upload_directory = "uploads/profile/";

                    if (!is_dir($upload_directory)) {
                        mkdir($upload_directory, 0755, true);
                    }


                    /* Generate unique filename */
                    $new_filename =
                        "user_" .
                        $user_id .
                        "_" .
                        time() .
                        "." .
                        $extension;

                    $destination =
                        $upload_directory .
                        $new_filename;


                    /* Move uploaded file */
                    if (move_uploaded_file(
                        $file["tmp_name"],
                        $destination
                    )) {

                        /* Delete old profile image */
                        if (!empty($profile_image)) {

                            $old_file = $profile_image;

                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }


                        /* Save new image path */
                        $stmt = $conn->prepare(
                            "UPDATE users
                             SET profile_image = ?
                             WHERE id = ?"
                        );

                        $stmt->bind_param(
                            "si",
                            $destination,
                            $user_id
                        );

                        if ($stmt->execute()) {

                            $profile_image = $destination;

                            $message =
                                "Profile photo updated successfully.";

                        } else {

                            $error =
                                "Photo uploaded but could not be saved.";
                        }

                    } else {

                        $error =
                            "Unable to save the uploaded image.";
                    }
                }
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

    <title>My Profile | SMARTSPEND</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        body {
            font-family: 'Inter', sans-serif;
        }

    </style>

</head>


<body class="bg-gray-50 text-[#17324D]">


<!-- =====================================================
     NAVBAR
===================================================== -->

<header class="bg-white border-b border-gray-200">

    <div class="max-w-[1400px] mx-auto px-5 lg:px-8">

        <div class="h-20 flex items-center justify-between">

            <!-- LOGO -->

            <a
                href="dashboard.php"
                class="flex items-center gap-2"
            >

                <div
                    class="w-9 h-9 bg-[#0056B3] text-white
                           flex items-center justify-center
                           font-bold"
                >
                    ₹
                </div>

                <span class="font-extrabold text-xl">
                    SMARTSPEND
                </span>

            </a>


            <!-- BACK -->

            <a
                href="dashboard.php"
                class="text-sm font-semibold
                       text-[#0056B3]
                       hover:underline"
            >
                ← Back to Dashboard
            </a>

        </div>

    </div>

</header>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="max-w-3xl mx-auto px-5 py-12">

    <div class="bg-white border border-gray-200
                shadow-sm p-8">


        <!-- TITLE -->

        <div class="mb-8">

            <p
                class="text-[#0056B3]
                       text-xs
                       uppercase
                       tracking-widest
                       font-bold
                       mb-2"
            >
                ACCOUNT SETTINGS
            </p>

            <h1 class="text-3xl font-extrabold">
                My Profile
            </h1>

            <p class="text-gray-500 mt-2">
                Manage your profile photo and account information.
            </p>

        </div>



        <!-- SUCCESS MESSAGE -->

        <?php if (!empty($message)): ?>

            <div
                class="mb-6 p-4
                       bg-green-50
                       border border-green-200
                       text-green-700
                       text-sm"
            >
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>



        <!-- ERROR MESSAGE -->

        <?php if (!empty($error)): ?>

            <div
                class="mb-6 p-4
                       bg-red-50
                       border border-red-200
                       text-red-700
                       text-sm"
            >
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>



        <!-- PROFILE PHOTO -->

        <div
            class="flex flex-col items-center
                   border-b border-gray-200
                   pb-8 mb-8"
        >

            <?php if (!empty($profile_image)): ?>

                <img
                    src="<?= htmlspecialchars($profile_image) ?>"
                    alt="Profile Photo"
                    class="w-32 h-32 rounded-full
                           object-cover
                           border-4 border-gray-100
                           shadow"
                >

            <?php else: ?>

                <div
                    class="w-32 h-32 rounded-full
                           bg-[#0056B3]
                           text-white
                           flex items-center
                           justify-center
                           text-5xl
                           font-bold"
                >
                    <?= strtoupper(substr($name, 0, 1)) ?>
                </div>

            <?php endif; ?>


            <h2 class="text-xl font-bold mt-5">
                <?= htmlspecialchars($name) ?>
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Profile Photo
            </p>

        </div>



        <!-- UPLOAD FORM -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <label
                class="block
                       text-sm
                       font-semibold
                       mb-2"
            >
                Choose a new profile photo
            </label>


            <input
                type="file"
                name="profile_image"
                accept="image/jpeg,image/png,image/webp"
                required
                class="block w-full
                       border border-gray-300
                       p-3
                       text-sm
                       bg-white
                       cursor-pointer"
            >


            <p class="text-xs text-gray-500 mt-2">
                JPG, JPEG, PNG or WEBP. Maximum size: 5 MB.
            </p>


            <button
                type="submit"
                class="mt-6
                       bg-[#0056B3]
                       text-white
                       px-6 py-3
                       font-bold
                       text-sm
                       hover:bg-[#004494]"
            >
                Upload Profile Photo
            </button>

        </form>



        <!-- ACCOUNT INFO -->

        <div class="mt-10 pt-8 border-t border-gray-200">

            <h2 class="font-bold text-lg mb-4">
                Account Information
            </h2>

            <div>

                <p class="text-xs uppercase
                          tracking-wider
                          text-gray-500
                          font-semibold">
                    Full Name
                </p>

                <p class="font-semibold mt-1">
                    <?= htmlspecialchars($name) ?>
                </p>

            </div>

        </div>

    </div>

</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="bg-[#17324D] text-white mt-12">

    <div class="max-w-[1400px] mx-auto px-5 py-8">

        <p class="font-bold">
            SMARTSPEND
        </p>

        <p class="text-gray-400 text-sm mt-1">
            Your money. Your control.
        </p>

    </div>

</footer>


</body>

</html>
```

### Then add the Profile link to your dashboard

Your dashboard currently has the user's initial in the top-right area.

You can make the profile circle clickable:

```php
<a href="profile.php"
   class="w-10 h-10 rounded-full overflow-hidden block">

    <?php if (!empty($user["profile_image"])): ?>

        <img
            src="<?= htmlspecialchars($user["profile_image"]) ?>"
            alt="Profile"
            class="w-full h-full object-cover"
        >

    <?php else: ?>

        <div
            class="w-full h-full bg-primary
                   text-white
                   flex items-center
                   justify-center
                   font-bold"
        >
            <?= strtoupper(substr($name, 0, 1)) ?>
        </div>

    <?php endif; ?>

</a>
