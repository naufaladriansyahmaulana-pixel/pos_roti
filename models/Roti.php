<?php
class Roti {
    private $conn;
    private $table_name = "roti";

    public $id;
    public $kode_roti;
    public $nama_roti;
    public $kategori_id;
    public $satuan;
    public $harga_beli;
    public $harga_jual;
    public $diskon;
    public $stok;
    public $stok_minimum;
    public $tanggal_expired;
    public $deskripsi;
    public $gambar_roti;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET kode_roti=:kode_roti, nama_roti=:nama_roti, kategori_id=:kategori_id, 
                      satuan=:satuan, harga_beli=:harga_beli, harga_jual=:harga_jual, 
                      diskon=:diskon, stok=:stok, stok_minimum=:stok_minimum, tanggal_expired=:tanggal_expired, 
                      deskripsi=:deskripsi, gambar_roti=:gambar_roti";

        $stmt = $this->conn->prepare($query);

        $this->kode_roti = htmlspecialchars(strip_tags($this->kode_roti));
        $this->nama_roti = htmlspecialchars(strip_tags($this->nama_roti));
        $this->kategori_id = htmlspecialchars(strip_tags($this->kategori_id));
        $this->satuan = htmlspecialchars(strip_tags($this->satuan));
        $this->harga_beli = htmlspecialchars(strip_tags($this->harga_beli));
        $this->harga_jual = htmlspecialchars(strip_tags($this->harga_jual));
        $this->diskon = htmlspecialchars(strip_tags($this->diskon));
        $this->stok = htmlspecialchars(strip_tags($this->stok));
        $this->stok_minimum = htmlspecialchars(strip_tags($this->stok_minimum));
        $this->tanggal_expired = htmlspecialchars(strip_tags($this->tanggal_expired));
        $this->deskripsi = htmlspecialchars(strip_tags($this->deskripsi));
        $this->gambar_roti = htmlspecialchars(strip_tags($this->gambar_roti));

        $stmt->bindParam(':kode_roti', $this->kode_roti);
        $stmt->bindParam(':nama_roti', $this->nama_roti);
        $stmt->bindParam(':kategori_id', $this->kategori_id);
        $stmt->bindParam(':satuan', $this->satuan);
        $stmt->bindParam(':harga_beli', $this->harga_beli);
        $stmt->bindParam(':harga_jual', $this->harga_jual);
        $stmt->bindParam(':diskon', $this->diskon);
        $stmt->bindParam(':stok', $this->stok);
        $stmt->bindParam(':stok_minimum', $this->stok_minimum);
        $stmt->bindParam(':tanggal_expired', $this->tanggal_expired);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        $stmt->bindParam(':gambar_roti', $this->gambar_roti);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT o.*, k.nama_kategori 
                  FROM " . $this->table_name . " o
                  LEFT JOIN kategori_roti k ON o.kategori_id = k.id
                  ORDER BY o.nama_roti";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
 
    public function readOne() {
        $query = "SELECT o.*, k.nama_kategori 
                  FROM " . $this->table_name . " o
                  LEFT JOIN kategori_roti k ON o.kategori_id = k.id
                  WHERE o.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->kode_roti = $row['kode_roti'];
            $this->nama_roti = $row['nama_roti'];
            $this->kategori_id = $row['kategori_id'];
            $this->satuan = $row['satuan'];
            $this->harga_beli = $row['harga_beli'];
            $this->harga_jual = $row['harga_jual'];
            $this->diskon = $row['diskon'];
            $this->stok = $row['stok'];
            $this->stok_minimum = $row['stok_minimum'];
            $this->tanggal_expired = $row['tanggal_expired'];
            $this->deskripsi = $row['deskripsi'];
            $this->gambar_roti = $row['gambar_roti'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET kode_roti=:kode_roti, nama_roti=:nama_roti, kategori_id=:kategori_id, 
                      satuan=:satuan, harga_beli=:harga_beli, harga_jual=:harga_jual, 
                      diskon=:diskon, stok=:stok, stok_minimum=:stok_minimum, tanggal_expired=:tanggal_expired, 
                      deskripsi=:deskripsi, gambar_roti=:gambar_roti
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->kode_roti = htmlspecialchars(strip_tags($this->kode_roti));
        $this->nama_roti = htmlspecialchars(strip_tags($this->nama_roti));
        $this->kategori_id = htmlspecialchars(strip_tags($this->kategori_id));
        $this->satuan = htmlspecialchars(strip_tags($this->satuan));
        $this->harga_beli = htmlspecialchars(strip_tags($this->harga_beli));
        $this->harga_jual = htmlspecialchars(strip_tags($this->harga_jual));
        $this->diskon = htmlspecialchars(strip_tags($this->diskon));
        $this->stok = htmlspecialchars(strip_tags($this->stok));
        $this->stok_minimum = htmlspecialchars(strip_tags($this->stok_minimum));
        $this->tanggal_expired = htmlspecialchars(strip_tags($this->tanggal_expired));
        $this->deskripsi = htmlspecialchars(strip_tags($this->deskripsi));
        $this->gambar_roti = htmlspecialchars(strip_tags($this->gambar_roti));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':kode_roti', $this->kode_roti);
        $stmt->bindParam(':nama_roti', $this->nama_roti);
        $stmt->bindParam(':kategori_id', $this->kategori_id);
        $stmt->bindParam(':satuan', $this->satuan);
        $stmt->bindParam(':harga_beli', $this->harga_beli);
        $stmt->bindParam(':harga_jual', $this->harga_jual);
        $stmt->bindParam(':diskon', $this->diskon);
        $stmt->bindParam(':stok', $this->stok);
        $stmt->bindParam(':stok_minimum', $this->stok_minimum);
        $stmt->bindParam(':tanggal_expired', $this->tanggal_expired);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        $stmt->bindParam(':gambar_roti', $this->gambar_roti);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateStok($roti_id, $jumlah) {
        $query = "UPDATE " . $this->table_name . " SET stok = stok + :jumlah WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jumlah', $jumlah);
        $stmt->bindParam(':id', $roti_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getTotalroti() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getrotiExpired() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE tanggal_expired <= CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getStokMinimum() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE stok <= stok_minimum";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function search($keyword) {
        $query = "SELECT o.*, k.nama_kategori 
                  FROM " . $this->table_name . " o
                  LEFT JOIN kategori_roti k ON o.kategori_id = k.id
                  WHERE o.nama_roti LIKE :keyword OR o.kode_roti LIKE :keyword
                  ORDER BY o.nama_roti";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();

        return $stmt;
    }
}
?>
