<?php

include_once __DIR__ . '/../../config/database.php';

if(isset($_POST['submit']))
{
    $name = $_POST['name'];

    $description =
    $_POST['description'];

    mysqli_query(

        $conn,

        "INSERT INTO categories
        (name, description)

        VALUES

        ('$name','$description')"
    );

    header(
        "Location: index.php"
    );
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-5">

<h3>Tambah Category</h3>

<form method="POST">

<div class="mb-3">

<label>Name</label>

<input
type="text"
name="name"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
class="form-control"></textarea>

</div>

<button
type="submit"
name="submit"
class="btn btn-primary">

Simpan

</button>

</form>

</div>

<?php include '../../includes/footer.php'; ?>