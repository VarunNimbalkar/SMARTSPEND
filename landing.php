<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SMARTSPEND | Take Control of Your Money</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3f37c9',
                        navy: '#3f37c9',
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

        .hero-pattern {
            background:
                radial-gradient(circle at 85% 20%, rgba(0, 86, 179, 0.12), transparent 30%),
                radial-gradient(circle at 90% 80%, rgba(40, 125, 79, 0.08), transparent 25%),
                #ffffff;
        }

        .soft-shadow {
            box-shadow: 0 3px 14px rgba(23, 50, 77, 0.08);
        }
    </style>

</head>


<body class="bg-white text-navy">


<!-- =====================================================
     NAVBAR
===================================================== -->

<header class="border-b border-gray-200 bg-white sticky top-0 z-50">

    <div class="max-w-7xl mx-auto px-5 lg:px-8">

        <div class="h-20 flex items-center gap-6">

            <!-- LOGO -->

            <a href="landing.php"
               class="flex items-center gap-2 shrink-0">

                <div class="w-9 h-9 bg-primary text-white
                            flex items-center justify-center
                            font-bold rounded-sm">

                    ₹

                </div>

                <span class="text-xl font-extrabold tracking-tight">
                    SMARTSPEND
                </span>

            </a>


            <!-- EXPLORE -->

            <button
                class="hidden md:flex items-center gap-2
                       px-4 py-3
                       text-sm font-semibold
                       hover:bg-gray-100">

                Explore Categories

                <span>⌄</span>

            </button>


            <!-- SEARCH -->

            <div class="hidden lg:flex flex-1 max-w-xl mx-auto">

                <div class="relative w-full">

                    <span class="absolute left-4 top-1/2
                                 -translate-y-1/2
                                 text-gray-500">

                        🔍

                    </span>

                    <input
                        type="text"
                        placeholder="Search for transactions, categories, or tags..."
                        class="w-full h-12
                               border border-gray-400
                               pl-11 pr-4
                               text-sm
                               focus:outline-none
                               focus:ring-2
                               focus:ring-primary">

                </div>

            </div>


            <!-- NAV LINKS -->

            <nav class="hidden xl:flex items-center gap-6 ml-auto">

                <a href="reports.php"
                   class="text-sm font-semibold hover:text-primary">

                    Analytics

                </a>

                <a href="budget.php"
                   class="text-sm font-semibold hover:text-primary">

                    Budgets

                </a>

                <button class="text-xl">
                    🔔
                </button>

                <div class="w-9 h-9
                            rounded-full
                            bg-primary
                            text-white
                            flex items-center justify-center
                            font-bold">

                    V

                </div>

            </nav>


        </div>

    </div>

</header>



<!-- =====================================================
     HERO
===================================================== -->

<section class="hero-pattern">

    <div class="max-w-7xl mx-auto
                px-5 lg:px-8
                py-20 lg:py-28">

        <div class="grid lg:grid-cols-2
                    gap-14 items-center">


            <!-- HERO TEXT -->

            <div>

                <p class="text-primary
                          font-bold
                          text-sm
                          uppercase
                          tracking-widest
                          mb-5">

                    YOUR FINANCIAL DASHBOARD

                </p>


                <h1 class="text-4xl md:text-5xl lg:text-6xl
                           font-extrabold
                           leading-tight
                           tracking-tight
                           text-navy">

                    Take control of
                    <span class="text-primary">
                        your money.
                    </span>

                </h1>


                <p class="mt-6
                          text-lg
                          text-gray-600
                          leading-relaxed
                          max-w-xl">

                    Track your income, expenses and budgets
                    in one simple place. Understand where
                    your money goes and make better financial
                    decisions.

                </p>


                <div class="flex flex-wrap gap-4 mt-9">

                    <a href="dashboard.php"
                       class="bg-primary
                              text-white
                              px-6 py-3.5
                              font-bold
                              text-sm
                              hover:bg-primaryDark
                              transition">

                        Open Dashboard →

                    </a>


                    <a href="reports.php"
                       class="border-2
                              border-primary
                              text-primary
                              px-6 py-3
                              font-bold
                              text-sm
                              hover:bg-purple-50
                              transition">

                        View Reports

                    </a>

                </div>

            </div>

            <div class="relative">

                <div class="bg-surface
                            border border-gray-200
                            p-7
                            soft-shadow">

                    <!-- TOP -->

                    <div class="flex justify-between items-center mb-8">

                        <div>

                            <p class="text-xs
                                      text-gray-500
                                      uppercase
                                      tracking-wider">

                                Financial Overview

                            </p>

                            <h3 class="text-2xl
                                       font-bold
                                       mt-1">

                                ₹42,850

                            </h3>

                        </div>

                        <span class="bg-green-100
                                     text-income
                                     text-xs
                                     font-bold
                                     px-3 py-1.5">

                            +12.4%

                        </span>

                    </div>


                    <!-- GRAPH -->

                    <div class="h-44
                                flex items-end
                                gap-3
                                border-b
                                border-gray-200
                                pb-3">

                        <div class="bg-blue-200 w-full h-[35%]"></div>

                        <div class="bg-purple-300 w-full h-[50%]"></div>

                        <div class="bg-purple-400 w-full h-[42%]"></div>

                        <div class="bg-primaryDark-500 w-full h-[65%]"></div>

                        <div class="bg-primaryDark-600 w-full h-[55%]"></div>

                        <div class="bg-blue w-full h-[80%]"></div>

                        <div class="bg-blue w-full h-[92%]"></div>

                    </div>


                    <!-- SUMMARY -->

                    <div class="grid grid-cols-3 gap-4 mt-6">

                        <div>
                            <p class="text-xs text-gray-500">
                                Income
                            </p>

                            <p class="font-bold text-income mt-1">
                                ₹65,000
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">
                                Expenses
                            </p>

                            <p class="font-bold text-expense mt-1">
                                ₹22,150
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">
                                Savings
                            </p>

                            <p class="font-bold text-primary mt-1">
                                ₹42,850
                            </p>
                        </div>

                    </div>

                </div>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     FEATURES
===================================================== -->

<section class="bg-surface border-y border-gray-200">

    <div class="max-w-7xl mx-auto
                px-5 lg:px-8
                py-12">

        <div class="grid md:grid-cols-3 gap-8">

            <div class="flex gap-4">

                <div class="text-2xl">
                    📊
                </div>

                <div>

                    <h3 class="font-bold">
                        Understand your spending
                    </h3>

                    <p class="text-sm text-gray-600 mt-2">
                        See exactly where your money goes.
                    </p>

                </div>

            </div>


            <div class="flex gap-4">

                <div class="text-2xl">
                    🎯
                </div>

                <div>

                    <h3 class="font-bold">
                        Manage your budget
                    </h3>

                    <p class="text-sm text-gray-600 mt-2">
                        Set limits and stay on track.
                    </p>

                </div>

            </div>


            <div class="flex gap-4">

                <div class="text-2xl">
                    💡
                </div>

                <div>

                    <h3 class="font-bold">
                        Get useful insights
                    </h3>

                    <p class="text-sm text-gray-600 mt-2">
                        Turn your spending data into decisions.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="bg-navy text-white">

    <div class="max-w-7xl mx-auto
                px-5 lg:px-8
                py-10">

        <div class="flex flex-col md:flex-row
                    justify-between
                    gap-5">

            <div>

                <h3 class="font-bold text-lg">
                    SMARTSPEND
                </h3>

                <p class="text-gray-300 text-sm mt-2">
                    Your money. Your control.
                </p>

            </div>

            <p class="text-gray-400 text-sm">
                © 2026 SMARTSPEND
            </p>

        </div>

    </div>

</footer>


</body>
</html>