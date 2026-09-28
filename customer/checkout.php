<?php
session_start();
require_once "../config/database.php";

if (empty($_SESSION["cart"])) {
    header("Location: menu.php");
    exit;
}

$error = "";
$total = 0;
foreach ($_SESSION["cart"] as $item) {
    $total += $item["harga"] * $item["quantitas"];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama_customer"] ?? "");
    $telepon = trim($_POST["telfon_customer"] ?? "");

    if ($nama === "" || $telepon === "") {
        $error = "Nama dan nomor telepon wajib diisi.";
    } else {
        $connection->begin_transaction();

        try {
            $checkStock = $connection->prepare(
                "SELECT stok FROM produk WHERE uuid_produk = ? FOR UPDATE"
            );

            foreach ($_SESSION["cart"] as $item) {
                $checkStock->bind_param("s", $item["uuid_produk"]);
                $checkStock->execute();
                $stock = $checkStock->get_result()->fetch_assoc();

                if (!$stock || (int)$stock["stok"] < $item["quantitas"]) {
                    throw new Exception("Stok produk " . $item["nama_produk"] . " tidak mencukupi.");
                }
            }

            $uuidPesanan = null;
            $kodePesanan = random_int(100000, 999999);

            do {
                $statement = $connection->prepare("SELECT uuid_pesanan FROM pesanan WHERE kode_pesanan = ?");
                $statement->bind_param("i", $kodePesanan);
                $statement->execute();
                $exists = $statement->get_result()->num_rows > 0;
                if ($exists) {
                    $kodePesanan = random_int(100000, 999999);
                }
            } while ($exists);

            $insertOrder = $connection->prepare(
                "INSERT INTO pesanan
                (kode_pesanan, nama_customer, telfon_customer, total, status_pembayaran, status_pesanan)
                VALUES (?, ?, ?, ?, 'PENDING', 'SEDANG DIPROSES')"
            );
            $insertOrder->bind_param("issd", $kodePesanan, $nama, $telepon, $total);
            $insertOrder->execute();

            $uuidPesanan = $connection->insert_id;
            // Ambil UUID yang dibuat MySQL.
            $result = $connection->query(
                "SELECT uuid_pesanan FROM pesanan WHERE kode_pesanan = " . (int)$kodePesanan . " LIMIT 1"
            );
            $uuidPesanan = $result->fetch_assoc()["uuid_pesanan"];

            /*
             * Schema bawaan SQL menggunakan uuid_kategori pada detail_pesanan.
             * Karena itu kode menyimpan kategori produk pada detail, sementara
             * nama/harga produk ditentukan saat transaksi dibuat.
             */
            $insertDetail = $connection->prepare(
                "INSERT INTO detail_pesanan
                (uuid_pesanan, uuid_produk, quantitas, harga, subtotal)
                VALUES (?, ?, ?, ?, ?)"
            );
            $updateStock = $connection->prepare(
                "UPDATE produk SET stok = stok - ? WHERE uuid_produk = ?"
            );

            foreach ($_SESSION["cart"] as $item) {
                $subtotal = $item["harga"] * $item["quantitas"];
                // Tipe parameter: string, string, integer, double, double.
                $insertDetail->bind_param(
                    "ssidd",
                    $uuidPesanan,
                    $item["uuid_produk"],
                    $item["quantitas"],
                    $item["harga"],
                    $subtotal
                );
                $insertDetail->execute();

                $updateStock->bind_param("is", $item["quantitas"], $item["uuid_produk"]);
                $updateStock->execute();
            }

            $connection->commit();
            $_SESSION["cart"] = [];

            header("Location: success.php?kode=" . urlencode((string)$kodePesanan));
            exit;
        } catch (Throwable $exception) {
            $connection->rollback();
            $error = $exception->getMessage();
        }
    }
}
?>
<?php $pageTitle = "Checkout"; include "../includes/header.php"; ?>

<h1 class="text-3xl font-semibold mb-4">Checkout</h1>

<?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-xl mb-4">Data Pelanggan</h2>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input name="nama_customer" required maxlength="50" class="w-full border rounded-md px-3 py-2"
                       value="<?= htmlspecialchars($_POST["nama_customer"] ?? "") ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nomor Telepon</label>
                <input name="telfon_customer" required minlength="10" maxlength="16" class="w-full border rounded-md px-3 py-2"
                       value="<?= htmlspecialchars($_POST["telfon_customer"] ?? "") ?>">
            </div>
            <button class="w-full bg-orange-600 hover:bg-orange-700 text-white font-light rounded-md py-2.5">Buat Pesanan</button>
        </form>
    </div>

    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-xl mb-4">Ringkasan</h2>
        <div class="space-y-3">
            <?php foreach ($_SESSION["cart"] as $item): ?>
                <div class="flex justify-between gap-4">
                    <span><?= htmlspecialchars($item["nama_produk"]) ?> × <?= (int)$item["quantitas"] ?></span>
                    <span class="font-medium">Rp <?= number_format($item["harga"] * $item["quantitas"], 0, ",", ".") ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="border-t mt-5 pt-4 flex justify-between font-bold text-lg">
            <span class="font-semibold text-gray-600">Total</span>
            <span>Rp <?= number_format($total, 0, ",", ".") ?></span>
        </div>
        <p class="text-sm text-slate-500 mt-4">
            Pembayaran dilakukan secara langsung di kasir.
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
