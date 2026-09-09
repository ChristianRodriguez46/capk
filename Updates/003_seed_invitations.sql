-- Sample invitations seed data 

INSERT INTO invitations (token, email, agency_id, role, invited_by, expires_at, created_at) VALUES
('invite-admin2-a1b2c3d4e5f6',  'admin2@capk211.org',                    NULL, 'admin',  1, DATE_ADD(NOW(), INTERVAL 14 DAY), NOW()),
('invite-capk-f1a2b3c4d5e6',    'manager@capk.org',                       8, 'agency', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), NOW()),
('invite-dhs-a7b8c9d0e1f2',     'manager@kerncounty.com',                 9, 'agency', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), NOW()),
('invite-sa-b3c4d5e6f7a8',      'manager@salvationarmyusa.org',          10, 'agency', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), NOW()),
('invite-bethany-c9d0e1f2a3b4', 'manager@bakersfieldhomelesscenter.org', 11, 'agency', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), NOW()),
('invite-csv-d5e6f7a8b9c0',     'manager@clinicasierravista.org',        12, 'agency', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), NOW());
