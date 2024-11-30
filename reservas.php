<?php
// Ativar a sessão
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION["email"])) {
    echo '<script>alert("Usuário não está logado!"); window.location="login.php";</script>';
    exit();
}

// Dados de conexão ao banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salas";

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão com o banco de dados
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Armazena o email do usuário logado
$email = $_SESSION["email"];

// Define a quantidade de dias para as "últimas reservas" (exemplo: últimas 7 dias)
$days_ago = 7;
$current_date = date('Y-m-d'); // Data atual
$current_time = date('H:i'); // Hora atual
$date_limit = date('Y-m-d', strtotime($current_date . ' - ' . $days_ago . ' days'));


// Consulta SQL para obter as últimas reservas do usuário nos últimos 7 dias
$sql_last_reservations = "
    SELECT r.id_sala, s.nomesala, r.datareserva, r.horarioinicial, r.horariofinal, r.qtdlugares
    FROM reservas r
    JOIN sala s ON s.id_sala = r.id_sala
    JOIN user u ON u.id_user = r.id_user
    WHERE u.email = ? AND r.datareserva >= ?
    ORDER BY r.datareserva DESC, r.horarioinicial DESC
";

$stmt = $conn->prepare($sql_last_reservations);
$stmt->bind_param("ss", $email, $date_limit);
$stmt->execute();
$result_last_reservations = $stmt->get_result();

// Consulta SQL para obter os nomes das salas e a quantidade de lugares
$sql_salas = "SELECT id_sala, nomesala, qtdlugares FROM sala";
$result_salas = $conn->query($sql_salas);

// Verifica se o formulário de nova reserva foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sala = $_POST["id_sala"];
    $datareserva = $_POST["datareserva"];
    $horarioinicial = $_POST["horarioinicial"];
    $horariofinal = $_POST["horariofinal"];
    $qtdlugares = $_POST["qtdlugares"];

    // Verifica se a data da reserva é anterior à data atual
    if ($datareserva < $current_date) {
        echo '<script>alert("A data de reserva não pode ser anterior à data atual!");</script>';
    }
    // Verifica se a data da reserva é hoje, mas o horário é anterior ao horário atual
    elseif ($datareserva == $current_date && $horarioinicial < $current_time) {
        echo '<script>alert("O horário de início não pode ser anterior ao horário atual!");</script>';
    }
    // Verifica se o horário final é anterior ao horário inicial
    elseif ($horariofinal <= $horarioinicial) {
        echo '<script>alert("O horário final deve ser posterior ao horário inicial!");</script>';
    } else {
        // Obtém a capacidade total da sala
        $sql_capacity = "SELECT qtdlugares FROM sala WHERE id_sala = ?";
        $stmt_capacity = $conn->prepare($sql_capacity);
        $stmt_capacity->bind_param("i", $id_sala);
        $stmt_capacity->execute();
        $result_capacity = $stmt_capacity->get_result();
        if ($result_capacity->num_rows === 0) {
            echo '<script>alert("Sala não encontrada!"); window.location="reservas.php";</script>';
            exit();
        }
        $row_capacity = $result_capacity->fetch_assoc();
        $max_lugares = $row_capacity["qtdlugares"];

        // Verifica se a quantidade de lugares solicitados não ultrapassa a capacidade da sala
        if ($qtdlugares > $max_lugares) {
            echo '<script>alert("A quantidade de lugares solicitada excede a capacidade da sala!");</script>';
        } else {
            // Verifica a quantidade de lugares já reservados no mesmo horário e data
            $sql_check_capacity = "
                SELECT SUM(qtdlugares) AS total_ocupado 
                FROM reservas 
                WHERE id_sala = ? AND datareserva = ? 
                AND (
                    (horarioinicial < ? AND horariofinal > ?) OR  -- Início da nova reserva dentro do intervalo de outra
                    (horarioinicial < ? AND horariofinal > ?) OR  -- Fim da nova reserva dentro do intervalo de outra
                    (horarioinicial >= ? AND horariofinal <= ?)   -- Nova reserva completamente englobada por outra
                )
            ";
            $stmt_check_capacity = $conn->prepare($sql_check_capacity);
            $stmt_check_capacity->bind_param(
                "isssssss",
                $id_sala,
                $datareserva,
                $horariofinal, $horarioinicial,
                $horariofinal, $horarioinicial,
                $horarioinicial, $horariofinal
            );
            $stmt_check_capacity->execute();
            $result_check_capacity = $stmt_check_capacity->get_result();
            $row_check_capacity = $result_check_capacity->fetch_assoc();
            $total_ocupado = $row_check_capacity["total_ocupado"] ?? 0;

            // Verifica se há capacidade disponível
            if ($total_ocupado + $qtdlugares > $max_lugares) {
                echo '<script>alert("Sala indisponível! Tente um outro dia e horário.");</script>';
            } else {
                // Insere a nova reserva no banco de dados
                $stmt_insert = $conn->prepare("
                    INSERT INTO reservas (id_user, id_sala, qtdlugares, datareserva, horarioinicial, horariofinal)
                    VALUES ((SELECT u.id_user FROM user u WHERE u.email = ? LIMIT 1), ?, ?, ?, ?, ?)
                ");
                $stmt_insert->bind_param("siisss", $email, $id_sala, $qtdlugares, $datareserva, $horarioinicial, $horariofinal);

                if ($stmt_insert->execute()) {
                    echo '<script>alert("Reserva realizada com sucesso!");</script>';
                } else {
                    echo '<script>alert("Erro ao realizar reserva: ' . $stmt_insert->error . '");</script>';
                }
            }
        }
    }
}

// Fechar a conexão
$conn->close();
?>





<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reúne Aqui - Reservas</title>
    <link rel="stylesheet" href="home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header class="container">
    <div class="logo">
        <img src="img/logo.png" alt="Logotipo do Reúne Aqui" class="logo img-fluid">
    </div>
</header>

<main class="container mt-4">
    <div class="row">
        <!-- Exibição das Últimas Reservas -->
        <div class="col-12">
            <h5>Últimas reservas realizadas:</h5>
            <?php
            if ($result_last_reservations->num_rows > 0) {
                echo '<table class="table table-bordered">';
                echo '<thead class="table-warning">
                        <tr>
                            <th>Sala</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Quantidade de Lugares</th>
                        </tr>
                    </thead><tbody>';
                while ($row = $result_last_reservations->fetch_assoc()) {
                    // Formatação da data e horário
                    $data_reserva_formatada = date('d/m/Y', strtotime($row['datareserva']));
                    $horario_inicial_formatado = date('H:i', strtotime($row['horarioinicial']));
                    $horario_final_formatado = date('H:i', strtotime($row['horariofinal']));

                    echo "<tr>
                            <td>" . $row['nomesala'] . "</td>
                            <td>" . $data_reserva_formatada . "</td>
                            <td>" . $horario_inicial_formatado . " - " . $horario_final_formatado . "</td>
                            <td>" . $row['qtdlugares'] . "</td>
                          </tr>";
                }
                echo '</tbody></table>';
            } else {
                echo "<p>Você não fez nenhuma reserva nos últimos 7 dias.</p>";
            }
            ?>
        </div>

        <!-- Formulário para Nova Reserva -->
        <div class="col-12 mt-4">
            <h5>Fazer uma nova reserva:</h5>
            <form method="POST">
                <div class="mb-3">
                    <label for="id_sala" class="form-label">Sala</label>
                    <select name="id_sala" id="id_sala" class="form-control" required>
                        <?php
                        // Exibe as salas cadastradas no banco de dados
                        while ($row = $result_salas->fetch_assoc()) {
                            echo "<option value='" . $row['id_sala'] . "'>" . $row['nomesala'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="datareserva" class="form-label">Data da Reserva</label>
                    <input type="date" name="datareserva" id="datareserva" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="horarioinicial" class="form-label">Horário Inicial</label>
                    <input type="time" name="horarioinicial" id="horarioinicial" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="horariofinal" class="form-label">Horário Final</label>
                    <input type="time" name="horariofinal" id="horariofinal" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="qtdlugares" class="form-label">Quantidade de Lugares</label>
                    <input type="number" name="qtdlugares" id="qtdlugares" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Reservar</button>
            </form>
        </div>
    </div>
</main>

<footer class="container text-center mt-4">
    <p>DESENVOLVIDO POR BBE®</p>
</footer>

</body>
</html>
