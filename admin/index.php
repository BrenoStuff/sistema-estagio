<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="container py-5">
    <?php
    require '../backend/auth/verifica.php';
    verifica_acesso('admin');

    require_once '../backend/helpers/db-connect.php';

    $user_id = $_SESSION['usuario'];
    $sql = "SELECT * FROM usuarios WHERE user_id = '$user_id'";
    $dado = $conexao->query($sql);
    if ($dado->num_rows > 0) {
        $usuario = $dado->fetch_assoc();
    } else {
        header("location:../error.php?aviso=Erro ao carregar os dados do usuário!");
        exit();
    }
    ?>

    <h1> Bem vindo <?php echo $usuario['user_nome']; ?> </h1>
    <h2> Você está logado como: <?php echo $usuario['user_acesso']; ?> </h2>
    <a href="../backend/auth/logout.php" class="btn btn-danger">Sair</a>

    <br><br>
    <h2> Contratos</h2>

    <form action="index.php" method="POST">
        <div class="row">

            <div class="col">
                <input type="text" class="form-control" name="aluno" id="aluno" placeholder="Nome do aluno">
            </div>

            <div class="col">
                <select class="form-select" name="empresa" id="empresa">
                    <option value="">Selecione a empresa</option>
                    <?php
                    $sql = "SELECT * FROM empresas";
                    $dado = $conexao->query($sql);
                    if ($dado->num_rows > 0) {
                        while ($empresa = $dado->fetch_assoc()) {
                            echo "<option value='" . $empresa['empr_id'] . "'>" . $empresa['empr_nome'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="col">
                <select class="form-select" name="status" id="status">
                    <option value="">Selecione o status</option>
                    <option value="1">Ativo</option>
                    <option value="0">Inativo</option>
                </select>
            </div>

            <div class="col">
                <input type="date" class="form-control" name="data_inicio" id="data_inicio">
            </div>

            <div class="col">
                <select class="form-select" name="curso" id="curso">
                    <option value="">Selecione o curso</option>
                    <?php
                    $sql = "SELECT * FROM cursos";
                    $dado = $conexao->query($sql);
                    if ($dado->num_rows > 0) {
                        while ($curso = $dado->fetch_assoc()) {
                            echo "<option value='" . $curso['curs_id'] . "'>" . $curso['curs_nome'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="col">
                <select class="form-select" name="ordenar" id="ordenar">
                    <option value="">Ordenar por</option>
                    <option value="empresa">Empresa</option>
                    <option value="data_inicio">Data de início</option>
                    <option value="data_fim">Data de término</option>
                    <option value="aluno">Nome do aluno</option>
                </select>
            </div>

            <div class="col">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>
    <br>
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">Nome do aluno</th>
                <th scope="col">Empresa</th>
                <th scope="col">Data de início</th>
                <th scope="col">Data de término</th>
                <th scope="col">Rel. Inicial </th>
                <th scope="col">Rel. Final </th>
                <th scope="col">Curso</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM contratos JOIN empresas ON cntr_id_empresa = empr_id JOIN usuarios ON cntr_id_usuario = user_id JOIN cursos ON user_id_curs = curs_id";
            if (isset($_POST['aluno']) || isset($_POST['empresa']) || isset($_POST['status']) || isset($_POST['data_inicio']) || isset($_POST['curso'])) {
                $aluno = $_POST['aluno'];
                $empresa = $_POST['empresa'];
                $status = $_POST['status'];
                $data_inicio = $_POST['data_inicio'];
                $curso = $_POST['curso'];

                $sql .= " WHERE 1=1";
                if ($aluno != '') {
                    $sql .= " AND user_nome LIKE '%$aluno%'";
                }
                if ($empresa != '') {
                    $sql .= " AND cntr_id_empresa = '$empresa'";
                }
                if ($status != '') {
                    $sql .= " AND cntr_ativo = '$status'";
                }
                if ($data_inicio != '') {
                    $sql .= " AND cntr_data_inicio >= '$data_inicio'";
                }
                if ($curso != '') {
                    $sql .= " AND user_id_curs = '$curso'";
                }
            }

            if (isset($_POST['ordenar'])) {
                $ordenar = $_POST['ordenar'];
                if ($ordenar == 'empresa') {
                    $sql .= " ORDER BY empr_nome";
                } elseif ($ordenar == 'data_inicio') {
                    $sql .= " ORDER BY cntr_data_inicio";
                } elseif ($ordenar == 'data_fim') {
                    $sql .= " ORDER BY cntr_data_fim";
                } elseif ($ordenar == 'aluno') {
                    $sql .= " ORDER BY user_nome";
                }
            }

            $dado = $conexao->query($sql);
            if ($dado->num_rows > 0) {
                while ($contrato = $dado->fetch_assoc()) {
                    echo "<tr>";
                        echo "<th scope='row'>" . $contrato['user_nome'] . "</th>";
                        echo "<td>" . $contrato['empr_nome'] . "</td>";
                        echo "<td>" . date('d/m/Y', strtotime($contrato['cntr_data_inicio'])) . "</td>";
                        echo "<td>" . date('d/m/Y', strtotime($contrato['cntr_data_fim'])) . "</td>";
                        echo "<td>" . ($contrato['cntr_id_relatorio_inicial'] ? $contrato['cntr_id_relatorio_inicial'] : 'Não enviado') . "</td>";
                        echo "<td>" . ($contrato['cntr_id_relatorio_final'] ? $contrato['cntr_id_relatorio_final'] : 'Não enviado') . "</td>";
                        echo "<td>" . $contrato['curs_nome'] . "</td>";
                        echo "<td>" . ($contrato['cntr_ativo'] ? 'Ativo' : 'Inativo') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Nenhum contrato encontrado.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Modal editar contrato -->

    

    <script src="../js/bootstrap.js"></script>
    <script src="../js/scripts.js"></script>
</body>
</html>