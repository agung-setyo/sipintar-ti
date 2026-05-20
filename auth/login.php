<?php

include '../config/session.php';
include '../helpers/csrf_helper.php';

$csrf = generate_csrf_token();

// Jika sudah login
if(isset($_SESSION['user_id'])){

    if($_SESSION['role'] == 'admin'){
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../peminjam/dashboard.php");
    }

    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>Login SIPINTAR-TI</title>

    <link href=
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <style>

        body{
            background: #f4f6f9;
        }

        .login-card{
            border-radius: 15px;
            overflow: hidden;
        }

        .login-header{
            background: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .login-header h3{
            margin: 0;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="row justify-content-center align-items-center"
    style="min-height:100vh;">

        <div class="col-md-5">

            <div class="card shadow login-card">

                <div class="login-header">

                    <h3>SIPINTAR-TI</h3>

                    <small>
                        Sistem Informasi Peminjaman Inventaris
                    </small>

                </div>

                <div class="card-body p-4">

                    <?php if(isset($_GET['success'])) : ?>

                        <div class="alert alert-success">

                            Register berhasil.
                            Silakan login.

                        </div>

                    <?php endif; ?>

                    <?php if(isset($_GET['error'])) : ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($_GET['error']) ?>

                        </div>

                    <?php endif; ?>

                    <form action="process_login.php"
                    method="POST">

                        <input type="hidden"
                        name="csrf_token"
                        value="<?= $csrf ?>">

                        <div class="mb-3">

                            <label class="form-label">

                                Email

                            </label>

                            <input type="email"
                            name="email"
                            class="form-control"
                            placeholder="Masukkan email"
                            required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Password

                            </label>

                            <input type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required>

                        </div>

                        <div class="form-check mb-3">

                            <input class="form-check-input"
                            type="checkbox"
                            onclick="showPassword()">

                            <label class="form-check-label">

                                Tampilkan Password

                            </label>

                        </div>

                        <button type="submit"
                        class="btn btn-primary w-100">

                            Login

                        </button>

                    </form>

                    <hr>

                    <div class="text-center">

                        Belum punya akun?

                        <a href="register.php">

                            Register

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

function showPassword(){

    var x = document.getElementById("password");

    if(x.type === "password"){
        x.type = "text";
    }else{
        x.type = "password";
    }

}

</script>

</body>
</html>