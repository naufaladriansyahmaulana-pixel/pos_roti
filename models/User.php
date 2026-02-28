<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $nama_lengkap;
    public $email;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password, nama_lengkap, email, role 
                  FROM " . $this->table_name . " 
                  WHERE username = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->nama_lengkap = $row['nama_lengkap'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET username=:username, password=:password, nama_lengkap=:nama_lengkap, 
                      email=:email, role=:role";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->nama_lengkap = htmlspecialchars(strip_tags($this->nama_lengkap));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':nama_lengkap', $this->nama_lengkap);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT id, username, nama_lengkap, email, role, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY nama_lengkap";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne() {
        $query = "SELECT id, username, nama_lengkap, email, role 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->username = $row['username'];
            $this->nama_lengkap = $row['nama_lengkap'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET username=:username, nama_lengkap=:nama_lengkap, 
                      email=:email, role=:role
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nama_lengkap = htmlspecialchars(strip_tags($this->nama_lengkap));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':nama_lengkap', $this->nama_lengkap);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
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

    public function changePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " SET password=:password WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
