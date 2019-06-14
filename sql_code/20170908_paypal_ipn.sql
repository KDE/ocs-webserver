CREATE TABLE `paypal_ipn`
(
    `id`             INT(11)     NOT NULL AUTO_INCREMENT,
    `created_at`     TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `txn_type`       VARCHAR(50) NULL     DEFAULT NULL,
    `ipn_track_id`   VARCHAR(50) NULL     DEFAULT NULL,
    `txn_id`         VARCHAR(50) NULL     DEFAULT NULL,
    `payer_email`    VARCHAR(50) NULL     DEFAULT NULL,
    `payer_id`       VARCHAR(50) NULL     DEFAULT NULL,
    `auth_amount`    VARCHAR(50) NULL     DEFAULT NULL,
    `mc_currency`    VARCHAR(50) NULL     DEFAULT NULL,
    `mc_fee`         VARCHAR(50) NULL     DEFAULT NULL,
    `mc_gross`       VARCHAR(50) NULL     DEFAULT NULL,
    `memo`           VARCHAR(50) NULL     DEFAULT NULL,
    `payer_status`   VARCHAR(50) NULL     DEFAULT NULL,
    `payment_date`   VARCHAR(50) NULL     DEFAULT NULL,
    `payment_fee`    VARCHAR(50) NULL     DEFAULT NULL,
    `payment_status` VARCHAR(50) NULL     DEFAULT NULL,
    `payment_type`   VARCHAR(50) NULL     DEFAULT NULL,
    `pending_reason` VARCHAR(50) NULL     DEFAULT NULL,
    `reason_code`    VARCHAR(50) NULL     DEFAULT NULL,
    `custom`         VARCHAR(50) NULL     DEFAULT NULL,
    `raw`            TEXT        NULL,
    PRIMARY KEY (`id`)
)
    COMMENT ='Save all PayPal IPNs here'
    ENGINE = InnoDB
;

