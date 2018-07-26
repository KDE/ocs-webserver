ALTER TABLE `member_dl_plings`
  ADD COLUMN `num_plings` BIGINT NULL DEFAULT NULL
  AFTER `probably_payout_amount`;
