INSERT INTO users (name, email, password, role, identity_type, identity_number, is_active, created_at, updated_at)
VALUES ('Administrator', 'admin@sipintar.com', '$2y$12$HgwBqAmQt2D3mxJjc0e28.MIm9TT4Zv7rS8H5Y38Uym.csdO1Yjo6', 'admin', 'petugas', 'ADM001', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = 'Administrator',
    password = '$2y$12$HgwBqAmQt2D3mxJjc0e28.MIm9TT4Zv7rS8H5Y38Uym.csdO1Yjo6',
    role = 'admin',
    identity_type = 'petugas',
    identity_number = 'ADM001',
    is_active = 1,
    updated_at = NOW();
