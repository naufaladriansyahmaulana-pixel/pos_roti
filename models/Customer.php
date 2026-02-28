<?php
class Customer {
    private $conn;
    private $table_name = "customer";

    public $id;
    public $nama_customer;
    public $alamat;
    public $telepon;
    public $email;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET nama_customer=:nama_customer, alamat=:alamat, telepon=:telepon, email=:email";

        $stmt = $this->conn->prepare($query);

        $this->nama_customer = htmlspecialchars(strip_tags($this->nama_customer));
        $this->alamat = htmlspecialchars(strip_tags($this->alamat));
        $this->telepon = htmlspecialchars(strip_tags($this->telepon));
        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(':nama_customer', $this->nama_customer);
        $stmt->bindParam(':alamat', $this->alamat);
        $stmt->bindParam(':telepon', $this->telepon);
        $stmt->bindParam(':email', $this->email);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nama_customer";

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
            $this->nama_customer = $row['nama_customer'];
            $this->alamat = $row['alamat'];
            $this->telepon = $row['telepon'];
            $this->email = $row['email'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nama_customer=:nama_customer, alamat=:alamat, telepon=:telepon, email=:email
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->nama_customer = htmlspecialchars(strip_tags($this->nama_customer));
        $this->alamat = htmlspecialchars(strip_tags($this->alamat));
        $this->telepon = htmlspecialchars(strip_tags($this->telepon));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':nama_customer', $this->nama_customer);
        $stmt->bindParam(':alamat', $this->alamat);
        $stmt->bindParam(':telepon', $this->telepon);
        $stmt->bindParam(':email', $this->email);
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
