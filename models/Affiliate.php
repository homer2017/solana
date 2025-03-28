<?php
namespace Models;

use Config\Database;

class Affiliate {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function saveOrUpdate($publicKey, $refCode, $refLink, $parentPublicKey = null) {
        $stmt = $this->db->prepare(
            "INSERT INTO affiliates (public_key, ref_code, ref_link, parent_public_key) 
             VALUES (?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE ref_code = ?, ref_link = ?"
        );
        if (!$stmt) {
            throw new \Exception("Lỗi prepare: " . $this->db->error);
        }

        $stmt->bind_param('ssssss', $publicKey, $refCode, $refLink, $parentPublicKey, $refCode, $refLink);
        if (!$stmt->execute()) {
            throw new \Exception("Lỗi execute: " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

    public function getAffiliatesByParent($parentPublicKey) {
        $stmt = $this->db->prepare(
            "SELECT public_key, ref_code, ref_link, created_at 
             FROM affiliates 
             WHERE parent_public_key = ?"
        );
        if (!$stmt) {
            throw new \Exception("Lỗi prepare: " . $this->db->error);
        }

        $stmt->bind_param('s', $parentPublicKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $affiliates = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $affiliates;
    }
}