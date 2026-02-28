<?php
class Pengaturan {
    private $conn;
    private $table_name = "pengaturan";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function get($key) {
        $query = "SELECT nilai FROM " . $this->table_name . " WHERE kunci = :kunci LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kunci', $key);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['nilai'];
        }
        return null;
    }

    public function set($key, $value) {
        $query = "INSERT INTO " . $this->table_name . " (kunci, nilai) 
                  VALUES (:kunci, :nilai)
                  ON DUPLICATE KEY UPDATE nilai = :nilai";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kunci', $key);
        $stmt->bindParam(':nilai', $value);
        
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT kunci, nilai FROM " . $this->table_name . " ORDER BY kunci";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['kunci']] = $row['nilai'];
        }
        return $settings;
    }

    public function updateAll($settings) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>

