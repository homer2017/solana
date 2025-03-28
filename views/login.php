<?php
$title = "Đăng nhập Solana";
$basePath = '/baby3';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-12">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center">Đăng nhập bằng Phantom</h1>
                <button id="loginButton" class="btn btn-primary w-100">Đăng nhập</button>
                <p id="status" class="mt-3 text-center"></p>
            </div>
        </div>
    </div>
</div>

<script>
    const status = document.getElementById('status');
    const loginButton = document.getElementById('loginButton');
    const basePath = '<?php echo addslashes($basePath); ?>';

    async function login() {
        if (!window.solana || !window.solana.isPhantom) {
            status.innerText = "Vui lòng cài đặt ví Phantom!";
            return;
        }

        try {
            const provider = window.solana;
            await provider.connect();
            const publicKey = provider.publicKey.toString();

            const message = "Xác nhận đăng nhập lúc " + new Date().toISOString();
            const encodedMessage = new TextEncoder().encode(message);
            const signature = await provider.signMessage(encodedMessage, 'utf8');

            console.log("Sending POST to: " + basePath + '/login');
            const response = await fetch(basePath + '/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    publicKey,
                    signature: Array.from(signature.signature),
                    message
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();
            if (result.success) {
                localStorage.setItem('publicKey', publicKey);
                localStorage.setItem('refLink', result.refLink);
                document.cookie = "publicKey=" + publicKey + "; path=/";
                status.innerText = "Đăng nhập thành công! Chuyển hướng...";
                setTimeout(() => window.location.href = basePath + '/dashboard', 1000);
            } else {
                status.innerText = result.message;
            }
        } catch (error) {
            console.error('Lỗi:', error);
            status.innerText = "Lỗi: " + error.message;
        }
    }

    loginButton.addEventListener('click', login);
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/layouts/main.php';