<?php
$title = "Dashboard";
$basePath = '/baby3';
ob_start();
?>
<div class="row">
    <div class="col-12 col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Thông tin User</h5>
                <p><strong>Public Key:</strong> <span id="publicKey"></span></p>
                <p><strong>Ref Link:</strong> <span id="refLink"></span></p>
                <button id="transferButton" class="btn btn-success w-100">Chuyển 1 SOL</button>
                <p id="status" class="mt-3"></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Danh sách Affiliates</h5>
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th>Public Key</th>
                            <th>Ref Code</th>
                            <th>Ngày tham gia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($affiliates as $affiliate): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($affiliate['public_key']); ?></td>
                                <td><?php echo htmlspecialchars($affiliate['ref_code']); ?></td>
                                <td><?php echo htmlspecialchars($affiliate['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($affiliates)): ?>
                            <tr><td colspan="3" class="text-center">Chưa có affiliates</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof window.Buffer === 'undefined' || typeof window.Buffer.from !== 'function') {
        window.Buffer = {
            from: function (data, encoding) {
                console.log("Buffer.from called with data:", data, "encoding:", encoding);
                if (typeof data === 'string' && encoding === 'utf8') {
                    return new TextEncoder().encode(data);
                }
                if (typeof data === 'string' && encoding === 'hex') {
                    const cleanedHex = data.replace(/^0x/, '');
                    const arr = new Uint8Array(cleanedHex.length / 2);
                    for (let i = 0; i < arr.length; i++) {
                        arr[i] = parseInt(cleanedHex.slice(i * 2, i * 2 + 2), 16);
                    }
                    return arr;
                }
                if (typeof data === 'number' || typeof data === 'bigint') {
                    const bigIntData = BigInt(data);
                    const arr = new Uint8Array(8);
                    for (let i = 0; i < 8; i++) {
                        arr[i] = Number((bigIntData >> BigInt(8 * i)) & BigInt(0xff));
                    }
                    return arr;
                }
                return new Uint8Array(data);
            }
        };
    }

    const publicKeyElement = document.getElementById('publicKey');
    const refLinkElement = document.getElementById('refLink');
    const status = document.getElementById('status');
    const transferButton = document.getElementById('transferButton');
    const basePath = '<?php echo addslashes($basePath); ?>';

    publicKeyElement.innerText = localStorage.getItem('publicKey') || 'Không có';
    refLinkElement.innerText = localStorage.getItem('refLink') || 'Không có';

    async function transferSOL() {
        if (!window.solana || !window.solana.isPhantom) {
            status.innerText = "Vui lòng cài đặt ví Phantom!";
            return;
        }

        if (!window.solanaWeb3) {
            status.innerText = "Thư viện Solana Web3 không tải được!";
            console.error("Solana Web3 không khả dụng. Kiểm tra script CDN hoặc file local.");
            return;
        }

        const { Transaction, SystemProgram, LAMPORTS_PER_SOL, PublicKey, Connection } = window.solanaWeb3;

        try {
            const provider = window.solana;
            await provider.connect();
            const publicKey = provider.publicKey.toString();

            const toPublicKey = '7qneCS3jwyzrFGEdqToqnPLKYxPRoELAcFVdtvpr8Ywz';
            const transaction = new Transaction().add(
                SystemProgram.transfer({
                    fromPubkey: provider.publicKey,
                    toPubkey: new PublicKey(toPublicKey),
                    lamports: LAMPORTS_PER_SOL * 1
                })
            );

            const connection = new Connection("https://api.devnet.solana.com", "confirmed");
            const { blockhash } = await connection.getLatestBlockhash();
            transaction.recentBlockhash = blockhash;
            transaction.feePayer = provider.publicKey;

            const signedTransaction = await provider.signTransaction(transaction);
            const serializedTx = signedTransaction.serialize().toString('base64');

            console.log("Sending POST to: " + basePath + '/dashboard');
            const response = await fetch(basePath + '/dashboard', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ publicKey, signedTransaction: serializedTx })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();
            if (result.success) {
                status.innerText = `Giao dịch thành công! Signature: ${result.signature}`;
            } else {
                status.innerText = result.message;
            }
        } catch (error) {
            console.error('Lỗi:', error);
            status.innerText = "Lỗi: " + error.message;
        }
    }

    transferButton.addEventListener('click', transferSOL);
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/layouts/main.php';