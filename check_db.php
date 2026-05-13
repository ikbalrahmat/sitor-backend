<?php
// Script diagnostik sederhana
echo "=== CEK DATABASE ===\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "[OK] Koneksi MySQL berhasil!\n";
    
    // Cek apakah database sitor_db ada
    $result = $pdo->query("SHOW DATABASES LIKE 'sitor_db'");
    if ($result->rowCount() > 0) {
        echo "[OK] Database sitor_db ditemukan!\n";
        
        // Cek tabel
        $pdo->exec("USE sitor_db");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "[OK] Jumlah tabel: " . count($tables) . "\n";
        
        if (in_array('users', $tables)) {
            $users = $pdo->query("SELECT id, nama, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
            echo "[OK] Jumlah user: " . count($users) . "\n";
            foreach ($users as $u) {
                echo "     - {$u['nama']} ({$u['email']}) [{$u['role']}]\n";
            }
        } else {
            echo "[ERROR] Tabel users TIDAK ditemukan!\n";
            echo "Tabel yang ada: " . implode(', ', $tables) . "\n";
        }
    } else {
        echo "[ERROR] Database sitor_db TIDAK ditemukan!\n";
        echo "Membuat database sitor_db...\n";
        $pdo->exec("CREATE DATABASE sitor_db");
        echo "[OK] Database sitor_db berhasil dibuat!\n";
    }
} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
