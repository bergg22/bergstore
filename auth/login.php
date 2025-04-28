<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Usuário e senha são obrigatórios";
    } else {
        try {
            $conn = getConnection();
            
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Usuário ou senha inválidos";
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
    <title>Login - BergStore</title>
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
            
            <form class="auth-form" action="login.php" method="POST" data-validate>
                <div class="form-group">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" id="username" name="username" required data-error-message="Usuário é obrigatório">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" id="password" name="password" required data-error-message="Senha é obrigatória">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Entrar</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>Não tem uma conta? <a href="/teste/auth/register.php">Registre-se</a></p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>