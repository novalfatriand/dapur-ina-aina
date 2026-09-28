<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("administrator");

$tanggalMulai = $_GET["tanggal_mulai"] ?? "";
$tanggalSelesai = $_GET["tanggal_selesai"] ?? "";

$where = "WHERE p.status_pembayaran = 'PAID'";
$params = [];
$types = "";

if ($tanggalMulai !== "") {
    $where .= " AND DATE(p.created_at) >= ?";
    $params[] = $tanggalMulai;
    $types .= "s";
}

if ($tanggalSelesai !== "") {
    $where .= " AND DATE(p.created_at) <= ?";
    $params[] = $tanggalSelesai;
    $types .= "s";
}

$queryRingkasan = "
    SELECT
        COUNT(DISTINCT p.uuid_pesanan) AS total_transaksi,
        COALESCE(SUM(p.total), 0) AS total_pendapatan,
        COALESCE(SUM(dp.quantitas), 0) AS total_produk
    FROM pesanan p
    LEFT JOIN detail_pesanan dp
        ON dp.uuid_pesanan = p.uuid_pesanan
    $where
";

$stmtRingkasan = $connection->prepare($queryRingkasan);

if (!empty($params)) {
    $stmtRingkasan->bind_param($types, ...$params);
}

$stmtRingkasan->execute();

$ringkasan = $stmtRingkasan->get_result()->fetch_assoc();

$stmtRingkasan->close();

$queryLaporan = "
    SELECT
        p.uuid_pesanan,
        p.kode_pesanan,
        p.nama_customer,
        p.telfon_customer,
        p.total,
        p.status_pembayaran,
        p.status_pesanan,
        p.created_at,
        p.paid_at
    FROM pesanan p
    $where
    ORDER BY p.created_at DESC
";

$stmtLaporan = $connection->prepare($queryLaporan);

if (!empty($params)) {
    $stmtLaporan->bind_param($types, ...$params);
}

$stmtLaporan->execute();

$laporan = $stmtLaporan->get_result();

?>

<?php require_once "../includes/header.php"; ?>

<div class="max-w-6xl mx-auto px-4 py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Laporan Penjualan
        </h1>

        <p class="text-sm text-slate-500 mt-1">
            Laporan transaksi penjualan yang sudah dibayar.
        </p>
    </div>

    <!-- Filter -->
    <div class="bg-white border rounded-xl p-5 mb-6">

        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Tanggal Mulai
                </label>

                <input
                    type="date"
                    name="tanggal_mulai"
                    value="<?= htmlspecialchars($tanggalMulai) ?>"
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-300" required
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Tanggal Selesai
                </label>

                <input
                    type="date"
                    name="tanggal_selesai"
                    value="<?= htmlspecialchars($tanggalSelesai) ?>"
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-300" required
                >
            </div>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-700"
                >
                    Terapkan
                </button>

                <a
                    href="laporan.php"
                    class="border px-4 py-2 rounded-lg text-slate-700 hover:bg-slate-50"
                >
                    Reset
                </a>

            </div>

        </form>

    </div>

    <!-- Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white border rounded-xl p-5">
            <p class="text-sm text-slate-500">
                Total Transaksi
            </p>

            <p class="text-2xl font-bold text-slate-800 mt-2">
                <?= number_format($ringkasan["total_transaksi"]) ?>
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5">
            <p class="text-sm text-slate-500">
                Total Pendapatan
            </p>

            <p class="text-2xl font-bold text-slate-800 mt-2">
                Rp <?= number_format($ringkasan["total_pendapatan"], 0, ",", ".") ?>
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5">
            <p class="text-sm text-slate-500">
                Produk Terjual
            </p>

            <p class="text-2xl font-bold text-slate-800 mt-2">
                <?= number_format($ringkasan["total_produk"]) ?>
            </p>
        </div>

    </div>

    <!-- Tabel Laporan -->
    <div class="bg-white border rounded-xl overflow-hidden">

        <div class="px-5 py-4 border-b">
            <h2 class="font-semibold text-slate-800">
                Data Penjualan
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600">
                            No
                        </th>

                        <th class="text-left px-5 py-3 font-semibold text-slate-600">
                            Kode Pesanan
                        </th>

                        <th class="text-left px-5 py-3 font-semibold text-slate-600">
                            Customer
                        </th>

                        <th class="text-left px-5 py-3 font-semibold text-slate-600">
                            Tanggal
                        </th>

                        <th class="text-left px-5 py-3 font-semibold text-slate-600">
                            Total
                        </th>

                        <th class="text-left px-5 py-3 font-semibold text-slate-600">
                            Pembayaran
                        </th>

                        <th class="text-left px-5 py-3 font-semibold text-slate-600">
                            Status Pesanan
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    <?php if ($laporan->num_rows > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($data = $laporan->fetch_assoc()): ?>

                            <tr class="hover:bg-slate-50">

                                <td class="px-5 py-4 text-slate-600">
                                    <?= $no++ ?>
                                </td>

                                <td class="px-5 py-4 font-medium text-slate-800">
                                    #<?= htmlspecialchars($data["kode_pesanan"]) ?>
                                </td>

                                <td class="px-5 py-4">

                                    <p class="font-medium text-slate-800">
                                        <?= htmlspecialchars($data["nama_customer"]) ?>
                                    </p>

                                    <p class="text-xs text-slate-500 mt-1">
                                        <?= htmlspecialchars($data["telfon_customer"]) ?>
                                    </p>

                                </td>

                                <td class="px-5 py-4 text-slate-600">

                                    <?= date(
                                        "d-m-Y H:i",
                                        strtotime($data["created_at"])
                                    ) ?>

                                </td>

                                <td class="px-5 py-4 font-medium text-slate-800">

                                    Rp <?= number_format(
                                        $data["total"],
                                        0,
                                        ",",
                                        "."
                                    ) ?>

                                </td>

                                <td class="px-5 py-4">

                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <?= htmlspecialchars($data["status_pembayaran"]) ?>
                                    </span>

                                </td>

                                <td class="px-5 py-4">

                                    <?php
                                    $status = $data["status_pesanan"];

                                    if ($status === "SELESAI") {
                                        $statusClass = "bg-green-100 text-green-700";
                                    } elseif ($status === "SIAP") {
                                        $statusClass = "bg-blue-100 text-blue-700";
                                    } else {
                                        $statusClass = "bg-yellow-100 text-yellow-700";
                                    }
                                    ?>

                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="px-5 py-10 text-center text-slate-500"
                            >
                                Tidak ada data penjualan.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php
$stmtLaporan->close();
require_once "../includes/footer.php";
?>