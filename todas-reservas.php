<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <title>Reúne Aqui Admin</title>
</head>

<header class="container">
    <div class="logo">
        <img src="img/logo.png" alt="Logotipo do Reúne Aqui" class="logo img-fluid">
    </div>
</header>

<main class="corpo">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row container-fluid">
            <div class="col-12 col-md-7 p-0 d-flex flex-column justify-content-center align-items-center">

                <h5 class="text-center mb-3">Todas as reservas</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-warning">
                            <tr>
                                <th>Usuário</th>
                                <th>Sala</th>
                                <th>Data</th>
                                <th>Horário</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
// Configuração do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salas";

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Consulta para obter todas as reservas com junção das tabelas user e sala
$sql = "
    SELECT u.nome, s.nomesala, r.datareserva, r.horarioinicial, r.horariofinal
    FROM reservas r
    JOIN user u ON u.id_user = r.id_user
    JOIN sala s ON s.id_sala = r.id_sala
";

$resultAllReservas = $conn->query($sql);

// Verificar se há resultados
if ($resultAllReservas->num_rows > 0) {
    // Exibir os dados
    while ($row = $resultAllReservas->fetch_assoc()) {
        // Formatação da data e horários
        $data_reserva_formatada = date('d/m/Y', strtotime($row['datareserva']));
        $horario_inicial_formatado = date('H:i', strtotime($row['horarioinicial']));
        $horario_final_formatado = date('H:i', strtotime($row['horariofinal']));

        echo "<tr>
                <td>" . $row['nome'] . "</td>
                <td>" . $row['nomesala'] . "</td>
                <td>" . $data_reserva_formatada . "</td>
                <td>" . $horario_inicial_formatado . " - " . $horario_final_formatado . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>Nenhuma reserva encontrada</td></tr>";
}

$conn->close();
?>
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn w-100" data-bs-toggle="modal" data-bs-target="#cadsModal">CADASTRAR SALAS</button>

            </div>
        </div>
    </div>

    <div class="modal fade" id="cadsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">CADASTRO DE SALA</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form action="cadastro-salas.php" method="POST">
                        <div class="mb-2">
                            <label for="nomesala" class="form-label">Nome da sala:</label>
                            <input type="text" class="form-control" id="nomesala" name="nomesala" required>
                        </div>
                        <div class="mb-2">
                            <label for="softwares" class="form-label">Nº de lugares:</label>
                            <input type="number" class="form-control" id="qtdlugares" name="qtdlugares" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Concluir</button>
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

<script>
    function toggleSenha(event) {
        event.preventDefault();
        const senhaInput = document.getElementById('senha');
        const senhaIcon = document.getElementById('senha-icon');
        if (senhaInput.type === 'password') {
            senhaInput.type = 'text';
            senhaIcon.classList.remove('bi-eye-slash');
            senhaIcon.classList.add('bi-eye');
        } else {
            senhaInput.type = 'password';
            senhaIcon.classList.remove('bi-eye');
            senhaIcon.classList.add('bi-eye-slash');
        }
        document.querySelector('form').addEventListener('submit', function (event) {
            const senha = document.getElementById('cad-senha').value;
            const confirmaSenha = document.getElementById('confirma-senha').value;
            if (senha !== confirmaSenha) {
                event.preventDefault();
                alert("As senhas não coincidem!");
            }
        });
    }
</script>

</body>
</html>
