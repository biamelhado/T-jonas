<?php
session_start();

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salas";

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Tratamento de inserção de dados
$resultdisp = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['disp'])) {
    $qtlugares = $conn->real_escape_string($_POST['qtdlugares']);
    $datareserva = $conn->real_escape_string($_POST['datareserva']);
    $horarioinicial = $conn->real_escape_string($_POST['horarioinicial']);
    $horariofinal = $conn->real_escape_string($_POST['horariofinal']);

    // Consulta para selecionar salas disponíveis
    $sql = "SELECT sala.* 
            FROM sala
            WHERE sala.num_act >= $qtlugares
              AND NOT EXISTS (
                  SELECT 1
                  FROM sala_reservas
                  JOIN reservas ON sala_reservas.id_reserva = reservas.id_reserva
                  WHERE sala_reservas.id_sala = sala.id_sala
                    AND reservas.datareserva = '$datareserva'
                    AND (
                        '$horarioinicial' BETWEEN reservas.horarioinicial AND reservas.horariofinal
                        OR '$horariofinal' BETWEEN reservas.horarioinicial AND reservas.horariofinal
                    )
              );";
    $resultdisp = $conn->query($sql);

    // Salva os dados da reserva na sessão
    $_SESSION["qtdlugares"] = $qtlugares;
    $_SESSION["datareserva"] = $datareserva;
    $_SESSION["horarioinicial"] = $horarioinicial;
    $_SESSION["horariofinal"] = $horariofinal;
}

// Recupera o email do usuário logado
$email = $_SESSION["email"];

// Consulta para listar as reservas do usuário
$sql = "SELECT 
            sala.numsala AS numero, 
            reservas.datareserva, 
            CONCAT(DATE_FORMAT(reservas.horarioinicial, '%H:%i'), ' às ', DATE_FORMAT(reservas.horariofinal, '%H:%i')) AS horario
        FROM sala
        JOIN sala_reservas ON sala.id_sala = sala_reservas.id_sala
        JOIN reservas ON sala_reservas.id_reserva = reservas.id_reserva
        WHERE reservas.id_user = (SELECT id_user FROM user WHERE email = '$email');";
$resultreservas = $conn->query($sql);
// Destruir sessão se o usuário clicar no botão "Sair"
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: home.php");
    exit;
}
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
    <title>Reservas</title>
</head>

<header class="container">
    <div class="logo">
        <img src="img/logo.png" alt="Logotipo do Reúne Aqui" class="logo img-fluid">
    </div>
        <!-- Botão de Sair -->
        <div class="logout-button">
        <a href="?logout=true" class="btn btn-danger">Sair</a>
    </div>
</header>

<main class="corpo">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row container-fluid">

            <!-- Formulário para verificar disponibilidade -->
            <div class="col-12 col-md-7 p-0 d-flex flex-column justify-content-center align-items-center">
                <div class="card p-3 mb-4">
                    <h5 class="text-center mb-3">Reserve uma sala</h5>
                    <form action="reservas.php" method="POST">
                        <div class="mb-3">
                            <label for="qtdlugares" class="form-label">Quantidade de assentos:</label>
                            <input type="number" class="form-control" name="qtdlugares" required>
                        </div>
                        <div class="mb-3">
                            <label for="datareserva" class="form-label">Data:</label>
                            <input type="date" class="form-control" name="datareserva" required>
                        </div>
                        <div class="mb-3">
                            <label for="horarioinicial" class="form-label">Horário inicial:</label>
                            <input type="time" class="form-control" name="horarioinicial" required>
                        </div>
                        <div class="mb-3">
                            <label for="horariofinal" class="form-label">Horário final:</label>
                            <input type="time" class="form-control" name="horariofinal" required>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100" name="disp">Verificar disponibilidade</button>
                    </form>
                </div>

                <!-- Tabela de salas disponíveis -->
                <div class="card p-3 mb-4">
                    <h5 class="text-center mb-3">Salas disponíveis</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-warning">
                                <tr>
                                    <th>Sala</th>
                                    <th>Nº de assentos</th>
                                    <th>Reservar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultdisp != null) { ?>
                                    <?php while ($row = $resultdisp->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= $row['numsala'] ?></td>
                                            <td><?= $row['num_act'] ?></td>
                                            <td><a href="criarreserva.php?id_sala=<?= $row['id_sala'] ?>">Reservar</a></td>
                                        </tr>
                                    <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabela de reservas -->
                <div class="card p-3">
                    <h5 class="text-center mb-3">Últimas reservas</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-warning">
                                <tr>
                                    <th>Sala</th>
                                    <th>Data</th>
                                    <th>Horário</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultreservas != null) { ?>
                                    <?php while ($row = $resultreservas->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= $row['numero'] ?></td>
                                            <td><?= $row['datareserva'] ?></td>
                                            <td><?= $row['horario'] ?></td>
                                        </tr>
                                    <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
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
