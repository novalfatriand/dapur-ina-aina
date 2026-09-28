<?php

    require_once "../includes/auth.php";
    requireRole("cashier");
    require_once "../config/database.php";

    $data_pesanan = null;
    $details = null;
    $error = "";

    $kode_pesanan = (int)($_GET["s"] ?? $_POST["kode_pesanan"] ?? 0);

    if (isset($_GET["s"])) {
        $query_pesanan = $connection->prepare(
            "SELECT uuid_pesanan, kode_pesanan, nama_customer, telfon_customer, total, status_pembayaran, status_pesanan, created_at, paid_at, updated_at   
            FROM pesanan
            WHERE kode_pesanan = ?
            LIMIT 1" 
        );

        $query_pesanan->bind_param("i", $kode_pesanan);
        $query_pesanan->execute();
        $data_pesanan = $query_pesanan->get_result()->fetch_assoc();

        if (!$data_pesanan) {
            $error = "Pesanan tidak ditemukan! Periksa kode pesanan.";
        } else {
            $statement = $connection->prepare(
                "SELECT d.quantitas, d.harga, d.subtotal, p.nama_produk
                FROM detail_pesanan d
                JOIN produk p ON p.uuid_produk = d.uuid_produk
                WHERE d.uuid_pesanan = ?"
            );

            $statement->bind_param("s", $data_pesanan["uuid_pesanan"]);
            $statement->execute();
            $details = $statement->get_result();
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $action = $_POST["action"] ?? "";

        if ($action === "payment") {
            $statement = $connection->prepare(
                "UPDATE pesanan SET status_pembayaran = 'PAID', paid_at = NOW() WHERE kode_pesanan = ?"
            );
            $statement->bind_param("i", $kode_pesanan);
            $statement->execute();

            header("Location: index.php?s=" . $kode_pesanan);
            exit;
        }


        if ($action === "status") {
            $status = $_POST["status_pesanan"] ?? "";
            $allowed = ["SEDANG DIPROSES", "SIAP", "SELESAI"];

            if (in_array($status, $allowed, true)) {
                $statement = $connection->prepare("UPDATE pesanan SET status_pesanan = ? WHERE kode_pesanan = ?");
                $statement->bind_param("si", $status, $kode_pesanan);
                $statement->execute();
            }

            header("Location: index.php?s=" . $kode_pesanan);
            exit;
        }
    }

?>

<?php $pageTitle = "Kasir"; include "../includes/header.php"; ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-semibold">Dashboard Kasir</h1>
        <p class="text-slate-500">Kelola pembayaran dan status pesanan.</p>
    </div>
    <a href="../customer/menu.php" class="text-orange-600 hover:underline">Buat Pesanan</a>
</div>

<div class="grid md:grid-cols-3 gap-6">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h1 class="font-semibold text-xl mb-4">Cari Pesanan</h1>
        <?php if ($error): ?><div class="bg-red-50 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="GET" action="index.php" class="space-y-4">
            <input type="number" name="s" maxlength="6" required placeholder="Kode Pesanan" value="<?= htmlspecialchars($_GET["s"] ?? "") ?>" class="w-full border rounded-md px-3 py-2.5">
            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-light rounded-md py-2.5">Cari</button>
        </form>
    </div>

    <div class="md:col-span-2 bg-white border rounded-xl overflow-hidden">
        <div class="p-5 border-b"><h2 class="font-medium text-gray-600">Form Konfirmasi Pembayaran</h2></div>
        <?php if ($data_pesanan): ?>
            <div class="bg-white p-6">
                    <div class="flex justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold">Pesanan #<?= (int)$data_pesanan["kode_pesanan"] ?></h1>
                            <p class="text-slate-500 capitalize mt-1"><?= htmlspecialchars($data_pesanan["nama_customer"]) ?> · <?= htmlspecialchars($data_pesanan["telfon_customer"]) ?></p>
                        </div>
                        <b class="text-xl">IDR <?= number_format($data_pesanan["total"], 0, ",", ".") ?></b>
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
                                <?php if ($data_pesanan["status_pembayaran"] == 'PAID'): ?>
                                    <div class="bg-green-50 border border-green-300 text-green-700 rounded font-semibold px-3 py-1.5 w-fit"><i class="fas fa-check-circle fa-sm mr-1"></i>PAID</div>
                                <?php elseif ($data_pesanan["status_pembayaran"] == 'PENDING'): ?>
                                    <div class="bg-orange-50 border border-orange-200 text-orange-700 rounded font-medium px-3 py-1.5 w-fit"><i class="far fa-clock fa-sm mr-1"></i>PENDING</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($data_pesanan["status_pembayaran"] === "PENDING"): ?>
                            <form method="POST">
                                <input type="hidden" name="kode_pesanan" value="<?= $kode_pesanan ?>">
                                <input type="hidden" name="action" value="payment">
                                <button class="bg-slate-600 hover:bg-slate-800 text-white font-light px-4 py-2 rounded-md mt-5">Konfirmasi Pembayaran</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($data_pesanan["status_pembayaran"] === 'PAID'): ?>
                            <form method="POST">
                                <input type="hidden" name="kode_pesanan" value="<?= $kode_pesanan ?>">
                                <input type="hidden" name="action" value="status">
                                <label class="block text-sm text-slate-500 mb-1">Status Pesanan</label>
                                <div class="flex gap-3">
                                    <select name="status_pesanan" class="border rounded-md px-3 py-1.5 flex-1">
                                        <?php foreach (["SEDANG DIPROSES", "SIAP", "SELESAI"] as $status): ?>
                                            <option <?= $data_pesanan["status_pesanan"] === $status ? "selected" : "" ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="bg-slate-600 hover:bg-slate-800 text-white font-light px-5 py-1.5 rounded-md">Update</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
        <?php endif; ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>