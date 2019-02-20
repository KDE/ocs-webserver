ALTER TABLE `config_store`
	ADD COLUMN `stay_in_context` INT(1) NULL DEFAULT 0 AFTER `render_view_postfix`;

UPDATE config_store s
SET s.stay_in_context = 0
WHERE s.stay_in_context IS NULL;

