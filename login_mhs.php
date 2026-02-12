<?php
session_start();
include 'koneksi.php';

// LOGIN MAHASISWA
if (isset($_POST['login'])) {

    $nim = mysqli_real_escape_string($koneksi, $_POST['nim'] ?? '');
    $password = mysqli_real_escape_string($koneksi, $_POST['password'] ?? '');

    if ($nim === '' || $password === '') {
        $error = "NIM dan password wajib diisi!";
    } else {
        $query = mysqli_query($koneksi, "
            SELECT * FROM mahasiswa 
            WHERE nim='$nim' AND password='$password'
        ");

        if (mysqli_num_rows($query) === 1) {
            $_SESSION['mahasiswa'] = mysqli_fetch_assoc($query);

            // REDIRECT AMAN
            header("Location: dashboard_mhs.php");
            exit;
        } else {
            $error = "NIM atau password salah!";
        }
    }
}

// SIGNUP MAHASISWA
if (isset($_POST['signup'])) {

    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $nim      = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $telepon  = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $alamat   = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $cek = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nim='$nim'");

    if (mysqli_num_rows($cek) > 0) {
        $error = "NIM sudah digunakan!";
    } else {
        mysqli_query($koneksi, "
            INSERT INTO mahasiswa 
            (nama, nim, no_telepon, alamat, password)
            VALUES 
            ('$nama', '$nim', '$telepon', '$alamat', '$password')
        ");
        $success = "Pendaftaran berhasil! Silakan login.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login & Signup</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #4f46e5;
      --success: #16a34a;
      --danger: #ef4444;
      --text: #1e293b;
      --muted: #6b7280;
      --bg: #f5f7fa;
      --card: #ffffff;
      --radius: 12px;
      --shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    body {
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      color: var(--text);
    }

    .auth-card {
      width: 100%;
      max-width: 400px;
      background: var(--card);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 2rem;
      animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .auth-header {
      text-align: center;
      margin-bottom: 1.75rem;
    }

    .auth-title {
      font-weight: 700;
      font-size: 1.5rem;
      margin-bottom: 0.25rem;
      color: var(--text);
    }

    .auth-subtitle {
      font-size: 0.9rem;
      color: var(--muted);
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 0.4rem;
      color: var(--text);
    }

    .form-control {
      width: 100%;
      padding: 0.65rem 0.9rem;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 0.9rem;
      background: #fff;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .btn {
      width: 100%;
      padding: 0.7rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-primary {
      background: var(--primary);
      color: #fff;
    }

    .btn-primary:hover {
      background: #4338ca;
      transform: translateY(-1px);
    }

    .auth-switch {
      text-align: center;
      margin-top: 1.25rem;
      font-size: 0.85rem;
      color: var(--muted);
    }

    .auth-switch a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      margin-left: 0.25rem;
    }

    .auth-switch a:hover {
      text-decoration: underline;
    }

    .password-toggle {
      position: relative;
    }

    .password-toggle .toggle-btn {
      position: absolute;
      right: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 0.25rem;
    }

    .toggle-btn i {
      font-size: 1rem;
    }

    .hidden {
      display: none;
    }

    @media (max-width: 576px) {
      .auth-card {
        padding: 1.5rem;
      }

      .auth-title {
        font-size: 1.25rem;
      }
    }
  </style>
</head>
<body>
  <div class="auth-card" id="loginForm">
    <div class="auth-header">
      <h1 class="auth-title">Masuk</h1>
      <p class="auth-subtitle">Gunakan akun Anda</p>
    </div>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">NIM</label>
            <input type="number" name="nim" class="form-control" placeholder="Masukkan NIM" required />
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-toggle">
            <input type="password" id="loginPassword" name="password" class="form-control" placeholder="••••••••" required />
            <button type="button" class="toggle-btn" onclick="togglePassword('loginPassword', this)">
                <i class="fa-solid fa-eye"></i>
            </button>
            </div>
        </div>
        <button type="submit" name="login" class="btn btn-primary">Masuk</button>
        </form>

    <div class="auth-switch">
      Belum punya akun?
      <a href="#" onclick="showSignup()">Daftar</a>
    </div>
  </div>

  <div class="auth-card hidden" id="signupForm">
    <div class="auth-header">
      <h1 class="auth-title">Daftar</h1>
      <p class="auth-subtitle">Buat akun baru</p>
    </div>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required />
        </div>
        <div class="form-group">
            <label class="form-label">NIM</label>
            <input type="number" name="nim" class="form-control" required />
        </div>
        <div class="form-group">
            <label class="form-label">No Telepon</label>
            <input type="number" name="telepon" class="form-control" required />
        </div>
        <div class="form-group">
            <label class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control" required />
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="password-toggle">
            <input type="password" name="password" id="signupPassword" class="form-control" required minlength="6" />
            <button type="button" class="toggle-btn" onclick="togglePassword('signupPassword', this)">
                <i class="fa-solid fa-eye"></i>
            </button>
            </div>
        </div>
        <button type="submit" name="signup" class="btn btn-primary">Daftar</button>
        </form>

    <div class="auth-switch">
      Sudah punya akun?
      <a href="#" onclick="showLogin()">Masuk</a>
    </div>
  </div>

  <script>
    function showSignup() {
      document.getElementById("loginForm").classList.add("hidden");
      document.getElementById("signupForm").classList.remove("hidden");
    }

    function showLogin() {
      document.getElementById("signupForm").classList.add("hidden");
      document.getElementById("loginForm").classList.remove("hidden");
    }

    function togglePassword(id, btn) {
      const input = document.getElementById(id);
      const icon = btn.querySelector("i");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }

    function handleLogin(e) {
      e.preventDefault();
      alert("Login berhasil! (Demo)");
    }

    function handleSignup(e) {
      e.preventDefault();
      const pass = document.getElementById("signupPassword").value;
      const confirm = document.getElementById("confirmPassword").value;
      if (pass !== confirm) {
        alert("Password tidak cocok!");
        return;
      }
      alert("Pendaftaran berhasil! (Demo)");
      showLogin();
    }
  </script>
</body>
</html>
