<?php
require_once "../includes/auth.php";
requireRole("cashier");
require_once "../config/database.php";

$orders = $connection->query(
    "SELECT * FROM pesanan ORDER BY created_at DESC"
);
?>
<?php $pageTitle = "Kasir"; include "../includes/header.php"; ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-semibold">Kelola Pesanan</h1>
        <p class="text-slate-500">Kelola pembayaran dan status pesanan.</p>
    </div>
</div>

<div class="bg-white border rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-100">
                <tr>
                    <th class="text-left p-4">Kode Pesanan</th>
                    <th class="text-left p-4">Pelanggan</th>
                    <th class="text-right p-4">Total</th>
                    <th class="text-left p-4">Pembayaran</th>
                    <th class="text-left p-4">Status</th>
                    <th class="p-4 w-28"></th>
                </tr>
            </thead>
            <tbody>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-4 font-semibold"><?= (int)$order["kode_pesanan"] ?></td>
                    <td class="p-4"><?= htmlspecialchars($order["nama_customer"]) ?></td>
                    <td class="p-4 text-right">Rp <?= number_format($order["total"], 0, ",", ".") ?></td>
                    <td class="p-4">
                        <?php if ($order["status_pembayaran"] == 'PENDING'): ?>
                            <div class="bg-orange-50 border border-orange-200 text-orange-700 rounded font-medium px-2 py-0.5 w-fit">PENDING</div>
                        <?php elseif ($order["status_pembayaran"] == 'PAID'): ?>
                            <div class="bg-green-50 border border-green-200 text-green-700 rounded font-medium px-2 py-0.5 w-fit">PAID</div>
                        <?php endif; ?>
                    </td>
                    <td class="p-4">
                        <?php if ($order["status_pesanan"] == 'SEDANG DIPROSES'): ?>
                            <span class="bg-orange-50 border border-orange-200 text-orange-700 font-normal px-3 py-1 rounded-full text-sm"><?= htmlspecialchars($order["status_pesanan"]) ?></span>
                        <?php elseif ($order["status_pesanan"] == 'SIAP'): ?>
                            <span class="bg-green-50 border border-green-300 text-green-700 font-normal px-3 py-1 rounded-full text-sm">READY TO PICK UP</span>
                        <?php else: ?>
                            <span class="bg-green-600 border border-green-600 text-white font-normal px-3 py-1 rounded-full text-sm">PESANAN SELESAI</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-right">
                        <a href="pesanan.php?kode=<?= (int)$order["kode_pesanan"] ?>" class="text-orange-600 hover:underline"><i class="far fa-edit mr-1"></i> Kelola</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
