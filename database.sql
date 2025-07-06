CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE praktikum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    semester VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE modul (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE modul ADD COLUMN id_praktikum INT AFTER id, ADD FOREIGN KEY (id_praktikum) REFERENCES praktikum(id) ON DELETE CASCADE;

CREATE TABLE laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mahasiswa INT,
    id_modul INT,
    file VARCHAR(255),
    status ENUM('dikirim','diterima','ditolak') DEFAULT 'dikirim',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE laporan MODIFY status ENUM('dikirim','dinilai') DEFAULT 'dikirim';
ALTER TABLE laporan ADD COLUMN feedback TEXT AFTER status;
ALTER TABLE laporan
    ADD FOREIGN KEY (id_mahasiswa) REFERENCES users(id) ON DELETE CASCADE,
    ADD FOREIGN KEY (id_modul) REFERENCES modul(id) ON DELETE CASCADE;
ALTER TABLE laporan ADD COLUMN nilai VARCHAR(10) AFTER feedback;

CREATE TABLE praktikum_peserta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_praktikum INT NOT NULL,
    id_mahasiswa INT NOT NULL,
    daftar_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (id_praktikum, id_mahasiswa),
    FOREIGN KEY (id_praktikum) REFERENCES praktikum(id) ON DELETE CASCADE,
    FOREIGN KEY (id_mahasiswa) REFERENCES users(id) ON DELETE CASCADE
);