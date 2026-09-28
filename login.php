<?php
session_start();
require_once "config/database.php";

if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $statement = $connection->prepare(
        "SELECT uuid_user, nama, email, password, role FROM users WHERE email = ? LIMIT 1"
    );
    $statement->bind_param("s", $email);
    $statement->execute();
    $result = $statement->get_result();
    $user = $result->fetch_assoc();

    // Database contoh pada SQL memakai password plain text "1234".
    // Untuk sistem nyata gunakan password_hash/password_verify.
    if ($user && hash_equals((string)$user["password"], (string)$password)) {
        $_SESSION["user"] = [
            "uuid_user" => $user["uuid_user"],
            "nama" => $user["nama"],
            "email" => $user["email"],
            "role" => $user["role"]
        ];

        if ($user["role"] === "administrator") {
            header("Location: admin/index.php");
        } else {
            header("Location: kasir/index.php");
        }
        exit;
    }

    $error = "Email atau password salah.";
}
?>
<?php $pageTitle = "Login"; include "includes/header.php"; ?>

<div class="max-w-md mx-auto bg-white border rounded-2xl shadow-sm p-6">
    <h1 class="text-2xl font-semibold mb-2">Login</h1>
    <p class="text-slate-500 mb-6">Masuk sebagai Administrator atau Cashier.</p>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block text-sm text-gray-500 font-medium mb-1">Email</label>
            <input type="email" name="email" required class="w-full border rounded-md px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm text-gray-500 font-medium mb-1">Password</label>
            <input type="password" name="password" required class="w-full border rounded-md px-3 py-2.5">
        </div>
        <button class="w-full bg-orange-600 hover:bg-orange-700 text-white font-light rounded-md py-2.5">
            Login
        </button>
    </form>

    <div class="mt-5 text-sm text-slate-500">
        Akun bawaan SQL: <b>fatriandn@gmail.com</b> / <b>1234</b>
    </div>
</div>

<?php include "includes/footer.php"; ?>
