<!doctype html>
<html lang="it">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Authentication Unit Tests</title>
</head>

<body>
    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <div class="container text-center fw-light">
            <?php
            require('../../authentication/auth-helper.php');
            require('../../authentication/db_connection.php');

            $testCount = 0; // Contatore dei test superati

            // Test caso email vuota
            $_POST['email'] = '';
            $result = validate_input_email($_POST['email']);
            assert($result === '');
            $testCount++;

            // Test caso email valida
            $_POST['email'] = 'example@example.com';
            $result = validate_input_email($_POST['email']);
            assert($result === 'example@example.com');
            $testCount++;

            // Test caso password vuota
            $_POST['password'] = '';
            $result = validate_input_text($_POST['password']);
            assert($result === '');
            $testCount++;

            // Test caso password valida
            $_POST['password'] = 'password123';
            $result = validate_password($_POST['password']);
            assert($result === false);
            $testCount++;

            // Test hashing
            $email = 'example@example.com';
            $password = 'password123';
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($password == $hashedPassword) {
                $testCount++;
            }

            // Test inserimento record
            $con = get_connection();
            $stmt = $con->prepare("INSERT INTO persone (email, digest_password) VALUES (:email, :password)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            $testCount++;

            // Test elimina record
            $stmt = $con->prepare("DELETE FROM persone WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $testCount++;

            echo "Unit tests completato con successo. Tests passati: " . $testCount . "/7\n";
            ?>
    </div>


</body>

</html>