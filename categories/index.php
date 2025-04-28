<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


require_once '../config/database.php';
$conn = getConnection();


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $categoryId = $_GET['delete'];
    
    try {

        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM games WHERE category_id = :id");
        $stmt->bindParam(':id', $categoryId);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($count > 0) {
            $errorMessage = "Não é possível excluir esta categoria pois existem jogos associados a ela.";
        } else {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            
            $successMessage = "Categoria excluída com sucesso!";
        }
    } catch (PDOException $e) {
        $errorMessage = "Erro ao excluir a categoria: " . $e->getMessage();
    }
}


try {
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(g.id) as game_count 
        FROM categories c
        LEFT JOIN games g ON c.id = g.category_id
        GROUP BY c.id
        ORDER BY c.name
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Erro ao buscar categorias: " . $e->getMessage();
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias - BergStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php include '../components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include '../components/header.php'; ?>
            
            <div class="dashboard">
                <div class="page-header">
                    <h1 class="animate-on-load">Gerenciar Categorias</h1>
                    <a href="create.php" class="btn btn-primary animate-on-load">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Nova Categoria
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
                        <h2 class="table-title">Lista de Categorias</h2>
                    </div>
                    
                    <?php if (count($categories) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Jogos</th>
                                        <th>Data de Criação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                                            <td><?php echo !empty($category['description']) ? htmlspecialchars(substr($category['description'], 0, 50)) . (strlen($category['description']) > 50 ? '...' : '') : '-'; ?></td>
                                            <td><?php echo $category['game_count']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn-edit">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </a>
                                                    <a href="index.php?delete=<?php echo $category['id']; ?>" class="btn-delete" data-confirm-message="Tem certeza que deseja excluir esta categoria? Esta ação não pode ser desfeita.">
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
                            <p>Nenhuma categoria encontrada. Comece adicionando uma nova categoria!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>