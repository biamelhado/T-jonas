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
    $nomesala = isset($_POST['nomesala']) ? $_POST['nomesala'] : null;
    $qtdlugares = isset($_POST['qtdlugares']) ? (int)$_POST['qtdlugares'] : null;

    // Exibir valores recebidos para debug
    echo "nomesala: $nomesala, qtdlugares: $qtdlugares"; // Para ver os valores recebidos
    
    if (!empty($nomesala) && !empty($qtdlugares)) {
        // Inserir dados no banco
        $sql = "INSERT INTO sala (nomesala, qtdlugares) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nomesala, $qtdlugares);

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