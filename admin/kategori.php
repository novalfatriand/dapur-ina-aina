<?php
require_once "../includes/auth.php";
requireRole("administrator");
require_once "../config/database.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $uuid = $_POST["uuid_kategori"] ?? "";
    $nama = trim($_POST["nama_kategori"] ?? "");

    if ($action === "save") {
        if ($nama === "") {
            $error = "Nama kategori wajib diisi.";
        } else {
            if ($uuid === "") {
                $statement = $connection->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
                $statement->bind_param("s", $nama);
                $statement->execute();
            } else {
                $statement = $connection->prepare("UPDATE kategori SET nama_kategori = ? WHERE uuid_kategori = ?");
                $statement->bind_param("ss", $nama, $uuid);
                $statement->execute();
            }
            header("Location: kategori.php");
            exit;
        }
    }

    if ($action === "delete") {
        $statement = $connection->prepare("DELETE FROM kategori WHERE uuid_kategori = ?");
        $statement->bind_param("s", $uuid);
        if ($statement->execute()) {
            header("Location: kategori.php");
            exit;
        }
        $error = "Kategori tidak dapat dihapus karena masih digunakan produk/pesanan.";
    }
}

$edit = null;
if (isset($_GET["edit"])) {
    $statement = $connection->prepare("SELECT * FROM kategori WHERE uuid_kategori = ?");
    $statement->bind_param("s", $_GET["edit"]);
    $statement->execute();
    $edit = $statement->get_result()->fetch_assoc();
}

$categories = $connection->query("SELECT * FROM kategori ORDER BY nama_kategori");
?>
<?php $pageTitle = "Kategori"; include "../includes/header.php"; ?>

<div class="grid md:grid-cols-3 gap-6">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h1 class="font-bold text-xl mb-4"><?= $edit ? "Edit Kategori" : "Tambah Kategori" ?></h1>
        <?php if ($error): ?><div class="bg-red-50 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" class="space-y-4">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="uuid_kategori" value="<?= htmlspecialchars($edit["uuid_kategori"] ?? "") ?>">
            <input name="nama_kategori" maxlength="15" required placeholder="Nama kategori"
                   value="<?= htmlspecialchars($edit["nama_kategori"] ?? "") ?>"
                   class="w-full border rounded-lg px-3 py-2">
            <button class="w-full bg-orange-600 text-white rounded-lg py-2.5">Simpan</button>
            <?php if ($edit): ?><a href="kategori.php" class="block text-center text-sm text-slate-500">Batal</a><?php endif; ?>
        </form>
    </div>

    <div class="md:col-span-2 bg-white border rounded-xl overflow-hidden">
        <div class="p-5 border-b"><h2 class="font-bold">Daftar Kategori</h2></div>
        <table class="w-full text-sm">
            <thead class="bg-slate-100"><tr><th class="text-left p-4">Nama</th><th class="p-4"></th></tr></thead>
            <tbody>
            <?php while ($category = $categories->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-4"><?= htmlspecialchars($category["nama_kategori"]) ?></td>
                    <td class="p-4 text-right">
                        <a class="text-orange-600 mr-3" href="?edit=<?= urlencode($category["uuid_kategori"]) ?>">Edit</a>
                        <form method="post" class="inline" onsubmit="return confirm('Hapus kategori ini?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="uuid_kategori" value="<?= htmlspecialchars($category["uuid_kategori"]) ?>">
                            <button class="text-red-600">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
