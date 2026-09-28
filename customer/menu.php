<?php

    require_once "../config/database.php";

    $categories = [];
    $result = $connection->query("SELECT uuid_kategori, nama_kategori FROM kategori ORDER BY nama_kategori");
    
    while ($row = $result->fetch_assoc()) {
        $categories[$row["uuid_kategori"]] = $row["nama_kategori"];
    }

    $products = $connection->query(
        "SELECT p.uuid_produk, p.nama_produk, p.harga, p.stok, p.uuid_kategori, k.nama_kategori
        FROM produk p
        JOIN kategori k ON k.uuid_kategori = p.uuid_kategori
        ORDER BY k.nama_kategori, p.nama_produk"
    );

?>

<?php $pageTitle = "Menu"; include "../includes/header.php"; ?>

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Menu Dapur Ina Aina</h1>
            <p class="text-slate-500 mt-1">Pilih makanan dan minuman yang ingin dipesan.</p>
        </div>
        <a href="cart.php" class="bg-orange-600 text-white px-5 py-2.5 rounded-md font-light text-center hover:bg-orange-700"><i class="fas fa-shopping-cart mr-2.5"></i>Keranjang</a>
    </div>

    <?php if ($products->num_rows === 0): ?>
        <div class="bg-white border rounded-xl p-8 text-center text-slate-500">
            Tidak ada produk tersedia.
        </div>
    <?php else: ?>
        <p class="font-semibold text-base text-orange-600 mb-2 ml-1">Makanan Utama</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <?php
                $makanan_utama = $connection->query(
                    "SELECT p.uuid_produk, p.nama_produk, p.harga, p.stok, p.uuid_kategori, k.nama_kategori
                        FROM produk p
                        JOIN kategori k ON k.uuid_kategori = p.uuid_kategori
                        WHERE k.nama_kategori = 'Makanan Utama'
                        ORDER BY p.nama_produk"
                );
            ?>
            <?php while ($makanan = $makanan_utama->fetch_assoc()): ?>
                <div class="bg-white border rounded-xl p-5 shadow-sm hover:shadow-lg">
                    <div class="text-sm text-orange-600 font-normal"><?= htmlspecialchars($makanan["nama_kategori"]) ?></div>
                    <h2 class="font-bold text-xl mt-1"><?= htmlspecialchars($makanan["nama_produk"]) ?></h2>
                    <div class="text-lg font-medium mt-3">Rp <?= number_format($makanan["harga"], 0, ",", ".") ?></div>

                    <form action="cart.php" method="post" class="mt-4 flex gap-2">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="uuid_produk" value="<?= htmlspecialchars($makanan["uuid_produk"]) ?>">
                        <input type="number" name="quantitas" value="1" min="1" max="<?= max(1, (int)$makanan["stok"]) ?>"
                            class="w-20 border rounded-lg px-3 py-2" <?= $makanan["stok"] <= 0 ? "disabled" : "" ?>>
                        <button class="flex-1 bg-slate-900 text-white rounded-lg px-3 py-2 disabled:bg-slate-300"
                                <?= $makanan["stok"] <= 0 ? "disabled" : "" ?>>
                            Tambah
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>


        <p class="font-semibold text-base text-orange-600 mt-6 mb-2 ml-1">Appetizer</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <?php
                $appetizers = $connection->query(
                    "SELECT p.uuid_produk, p.nama_produk, p.harga, p.stok, p.uuid_kategori, k.nama_kategori
                        FROM produk p
                        JOIN kategori k ON k.uuid_kategori = p.uuid_kategori
                        WHERE k.nama_kategori = 'Appetizer'
                        ORDER BY p.nama_produk"
                );
            ?>
            <?php while ($appetizer = $appetizers->fetch_assoc()): ?>
                <div class="bg-white border rounded-xl p-5 shadow-sm hover:shadow-lg">
                    <div class="text-sm text-orange-600 font-normal"><?= htmlspecialchars($appetizer["nama_kategori"]) ?></div>
                    <h2 class="font-bold text-xl mt-1"><?= htmlspecialchars($appetizer["nama_produk"]) ?></h2>
                    <div class="text-lg font-medium mt-3">Rp <?= number_format($appetizer["harga"], 0, ",", ".") ?></div>

                    <form action="cart.php" method="post" class="mt-4 flex gap-2">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="uuid_produk" value="<?= htmlspecialchars($appetizer["uuid_produk"]) ?>">
                        <input type="number" name="quantitas" value="1" min="1" max="<?= max(1, (int)$appetizer["stok"]) ?>"
                            class="w-20 border rounded-lg px-3 py-2" <?= $appetizer["stok"] <= 0 ? "disabled" : "" ?>>
                        <button class="flex-1 bg-slate-900 text-white rounded-lg px-3 py-2 disabled:bg-slate-300"
                                <?= $appetizer["stok"] <= 0 ? "disabled" : "" ?>>
                            Tambah
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>


        <p class="font-semibold text-base text-orange-600 mt-6 mb-2 ml-1">Minuman</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <?php
                $drinks = $connection->query(
                    "SELECT p.uuid_produk, p.nama_produk, p.harga, p.stok, p.uuid_kategori, k.nama_kategori
                        FROM produk p
                        JOIN kategori k ON k.uuid_kategori = p.uuid_kategori
                        WHERE k.nama_kategori = 'Minuman'
                        ORDER BY p.nama_produk"
                );
            ?>
            <?php while ($minuman = $drinks->fetch_assoc()): ?>
                <div class="bg-white border rounded-xl p-5 shadow-sm hover:shadow-lg">
                    <div class="text-sm text-orange-600 font-normal"><?= htmlspecialchars($minuman["nama_kategori"]) ?></div>
                    <h2 class="font-bold text-xl mt-1"><?= htmlspecialchars($minuman["nama_produk"]) ?></h2>
                    <div class="text-lg font-medium mt-3">Rp <?= number_format($minuman["harga"], 0, ",", ".") ?></div>

                    <form action="cart.php" method="post" class="mt-4 flex gap-2">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="uuid_produk" value="<?= htmlspecialchars($minuman["uuid_produk"]) ?>">
                        <input type="number" name="quantitas" value="1" min="1" max="<?= max(1, (int)$minuman["stok"]) ?>"
                            class="w-20 border rounded-lg px-3 py-2" <?= $minuman["stok"] <= 0 ? "disabled" : "" ?>>
                        <button class="flex-1 bg-slate-900 text-white rounded-lg px-3 py-2 disabled:bg-slate-300"
                                <?= $minuman["stok"] <= 0 ? "disabled" : "" ?>>
                            Tambah
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

<?php include "../includes/footer.php"; ?>