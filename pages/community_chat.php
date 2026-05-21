<?php
/**
 * NOXARA - Community Chat
 */
require_once __DIR__ . '/../config/bootstrap.php';
$user = require_login();
$page_title = 'Community Chat';
$show_back = true;

// Handle send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_ajax()) {
    verify_csrf();
    $message = trim($_POST['message'] ?? '');
    if ($message && mb_strlen($message) <= 500) {
        $stmt = db()->prepare("INSERT INTO chat_messages (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('is', $user['id'], $message);
        $stmt->execute();
        $stmt->close();
        json_response(['success' => true]);
    }
    json_response(['success' => false, 'message' => 'Pesan tidak valid.'], 400);
}

// Get recent messages
$messages = db()->query("
    SELECT cm.*, u.username, u.avatar 
    FROM chat_messages cm 
    JOIN users u ON u.id = cm.user_id 
    ORDER BY cm.created_at DESC 
    LIMIT 50
")->fetch_all(MYSQLI_ASSOC);
$messages = array_reverse($messages);

include INCLUDES_PATH . '/header.php';
?>

<section class="page-section chat-section">
    <div class="section-header">
        <h2 class="section-title">Community Chat</h2>
        <span class="badge-count"><?= count($messages) ?> pesan</span>
    </div>

    <div class="chat-container glassmorphism" id="chatContainer">
        <div class="chat-messages" id="chatMessages">
            <?php foreach ($messages as $msg): ?>
            <div class="chat-msg <?= $msg['user_id'] == $user['id'] ? 'own' : '' ?>">
                <div class="chat-msg-header">
                    <span class="chat-username"><?= sanitize($msg['username']) ?></span>
                    <span class="chat-time"><?= time_ago($msg['created_at']) ?></span>
                </div>
                <div class="chat-msg-body"><?= sanitize($msg['message']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- Send Form -->
    <div class="chat-input-bar glassmorphism">
        <input type="text" id="chatInput" class="form-input" placeholder="Tulis pesan..." maxlength="500">
        <button onclick="sendChat()" class="btn btn-primary btn-icon" id="btnSend">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </div>
</section>

<?php
$extra_js = '
<script>
const chatBox = document.getElementById("chatMessages");
chatBox.scrollTop = chatBox.scrollHeight;

document.getElementById("chatInput").addEventListener("keypress", function(e) {
    if (e.key === "Enter") sendChat();
});

async function sendChat() {
    const input = document.getElementById("chatInput");
    const msg = input.value.trim();
    if (!msg) return;
    input.value = "";
    const formData = new FormData();
    formData.append("message", msg);
    formData.append("csrf_token", "' . ($_SESSION['csrf_token'] ?? '') . '");
    try {
        const res = await fetch(location.href, {
            method: "POST", credentials: "same-origin",
            headers: {"X-Requested-With": "XMLHttpRequest"},
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            const el = document.createElement("div");
            el.className = "chat-msg own";
            el.innerHTML = "<div class=\"chat-msg-header\"><span class=\"chat-username\">' . sanitize($user['username']) . '</span><span class=\"chat-time\">Baru saja</span></div><div class=\"chat-msg-body\">" + msg.replace(/</g,"&lt;") + "</div>";
            chatBox.appendChild(el);
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    } catch(e) { showToast("Gagal mengirim.", "error"); }
}
</script>';
include INCLUDES_PATH . '/footer.php';
?>
