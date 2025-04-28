<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Todos os campos são obrigatórios";
    } else if ($password !== $confirm_password) {
        $error = "As senhas não coincidem";
    } else {
        try {
            $conn = getConnection();
            
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error = "Este nome de usuário já está em uso";
            } else {

                $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $error = "Este email já está em uso";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->execute();
                    
                    $success = "Registro concluído com sucesso! Você já pode fazer login.";
                }
            }
        } catch (PDOException $e) {
            $error = "Erro no servidor. Tente novamente mais tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - BergStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card animate-on-load">
            <div class="auth-logo">
                <h1>BergStore</h1>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" action="register.php" method="POST" data-validate>
                <div class="form-group">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" id="username" name="username" required data-error-message="Usuário é obrigatório">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" required data-error-message="Email válido é obrigatório">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" id="password" name="password" required data-error-message="Senha é obrigatória">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirmar Senha</label>
                    <input type="password" id="confirm_password" name="confirm_password" required data-error-message="Confirmação de senha é obrigatória">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Registrar</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>Já tem uma conta? <a href="/teste/auth/login.php">Faça login</a></p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>