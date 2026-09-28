<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $uuidProduk = $_POST["uuid_produk"] ?? "";
        $quantitas = max(1, (int)($_POST["quantitas"] ?? 1));

        $statement = $connection->prepare(
            "SELECT uuid_produk, nama_produk, harga, stok, uuid_kategori
             FROM produk WHERE uuid_produk = ? LIMIT 1"
        );
        $statement->bind_param("s", $uuidProduk);
        $statement->execute();
        $product = $statement->get_result()->fetch_assoc();

        if ($product && $product["stok"] >= $quantitas) {
            $oldQty = $_SESSION["cart"][$uuidProduk]["quantitas"] ?? 0;
            $newQty = $oldQty + $quantitas;

            if ($newQty <= (int)$product["stok"]) {
                $_SESSION["cart"][$uuidProduk] = [
                    "uuid_produk" => $product["uuid_produk"],
                    "uuid_kategori" => $product["uuid_kategori"],
                    "nama_produk" => $product["nama_produk"],
                    "harga" => (float)$product["harga"],
                    "quantitas" => $newQty
                ];
            }
        }
    }

    if ($action === "update") {
        foreach ($_POST["quantitas"] ?? [] as $uuidProduk => $qty) {
            $qty = max(0, (int)$qty);

            if (!isset($_SESSION["cart"][$uuidProduk])) {
                continue;
            }

            $statement = $connection->prepare("SELECT stok FROM produk WHERE uuid_produk = ?");
            $statement->bind_param("s", $uuidProduk);
            $statement->execute();
            $product = $statement->get_result()->fetch_assoc();

            if (!$product || $qty <= 0) {
                unset($_SESSION["cart"][$uuidProduk]);
            } else {
                $_SESSION["cart"][$uuidProduk]["quantitas"] = min($qty, (int)$product["stok"]);
            }
        }
    }

    header("Location: cart.php");
    exit;
}

$total = 0;
foreach ($_SESSION["cart"] as $item) {
    $total += $item["harga"] * $item["quantitas"];
}
?>
<?php $pageTitle = "Pesanan"; include "../includes/header.php"; ?>

<h1 class="text-3xl font-bold mb-6">Pesanan Saya</h1>

<?php if (empty($_SESSION["cart"])): ?>
    <div class="bg-white border rounded-xl p-8 text-center">
        <p class="text-slate-600 mb-4">Belum ada menu yang dipilih.</p>
        <a href="menu.php" class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-light px-5 py-2.5 rounded-md">Kembali ke Menu</a>
    </div>
<?php else: ?>
    <form method="post">
        <input type="hidden" name="action" value="update">

        <div class="bg-white border rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="text-left p-4">Produk</th>
                            <th class="text-right p-4">Harga</th>
                            <th class="text-center p-4">Qty</th>
                            <th class="text-right p-4">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($_SESSION["cart"] as $uuid => $item): ?>
                        <?php $subtotal = $item["harga"] * $item["quantitas"]; ?>
                        <tr class="border-t">
                            <td class="p-4"><?= htmlspecialchars($item["nama_produk"]) ?></td>
                            <td class="p-4 text-right">Rp <?= number_format($item["harga"], 0, ",", ".") ?></td>
                            <td class="p-4 text-center">
                                <input type="number" name="quantitas[<?= htmlspecialchars($uuid) ?>]"
                                       value="<?= (int)$item["quantitas"] ?>" min="0"
                                       class="w-20 border rounded-lg px-2 py-1 text-center">
                            </td>
                            <td class="p-4 text-right">Rp <?= number_format($subtotal, 0, ",", ".") ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center mt-5">
            <a href="menu.php" class="text-orange-600 hover:underline">← Tambah Menu</a>
            <div class="flex items-center gap-3">
                <strong class="text-xl font-semibold mt-1 mr-3"><span class="font-normal text-gray-600">Total</span> Rp <?= number_format($total, 0, ",", ".") ?></strong>
                <button class="bg-slate-600 hover:bg-slate-800 text-white font-light px-5 py-2.5 rounded-md">Update</button>
                <a href="checkout.php" class="bg-orange-600 hover:bg-orange-700 text-white font-light px-5 py-2.5 rounded-md">Checkout</a>
            </div>
        </div>
    </form>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>
