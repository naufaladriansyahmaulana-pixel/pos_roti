<?php
class Pembelian {
    private $conn;
    private $table_name = "pembelian";

    public $id;
    public $no_faktur;
    public $vendor_id;
    public $user_id;
    public $total_harga;
    public $tanggal_pembelian;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET no_faktur=:no_faktur, vendor_id=:vendor_id, user_id=:user_id, 
                      total_harga=:total_harga, tanggal_pembelian=:tanggal_pembelian, status=:status";

        $stmt = $this->conn->prepare($query);

        $this->no_faktur = htmlspecialchars(strip_tags($this->no_faktur));
        $this->vendor_id = htmlspecialchars(strip_tags($this->vendor_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->total_harga = htmlspecialchars(strip_tags($this->total_harga));
        $this->tanggal_pembelian = htmlspecialchars(strip_tags($this->tanggal_pembelian));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(':no_faktur', $this->no_faktur);
        $stmt->bindParam(':vendor_id', $this->vendor_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':total_harga', $this->total_harga);
        $stmt->bindParam(':tanggal_pembelian', $this->tanggal_pembelian);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT p.*, s.nama_vendor, u.nama_lengkap as user_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN vendor s ON p.vendor_id = s.id
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.tanggal_pembelian DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT p.*, s.nama_vendor, u.nama_lengkap as user_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN vendor s ON p.vendor_id = s.id
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->no_faktur = $row['no_faktur'];
            $this->vendor_id = $row['vendor_id'];
            $this->user_id = $row['user_id'];
            $this->total_harga = $row['total_harga'];
            $this->tanggal_pembelian = $row['tanggal_pembelian'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function readRecent($limit = 10) {
        $query = "SELECT p.*, s.nama_vendor, u.nama_lengkap as user_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN vendor s ON p.vendor_id = s.id
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.tanggal_pembelian DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getDetailPembelian($pembelian_id) {
        $query = "SELECT dp.*, o.nama_roti, o.kode_roti
                  FROM detail_pembelian dp
                  LEFT JOIN roti o ON dp.roti_id = o.id
                  WHERE dp.pembelian_id = :pembelian_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pembelian_id', $pembelian_id);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalPembelianBulan() {
        $query = "SELECT COALESCE(SUM(total_harga), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE MONTH(tanggal_pembelian) = MONTH(CURDATE()) 
                  AND YEAR(tanggal_pembelian) = YEAR(CURDATE())
                  AND status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getLaporanPembelian($start_date, $end_date) {
        $query = "SELECT p.*, s.nama_vendor, u.nama_lengkap as user_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN vendor s ON p.vendor_id = s.id
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE DATE(p.tanggal_pembelian) BETWEEN :start_date AND :end_date
                  ORDER BY p.tanggal_pembelian DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();

        return $stmt;
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status=:status WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function generateNoFaktur() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $count = $row['count'] + 1;
        $no_faktur = 'PBL' . date('Ymd') . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        return $no_faktur;
    }
}
?>
