<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

    <div class="container-fluid">

        <a class="navbar-brand"
        href="#">

            SIPINTAR-TI

        </a>

        <div class="d-flex">

            <span class="text-white me-3">

                <?= $_SESSION['name']; ?>

            </span>

            <a href="../auth/logout.php"
            class="btn btn-danger btn-sm">

                Logout

            </a>

        </div>

    </div>

</nav>