<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

if (!isset($_GET['q']) || empty($_GET['q'])) {
    header("Location: index.php");
    exit();
}

$searchQuery = $_GET['q'];

require_once 'config/database.php';
$conn = getConnection();

try {
    $stmt = $conn->prepare("
        SELECT g.*, c.name as category_name 
        FROM games g
        JOIN categories c ON g.category_id = c.id
        WHERE g.title LIKE :query OR g.description LIKE :query OR g.developer LIKE :query OR g.publisher LIKE :query
        ORDER BY g.title
    ");
    $searchParam = "%" . $searchQuery . "%";
    $stmt->bindParam(':query', $searchParam);
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
    <title>Resultados da Pesquisa - BergStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php include 'components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'components/header.php'; ?>
            
            <div class="dashboard">
                <h1 class="animate-on-load">Resultados da Pesquisa: "<?php echo htmlspecialchars($searchQuery); ?>"</h1>
                
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
                
                <div class="search-results-container animate-on-load">
                    <div class="search-summary">
                        <p><?php echo count($games); ?> resultados encontrados</p>
                    </div>
                    
                    <?php if (count($games) > 0): ?>
                        <div class="search-results">
                            <?php foreach ($games as $game): ?>
                                <div class="search-result-item">
                                    <?php if (!empty($game['image_url'])): ?>
                                        <div class="result-image">
                                            <img src="<?php echo htmlspecialchars($game['image_url']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="result-content">
                                        <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                                        <div class="result-meta">
                                            <span class="category-badge"><?php echo htmlspecialchars($game['category_name']); ?></span>
                                            <?php if (!empty($game['release_year'])): ?>
                                                <span class="year-badge"><?php echo $game['release_year']; ?></span>
                                            <?php endif; ?>
                                            <span class="price-badge">R$ <?php echo number_format($game['price'], 2, ',', '.'); ?></span>
                                        </div>
                                        <?php if (!empty($game['description'])): ?>
                                            <p class="result-description"><?php echo htmlspecialchars(substr($game['description'], 0, 150)) . (strlen($game['description']) > 150 ? '...' : ''); ?></p>
                                        <?php endif; ?>
                                        <div class="result-actions">
                                            <a href="/teste/games/view.php?id=<?php echo $game['id']; ?>" class="btn btn-primary">Ver Detalhes</a>
                                            <a href="/teste/games/edit.php?id=<?php echo $game['id']; ?>" class="btn btn-edit">Editar</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-search-results">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                            <p>Nenhum resultado encontrado para "<?php echo htmlspecialchars($searchQuery); ?>".</p>
                            <p>Tente outra pesquisa ou <a href="/teste/games/create.php">adicione um novo jogo</a>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>