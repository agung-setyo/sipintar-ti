<?php
include '../config/session.php';
include '../helpers/csrf_helper.php';

$csrf = generate_csrf_token();
?>

<!DOCTYPE html>
<html>
<head>

    <title>Register SIPINTAR-TI</title>

    <link href=
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-header text-center">

                    <h3>Register SIPINTAR-TI</h3>

                </div>

                <div class="card-body">

                    <form action="process_register.php"
                    method="POST">

                        <input type="hidden"
                        name="csrf_token"
                        value="<?= $csrf ?>">

                        <div class="mb-3">

                            <label>Nama</label>

                            <input type="text"
                            name="name"
                            class="form-control"
                            required>

                        </div>

                        <div class="mb-3">

                            <label>Email</label>

                            <input type="email"
                            name="email"
                            class="form-control"
                            required>

                        </div>

                        <div class="mb-3">

                            <label>Password</label>

                            <input type="password"
                            name="password"
                            class="form-control"
                            required>

                        </div>

                        <div class="mb-3">

                            <label>Jenis Identitas</label>

                            <select
                            name="identity_type"
                            class="form-control"
                            required>

                                <option value="">
                                    -- Pilih --
                                </option>

                                <option value="dosen">
                                    Dosen
                                </option>

                                <option value="mahasiswa">
                                    Mahasiswa
                                </option>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label>NIM / NIP</label>

                            <input type="text"
                            name="identity_number"
                            class="form-control"
                            required>

                        </div>

                        <button type="submit"
                        class="btn btn-primary w-100">

                            Register

                        </button>

                    </form>

                    <div class="text-center mt-3">

                        <a href="login.php">
                            Sudah punya akun?
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>