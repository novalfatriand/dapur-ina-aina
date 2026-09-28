<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $current_page = basename($_SERVER['PHP_SELF']);

    $pageTitle = $pageTitle ?? "Dapur Ina Aina";

?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pageTitle) ?> - Dapur Ina Aina</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            poppins: ['Poppins']
                        },
                    },
                },
            };
        </script>
    </head>

    <body class="font-poppins bg-slate-50 text-slate-800">
        <nav class="bg-white border-b sticky top-0 z-50">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <a href="<?= isset($_SESSION["user"]) ? "../index.php" : "index.php" ?>" class="font-bold text-xl text-orange-600">
                    Dapur Ina Aina
                </a>

                <div class="<?= isset($_SESSION["user"]) ? 'hidden' : 'block' ?> flex flex-row items-center gap-3">
                    <a href="../customer/status.php" class="bg-white border border-orange-600 text-orange-600 px-5 py-2 rounded-md text-center text-sm hover:bg-orange-700 hover:border-orange-700 hover:text-white">Cek Status Pesanan</a>
                    <a href="../login.php" class="bg-orange-600 text-white px-5 py-2 rounded-md text-center text-sm hover:bg-orange-700">Login</a>
                </div>

                <div class="<?= isset($_SESSION["user"]) && $_SESSION["user"]["role"] === 'cashier' ? 'block' : 'hidden' ?> flex flex-row items-center gap-7">
                    <a href="index.php" class="text-center text-base font-normal <?= ($current_page == 'index.php') ? 'text-orange-600' : 'text-gray-600' ?> hover:text-orange-600">Dashboard</a>
                    <a href="daftar-pesanan.php" class="text-center text-base font-normal <?= ($current_page == 'daftar-pesanan.php' || $current_page == 'pesanan.php') ? 'text-orange-600' : 'text-gray-600' ?> hover:text-orange-600">Pesanan</a>
                </div>

                <div class="<?= isset($_SESSION["user"]) && $_SESSION["user"]["role"] === 'administrator' ? 'block' : 'hidden' ?> flex flex-row items-center gap-7">
                    <a href="index.php" class="text-center text-base font-normal <?= ($current_page == 'index.php') ? 'text-orange-600' : 'text-gray-600' ?> hover:text-orange-600">Dashboard</a>
                    <a href="laporan.php" class="text-center text-base font-normal <?= ($current_page == 'laporan.php') ? 'text-orange-600' : 'text-gray-600' ?> hover:text-orange-600">Laporan</a>
                </div>
                
                <?php if (isset($_SESSION["user"])): ?>
                    <div class="flex items-center gap-4 text-sm">
                        <span><?= htmlspecialchars($_SESSION["user"]["nama"]) ?></span>
                        <a href="<?= isset($_SESSION["user"]) && $_SESSION["user"]["role"] === "administrator" ? "../logout.php" : "../logout.php" ?>" class="text-red-600 hover:underline">
                            <div class="bg-orange-50 hover:bg-orange-200 border border-orange-200 text-orange-700 text-sm font-normal px-3 py-1.5 rounded-md">Logout</div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
        <main class="max-w-6xl mx-auto px-4 py-8">
