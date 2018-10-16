CREATE TABLE git_group
(
  git_group_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  group_name VARCHAR(255),
  group_id INT,
  group_full_path VARCHAR(255)
);
CREATE INDEX git_group__group_id ON git_group (group_id);

CREATE TABLE git_group_user
(
  git_group_user_id INT PRIMARY KEY AUTO_INCREMENT,
  group_id INT,
  user_id INT,
  user_name VARCHAR(255),
  user_email VARCHAR(255),
  group_access VARCHAR(40)
);
CREATE INDEX git_group_user__email ON git_group_user (user_email);
CREATE INDEX git_group_user__group_id ON git_group_user (group_id);