START TRANSACTION;

USE pling;

-- set this to the host which fits to the environment
SET @host = 'https://cdn.pling.cc'; -- integration
#SET @host = 'https://cdn.pling.com'; -- live

-- set this to the member table
SET @member_table = 'member';
#SET @member_table = concat("member_bak_",DATE_FORMAT(NOW(),'%Y%m%d')); -- only for testing purposes

-- name for backup table with current date
SET @backup_table = concat("member_bak_",DATE_FORMAT(NOW(),'%Y%m%d'));

-- backup original member data we going to change
SET @sql = CONCAT("CREATE TABLE ",@backup_table," as select member_id, avatar, avatar_type_id, profile_image_url, profile_image_url_bg, profile_img_src from member");
PREPARE stmt from @sql;
EXECUTE stmt;

-- update profile_image_url
SET @sql = CONCAT("UPDATE ",@member_table," SET profile_image_url = REPLACE(profile_image_url, 'https://cn.opendesktop.org', '",@host,"') WHERE profile_image_url LIKE 'https://cn.opendesktop.org%'");
PREPARE stmt from @sql;
EXECUTE stmt;

SET @sql = CONCAT("UPDATE ",@member_table," SET profile_image_url = REPLACE(profile_image_url, 'https://cn.opendesktop.cc', '",@host,"') WHERE profile_image_url LIKE 'https://cn.opendesktop.cc%'");
PREPARE stmt from @sql;
EXECUTE stmt;

SET @sql = CONCAT("UPDATE ",@member_table," SET profile_image_url = REPLACE(profile_image_url, 'https://cn.pling.com', '",@host,"') WHERE profile_image_url LIKE 'https://cn.pling.com%'");
PREPARE stmt from @sql;
EXECUTE stmt;

SET @sql = CONCAT("UPDATE ",@member_table," SET profile_image_url = REPLACE(profile_image_url, 'http://cn.pling.com', '",@host,"') WHERE profile_image_url LIKE 'http://cn.pling.com%'");
PREPARE stmt from @sql;
EXECUTE stmt;

-- update profile_image_url_bg
SET @sql = CONCAT("UPDATE ",@member_table," SET profile_image_url_bg = REPLACE(profile_image_url_bg, 'https://cn.opendesktop.org', '",@host,"') WHERE profile_image_url_bg LIKE 'https://cn.opendesktop.org%'");
PREPARE stmt from @sql;
EXECUTE stmt;

SET @sql = CONCAT("UPDATE ",@member_table," SET profile_image_url_bg = REPLACE(profile_image_url_bg, 'https://cn.opendesktop.cc', '",@host,"') WHERE profile_image_url_bg LIKE 'https://cn.opendesktop.cc%'");
PREPARE stmt from @sql;
EXECUTE stmt;

SET @sql = CONCAT("UPDATE ",@member_table," SET profile_image_url_bg = REPLACE(profile_image_url_bg, 'https://cn.pling.com', '",@host,"') WHERE profile_image_url_bg LIKE 'https://cn.pling.com%'");
PREPARE stmt from @sql;
EXECUTE stmt;

COMMIT;