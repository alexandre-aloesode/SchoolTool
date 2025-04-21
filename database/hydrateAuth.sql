USE `schooltool_auth`;

INSERT INTO `role` (name) VALUES
  ('student'),
  ('hors-parcours'),
  ('alumni'),
  ('admin'),
  ('crm'),
  ('corrector'),
  ('teacher'),
  ('peda'),
  ('re');

INSERT INTO `user` (email, role_id) VALUES
  ('herve.beziat@laplateforme.io', 1),
  ('julie.lambert@laplateforme.io', 1);

INSERT INTO `scope` (user_id, scope_value) VALUES
  (1, 1),
  (2, 2);
