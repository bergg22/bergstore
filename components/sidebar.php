<aside class="sidebar">
    <div class="logo">
        <h2>BergStore</h2>
    </div>
    
    <nav class="nav-menu">
        <ul>
            <li><a href="/teste/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                <span>Dashboard</span>
            </a></li>
            <li><a href="/teste/games/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'games/') !== false ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                <span>Jogos</span>
            </a></li>
            <li><a href="/teste/categories/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'categories/') !== false ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polyline></svg>
                <span>Categorias</span>
            </a></li>
            <li><a href="/teste/reports/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'reports/') !== false ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                <span>Relatórios</span>
            </a></li>
        </ul>
    </nav>
    
    <div class="user-info">
        <?php if (isset($_SESSION['username'])): ?>
            <div class="avatar">
                <span><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></span>
            </div>
            <div class="user-details">
                <p class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <a href="/teste/auth/logout.php" class="logout-link">Sair</a>
            </div>
        <?php endif; ?>
    </div>
</aside>