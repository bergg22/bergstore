<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';
$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "O nome da categoria é obrigatório";
    }
    
    if (empty($errors)) {
        try {

            $stmt = $conn->prepare("SELECT id FROM categories WHERE name = :name");
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Já existe uma categoria com este nome";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO categories (name, description) 
                    VALUES (:name, :description)
                ");
                
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                
                $stmt->execute();
                

                header("Location: index.php?success=created");
                exit();
            }
        } catch (PDOException $e) {
            $errorMessage = "Erro ao salvar a categoria: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Nova Categoria - BergStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <?php include '../components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include '../components/header.php'; ?>
            
            <div class="dashboard">
                <h1 class="animate-on-load">Adicionar Nova Categoria</h1>
                
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
                            <label for="name" class="form-label">Nome da Categoria*</label>
                            <input type="text" id="name" name="name" required value="<?php echo $_POST['name'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea id="description" name="description" rows="4"><?php echo $_POST['description'] ?? ''; ?></textarea>
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