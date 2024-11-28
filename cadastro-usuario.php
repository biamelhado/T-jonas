<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salas";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = mysqli_real_escape_string($conn, $_POST['senha']);
    $confirmaSenha = mysqli_real_escape_string($conn, $_POST['confirma_senha']);

    // Verificar se as senhas coincidem
    if ($senha !== $confirmaSenha) {
        echo '<script>alert("As senhas não coincidem!"); window.history.back();</script>';
        exit;
    }

    // Inserir no banco de dados
    $sql = "INSERT INTO user (nome, email, senha) VALUES ('$nome', '$email', '$senha')";

    if (mysqli_query($conn, $sql)) {
        echo '<script>alert("Usuário cadastrado com sucesso!"); window.location.href="home.php";</script>';
    } else {
        echo "Erro ao cadastrar: " . mysqli_error($conn);
    }
}

mysqli_close($conn);


?>
