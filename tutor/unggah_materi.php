<?php
class Materi {
    private $conn;
    private $table_name = "materi";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Upload materi (disesuaikan tanpa mata_pelajaran, dan field bernama 'file' & 'tutor_id')
    public function uploadMateri($judul, $deskripsi, $file, $tutor_id, $tipe_file = 'pdf') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (judul, deskripsi, file, tutor_id, tipe_file, created_at) 
                  VALUES (:judul, :deskripsi, :file, :tutor_id, :tipe_file, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':judul', $judul);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':file', $file);
        $stmt->bindParam(':tutor_id', $tutor_id);
        $stmt->bindParam(':tipe_file', $tipe_file);

        return $stmt->execute();
    }

    // Ambil daftar materi berdasarkan tutor_id
    public function getMateriByTutor($tutor_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE tutor_id = :tutor_id 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tutor_id', $tutor_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hapus materi berdasarkan ID dan tutor_id
    public function deleteMateri($id, $tutor_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id = :id AND tutor_id = :tutor_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':tutor_id', $tutor_id);

        return $stmt->execute();
    }
}
?>
