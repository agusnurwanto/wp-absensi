<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - WP Absensi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }

        .offline-container {
            text-align: center;
            color: white;
            max-width: 600px;
            width: 100%;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .offline-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .apology-message {
            font-size: 1.2em;
            margin-bottom: 30px;
            line-height: 1.6;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .game-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            margin-top: 20px;
        }

        .game-title {
            color: #667eea;
            font-size: 1.5em;
            margin-bottom: 20px;
            font-weight: bold;
        }

        #gameCanvas {
            border: 3px solid #667eea;
            border-radius: 10px;
            display: block;
            margin: 0 auto;
            background: #f7f7f7;
            max-width: 100%;
            height: auto;
        }

        .game-instructions {
            color: #666;
            margin-top: 15px;
            font-size: 0.9em;
        }

        .score {
            color: #667eea;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 10px;
        }

        .retry-info {
            color: rgba(255, 255, 255, 0.8);
            margin-top: 20px;
            font-size: 0.9em;
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 2em;
            }

            .apology-message {
                font-size: 1em;
            }

            .game-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="offline-container">
        <div class="offline-icon">📡❌</div>
        <h1>Oops! Tidak Ada Koneksi</h1>
        <div class="apology-message">
            <p>Mohon maaf, Anda sedang offline dan tidak dapat mengakses aplikasi absensi saat ini.</p>
            <p>Silakan periksa koneksi internet Anda dan coba lagi.</p>
        </div>
        <div class="retry-info">
            💡 Halaman akan otomatis terhubung kembali saat koneksi tersedia
        </div>

        <div class="game-container">
            <div class="game-title">🦖 Sambil Menunggu, Yuk Main Game!</div>
            <canvas id="gameCanvas" width="600" height="200"></canvas>
            <div class="game-instructions">
                Tekan <strong>SPASI</strong> atau <strong>TAP</strong> untuk melompat
            </div>
            <div class="score">Skor: <span id="scoreDisplay">0</span></div>
        </div>
    </div>

    <script>
        // Dino Game Implementation
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const scoreDisplay = document.getElementById('scoreDisplay');

        // Game variables
        let gameRunning = false;
        let gameOver = false;
        let score = 0;
        let frameCount = 0;

        // Dino properties
        const dino = {
            x: 50,
            y: 150,
            width: 40,
            height: 50,
            velocityY: 0,
            gravity: 0.6,
            jumpPower: -12,
            isJumping: false
        };

        // Obstacles
        let obstacles = [];
        const obstacleWidth = 20;
        const obstacleHeight = 50;
        const obstacleSpeed = 5;

        // Ground
        const groundY = 180;

        // Colors
        const primaryColor = '#667eea';
        const dangerColor = '#e74c3c';

        function drawDino() {
            ctx.fillStyle = primaryColor;
            // Body
            ctx.fillRect(dino.x, dino.y, dino.width, dino.height);
            // Eye
            ctx.fillStyle = 'white';
            ctx.fillRect(dino.x + 25, dino.y + 10, 8, 8);
            ctx.fillStyle = 'black';
            ctx.fillRect(dino.x + 28, dino.y + 13, 3, 3);
            // Legs
            ctx.fillStyle = primaryColor;
            if (Math.floor(frameCount / 10) % 2 === 0) {
                ctx.fillRect(dino.x + 5, dino.y + dino.height, 8, 10);
                ctx.fillRect(dino.x + 25, dino.y + dino.height, 8, 10);
            } else {
                ctx.fillRect(dino.x + 10, dino.y + dino.height, 8, 10);
                ctx.fillRect(dino.x + 20, dino.y + dino.height, 8, 10);
            }
        }

        function drawObstacle(obstacle) {
            ctx.fillStyle = dangerColor;
            // Cactus shape
            ctx.fillRect(obstacle.x, obstacle.y, obstacleWidth, obstacleHeight);
            ctx.fillRect(obstacle.x - 5, obstacle.y + 10, 10, 20);
            ctx.fillRect(obstacle.x + obstacleWidth - 5, obstacle.y + 15, 10, 15);
        }

        function drawGround() {
            ctx.strokeStyle = '#ccc';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(0, groundY + dino.height);
            ctx.lineTo(canvas.width, groundY + dino.height);
            ctx.stroke();
        }

        function drawScore() {
            scoreDisplay.textContent = score;
        }

        function jump() {
            if (!dino.isJumping && gameRunning && !gameOver) {
                dino.velocityY = dino.jumpPower;
                dino.isJumping = true;
            }
        }

        function updateDino() {
            dino.velocityY += dino.gravity;
            dino.y += dino.velocityY;

            // Ground collision
            if (dino.y >= groundY) {
                dino.y = groundY;
                dino.velocityY = 0;
                dino.isJumping = false;
            }
        }

        function updateObstacles() {
            // Move obstacles
            obstacles.forEach(obstacle => {
                obstacle.x -= obstacleSpeed;
            });

            // Remove off-screen obstacles
            obstacles = obstacles.filter(obstacle => obstacle.x + obstacleWidth > 0);

            // Add new obstacles
            if (frameCount % 100 === 0) {
                obstacles.push({
                    x: canvas.width,
                    y: groundY,
                    width: obstacleWidth,
                    height: obstacleHeight
                });
            }

            // Check collision
            obstacles.forEach(obstacle => {
                if (dino.x < obstacle.x + obstacle.width &&
                    dino.x + dino.width > obstacle.x &&
                    dino.y < obstacle.y + obstacle.height &&
                    dino.y + dino.height > obstacle.y) {
                    gameOver = true;
                }
            });
        }

        function updateScore() {
            if (gameRunning && !gameOver) {
                if (frameCount % 10 === 0) {
                    score++;
                }
            }
        }

        function drawGameOver() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = 'white';
            ctx.font = 'bold 30px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Game Over!', canvas.width / 2, canvas.height / 2 - 20);

            ctx.font = '20px Arial';
            ctx.fillText('Skor Akhir: ' + score, canvas.width / 2, canvas.height / 2 + 20);

            ctx.font = '16px Arial';
            ctx.fillText('Tekan SPASI atau TAP untuk main lagi', canvas.width / 2, canvas.height / 2 + 50);
        }

        function resetGame() {
            gameOver = false;
            gameRunning = true;
            score = 0;
            frameCount = 0;
            obstacles = [];
            dino.y = groundY;
            dino.velocityY = 0;
            dino.isJumping = false;
        }

        function gameLoop() {
            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw ground
            drawGround();

            if (!gameRunning) {
                // Start screen
                ctx.fillStyle = primaryColor;
                ctx.font = 'bold 24px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('Tekan SPASI atau TAP untuk Mulai', canvas.width / 2, canvas.height / 2);
            } else if (gameOver) {
                drawGameOver();
            } else {
                // Update game state
                frameCount++;
                updateDino();
                updateObstacles();
                updateScore();

                // Draw game objects
                drawDino();
                obstacles.forEach(drawObstacle);
                drawScore();
            }

            requestAnimationFrame(gameLoop);
        }

        // Event listeners
        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space') {
                e.preventDefault();
                if (!gameRunning) {
                    resetGame();
                } else if (gameOver) {
                    resetGame();
                } else {
                    jump();
                }
            }
        });

        canvas.addEventListener('click', () => {
            if (!gameRunning) {
                resetGame();
            } else if (gameOver) {
                resetGame();
            } else {
                jump();
            }
        });

        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            if (!gameRunning) {
                resetGame();
            } else if (gameOver) {
                resetGame();
            } else {
                jump();
            }
        });

        // Auto-reconnect check
        window.addEventListener('online', () => {
            location.reload();
        });

        // Start game loop
        gameLoop();

        // Responsive canvas
        function resizeCanvas() {
            const container = canvas.parentElement;
            const maxWidth = Math.min(600, container.clientWidth - 40);
            canvas.style.width = maxWidth + 'px';
            canvas.style.height = (maxWidth / 3) + 'px';
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();
    </script>
</body>

</html>