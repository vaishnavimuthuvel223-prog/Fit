<?php
require_once 'db.php';
checkLogin();
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$stmt = $conn->prepare('SELECT email, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$userEmail = $user['email'] ?? 'unknown';
$userCreated = $user['created_at'] ?? date('Y-m-d');
$stmt->close();

// daily default data
$today = date('Y-m-d');
$stmt = $conn->prepare('SELECT * FROM fitness_data WHERE user_id = ? AND date = ?');
$stmt->bind_param('is', $userId, $today);
$stmt->execute();
$result = $stmt->get_result();
$todayData = $result->fetch_assoc();
$stmt->close();

if (!$todayData) {
    $stmt = $conn->prepare('INSERT INTO fitness_data (user_id, date) VALUES (?, ?)');
    $stmt->bind_param('is', $userId, $today);
    $stmt->execute();
    $stmt->close();
    $todayData = ['steps' => 0, 'calories' => 0, 'water' => 0, 'date' => $today];
}

$stmt = $conn->prepare('SELECT * FROM goals WHERE user_id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$goal = $result->fetch_assoc();
$stmt->close();

if (!$goal) {
    $stmt = $conn->prepare('INSERT INTO goals (user_id) VALUES (?)');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
    $goal = ['steps_goal' => 10000, 'calories_goal' => 500, 'water_goal' => 2];
}

$steps = (int)($todayData['steps'] ?? 0);
$calories = (int)($todayData['calories'] ?? 0);
$water = (float)($todayData['water'] ?? 0);
$activeMinutes = min(180, max(0, round($steps / 80)));
$distanceKm = round(($steps / 1312) * 1.609, 2);
$heartRate = rand(62, 145);

// weekly activity data
$stmt = $conn->prepare('SELECT date, steps, calories, water FROM fitness_data WHERE user_id = ? ORDER BY date DESC LIMIT 7');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$weekRowsTemp = [];
while ($row = $result->fetch_assoc()) {
    $weekRowsTemp[] = $row;
}
$stmt->close();
$weekRows = array_reverse($weekRowsTemp);
$weekDays = [];
$stepsSeries = [];
$caloriesSeries = [];
$waterSeries = [];
foreach ($weekRows as $row) {
    $weekDays[] = date('D', strtotime($row['date']));
    $stepsSeries[] = (int)$row['steps'];
    $caloriesSeries[] = (int)$row['calories'];
    $waterSeries[] = (float)$row['water'];
}

// AI motivational suggestions
$aiTips = [
    'Consistency beats intensity. Keep moving!',
    'Drink 1 extra glass of water in the next hour.',
    'Youre strong. Set a 500-step challenge for your next break.',
    'Visualize your goals and take one small action now.',
    'Great progress! Add 10 extra minutes of activity this evening.'
];
$aiMessage = $aiTips[array_rand($aiTips)];

function safe($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FitPulse - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/dashboard.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="loader" id="pageLoader">
    <div class="spinner"></div>
</div>

<div class="dashboard-container">
    <div class="bubble-zone" id="bubbleZone"></div>
    <aside class="sidebar glass-effect">
        <div class="sidebar-header">
            <div class="logo-icon"></div>
            <h2>FitPulse</h2>
        </div>
        <nav class="sidebar-nav" role="tablist" aria-label="Dashboard sections">
            <button type="button" class="nav-item active" data-page="overview" role="tab" aria-selected="true"><span class="icon">📊</span><span>Overview</span></button>
            <button type="button" class="nav-item" data-page="activity" role="tab" aria-selected="false"><span class="icon">⚡</span><span>Activity</span></button>
            <button type="button" class="nav-item" data-page="goals" role="tab" aria-selected="false"><span class="icon">🎯</span><span>Goals</span></button>
            <button type="button" class="nav-item" data-page="profile" role="tab" aria-selected="false"><span class="icon">👤</span><span>Profile</span></button>
        </nav>
        <div class="sidebar-footer">
            <button class="btn-theme" id="themeToggle"> Dark Mode</button>
            <a href="logout.php" class="btn-logout"> Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Welcome, <span class="user-name"><?= safe($userName) ?></span>!</h1>
                <p class="header-date"><?= date('l, j F Y') ?></p>
            </div>
            <div class="header-right">
                <div class="notification-bell"></div>
                <div class="user-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
            </div>
        </header>

        <section id="overview" class="page-section active">
            <div class="stats-grid">
                <div class="stat-card glass-effect">
                    <div class="stat-header"><h3>Steps</h3><span class="stat-icon"></span></div>
                    <div class="stat-value"><?= number_format($steps) ?></div>
                    <div class="special-badge">Trend: <?= $steps > 8000 ? '🔥 Hot' : ($steps > 3000 ? '📈 Rising' : '⚡ Lift off') ?></div>
                </div>
                    <div class="stat-goal">Goal: <?= number_format($goal['steps_goal'] ?? 10000) ?></div>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= min(100, ($steps / ($goal['steps_goal'] ?? 10000)) * 100) ?>%"></div></div>
                </div>
                <div class="stat-card glass-effect">
                    <div class="stat-header"><h3>Calories</h3><span class="stat-icon"></span></div>
                    <div class="stat-value"><?= number_format($calories) ?></div>
                    <div class="stat-goal">Goal: <?= number_format($goal['calories_goal'] ?? 500) ?></div>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= min(100, ($calories / ($goal['calories_goal'] ?? 500)) * 100) ?>%"></div></div>
                </div>
                <div class="stat-card glass-effect">
                    <div class="stat-header"><h3>Water</h3><span class="stat-icon"></span></div>
                    <div class="stat-value"><?= $water ?> L</div>
                    <div class="stat-goal">Goal: <?= $goal['water_goal'] ?? 2 ?> L</div>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= min(100, ($water / ($goal['water_goal'] ?? 2)) * 100) ?>%"></div></div>
                </div>
                <div class="stat-card glass-effect">
                    <div class="stat-header"><h3>Active Time</h3><span class="stat-icon"></span></div>
                    <div class="stat-value"><?= $activeMinutes ?> min</div>
                    <div class="stat-goal">Estimated from steps</div>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= min(100, ($activeMinutes / 180) * 100) ?>%"></div></div>
                </div>
            </div>

            <div class="charts-section">
                <div class="chart-card glass-effect"><h3>Weekly Activity</h3><canvas id="activityChart"></canvas></div>
                <div class="chart-card glass-effect"><h3>Today\'s Metrics</h3>
                    <div class="metrics-grid"><div class="metric"><span class="metric-label">Distance</span><span class="metric-value"><?= $distanceKm ?> km</span></div><div class="metric"><span class="metric-label">Heart Rate</span><span class="metric-value"><?= $heartRate ?> bpm</span></div><div class="metric"><span class="metric-label">Avg Pace</span><span class="metric-value">6.2 km/h</span></div></div>
                </div>
            </div>

            <div class="playground-row">
                <div class="card glass-effect spotlight-feature">
                    <h3>Zen Breathing Coach</h3>
                    <div class="breathing-circle" id="breathingCircle"></div>
                    <p id="breathingText">Ready to relax? Tap start.</p>
                    <button class="btn btn-accent" id="startBreathing">Start 4-7-8</button>
                </div>
                <div class="card glass-effect spotlight-feature">
                    <h3>Energy Flow Matrix</h3>
                    <div class="energy-graph" id="energyGraph"></div>
                    <p>Live mood & stamina index by minute.</p>
                    <button class="btn btn-primary" id="refreshEnergy">Recalculate</button>
                </div>
            </div>

            <div class="section-row">
                <div class="card glass-effect">
                    <h3>Update Today\'s Progress</h3>
                    <form id="updateForm" class="form">
                        <div class="form-group"><label>Steps</label><input type="number" name="steps" value="<?= safe($steps) ?>" min="0" required></div>
                        <div class="form-group"><label>Calories</label><input type="number" name="calories" value="<?= safe($calories) ?>" min="0" required></div>
                        <div class="form-group"><label>Water (L)</label><input type="number" step="0.1" name="water" value="<?= safe($water) ?>" min="0" required></div>
                        <button type="submit" class="btn btn-primary">Save Progress</button>
                    </form>
                </div>

                <div class="card glass-effect"><h3>Workout Timer</h3><div class="timer-display" id="timerDisplay">00:00:00</div><div class="timer-controls"><button class="btn btn-small" id="startBtn">Start</button><button class="btn btn-small" id="stopBtn">Stop</button><button class="btn btn-small" id="resetBtn">Reset</button></div></div>
            </div>

            <div class="section-row">
                <div class="card glass-effect gradient-card"><h3> AI Suggestion</h3><p class="ai-message"><?= safe($aiMessage) ?></p><button class="btn btn-accent" id="newTipBtn">Get New Tip</button></div>
                <div class="card glass-effect"><h3>Set Goals</h3>
                    <form id="goalForm" class="form">
                        <div class="form-group"><label>Steps Goal</label><input type="number" name="steps_goal" value="<?= safe($goal['steps_goal'] ?? 10000) ?>" required></div>
                        <div class="form-group"><label>Calories Goal</label><input type="number" name="calories_goal" value="<?= safe($goal['calories_goal'] ?? 500) ?>" required></div>
                        <div class="form-group"><label>Water Goal (L)</label><input type="number" step="0.1" name="water_goal" value="<?= safe($goal['water_goal'] ?? 2) ?>" required></div>
                        <button type="submit" class="btn btn-primary">Update Goals</button>
                    </form>
                </div>
            </div>
        </section>

        <section id="activity" class="page-section">
            <div class="card glass-effect">
                <h3>Recent Activity</h3>
                <table class="activity-table"><thead><tr><th>Date</th><th>Steps</th><th>Calories</th><th>Water</th></tr></thead><tbody><?php foreach (array_reverse($weekRows) as $row): ?><tr><td><?= date('M d', strtotime($row['date'])) ?></td><td><?= number_format((int)$row['steps']) ?></td><td><?= number_format((int)$row['calories']) ?></td><td><?= $row['water'] ?> L</td></tr><?php endforeach; ?></tbody></table>
            </div>
        </section>

        <section id="goals" class="page-section">
            <div class="goals-grid"><div class="card glass-effect"><h3>Steps Goal</h3><div class="goal-display"><div class="goal-value"><?= number_format($goal['steps_goal'] ?? 10000) ?></div><div class="goal-status">per day</div></div></div><div class="card glass-effect"><h3>Calories Goal</h3><div class="goal-display"><div class="goal-value"><?= number_format($goal['calories_goal'] ?? 500) ?></div><div class="goal-status">per day</div></div></div><div class="card glass-effect"><h3>Water Goal</h3><div class="goal-display"><div class="goal-value"><?= number_format($goal['water_goal'] ?? 2, 1) ?></div><div class="goal-status">liters per day</div></div></div></div>
        </section>

        <section id="profile" class="page-section">
            <div class="card glass-effect profile-card"><h3>My Profile</h3><div class="profile-info"><div class="profile-item"><span class="profile-label">Name</span><span class="profile-value"><?= safe($userName) ?></span></div><div class="profile-item"><span class="profile-label">Email</span><span class="profile-value"><?= safe($userEmail) ?></span></div><div class="profile-item"><span class="profile-label">Member Since</span><span class="profile-value"><?= date('F j, Y', strtotime($userCreated)) ?></span></div></div></div>
        </section>
    </main>
</div>

<noscript>
    <style>
        .page-section { display: none !important; }
        #overview { display: block !important; }
    </style>
</noscript>

<div class="toast" id="toast"></div>

<script>
    const weekDays = <?= json_encode($weekDays) ?>;
    const stepsSeries = <?= json_encode($stepsSeries) ?>;
    const caloriesSeries = <?= json_encode($caloriesSeries) ?>;
    const waterSeries = <?= json_encode($waterSeries) ?>;

    function hideLoader() {
        const loader = document.getElementById('pageLoader');
        if (!loader) {
            console.warn('Loader element not found.');
            return;
        }
        loader.classList.add('hidden');
        console.info('Loader hidden.');
    }

    window.addEventListener('load', hideLoader);
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(hideLoader, 2000);
    });
    setTimeout(hideLoader, 5000);

    try {
        const ctx = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: weekDays,
                datasets: [
                    { label: 'Steps', data: stepsSeries, borderColor: '#FFD700', backgroundColor: 'rgba(255,215,0,0.1)', tension: 0.4, fill: true },
                    { label: 'Calories', data: caloriesSeries, borderColor: '#FF6B6B', backgroundColor: 'rgba(255,107,107,0.1)', tension: 0.4, fill: true },
                    { label: 'Water', data: waterSeries, borderColor: '#00D9FF', backgroundColor: 'rgba(0,217,255,0.1)', tension: 0.4, fill: true }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: { y: { beginAtZero: true } }
            }
        });
    } catch (err) {
        console.error('Chart init failed:', err);
        hideLoader();
    }

    function setActiveSection(pageId) {
        document.querySelectorAll('.nav-item').forEach(i => {
            const isActive = i.dataset.page === pageId;
            i.classList.toggle('active', isActive);
            i.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        document.querySelectorAll('.page-section').forEach(sec => {
            const visible = sec.id === pageId;
            sec.classList.toggle('active', visible);
            sec.style.display = visible ? 'block' : 'none';
        });
    }

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
            setActiveSection(item.dataset.page);
        });
    });

    // Ensure only one page is visible at startup
    setActiveSection('overview');
    setTimeout(() => setActiveSection('overview'), 100);
    setTimeout(() => setActiveSection('overview'), 300);
    setTimeout(() => setActiveSection('overview'), 700);


    document.getElementById('updateForm').addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'update_progress');
        fetch('api.php', { method: 'POST', body: new URLSearchParams(formData) })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else alert(data.message || 'Update failed'); })
            .catch(err => { console.error('Update progress failed:', err); });
    });

    document.getElementById('goalForm').addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'update_goals');
        fetch('api.php', { method: 'POST', body: new URLSearchParams(formData) })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else alert(data.message || 'Goal update failed'); })
            .catch(err => { console.error('Update goals failed:', err); });
    });

    let timerInterval = null, seconds = 0;
    const timerDisplay = document.getElementById('timerDisplay');

    function updateTimer() {
        const h = String(Math.floor(seconds/3600)).padStart(2, '0');
        const m = String(Math.floor((seconds%3600)/60)).padStart(2, '0');
        const s = String(seconds%60).padStart(2, '0');
        timerDisplay.textContent = `${h}:${m}:${s}`;
    }

    document.getElementById('startBtn').addEventListener('click', () => { if (!timerInterval) timerInterval = setInterval(() => { seconds++; updateTimer(); },1000); });
    document.getElementById('stopBtn').addEventListener('click', () => { clearInterval(timerInterval); timerInterval=null; });
    document.getElementById('resetBtn').addEventListener('click', () => { clearInterval(timerInterval); timerInterval=null; seconds=0; updateTimer(); });

    document.getElementById('newTipBtn').addEventListener('click', () => {
        const tips = ['Consistency beats intensity. Keep moving!','Drink more water!','Set a new challenge today!','You\'re doing great!'];
        document.querySelector('.ai-message').textContent = tips[Math.floor(Math.random() * tips.length)];
    });

    function showToast(text) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = text;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

    document.getElementById('themeToggle').addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        document.getElementById('themeToggle').textContent = isDark ? ' Light Mode' : ' Dark Mode';
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        document.getElementById('themeToggle').textContent = ' Light Mode';
    }

    // Zen breathing feature
    const breathingCircle = document.getElementById('breathingCircle');
    const breathingText = document.getElementById('breathingText');
    const startBreathing = document.getElementById('startBreathing');
    let breathingInterval = null;

    function runBreathingCycle() {
        if (!breathingCircle || !breathingText) return;
        const phases = [
            { text: 'Inhale (4s)', scale: 1.13, duration: 4000 },
            { text: 'Hold (7s)', scale: 1.02, duration: 7000 },
            { text: 'Exhale (8s)', scale: 0.85, duration: 8000 }
        ];
        let index = 0;

        function nextPhase() {
            const phase = phases[index];
            breathingText.textContent = phase.text;
            breathingCircle.style.transform = `scale(${phase.scale})`;
            breathingCircle.style.transition = `transform ${phase.duration}ms ease-in-out`;
            index = (index + 1) % phases.length;
            breathingInterval = setTimeout(nextPhase, phase.duration);
        }

        nextPhase();
    }

    startBreathing.addEventListener('click', () => {
        if (breathingInterval) {
            clearTimeout(breathingInterval);
            breathingInterval = null;
            breathingText.textContent = 'Ready to relax? Tap start.';
            breathingCircle.style.transform = 'scale(1)';
            return;
        }
        runBreathingCycle();
    });

    // Energy bar randomizer
    const energyGraph = document.getElementById('energyGraph');
    const refreshEnergy = document.getElementById('refreshEnergy');

    function updateEnergy() {
        if (!energyGraph) return;
        energyGraph.innerHTML = '';
        for (let i = 0; i < 8; i++) {
            const bar = document.createElement('div');
            bar.className = 'energy-bar';
            const hb = (Math.random() * 40) + 30;
            bar.style.height = `${hb}%`;
            bar.style.left = `${12 + i * 10}%`;
            bar.style.width = '8%';
            energyGraph.appendChild(bar);
        }
    }

    function generateBubbles(count) {
        const bubbleZone = document.getElementById('bubbleZone');
        if (!bubbleZone) return;
        bubbleZone.innerHTML = '';
        for (let i = 0; i < count; i++) {
            const bubble = document.createElement('div');
            bubble.className = 'bubble';
            const size = Math.random() * 24 + 22;
            bubble.style.width = `${size}px`;
            bubble.style.height = `${size}px`;
            bubble.style.left = `${Math.random() * 100}%`;
            bubble.style.bottom = `${Math.random() * 20}%`;
            bubble.style.animationDuration = `${12 + Math.random() * 10}s`;
            bubble.style.animationDelay = `${Math.random() * 6}s`;
            bubbleZone.appendChild(bubble);
        }
    }

    if (refreshEnergy) {
        refreshEnergy.addEventListener('click', () => {
            updateEnergy();
            showToast('Energy flow refreshed!');
        });
    }

    updateEnergy();
    generateBubbles(22);
    console.log('Dashboard loaded');
</script>
</body>
</html>
