<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header("Location: reservas.php");
    exit();
}

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

// Verifica se o link foi clicado e se a sala foi selecionada
if (isset($_GET['id_sala']) && isset($_SESSION['datareserva'])) {
    // Recupera os dados da reserva da sessão
    $numsala = $_GET['numsala'];
    $num_act = $_SESSION['num_act'];
    $datareserva = $_SESSION['datareserva'];
    $horarioinicial = $_SESSION['horarioinicial'];
    $horariofinal = $_SESSION['horariofinal'];

    // Recupera o id_user baseado no email do usuário
    $email = $_SESSION['email'];
    $sqlUser = "SELECT id_user FROM user WHERE email = '$email'";
    $resultUser = $conn->query($sqlUser);
    
    if ($resultUser->num_rows > 0) {
        $rowUser = $resultUser->fetch_assoc();
        $nome = $rowUser['nome'];

        // Insere a reserva na tabela 'reservas'
        $sqlReserva = "INSERT INTO reservas (nome, num_act, datareserva, horarioinicial, horariofinal) 
                       VALUES ('$nome', '$num_act', '$datareserva', '$horarioinicial', '$horariofinal')";
        
        if ($conn->query($sqlReserva) === TRUE) {
            // Recupera o ID da reserva recém-criada
            $id_reserva = $conn->insert_id;

            // Insere a relação entre a sala e a reserva na tabela 'sala_reservas'
            $sqlSalaReserva = "INSERT INTO sala_reservas (id_sala, id_reserva) VALUES ('$id_sala', '$id_reserva')";

            if ($conn->query($sqlSalaReserva) === TRUE) {
                echo "Reserva feita com sucesso!";
            } else {
                echo "Erro ao associar sala à reserva: " . $conn->error;
            }
        } else {
            echo "Erro ao criar a reserva: " . $conn->error;
        }
    } else {
        echo "Usuário não encontrado!";
    }
} else {
    echo "Dados de reserva não encontrados!";
}

$conn->close();
?>