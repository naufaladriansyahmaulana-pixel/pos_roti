<?php
class KategoriRoti {
    private $conn;
    private $table_name = "kategori_roti";

    public $id;
    public $nama_kategori;
    public $deskripsi;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET nama_kategori=:nama_kategori, deskripsi=:deskripsi";

        $stmt = $this->conn->prepare($query);

        $this->nama_kategori = htmlspecialchars(strip_tags($this->nama_kategori));
        $this->deskripsi = htmlspecialchars(strip_tags($this->deskripsi));

        $stmt->bindParam(':nama_kategori', $this->nama_kategori);
        $stmt->bindParam(':deskripsi', $this->deskripsi);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nama_kategori";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->nama_kategori = $row['nama_kategori'];
            $this->deskripsi = $row['deskripsi'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nama_kategori=:nama_kategori, deskripsi=:deskripsi
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->nama_kategori = htmlspecialchars(strip_tags($this->nama_kategori));
        $this->deskripsi = htmlspecialchars(strip_tags($this->deskripsi));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':nama_kategori', $this->nama_kategori);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
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
}
?>
