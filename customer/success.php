<?php
require_once "../config/database.php";

$kode = (int)($_GET["kode"] ?? 0);
$statement = $connection->prepare(
    "SELECT kode_pesanan, nama_customer, telfon_customer, total, status_pembayaran, status_pesanan, created_at
     FROM pesanan WHERE kode_pesanan = ? LIMIT 1"
);
$statement->bind_param("i", $kode);
$statement->execute();
$order = $statement->get_result()->fetch_assoc();
?>
<?php $pageTitle = "Pesanan Berhasil"; include "../includes/header.php"; ?>

<div class="max-w-xl mx-auto bg-white border rounded-2xl p-8 text-center">
    <?php if ($order): ?>
        <!-- <div class="text-5xl mb-4">✓</div> -->
        <h1 class="text-3xl font-semibold mt-8">Pesanan Berhasil Dibuat</h1>
        <p class="text-slate-500 mt-2">Simpan kode pesanan berikut untuk mengecek status.</p>

        <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 mt-6">
            <div class="text-sm text-orange-700">Kode Pesanan</div>
            <div class="text-4xl font-bold tracking-widest mt-1.5"><?= (int)$order["kode_pesanan"] ?></div>
        </div>

        <div class="text-left mt-6 space-y-3 text-sm">
            <div class="flex justify-between"><span>Nama</span><b><?= htmlspecialchars($order["nama_customer"]) ?></b></div>
            <div class="flex justify-between"><span>Total</span><b>Rp <?= number_format($order["total"], 0, ",", ".") ?></b></div>
            <div class="flex justify-between items-center"><span>Pembayaran</span>
                <?php if ($order["status_pembayaran"] == 'PENDING'): ?>
                    <div class="bg-orange-50 border border-orange-200 text-orange-700 rounded font-semibold px-2 py-0.5">PENDING</div>
                <?php elseif ($order["status_pembayaran"] == 'PAID'): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 rounded font-semibold px-2 py-0.5">PAID</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-9 mb-1 flex gap-2 justify-center">
            <a href="status.php" class="bg-slate-600 hover:bg-slate-800 text-white font-light px-6 py-2 rounded-md">Cek Status</a>
            <a href="menu.php" class="bg-orange-600 hover:bg-orange-700 text-white font-light px-6 py-2 rounded-md">Pesan Lagi</a>
        </div>
    <?php else: ?>
        <p>Pesanan tidak ditemukan.</p>
        <a href="menu.php" class="text-orange-600 hover:underline">Kembali</a>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
