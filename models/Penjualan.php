<?php
class Penjualan {
    private $conn;
    private $table_name = "penjualan";

    public $id;
    public $no_transaksi;
    public $user_id;
    public $customer_id;
    public $diskon;
    public $ppn;
    public $total_harga;
    public $total_bayar;
    public $kembalian;
    public $note;
    public $tanggal_penjualan;
    public $metode_transaksi;
    public $nomor_rekening;


    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET no_transaksi=:no_transaksi, user_id=:user_id, customer_id=:customer_id, 
                      diskon=:diskon, ppn=:ppn, total_harga=:total_harga, 
                      total_bayar=:total_bayar, kembalian=:kembalian, note=:note, metode_transaksi=:metode_transaksi, nomor_rekening=:nomor_rekening";

        $stmt = $this->conn->prepare($query);

        $this->no_transaksi = htmlspecialchars(strip_tags($this->no_transaksi));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->customer_id = !empty($this->customer_id) ? htmlspecialchars(strip_tags($this->customer_id)) : null;
        $this->diskon = htmlspecialchars(strip_tags($this->diskon));
        $this->ppn = htmlspecialchars(strip_tags($this->ppn));
        $this->total_harga = htmlspecialchars(strip_tags($this->total_harga));
        $this->total_bayar = htmlspecialchars(strip_tags($this->total_bayar));
        $this->kembalian = htmlspecialchars(strip_tags($this->kembalian));

        $this->metode_transaksi = htmlspecialchars(strip_tags($this->metode_transaksi));
        $this->nomor_rekening = !empty($this->nomor_rekening) ? htmlspecialchars(strip_tags($this->nomor_rekening)) : null;

        $this->note = !empty($this->note) ? htmlspecialchars(strip_tags($this->note)) : null;

        $stmt->bindParam(':no_transaksi', $this->no_transaksi);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':customer_id', $this->customer_id);
        $stmt->bindParam(':diskon', $this->diskon);
        $stmt->bindParam(':ppn', $this->ppn);
        $stmt->bindParam(':total_harga', $this->total_harga);
        $stmt->bindParam(':total_bayar', $this->total_bayar);
        $stmt->bindParam(':kembalian', $this->kembalian);

        $stmt->bindParam(':metode_transaksi', $this->metode_transaksi);
        $stmt->bindParam(':nomor_rekening', $this->nomor_rekening);

        $stmt->bindParam(':note', $this->note);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.tanggal_penjualan DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->no_transaksi = $row['no_transaksi'];
            $this->user_id = $row['user_id'];
            $this->customer_id = $row['customer_id'];
            $this->diskon = $row['diskon'];
            $this->ppn = $row['ppn'];
            $this->total_harga = $row['total_harga'];
            $this->total_bayar = $row['total_bayar'];
            $this->kembalian = $row['kembalian'];
            $this->note = $row['note'];
            $this->tanggal_penjualan = $row['tanggal_penjualan'];
            $this->metode_transaksi = $row['metode_transaksi'];
            $this->nomor_rekening = isset($row['nomor_rekening']) ? $row['nomor_rekening'] : null;

            return true;
        }
        return false;
    }

    public function readRecent($limit = 10) {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.tanggal_penjualan DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getDetailPenjualan($penjualan_id) {
        $query = "SELECT dp.*, o.nama_roti, o.kode_roti
                  FROM detail_penjualan dp
                  LEFT JOIN roti o ON dp.roti_id = o.id
                  WHERE dp.penjualan_id = :penjualan_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':penjualan_id', $penjualan_id);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalPenjualanHari() {
        $query = "SELECT COALESCE(SUM(total_harga), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE DATE(tanggal_penjualan) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTotalPenjualanBulan() {
        $query = "SELECT COALESCE(SUM(total_harga), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE MONTH(tanggal_penjualan) = MONTH(CURDATE()) 
                  AND YEAR(tanggal_penjualan) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTotalTransaksiHari() {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE DATE(tanggal_penjualan) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getLaporanPenjualan($start_date, $end_date) {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE DATE(p.tanggal_penjualan) BETWEEN :start_date AND :end_date
                  ORDER BY p.tanggal_penjualan DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();

        return $stmt;
    }

    public function generateNoTransaksi() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE DATE(tanggal_penjualan) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $count = $row['count'] + 1;
        $no_transaksi = 'TRX' . date('Ymd') . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        return $no_transaksi;
    }
}
?>
