<?php
// Ativar buffer de saída e iniciar sessão
ob_start();
session_start();

// Conectar ao banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salas";

// Conectar ao banco de dados
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}

// Login do usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['salvar'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = $_POST['senha'];

    // Buscar usuário pelo email
    $sql = "SELECT * FROM user WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Comparar a senha diretamente (sem criptografia)
        if ($senha === $user['senha']) {
            // Inicia a sessão para o usuário logado
            $_SESSION["email"] = $user['email'];
            $_SESSION["id_user"] = $user['id_user'];

            // Redireciona com base no tipo de usuário
            if ($email == 'admreunaaqui@gmail.com') {
                header("Location: todas-reservas.php");
                exit;
            } else {
                header("Location: reservas.php");
                exit;
            }
        } else {
            echo '<script>alert("Senha incorreta!");</script>';
        }
    } else {
        echo '<script>alert("E-mail não encontrado!");</script>';
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <title>Reúne Aqui</title>
</head>

<header class="container">
    <div class="logo">
        <img src="img/logo.png" alt="Logotipo do Reúne Aqui" class="logo img-fluid">
    </div>
</header>

<body>
    <main class="corpo">
        <div class="container d-flex justify-content-center align-items-center min-vh-100">
            <div class="row container-fluid">
                <div class="col-12 col-md-5 p-0 d-flex justify-content-center align-items-stretch">
                    <img src="img/sla-reune.jpg" alt="Imagem da sala do Reúne Aqui" class="sala img-thumbnail img-fluid">
                </div>
                <div class="col-12 col-md-7 p-0 d-flex flex-column justify-content-center align-items-center">
                    <div class="mb-4 p-3 text-center welcome-text w-100">
                        <h3>Seu Portal que Reserva Salas de Reunião!</h3>
                        <h5>Organize suas reuniões de forma rápida e prática.</h5>
                        <p>Faça o login e garanta o espaço ideal para sua equipe!</p>
                    </div>
                    <form method="post" class="form w-80">
                        <div class="mb-2">
                            <label for="login" class="label p-2">LOGIN</label>
                            <input type="email" class="input form-control" id="user" placeholder="@gmail.com" name="email" required>
                        </div>
                        <div class="mb-2">
                            <label for="pwd" class="label p-2">SENHA</label>
                            <div class="input-group">
                                <input type="password" class="input form-control" id="senha" name="senha" required>
                                <button type="button" class="btn btn-outline-secondary" aria-label="Mostrar senha" onclick="toggleSenha(event)">
                                    <i class="bi bi-eye-slash" id="senha-icon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <button type="submit" id="btn" class="btn btn-light w-100">ENTRAR</button>
                        </div>
                        <button type="button" class="btn btn-link w-100" data-bs-toggle="modal" data-bs-target="#caduModal">Cadastre-se</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function toggleSenha(event) {
                const senhaInput = document.getElementById('senha');
                const icon = document.getElementById('senha-icon');
                if (senhaInput.type === 'password') {
                    senhaInput.type = 'text';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    senhaInput.type = 'password';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            }
        </script>

        <!-- Modal para cadastro -->
        <div class="modal fade" id="caduModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">CADASTRE-SE</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="cadastro-usuario.php" method="POST">
                            <div class="mb-2">
                                <label for="nome" class="form-label">NOME</label>
                                <input type="text" class="form-control" id="cad-nome" name="nome" required>
                            </div>
                            <div class="mb-2">
                                <label for="email" class="form-label">E-MAIL</label>
                                <input type="email" class="form-control" id="cad-email" name="email" required>
                            </div>
                            <div class="mb-2">
                                <label for="senha" class="form-label">SENHA</label>
                                <input type="password" class="form-control" id="cad-senha" name="senha" required>
                            </div>
                            <div class="mb-2">
                                <label for="confirma-senha" class="form-label">CONFIRME SUA SENHA</label>
                                <input type="password" class="form-control" id="confirma-senha" name="confirma_senha" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" name="salvar">Concluir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <footer class="rodape mt-5 py-3 text-black">
        <div class="container text-center">
            <p class="m-0">DESENVOLVIDO POR BBE®</p>
        </div>
    </footer>

</body>

</html>
