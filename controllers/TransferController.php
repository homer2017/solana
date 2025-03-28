<?php
namespace Controllers;

use Models\Affiliate;

class TransferController {
    public function dashboard() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);

            $publicKey = filter_var($data['publicKey'] ?? '', FILTER_SANITIZE_STRING);
            $signedTransaction = filter_var($data['signedTransaction'] ?? '', FILTER_SANITIZE_STRING);

            if (!$publicKey || !$signedTransaction) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                exit;
            }

            try {
                // Xử lý giao dịch trên blockchain (giả lập)
                // Thực tế, bạn cần gửi signedTransaction tới Solana network
                $signature = "fake-signature-" . bin2hex(random_bytes(16)); // Giả lập

                echo json_encode(['success' => true, 'message' => 'Giao dịch thành công', 'signature' => $signature]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
            }
            exit;
        }

        // Hiển thị view cho GET
        $affiliate = new Affiliate();
        $publicKey = $_COOKIE['publicKey'] ?? '';
        $affiliates = $affiliate->getAffiliatesByParent($publicKey);
        require_once __DIR__ . '/../views/dashboard.php';
    }
}