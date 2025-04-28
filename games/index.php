<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';
$conn = getConnection();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $gameId = $_GET['delete'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM games WHERE id = :id");
        $stmt->bindParam(':id', $gameId);
        $stmt->execute();
        
        $successMessage = "Jogo excluído com sucesso!";
    } catch (PDOException $e) {
        $errorMessage = "Erro ao excluir o jogo: " . $e->getMessage();
    }
}

try {
    $stmt = $conn->prepare("
        SELECT g.*, c.name as category_name 
        FROM games g
        JOIN categories c ON g.category_id = c.id
        ORDER BY g.title
    ");
    $stmt->execute();
    $games = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Erro ao buscar jogos: " . $e->getMessage();
    $games = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Jogos - BergStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php include '../components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include '../components/header.php'; ?>
            
            <div class="dashboard">
                <div class="page-header">
                    <h1 class="animate-on-load">Gerenciar Jogos</h1>
                    <a href="create.php" class="btn btn-primary animate-on-load">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Novo Jogo
                    </a>
                </div>
                
                <?php if (isset($successMessage)): ?>
                    <div class="alert alert-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>
                
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
                
                <div class="data-table-container animate-on-load">
                    <div class="table-header">
                        <h2 class="table-title">Lista de Jogos</h2>
                    </div>
                    
                    <?php if (count($games) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Desenvolvedor</th>
                                        <th>Ano</th>
                                        <th>Preço</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($games as $game): ?>
                                        <tr>
                                            <td><?php echo $game['id']; ?></td>
                                            <td><?php echo htmlspecialchars($game['title']); ?></td>
                                            <td><?php echo htmlspecialchars($game['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($game['developer']); ?></td>
                                            <td><?php echo $game['release_year']; ?></td>
                                            <td>R$ <?php echo number_format($game['price'], 2, ',', '.'); ?></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="view.php?id=<?php echo $game['id']; ?>" class="btn-view">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $game['id']; ?>" class="btn-edit">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </a>
                                                    <a href="index.php?delete=<?php echo $game['id']; ?>" class="btn-delete" data-confirm-message="Tem certeza que deseja excluir este jogo?">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Nenhum jogo encontrado. Comece adicionando um novo jogo!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>