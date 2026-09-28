<?php
require_once "../includes/auth.php";
requireRole("cashier");
require_once "../config/database.php";

$kode = (int)($_GET["kode"] ?? $_POST["kode_pesanan"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "payment") {
        $statement = $connection->prepare(
            "UPDATE pesanan SET status_pembayaran='PAID', paid_at=NOW() WHERE kode_pesanan=?"
        );
        $statement->bind_param("i", $kode);
        $statement->execute();
    }

    if ($action === "status") {
        $status = $_POST["status_pesanan"] ?? "";
        $allowed = ["SEDANG DIPROSES", "SIAP", "SELESAI"];

        if (in_array($status, $allowed, true)) {
            $statement = $connection->prepare("UPDATE pesanan SET status_pesanan=? WHERE kode_pesanan=?");
            $statement->bind_param("si", $status, $kode);
            $statement->execute();
        }
    }

    header("Location: pesanan.php?kode=" . $kode);
    exit;
}

$statement = $connection->prepare("SELECT * FROM pesanan WHERE kode_pesanan=? LIMIT 1");
$statement->bind_param("i", $kode);
$statement->execute();
$order = $statement->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit;
}

$statement = $connection->prepare(
    "SELECT d.quantitas, d.harga, d.subtotal, p.nama_produk
     FROM detail_pesanan d
     JOIN produk p ON p.uuid_produk = d.uuid_produk
     WHERE d.uuid_pesanan=?"
);
$statement->bind_param("s", $order["uuid_pesanan"]);
$statement->execute();
$details = $statement->get_result();
?>
<?php $pageTitle = "Kelola Pesanan"; include "../includes/header.php"; ?>

<div class="max-w-3xl mx-auto">
    <a href="daftar-pesanan.php" class="text-orange-600 hover:underline">← Kembali</a>

    <div class="bg-white border rounded-xl p-6 mt-4">
        <div class="flex justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Pesanan #<?= (int)$order["kode_pesanan"] ?></h1>
                <p class="text-slate-500 mt-1"><?= htmlspecialchars($order["nama_customer"]) ?> · <?= htmlspecialchars($order["telfon_customer"]) ?></p>
            </div>
            <b class="text-xl">IDR <?= number_format($order["total"], 0, ",", ".") ?></b>
        </div>

        <div class="mt-6">
            <h2 class="font-medium mb-3">Detail Pesanan</h2>
            <?php while ($detail = $details->fetch_assoc()): ?>
                <div class="flex justify-between border-b py-2">
                    <span class="text-gray-500"><?= htmlspecialchars($detail["nama_produk"]) ?> × <?= (int)$detail["quantitas"] ?></span>
                    <span>Rp <?= number_format($detail["subtotal"], 0, ",", ".") ?></span>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="flex justify-between items-start mt-7">
            <div>
                <div class="text-sm text-slate-500 mb-1">Pembayaran</div>
                <div class="font-bold mb-3">
                    <?php if ($order["status_pembayaran"] == 'PAID'): ?>
                        <div class="bg-green-50 border border-green-300 text-green-700 rounded font-semibold px-3 py-1.5 w-fit"><i class="fas fa-check-circle fa-sm mr-1"></i>PAID</div>
                    <?php elseif ($order["status_pembayaran"] == 'PENDING'): ?>
                        <div class="bg-orange-50 border border-orange-200 text-orange-700 rounded font-medium px-3 py-1.5 w-fit"><i class="far fa-clock fa-sm mr-1"></i>PENDING</div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($order["status_pembayaran"] === "PENDING"): ?>
                <form method="POST">
                    <input type="hidden" name="kode_pesanan" value="<?= $kode ?>">
                    <input type="hidden" name="action" value="payment">
                    <button class="bg-slate-600 hover:bg-slate-800 text-white font-light px-4 py-2 rounded-md mt-5">Konfirmasi Pembayaran</button>
                </form>
            <?php endif; ?>

            <?php if ($order["status_pembayaran"] === 'PAID'): ?>
                <form method="POST">
                    <input type="hidden" name="kode_pesanan" value="<?= $kode ?>">
                    <input type="hidden" name="action" value="status">
                    <label class="block text-sm text-slate-500 mb-1">Status Pesanan</label>
                    <div class="flex gap-3">
                        <select name="status_pesanan" class="border rounded-md px-3 py-1.5 flex-1">
                            <?php foreach (["SEDANG DIPROSES", "SIAP", "SELESAI"] as $status): ?>
                                <option <?= $order["status_pesanan"] === $status ? "selected" : "" ?>><?= $status ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="bg-slate-600 hover:bg-slate-800 text-white font-light px-5 py-1.5 rounded-md">Update</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
