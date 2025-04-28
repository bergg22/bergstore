<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$gameId = $_GET['id'];

require_once '../config/database.php';
$conn = getConnection();

try {
    $stmt = $conn->prepare("
        SELECT g.*, c.name as category_name 
        FROM games g
        JOIN categories c ON g.category_id = c.id
        WHERE g.id = :id
    ");
    $stmt->bindParam(':id', $gameId);
    $stmt->execute();
    
    $game = $stmt->fetch();
    
    if (!$game) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    $errorMessage = "Erro ao buscar detalhes do jogo: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Jogo - BergStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php include '../components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include '../components/header.php'; ?>
            
            <div class="dashboard">
                <div class="page-header">
                    <h1 class="animate-on-load">Detalhes do Jogo</h1>
                    <div class="page-actions animate-on-load">
                        <a href="index.php" class="btn btn-cancel">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Voltar
                        </a>
                        <a href="edit.php?id=<?php echo $gameId; ?>" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Editar
                        </a>
                    </div>
                </div>
                
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
                
                <div class="game-detail-container animate-on-load">
                    <div class="game-detail-card">
                        <div class="game-detail-header">
                            <div class="game-image">
                                <?php if (!empty($game['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($game['image_url']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                        <p>Sem imagem</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="game-info">
                                <h2><?php echo htmlspecialchars($game['title']); ?></h2>
                                <div class="game-meta">
                                    <span class="category-badge"><?php echo htmlspecialchars($game['category_name']); ?></span>
                                    <?php if (!empty($game['release_year'])): ?>
                                        <span class="year-badge"><?php echo $game['release_year']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="game-price">
                                    <h3>R$ <?php echo number_format($game['price'], 2, ',', '.'); ?></h3>
                                </div>
                            </div>
                        </div>
                        
                        <div class="game-detail-content">
                            <div class="detail-section">
                                <h3>Descrição</h3>
                                <p><?php echo !empty($game['description']) ? nl2br(htmlspecialchars($game['description'])) : 'Sem descrição disponível.'; ?></p>
                            </div>
                            
                            <div class="detail-section">
                                <h3>Detalhes</h3>
                                <table class="detail-table">
                                    <tr>
                                        <th>Desenvolvedor</th>
                                        <td><?php echo !empty($game['developer']) ? htmlspecialchars($game['developer']) : 'Não informado'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Publisher</th>
                                        <td><?php echo !empty($game['publisher']) ? htmlspecialchars($game['publisher']) : 'Não informado'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Data de Cadastro</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($game['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Última Atualização</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($game['updated_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>