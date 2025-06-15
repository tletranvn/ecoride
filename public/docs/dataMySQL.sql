INSERT INTO user (email, roles, password, pseudo, credits, created_at, api_token)
VALUES (
  'admin@email.com',
  '["ROLE_ADMIN"]',
  '$2y$13$q0hxc0/viojAKn6J1TXMleSy3tSE82xiM7CI/0Lbxto4HQEuFEdEO',
  'Admin',
  20,
  NOW(),
  'token_admin_123'
);
INSERT INTO user (email, roles, password, pseudo, credits, created_at, api_token)
VALUES (
  'employe@email.com',
  '["ROLE_EMPLOYE"]',
  '$2y$13$kHhjW.dRgIYXhZubo/kule63PEJrXem8Mgz2GuIV.cd7pXz056Npe',
  'EmployeTest',
  20,
  NOW(),
  'token_employe_123'
);
INSERT INTO user (email, roles, password, pseudo, credits, created_at)
VALUES (
  'employe@email.com',
  '["ROLE_EMPLOYE"]',
  '$2y$13$kHhjW.dRgIYXhZubo/kule63PEJrXem8Mgz2GuIV.cd7pXz056Npe', 
  'EmployeTest',
  20,
  NOW()
);
INSERT INTO user (email, roles, password, pseudo, credits, created_at)
VALUES (
  'employe@email.com',
  '["ROLE_EMPLOYE"]',
  '$2y$13$kHhjW.dRgIYXhZubo/kule63PEJrXem8Mgz2GuIV.cd7pXz056Npe', 
  'EmployeTest',
  20,
  'employe',
  NOW()
);
UPDATE `user`
SET `credits` =500
WHERE email = 'vh@email.com';
UPDATE `user`
SET `credits` =500
WHERE email = 'tranletranvn@gmail.com';
UPDATE `user`
SET `credits` =500
WHERE email = 'anger@email.com';
UPDATE `user`
SET `credits` =500
WHERE email = 'mama@email.com';
UPDATE `user`
SET `credits` =500
WHERE email = 'jules@example.com';
UPDATE `user`
SET `credits` =500
WHERE email = 'jules@example.com';
UPDATE `trajet`
SET `places_restantes` = 3
WHERE ville_depart = 'Bordeaux';
UPDATE `trajet`
SET `places_restantes` = 3
WHERE ville_depart = 'Marseille';
UPDATE `trajet`
SET `places_restantes` = 3
WHERE ville_depart = 'Paris';
UPDATE `trajet`
SET `places_restantes` = 3
WHERE ville_depart = 'Nantes';
UPDATE `trajet`
SET `places_restantes` = 50
WHERE ville_depart = 'Nantes';
UPDATE `trajet`
SET `places_restantes` = 50
WHERE ville_depart = 'Nantes';
UPDATE `user`
SET `credits` = 5
WHERE email = 'mary@example.com';
UPDATE `user`
SET `credits` = 500
WHERE email = 'mary@example.com';
UPDATE `user`
SET password = '$2y$13$aDpsXVfawFp8N9MZXnbaKuARoMHl2kz6PO3TwX.vBW7TIxuka8tSO'
WHERE email = 'mary@example.com';
UPDATE `user`
SET password = '$2y$13$aDpsXVfawFp8N9MZXnbaKuARoMHl2kz6PO3TwX.vBW7TIxuka8tSO'
WHERE user = 'mary@example.com';