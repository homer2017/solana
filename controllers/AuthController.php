<?php
namespace Controllers;

use Models\Affiliate;

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);

            $publicKey = filter_var($data['publicKey'] ?? '', FILTER_SANITIZE_STRING);
            $signature = $data['signature'] ?? [];
            $message = filter_var($data['message'] ?? '', FILTER_SANITIZE_STRING);
            $parentRefCode = filter_var($_GET['ref'] ?? '', FILTER_SANITIZE_STRING);

            if (!$publicKey || !$signature || !$message) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                exit;
            }

            try {
                $refCode = bin2hex(random_bytes(4));
                $refLink = "http://localhost:8000/public/login?ref=$refCode";

                $affiliate = new Affiliate();
                $parentPublicKey = null;
                if ($parentRefCode) {
                    $stmt = $affiliate->db->prepare("SELECT public_key FROM affiliates WHERE ref_code = ?");
                    $stmt->bind_param('s', $parentRefCode);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $parentPublicKey = $row['public_key'];
                    }
                    $stmt->close();
                }

                $affiliate->saveOrUpdate($publicKey, $refCode, $refLink, $parentPublicKey);

                echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công!', 'refLink' => $refLink]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
            }
            exit;
        }

        // Hiển thị view cho GET
        require_once __DIR__ . '/../views/login.php';
    }
}