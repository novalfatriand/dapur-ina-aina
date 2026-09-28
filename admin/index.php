<?php
require_once "../includes/auth.php";
requireRole("administrator");
require_once "../config/database.php";

$counts = [];
foreach (["produk", "kategori", "pesanan", "users"] as $table) {
    $result = $connection->query("SELECT COUNT(*) AS jumlah FROM `$table`");
    $counts[$table] = (int)$result->fetch_assoc()["jumlah"];
}

$recentOrders = $connection->query(
    "SELECT kode_pesanan, nama_customer, total, status_pembayaran, status_pesanan, created_at
     FROM pesanan ORDER BY created_at DESC LIMIT 8"
);
?>
<?php $pageTitle = "Dashboard Administrator"; include "../includes/header.php"; ?>

<div class="flex justify-between items-center mb-7">
    <div>
        <h1 class="text-3xl font-bold">Dashboard Administrator</h1>
        <p class="text-slate-500">Kelola data menu dan transaksi.</p>
    </div>
    <a href="produk.php" class="bg-orange-600 text-white px-4 py-2 rounded-lg">Kelola Produk</a>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php foreach ([
        "produk" => "Produk",
        "kategori" => "Kategori",
        "pesanan" => "Pesanan",
        "users" => "Pengguna"
    ] as $key => $label): ?>
        <div class="bg-white border rounded-xl p-5">
            <div class="text-sm text-slate-500"><?= $label ?></div>
            <div class="text-3xl font-bold mt-1"><?= $counts[$key] ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid md:grid-cols-2 gap-4 mb-8">
    <a href="kategori.php" class="bg-white border rounded-xl p-5 hover:border-orange-400 hover:shadow-md">
        <b>Kelola Kategori</b>
        <p class="text-sm text-slate-500 mt-1">Tambah, ubah, dan hapus kategori.</p>
    </a>
    <a href="produk.php" class="bg-white border rounded-xl p-5 hover:border-orange-400 hover:shadow-md">
        <b>Kelola Produk</b>
        <p class="text-sm text-slate-500 mt-1">Atur nama, harga, kategori, dan stok.</p>
    </a>
</div>

<div class="bg-white border rounded-xl overflow-hidden">
    <div class="p-5 border-b">
        <h2 class="font-bold text-xl">Pesanan Terbaru</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-100">
                <tr>
                    <th class="text-left p-4">Kode</th>
                    <th class="text-left p-4">Pelanggan</th>
                    <th class="text-right p-4">Total</th>
                    <th class="text-left p-4">Pembayaran</th>
                    <th class="text-left p-4">Status</th>
                    <th class="p-4"></th>
                </tr>
            </thead>
            <tbody>
            <?php while ($order = $recentOrders->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-4 font-semibold"><?= (int)$order["kode_pesanan"] ?></td>
                    <td class="p-4"><?= htmlspecialchars($order["nama_customer"]) ?></td>
                    <td class="p-4 text-right">Rp <?= number_format($order["total"], 0, ",", ".") ?></td>
                    <td class="p-4"><?= htmlspecialchars($order["status_pembayaran"]) ?></td>
                    <td class="p-4"><?= htmlspecialchars($order["status_pesanan"]) ?></td>
                    <td class="p-4 text-right"><a class="text-orange-600" href="pesanan.php?kode=<?= (int)$order["kode_pesanan"] ?>">Detail</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
