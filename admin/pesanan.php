<?php
require_once "../includes/auth.php";
requireRole("administrator");
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $kode = (int)($_POST["kode_pesanan"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($action === "payment") {
        $statement = $connection->prepare(
            "UPDATE pesanan SET status_pembayaran='PAID', paid_at=NOW()
             WHERE kode_pesanan=? AND status_pembayaran='PENDING'"
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

    header("Location: pesanan.php");
    exit;
}

$orders = $connection->query(
    "SELECT * FROM pesanan ORDER BY created_at DESC"
);

$detailOrder = null;
$detailRows = null;

if (isset($_GET["kode"])) {
    $kode = (int)$_GET["kode"];

    $statement = $connection->prepare("SELECT * FROM pesanan WHERE kode_pesanan=? LIMIT 1");
    $statement->bind_param("i", $kode);
    $statement->execute();
    $detailOrder = $statement->get_result()->fetch_assoc();

    if ($detailOrder) {
        $statement = $connection->prepare(
            "SELECT d.quantitas, d.harga, d.subtotal, p.nama_produk
             FROM detail_pesanan d
             JOIN produk p ON p.uuid_produk = d.uuid_produk
             WHERE d.uuid_pesanan=?"
        );
        $statement->bind_param("s", $detailOrder["uuid_pesanan"]);
        $statement->execute();
        $detailRows = $statement->get_result();
    }
}
?>
<?php $pageTitle = "Pesanan"; include "../includes/header.php"; ?>

<h1 class="text-3xl font-bold mb-6">Data Pesanan Pelanggan</h1>

<?php if ($detailOrder): ?>
<div class="bg-white border rounded-xl p-6 mb-6">
    <div class="flex flex-col md:flex-row md:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold">Pesanan #<?= (int)$detailOrder["kode_pesanan"] ?></h2>
            <p class="text-slate-500"><?= htmlspecialchars($detailOrder["nama_customer"]) ?> · <?= htmlspecialchars($detailOrder["telfon_customer"]) ?></p>
        </div>
        <div class="text-right font-bold">Rp <?= number_format($detailOrder["total"], 0, ",", ".") ?></div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mt-6">
        <div>
            <h3 class="font-semibold mb-3">Detail Pesanan</h3>
            <div class="space-y-2">
                <?php while ($detail = $detailRows->fetch_assoc()): ?>
                    <div class="flex justify-between border-b pb-2">
                        <span><?= htmlspecialchars($detail["nama_produk"]) ?> × <?= (int)$detail["quantitas"] ?></span>
                        <span>Rp <?= number_format($detail["subtotal"], 0, ",", ".") ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div>
            <div class="mb-4">
                <span class="text-sm text-slate-500">Status Pembayaran</span>
                <div class="font-bold"><?= htmlspecialchars($detailOrder["status_pembayaran"]) ?></div>
            </div>

            <?php if ($detailOrder["status_pembayaran"] === "PENDING"): ?>
                <form method="post" class="mb-4">
                    <input type="hidden" name="kode_pesanan" value="<?= (int)$detailOrder["kode_pesanan"] ?>">
                    <input type="hidden" name="action" value="payment">
                    <button class="bg-emerald-600 text-white px-4 py-2 rounded-lg">Tandai Sudah Dibayar</button>
                </form>
            <?php endif; ?>

            <form method="post" class="space-y-2">
                <input type="hidden" name="kode_pesanan" value="<?= (int)$detailOrder["kode_pesanan"] ?>">
                <input type="hidden" name="action" value="status">
                <label class="block text-sm text-slate-500">Status Pesanan</label>
                <div class="flex gap-2">
                    <select name="status_pesanan" class="border rounded-lg px-3 py-2 flex-1">
                        <?php foreach (["SEDANG DIPROSES", "SIAP", "SELESAI"] as $status): ?>
                            <option <?= $detailOrder["status_pesanan"] === $status ? "selected" : "" ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="bg-slate-800 text-white px-4 py-2 rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="bg-white border rounded-xl overflow-hidden">
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
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-4 font-semibold"><?= (int)$order["kode_pesanan"] ?></td>
                    <td class="p-4"><?= htmlspecialchars($order["nama_customer"]) ?></td>
                    <td class="p-4 text-right">Rp <?= number_format($order["total"], 0, ",", ".") ?></td>
                    <td class="p-4"><?= htmlspecialchars($order["status_pembayaran"]) ?></td>
                    <td class="p-4"><?= htmlspecialchars($order["status_pesanan"]) ?></td>
                    <td class="p-4 text-right"><a class="text-orange-600" href="?kode=<?= (int)$order["kode_pesanan"] ?>">Detail</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
