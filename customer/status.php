<?php
require_once "../config/database.php";

$order = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kode = (int)($_POST["kode_pesanan"] ?? 0);
    $telepon = trim($_POST["telfon_customer"] ?? "");

    $statement = $connection->prepare(
        "SELECT kode_pesanan, nama_customer, telfon_customer, total, status_pembayaran, status_pesanan, created_at, paid_at, updated_at
         FROM pesanan
         WHERE kode_pesanan = ? AND telfon_customer = ?
         LIMIT 1"
    );
    $statement->bind_param("is", $kode, $telepon);
    $statement->execute();
    $order = $statement->get_result()->fetch_assoc();

    if (!$order) {
        $error = "Pesanan tidak ditemukan! Periksa kode pesanan dan nomor telepon.";
    }
}
?>
<?php $pageTitle = "Cek Status"; include "../includes/header.php"; ?>

<div class="max-w-xl mx-auto">
    <h1 class="text-3xl font-semibold">Cek Status Pesanan</h1>
    <p class="text-slate-500 mt-1 mb-6">Masukkan kode pesanan dan nomor telepon.</p>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded-md mb-4"><p class="text-sm"><?= htmlspecialchars($error) ?></p></div>
    <?php endif; ?>

    <form method="post" class="bg-white border rounded-xl p-6 space-y-4">
        <div>
            <label class="block text-gray-500 text-sm font-medium mb-1">Kode Pesanan</label>
            <input type="number" name="kode_pesanan" maxlength="6" required class="w-full border rounded-md px-3 py-2.5"
                   value="<?= htmlspecialchars($_POST["kode_pesanan"] ?? "") ?>">
        </div>
        <div>
            <label class="block text-gray-500 text-sm font-medium mb-1">Nomor Telepon</label>
            <input name="telfon_customer" minlength="10" maxlength="16" required class="w-full border rounded-md px-3 py-2.5"
                   value="<?= htmlspecialchars($_POST["telfon_customer"] ?? "") ?>">
        </div>
        <button class="w-full bg-orange-600 hover:bg-orange-700 text-white font-light rounded-md py-2.5">Cek Pesanan</button>
    </form>

    <?php if ($order): ?>
        <div class="bg-white border rounded-xl p-6 mt-6">
            <div class="flex justify-between gap-4">
                <h2 class="font-semibold text-xl">Pesanan #<?= (int)$order["kode_pesanan"] ?></h2>
                <?php if ($order["status_pesanan"] == 'SEDANG DIPROSES'): ?>
                    <span class="bg-orange-50 border border-orange-200 text-orange-700 font-medium px-4 py-1 rounded-full text-sm"><?= htmlspecialchars($order["status_pesanan"]) ?></span>
                <?php elseif ($order["status_pesanan"] == 'SIAP'): ?>
                    <span class="bg-green-50 border border-green-300 text-green-700 font-medium px-4 py-1 rounded-full text-sm">READY TO PICK UP</span>
                <?php else: ?>
                    <span class="bg-green-600 border border-green-600 text-white font-normal px-4 py-1 rounded-full text-sm">PESANAN SELESAI</span>
                <?php endif; ?>
            </div>
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between"><span>Pelanggan</span><b><?= htmlspecialchars($order["nama_customer"]) ?></b></div>
                <div class="flex justify-between"><span>Total</span><b>Rp <?= number_format($order["total"], 0, ",", ".") ?></b></div>
                <div class="flex justify-between"><span>Pembayaran</span>
                    <?php if ($order["status_pembayaran"] == 'PENDING'): ?>
                        <div class="bg-orange-50 border border-orange-200 text-orange-700 rounded font-semibold px-2 py-0.5">PENDING</div>
                    <?php elseif ($order["status_pembayaran"] == 'PAID'): ?>
                        <div class="bg-green-50 border border-green-200 text-green-700 rounded font-semibold px-2 py-0.5">PAID</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mt-5">
        <a href="menu.php" class="text-orange-600 hover:underline">← Kembali ke Menu</a>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
