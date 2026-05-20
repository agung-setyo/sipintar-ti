<div class="bg-light border"
style="width:250px; min-height:100vh;">

    <ul class="list-group rounded-0">

        <?php if($_SESSION['role'] == 'admin') : ?>

            <li class="list-group-item">
                <a href="../admin/dashboard.php">
                    Dashboard
                </a>
            </li>

            <li class="list-group-item">
                <a href="../admin/categories/index.php">
                    Categories
                </a>
            </li>

            <li class="list-group-item">
                <a href="../admin/items/index.php">
                    Items
                </a>
            </li>

            <li class="list-group-item">
                <a href="../admin/borrow/index.php">
                    Borrow Requests
                </a>
            </li>

            <li class="list-group-item">
                <a href="../admin/logs/index.php">
                    Audit Logs
                </a>
            </li>

        <?php endif; ?>

        <?php if($_SESSION['role'] == 'peminjam') : ?>

            <li class="list-group-item">
                <a href="../peminjam/dashboard.php">
                    Dashboard
                </a>
            </li>

            <li class="list-group-item">
                <a href="../peminjam/items.php">
                    Items
                </a>
            </li>

            <li class="list-group-item">
                <a href="../peminjam/borrow.php">
                    Borrow
                </a>
            </li>

            <li class="list-group-item">
                <a href="../peminjam/history.php">
                    History
                </a>
            </li>

        <?php endif; ?>

    </ul>

</div>