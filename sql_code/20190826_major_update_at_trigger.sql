CREATE DEFINER=`root`@`%` TRIGGER `trg_project_major_updated_at`
    BEFORE UPDATE
    ON `project`
    FOR EACH ROW
BEGIN
    IF `NEW`.`changed_at` <> `OLD`.`changed_at` AND DATEDIFF(`NEW`.`changed_at`, `OLD`.`major_updated_at`) >= 6 THEN
        SET `NEW`.`major_updated_at` = `NEW`.`changed_at`;
    END IF;
END