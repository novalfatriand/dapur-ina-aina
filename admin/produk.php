<?php
require_once "../includes/auth.php";
requireRole("administrator");
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $uuid = $_POST["uuid_produk"] ?? "";
    $uuidKategori = $_POST["uuid_kategori"] ?? "";
    $nama = trim($_POST["nama_produk"] ?? "");
    $harga = (float)($_POST["harga"] ?? 0);
    $stok = max(0, (int)($_POST["stok"] ?? 0));

    if ($action === "save") {
        if ($uuidKategori === "" || $nama === "" || $harga < 0) {
            $error = "Data produk belum lengkap.";
        } else {
            if ($uuid === "") {
                $statement = $connection->prepare(
                    "INSERT INTO produk (uuid_kategori, nama_produk, harga, stok) VALUES (?, ?, ?, ?)"
                );
                $statement->bind_param("ssdi", $uuidKategori, $nama, $harga, $stok);
                $statement->execute();
            } else {
                $statement = $connection->prepare(
                    "UPDATE produk SET uuid_kategori=?, nama_produk=?, harga=?, stok=? WHERE uuid_produk=?"
                );
                $statement->bind_param("ssdis", $uuidKategori, $nama, $harga, $stok, $uuid);
                $statement->execute();
            }
            header("Location: produk.php");
            exit;
        }
    }

    if ($action === "delete") {
        $statement = $connection->prepare("DELETE FROM produk WHERE uuid_produk = ?");
        $statement->bind_param("s", $uuid);
        $statement->execute();
        header("Location: produk.php");
        exit;
    }
}

$edit = null;
if (isset($_GET["edit"])) {
    $statement = $connection->prepare("SELECT * FROM produk WHERE uuid_produk = ?");
    $statement->bind_param("s", $_GET["edit"]);
    $statement->execute();
    $edit = $statement->get_result()->fetch_assoc();
}

$categories = $connection->query("SELECT uuid_kategori, nama_kategori FROM kategori ORDER BY nama_kategori");
$products = $connection->query(
    "SELECT p.*, k.nama_kategori
     FROM produk p JOIN kategori k ON k.uuid_kategori = p.uuid_kategori
     ORDER BY p.nama_produk"
);
?>
<?php $pageTitle = "Produk"; include "../includes/header.php"; ?>

<div class="grid md:grid-cols-3 gap-6">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h1 class="font-bold text-xl mb-4"><?= $edit ? "Edit Produk" : "Tambah Produk" ?></h1>
        <?php if ($error): ?><div class="bg-red-50 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" class="space-y-4">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="uuid_produk" value="<?= htmlspecialchars($edit["uuid_produk"] ?? "") ?>">

            <div>
                <label class="block text-sm mb-1">Kategori</label>
                <select name="uuid_kategori" required class="w-full border rounded-lg px-3 py-2">
                    <option value="">Pilih kategori</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($category["uuid_kategori"]) ?>"
                            <?= (($edit["uuid_kategori"] ?? "") === $category["uuid_kategori"]) ? "selected" : "" ?>>
                            <?= htmlspecialchars($category["nama_kategori"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Nama Produk</label>
                <input name="nama_produk" required maxlength="50" class="w-full border rounded-lg px-3 py-2"
                       value="<?= htmlspecialchars($edit["nama_produk"] ?? "") ?>">
            </div>

            <div>
                <label class="block text-sm mb-1">Harga</label>
                <input type="number" name="harga" min="0" step="0.01" required class="w-full border rounded-lg px-3 py-2"
                       value="<?= htmlspecialchars($edit["harga"] ?? "") ?>">
            </div>

            <div>
                <label class="block text-sm mb-1">Stok</label>
                <input type="number" name="stok" min="0" required class="w-full border rounded-lg px-3 py-2"
                       value="<?= htmlspecialchars($edit["stok"] ?? "0") ?>">
            </div>

            <button class="w-full bg-orange-600 text-white rounded-lg py-2.5">Simpan</button>
            <?php if ($edit): ?><a href="produk.php" class="block text-center text-sm text-slate-500">Batal</a><?php endif; ?>
        </form>
    </div>

    <div class="md:col-span-2 bg-white border rounded-xl overflow-hidden">
        <div class="p-5 border-b"><h2 class="font-bold">Daftar Produk</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100">
                    <tr><th class="text-left p-4">Produk</th><th class="text-left p-4">Kategori</th><th class="text-right p-4">Harga</th><th class="text-center p-4">Stok</th><th class="p-4"></th></tr>
                </thead>
                <tbody>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="p-4"><?= htmlspecialchars($product["nama_produk"]) ?></td>
                        <td class="p-4"><?= htmlspecialchars($product["nama_kategori"]) ?></td>
                        <td class="p-4 text-right">Rp <?= number_format($product["harga"], 0, ",", ".") ?></td>
                        <td class="p-4 text-center"><?= (int)$product["stok"] ?></td>
                        <td class="p-4 text-right whitespace-nowrap">
                            <a class="text-orange-600 mr-3" href="?edit=<?= urlencode($product["uuid_produk"]) ?>">Edit</a>
                            <form method="post" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="uuid_produk" value="<?= htmlspecialchars($product["uuid_produk"]) ?>">
                                <button class="text-red-600">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
