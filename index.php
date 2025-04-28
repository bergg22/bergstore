<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BergStore - Sistema de Gerenciamento de Jogos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-container">
        <?php include 'components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'components/header.php'; ?>
            
            <div class="dashboard">
                <h1 class="animate-on-load">Dashboard</h1>
                
                <div class="stats-container">
                    <div class="stat-card animate-on-load">
                        <?php
                        require_once 'config/database.php';
                        $conn = getConnection();
                        
                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM games");
                        $stmt->execute();
                        $gamesCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        ?>
                        <h3>Total de Jogos</h3>
                        <p class="stat-number"><?php echo $gamesCount; ?></p>
                    </div>
                    
                    <div class="stat-card animate-on-load">
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM categories");
                        $stmt->execute();
                        $categoriesCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        ?>
                        <h3>Categorias</h3>
                        <p class="stat-number"><?php echo $categoriesCount; ?></p>
                    </div>
                    
                    <div class="stat-card animate-on-load">
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
                        $stmt->execute();
                        $usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        ?>
                        <h3>Usuários</h3>
                        <p class="stat-number"><?php echo $usersCount; ?></p>
                    </div>
                </div>
                
                <div class="charts-container">
                    <div class="chart-card animate-on-load">
                        <h3>Jogos por Categoria</h3>
                        <div class="chart-container">
                            <canvas id="gamesByCategoryChart"></canvas>
                        </div>
                        <?php
                        $stmt = $conn->prepare("
                            SELECT c.name, COUNT(g.id) as count 
                            FROM categories c
                            LEFT JOIN games g ON c.id = g.category_id
                            GROUP BY c.id, c.name
                            ORDER BY count DESC
                        ");
                        $stmt->execute();
                        $categoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $labels = [];
                        $data = [];
                        
                        foreach ($categoryData as $category) {
                            $labels[] = $category['name'];
                            $data[] = $category['count'];
                        }
                        ?>
                        <script>
                            const ctx = document.getElementById('gamesByCategoryChart').getContext('2d');
                            Chart.defaults.color = '#E8EAED';
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: <?php echo json_encode($labels); ?>,
                                    datasets: [{
                                        label: 'Número de Jogos',
                                        data: <?php echo json_encode($data); ?>,
                                        backgroundColor: '#1A73E8',
                                        borderColor: '#0D47A1',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            labels: {
                                                color: '#E8EAED'
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                precision: 0,
                                                color: '#9AA0A6'
                                            },
                                            grid: {
                                                color: 'rgba(255, 255, 255, 0.1)'
                                            }
                                        },
                                        x: {
                                            ticks: {
                                                color: '#9AA0A6'
                                            },
                                            grid: {
                                                color: 'rgba(255, 255, 255, 0.1)'
                                            }
                                        }
                                    }
                                }
                            });
                        </script>
                    </div>
                    
                    <div class="chart-card animate-on-load">
                        <h3>Jogos Recentes</h3>
                        <ul class="recent-games">
                            <?php
                            $stmt = $conn->prepare("
                                SELECT g.title, c.name as category, g.created_at 
                                FROM games g
                                JOIN categories c ON g.category_id = c.id
                                ORDER BY g.created_at DESC
                                LIMIT 5
                            ");
                            $stmt->execute();
                            $recentGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($recentGames as $game) {
                                echo '<li class="recent-game-item">';
                                echo '<div class="game-info">';
                                echo '<h4>' . htmlspecialchars($game['title']) . '</h4>';
                                echo '<span class="category-badge">' . htmlspecialchars($game['category']) . '</span>';
                                echo '</div>';
                                echo '<span class="date">' . date('d/m/Y', strtotime($game['created_at'])) . '</span>';
                                echo '</li>';
                            }
                            
                            if (empty($recentGames)) {
                                echo '<li class="no-games">Nenhum jogo cadastrado ainda.</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>