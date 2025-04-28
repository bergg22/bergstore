<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';
$conn = getConnection();

try {
    $stmt = $conn->prepare("CALL ExportGamesToTXT()");
    $stmt->execute();
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($games)) {
        $fileName = 'bergstore_games_' . date('Y-m-d_H-i-s') . '.txt';
        $filePath = '../reports/' . $fileName;
        
        if (!file_exists('../reports')) {
            mkdir('../reports', 0777, true);
        }

        $fileContent = "ID,TÍTULO,CATEGORIA,ANO,PREÇO\n";
        foreach ($games as $game) {
            $fileContent .= $game['game_data'] . "\n";
        }

        file_put_contents($filePath, $fileContent);
        
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=' . basename($filePath));
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($filePath);
        exit;
    } else {
        $errorMessage = "Nenhum dado encontrado para exportação.";
    }
} catch (PDOException $e) {
    $errorMessage = "Erro ao exportar dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportar Jogos - BergStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php include '../components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include '../components/header.php'; ?>
            
            <div class="dashboard">
                <h1>Exportar Lista de Jogos</h1>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <div class="export-container">
                    <p>Se o download não iniciar automaticamente, <a href="export.php">clique aqui</a>.</p>
                    <a href="index.php" class="btn btn-primary">Voltar para Relatórios</a>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>