<?php
// Configuração do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salas";

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numsala = isset($_POST['numsala']) ? $_POST['numsala'] : null;
    $num_act = isset($_POST['num_act']) ? (int)$_POST['num_act'] : null;

    // Exibir valores recebidos para debug
    echo "numsala: $numsala, num_act: $num_act"; // Para ver os valores recebidos
    
    if (!empty($numsala) && !empty($num_act)) {
        // Inserir dados no banco
        $sql = "INSERT INTO sala (numsala, num_act) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $numsala, $num_act);

        if ($stmt->execute()) {
            echo "<script>alert('Sala cadastrada com sucesso!'); window.location.href = 'todas-reservas.php';</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar sala: " . $conn->error . "'); window.location.href = 'todas-reservas.php';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Erro: Campos obrigatórios não preenchidos.'); window.location.href = 'todas-reservas.php';</script>";
    }
}
?>