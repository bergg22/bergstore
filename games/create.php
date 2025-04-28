<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


require_once '../config/database.php';
$conn = getConnection();


try {
    $stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Erro ao buscar categorias: " . $e->getMessage();
    $categories = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $releaseYear = $_POST['release_year'] ?? null;
    $developer = $_POST['developer'] ?? '';
    $publisher = $_POST['publisher'] ?? '';
    $categoryId = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $imageUrl = $_POST['image_url'] ?? '';
    
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "O título é obrigatório";
    }
    
    if (empty($categoryId)) {
        $errors[] = "A categoria é obrigatória";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO games (title, description, release_year, developer, publisher, category_id, price, image_url) 
                VALUES (:title, :description, :release_year, :developer, :publisher, :category_id, :price, :image_url)
            ");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':release_year', $releaseYear);
            $stmt->bindParam(':developer', $developer);
            $stmt->bindParam(':publisher', $publisher);
            $stmt->bindParam(':category_id', $categoryId);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':image_url', $imageUrl);
            
            $stmt->execute();
            
            // Redirect to games list with success message
            header("Location: index.php?success=created");
            exit();
        } catch (PDOException $e) {
            $errorMessage = "Erro ao salvar o jogo: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Novo Jogo - BergStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php include '../components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include '../components/header.php'; ?>
            
            <div class="dashboard">
                <h1 class="animate-on-load">Adicionar Novo Jogo</h1>
                
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
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="form-container animate-on-load">
                    <form action="create.php" method="POST" data-validate>
                        <div class="form-group">
                            <label for="title" class="form-label">Título*</label>
                            <input type="text" id="title" name="title" required value="<?php echo $_POST['title'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea id="description" name="description" rows="4"><?php echo $_POST['description'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="release_year" class="form-label">Ano de Lançamento</label>
                            <input type="number" id="release_year" name="release_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo $_POST['release_year'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group" style="flex: 1; margin-right: 10px;">
                                <label for="developer" class="form-label">Desenvolvedor</label>
                                <input type="text" id="developer" name="developer" value="<?php echo $_POST['developer'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label for="publisher" class="form-label">Publisher</label>
                                <input type="text" id="publisher" name="publisher" value="<?php echo $_POST['publisher'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id" class="form-label">Categoria*</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="price" class="form-label">Preço (R$)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $_POST['price'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="image_url" class="form-label">URL da Imagem</label>
                            <input type="url" id="image_url" name="image_url" value="<?php echo $_POST['image_url'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php" class="btn btn-cancel">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>