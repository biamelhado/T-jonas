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

// Armazena o email do usuário, id da sala e outros dados de reserva
$email = $_SESSION["email"]; 
$id_sala = $_GET["id_sala"]; 
$qtdlugares = $_SESSION["qtdlugares"];
$datareserva = $_SESSION["datareserva"];
$horarioinicial = $_SESSION["horarioinicial"];
$horariofinal = $_SESSION["horariofinal"];

// Verifica se os dados necessários da reserva estão na sessão
if (!isset($qtdlugares, $datareserva, $horarioinicial, $horariofinal)) {
    echo '<script>alert("Dados de reserva não encontrados!"); window.location="reservas.php";</script>';
    exit();
}

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

// Verifica se já existe uma reserva para a sala no mesmo horário e data
$sql_check = "SELECT * FROM reservas 
              WHERE id_sala = ? 
              AND datareserva = ? 
              AND (
                  (horarioinicial <= ? AND horariofinal > ?)  -- Caso a reserva do usuário termine depois do horário inicial
                  OR
                  (horarioinicial < ? AND horariofinal >= ?)  -- Caso a reserva do usuário inicie antes do horário final
              )";

$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("issssss", $id_sala, $datareserva, $horarioinicial, $horarioinicial, $horariofinal);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// Se houver uma reserva para o mesmo horário e dia, exibe um alerta
if ($result_check->num_rows > 0) {
    echo '<script>alert("A sala já está reservada para este horário e data. Por favor, escolha outro horário."); window.location="reservas.php";</script>';
    exit();
}
// Prepara a declaração SQL para inserir os dados na tabela 'reservas'
$stmt = $conn->prepare("
    INSERT INTO reservas (id_user, id_sala, qtdlugares, datareserva, horarioinicial, horariofinal) 
    VALUES ((SELECT u.id_user FROM user u WHERE u.email = ? LIMIT 1), ?, ?, ?, ?, ?)
");

// Vincula os parâmetros à declaração preparada
$stmt->bind_param("siisss", $email, $id_sala, $qtdlugares, $datareserva, $horarioinicial, $horariofinal);

// Executa a declaração de inserção
if ($stmt->execute()) {
    echo '<script>alert("Reserva realizada com sucesso!");</script>';
} else {
    echo '<script>alert("Erro ao realizar reserva: ' . $stmt->error . '");</script>';
}

// Redireciona para a página de reservas após a operação
echo "<script type='text/javascript'>window.location='reservas.php';</script>";

// Fecha a conexão com o banco de dados
$conn->close();
?>
