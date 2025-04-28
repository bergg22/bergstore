<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';
$conn = getConnection();

$categoryFilter = $_GET['category'] ?? 'all';
$sortBy = $_GET['sort'] ?? 'title';
$sortOrder = $_GET['order'] ?? 'asc';

try {
    $stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Erro ao buscar categorias: " . $e->getMessage();
    $categories = [];
}

$query = "
    SELECT g.id, g.title, g.release_year, g.developer, g.publisher, g.price, c.name as category_name
    FROM games g
    JOIN categories c ON g.category_id = c.id
";

if ($categoryFilter !== 'all') {
    $query .= " WHERE g.category_id = :category_id";
}

$query .= " ORDER BY ";
switch ($sortBy) {
    case 'title':
        $query .= "g.title";
        break;
    case 'category':
        $query .= "c.name";
        break;
    case 'year':
        $query .= "g.release_year";
        break;
    case 'price':
        $query .= "g.price";
        break;
    default:
        $query .= "g.title";
}

$query .= " " . ($sortOrder === 'desc' ? 'DESC' : 'ASC');

try {
    $stmt = $conn->prepare($query);
    
    if ($categoryFilter !== 'all') {
        $stmt->bindParam(':category_id', $categoryFilter);
    }
    
    $stmt->execute();
    $games = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Erro ao buscar relatório: " . $e->getMessage();
    $games = [];
}

try {

    $stmt = $conn->prepare("
        SELECT c.name, COUNT(g.id) as count, IFNULL(SUM(g.price), 0) as total_price
        FROM categories c
        LEFT JOIN games g ON c.id = g.category_id
        GROUP BY c.id, c.name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $gamesByCategory = $stmt->fetchAll();
    

    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_games, IFNULL(SUM(price), 0) as total_value
        FROM games
    ");
    $stmt->execute();
    $totals = $stmt->fetch();
    

    $stmt = $conn->prepare("
        SELECT release_year, COUNT(*) as count
        FROM games
        WHERE release_year IS NOT NULL
        GROUP BY release_year
        ORDER BY release_year DESC
    ");
    $stmt->execute();
    $gamesByYear = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMessage = "Erro ao buscar dados de resumo: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - BergStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-container">
        <?php include '../components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include '../components/header.php'; ?>
            
            <div class="dashboard">
                <div class="page-header">
                    <h1 class="animate-on-load">Relatórios</h1>
                    <a href="export.php" class="btn btn-primary animate-on-load">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Exportar Lista
                    </a>
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
                
                <!-- Summary Cards -->
                <div class="stats-container">
                    <div class="stat-card animate-on-load">
                        <h3>Total de Jogos</h3>
                        <p class="stat-number"><?php echo isset($totals) ? $totals['total_games'] : 0; ?></p>
                    </div>
                    
                    <div class="stat-card animate-on-load">
                        <h3>Valor Total</h3>
                        <p class="stat-number">R$ <?php echo isset($totals) ? number_format($totals['total_value'], 2, ',', '.') : '0,00'; ?></p>
                    </div>
                    
                    <div class="stat-card animate-on-load">
                        <h3>Jogos por Categoria</h3>
                        <p class="stat-number"><?php echo count($categories); ?> categorias</p>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="charts-container">
                    <div class="chart-card animate-on-load">
                        <h3>Jogos por Categoria</h3>
                        <div class="chart-container">
                            <canvas id="gamesByCategoryChart"></canvas>
                        </div>
                        <?php
                        $categoryLabels = [];
                        $categoryData = [];
                        
                        foreach ($gamesByCategory as $category) {
                            $categoryLabels[] = $category['name'];
                            $categoryData[] = $category['count'];
                        }
                        ?>
                        <script>
                            const ctxCategory = document.getElementById('gamesByCategoryChart').getContext('2d');
                            Chart.defaults.color = '#E8EAED';
                            new Chart(ctxCategory, {
                                type: 'bar',
                                data: {
                                    labels: <?php echo json_encode($categoryLabels); ?>,
                                    datasets: [{
                                        label: 'Número de Jogos',
                                        data: <?php echo json_encode($categoryData); ?>,
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
                    
                    <?php if (!empty($gamesByYear)): ?>
                    <div class="chart-card animate-on-load">
                        <h3>Jogos por Ano de Lançamento</h3>
                        <div class="chart-container">
                            <canvas id="gamesByYearChart"></canvas>
                        </div>
                        <?php
                        $yearLabels = [];
                        $yearData = [];
                        
                        foreach ($gamesByYear as $yearGroup) {
                            $yearLabels[] = $yearGroup['release_year'];
                            $yearData[] = $yearGroup['count'];
                        }
                        ?>
                        <script>
                            const ctxYear = document.getElementById('gamesByYearChart').getContext('2d');
                            new Chart(ctxYear, {
                                type: 'line',
                                data: {
                                    labels: <?php echo json_encode($yearLabels); ?>,
                                    datasets: [{
                                        label: 'Número de Jogos',
                                        data: <?php echo json_encode($yearData); ?>,
                                        backgroundColor: 'rgba(26, 115, 232, 0.2)',
                                        borderColor: '#1A73E8',
                                        borderWidth: 2,
                                        tension: 0.3,
                                        pointBackgroundColor: '#1A73E8',
                                        pointBorderColor: '#fff',
                                        pointRadius: 5
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
                    <?php endif; ?>
                </div>
                
                <!-- Filtered Report -->
                <div class="report-filters animate-on-load">
                    <h3>Filtrar Relatório</h3>
                    <form action="index.php" method="GET" class="filter-form">
                        <div class="filter-group">
                            <label for="category">Categoria:</label>
                            <select name="category" id="category">
                                <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>Todas as Categorias</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="sort">Ordenar por:</label>
                            <select name="sort" id="sort">
                                <option value="title" <?php echo $sortBy === 'title' ? 'selected' : ''; ?>>Título</option>
                                <option value="category" <?php echo $sortBy === 'category' ? 'selected' : ''; ?>>Categoria</option>
                                <option value="year" <?php echo $sortBy === 'year' ? 'selected' : ''; ?>>Ano</option>
                                <option value="price" <?php echo $sortBy === 'price' ? 'selected' : ''; ?>>Preço</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="order">Ordem:</label>
                            <select name="order" id="order">
                                <option value="asc" <?php echo $sortOrder === 'asc' ? 'selected' : ''; ?>>Crescente</option>
                                <option value="desc" <?php echo $sortOrder === 'desc' ? 'selected' : ''; ?>>Decrescente</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                    </form>
                </div>
                
                <div class="data-table-container animate-on-load">
                    <div class="table-header">
                        <h2 class="table-title">Relatório de Jogos por Categoria</h2>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($games as $game): ?>
                                        <tr>
                                            <td><?php echo $game['id']; ?></td>
                                            <td><?php echo htmlspecialchars($game['title']); ?></td>
                                            <td><?php echo htmlspecialchars($game['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($game['developer'] ?? '-'); ?></td>
                                            <td><?php echo $game['release_year'] ?? '-'; ?></td>
                                            <td>R$ <?php echo number_format($game['price'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Nenhum jogo encontrado com os filtros selecionados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>