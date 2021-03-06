DROP TABLE `member_paypal`;
CREATE TABLE `member_paypal`
(
    `id`                          INT(11)      NOT NULL AUTO_INCREMENT,
    `member_id`                   INT(11)      NULL     DEFAULT NULL,
    `paypal_address`              VARCHAR(150) NULL     DEFAULT NULL,
    `is_active`                   INT(1)       NOT NULL DEFAULT '1',
    `name`                        VARCHAR(150) NULL     DEFAULT NULL,
    `address`                     VARCHAR(150) NULL     DEFAULT NULL,
    `currency`                    VARCHAR(150) NULL     DEFAULT NULL,
    `country_code`                VARCHAR(150) NULL     DEFAULT NULL,
    `last_payment_status`         VARCHAR(150) NULL     DEFAULT NULL,
    `last_payment_amount`         DOUBLE       NULL     DEFAULT NULL,
    `last_transaction_id`         VARCHAR(50)  NULL     DEFAULT NULL,
    `last_transaction_event_code` VARCHAR(50)  NULL     DEFAULT NULL,
    `created_at`                  TIMESTAMP    NULL     DEFAULT NULL,
    `changed_at`                  TIMESTAMP    NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = InnoDB
    AUTO_INCREMENT = 74
;

ALTER TABLE `member_paypal`
    ADD UNIQUE INDEX `uk_paypal_address` (`paypal_address`);

INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Алексей Варфоломеев', 'Completed', 'RUB', '-21870.12', 'varlesh@gmail.com', '7KK96863AX5754344',
        'Алексей, Варфоломеев', 'T0000', 'RU', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Paul Beyens', 'Completed', 'EUR', '-245.42', 'paulbeyens@gmail.com', '96661567PS4341036', 'Paul, Beyens',
        'T0000', 'BE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('LE MAOUT GUILLAUME', 'Completed', 'EUR', '-94.48', 'exebetche@hotmail.fr', '7F337922WT999935X',
        'LE MAOUT, GUILLAUME', 'T0000', 'FR', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('', 'Pending', 'USD', '-92.25', 'talamelli@libero.it', '3M436460MU296622V', '', 'T0000', '', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Brook Hill', 'Completed', 'USD', '-90.20', 'music_is4thesoul@yahoo.com', '8GG56561YA353312S', 'Brook, Hill',
        'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Keefer Rourke', 'Completed', 'CAD', '-113.03', 'keefer.rourke@gmail.com', '1DC62380HC6218649',
        'Keefer, Rourke', 'T0000', 'CA', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Ahmed Alharbi', 'Completed', 'USD', '-80.26', 'rsamalmot@gmail.com', '9EY96472EY000824V', 'Ahmed, Alharbi',
        'T0000', 'SA', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Matthieu James', 'Completed', 'EUR', '-63.42', 'matthieu.james@gmail.com', '2SY20996GF419200K',
        'Matthieu, James', 'T0000', 'FR', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('CHRISTOPHER HOLLAND', 'Completed', 'CAD', '-90.56', 'zrenfire@gmail.com', '1UL56145SN3373905',
        'CHRISTOPHER, HOLLAND', 'T0000', 'CA', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('eRescue', 'Completed', 'USD', '-66.47', 'jmtodaro@gmail.com', '99J12880X84142501', 'jeremy, todaro', 'T0000',
        'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Michail Vourlakos', 'Completed', 'EUR', '-44.59', 'mvourlakos@gmail.com', '6FA17975VJ3169237',
        'Michail, Vourlakos', 'T0000', 'GR', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('源斯 刘', 'Completed', 'USD', '-50.70', '1151548973@qq.com', '8HC22880PR727672Y', '源斯, 刘', 'T0000', 'C2', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('yanev', 'Completed', 'EUR', '-42.06', 'gericom.hummer@gmail.com', '1P966741WA233501Y', 'Nikola, Yanev',
        'T0000', 'BG', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('RAVEfinity Open Source Design Project.', 'Completed', 'USD', '-45.71', 'ravefinity@ravefinity.com',
        '67A45065856497501', 'Jared, Soto', 'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Doyle Harpole', 'Completed', 'USD', '-37.72', 'kubuntu4172@yahoo.com', '2UK688847E0212107', 'Doyle, Harpole',
        'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Dindin Hernawan', 'Completed', 'USD', '-36.56', 'dindin_hernawan@yahoo.com', '9TB91656EM146414W',
        'Dindin, Hernawan', 'T0000', 'ID', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Michele Guerra', 'Completed', 'EUR', '-30.66', 'mike.ieee1@gmail.com', '85J96429SE204210U', 'Michele, Guerra',
        'T0000', 'IT', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Charlie Henson', 'Completed', 'USD', '-35.18', 'microfreaks@gmail.com', '8LK70351BP7068617', 'Charlie, Henson',
        'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('DRA', 'Completed', 'EUR', '-28.65', 'daniruizdealegria@gmail.com', '26N24064E8419024F',
        'Daniel, Ruiz De Alegría', 'T0000', 'ES', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Holger Jaintsch', 'Pending', 'USD', '-25.04', 'hjaintsch@gmail.com', '2HL03710N2255450C', 'Holger, Jaintsch',
        'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Oliver Scholtz', 'Completed', 'EUR', '-20.22', 'scholli_tz@yahoo.de', '5NX21697YB5246447', 'Oliver, Scholtz',
        'T0000', 'ES', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Rudra Banerjee', 'Completed', 'SEK', '-182.78', 'bnrj.rudra@live.com', '7SJ50339R9869084T', 'Rudra, Banerjee',
        'T0000', 'SE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Sam Hewitt', 'Completed', 'CAD', '-25.90', 'hewittsamuel@gmail.com', '7UR19536RV483230G', 'Sam, Hewitt',
        'T0000', 'CA', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Daniel Faust', 'Pending', 'USD', '-15.78', 'hessijames@ymail.com', '5S9857397H8836636', 'Daniel, Faust',
        'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Stolmet Żywiec Michał Dybczak', 'Completed', 'PLN', '-52.91', 'biuro@stolmet-zywiec.pl', '40V40234RC0517103',
        'Michał, Dybczak', 'T0000', 'PL', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Milen Simic', 'Completed', 'EUR', '-12.02', 'milen.simic@gmail.com', '10809685DG1768543', 'Milen, Simic',
        'T0000', 'RS', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Pierre ORANGE', 'Completed', 'EUR', '-11.65', 'fruit94@msn.com', '5BD115921T533740F', 'Pierre, ORANGE',
        'T0000', 'FR', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Alessandro Roncone', 'Completed', 'EUR', '-11.24', 'alecive87@gmail.com', '2SN30584LL049141H',
        'Alessandro, Roncone', 'T0000', 'IT', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Nattapong Pullkhow', 'Completed', 'USD', '-13.03', 'exenatt@gmail.com', '0CV65795G9635992N',
        'Nattapong, Pullkhow', 'T0000', 'TH', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Максим Орлов', 'Completed', 'USD', '-12.56', 'uubboo@gmail.com', '36D61468MR0997132', 'Максим, Орлов', 'T0000',
        'RU', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('John Goodland', 'Completed', 'JPY', '-1107.00', 'zan642@gmail.com', '1EG313746H690200S', 'John, Goodland',
        'T0000', 'AU', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('alexis duca', 'Completed', 'EUR', '-8.88', 'finonino26@gmail.com', '2HD847814J4891837', 'alexis, duca',
        'T0000', 'FR', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('stephane sansonetti', 'Completed', 'EUR', '-8.61', 'steftrikia@gmail.com', '9TK45357NR821725B',
        'stephane, sansonetti', 'T0000', 'FR', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Kristofer Rickheden Gustavsson', 'Completed', 'SEK', '-83.99', 'hackan@mbox301.tele2.se', '8C058888879369153',
        'Kristofer, Rickheden Gustavsson', 'T0000', 'SE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('chris stanko', 'Completed', 'USD', '-10.00', '3gs32g@gmail.com', '9R125975B8300333G', 'chris, stanko', 'T0000',
        'CA', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Jelena Narezkina', 'Completed', 'EUR', '-7.50', 'jelena.narezkina@gmail.com', '5M148944SD7341924',
        'Jelena, Narezkina', 'T0000', 'LT', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Karoly Barcza', 'Completed', 'GBP', '-6.20', 'donate@blackpanther.hu', '2MM70282L7713503K', 'Karoly, Barcza',
        'T0000', 'GB', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('felipe aristizabal', 'Completed', 'USD', '-8.31', 'wfpaisa@gmail.com', '3S572330VV0356111',
        'felipe, aristizabal', 'T0000', 'CO', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Anton Gonzalez', 'Completed', 'USD', '-123.56', 'anzigo@gmail.com', '3L864047FA6931700', 'Anton, Gonzalez',
        'T0000', 'TT', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Jani Lindholm', 'Completed', 'EUR', '-6.85', 'paypal@em.tunk.org', '3SG398139W115384W', 'Jani, Lindholm',
        'T0000', 'FI', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Holger Jaintsch', 'Completed', 'USD', '-25.04', 'hjaintsch@gmail.com', '2HL03710N2255450C', 'Holger, Jaintsch',
        'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Daniel Faust', 'Completed', 'USD', '-15.78', 'hessijames@ymail.com', '5S9857397H8836636', 'Daniel, Faust',
        'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('József Makay', 'Completed', 'HUF', '-2028.00', 'makay.jozsef@gmail.com', '9WE09084XB549024S', 'József, Makay',
        'T0000', 'HU', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Darin Liebegott', 'Completed', 'USD', '-7.65', 'newhoa@gmail.com', '6WK65505WT041512U', 'Darin, Liebegott',
        'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Antonio Orefice', 'Completed', 'EUR', '-6.45', 'kokoko3k@gmail.com', '2DG114378U707135L', 'Antonio, Orefice',
        'T0000', 'IT', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Konrad Stahlhofer', 'Completed', 'USD', '-7.43', 'bluedxca93@googlemail.com', '1H6504536W0007106',
        'Konrad, Stahlhofer', 'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Everaldo Coelho', 'Completed', 'USD', '-6.99', 'everaldo@everaldo.com', '5LR93545UP308074U',
        'Everaldo, Coelho', 'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('pling.it', 'Completed', 'USD', '-10.10', 'payment@pling.it', '2XS937018E529852G', 'Clemens, Toennies', 'T0000',
        'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('pling.it', 'Completed', 'USD', '-1.00', 'payment@pling.it', '3WC96957X72670451', 'Clemens, Toennies', 'T0000',
        'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('alessandro rei', 'Completed', 'EUR', '-5.81', 'mentalrey@gmail.com', '7L627466BJ432823Y', 'alessandro, rei',
        'T0000', 'IT', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Tobias Kaiser', 'Pending', 'USD', '-6.76', 'tobi012@gmx.de', '7YT33817RJ175681T', 'Tobias, Kaiser', 'T0000',
        'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Christophe Bricheux-Duchossois', 'Completed', 'EUR', '-5.78', 'lefteye92@free.fr', '2HF89418AM1281924',
        'Christophe, Bricheux-Duchossois', 'T0000', 'FR', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Scott Ferguson', 'Completed', 'AUD', '-8.51', 's.cottscottf@gmail.com', '9VE84679U1239605B', 'Scott, Ferguson',
        'T0000', 'AU', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Ernesto Acosta', 'Completed', 'USD', '-6.61', 'elavdeveloper@gmail.com', '31D92286AS017950E',
        'Ernesto, Acosta', 'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Christopher Wood', 'Completed', 'CAD', '-8.31', 'frosspc@gmail.com', '9EC909814Y156190K', 'Christopher, Wood',
        'T0000', 'CA', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Tory Gaurnier', 'Completed', 'USD', '-6.18', 'KoRnKloWn@linuxmail.org', '10012372KS0103230', 'Tory, Gaurnier',
        'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Georg Eckert', 'Pending', 'USD', '-5.94', 'lion.d.gem.heart@gmail.com', '83C754513D680272U', 'Georg, Eckert',
        'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Александр Якубов', 'Completed', 'RUB', '-330.58', 'World-fly@yandex.ru', '7NB86158FR736702N',
        'Александр, Якубов', 'T0000', 'RU', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Miha Čančula', 'Completed', 'EUR', '-4.95', 'miha.cancula@gmail.com', '3JM45056RV7515218', 'Miha, Čančula',
        'T0000', 'SI', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('', 'Pending', 'USD', '-41.22', 'celli.mode@web.de', '23U45596HP4305453', '', 'T0000', '', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Daboribo Linux', 'Completed', 'USD', '-31.25', 'etles.team@gmail.com', '81232928MU912032S',
        'Erwin, Edy Cahyono', 'T0000', 'ID', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Антон Тихомиров', 'Completed', 'RUB', '-872.08', 'Diamond.kde@yahoo.com', '35776372FX094794U',
        'Антон, Тихомиров', 'T0000', 'RU', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Lars Acou', 'Completed', 'EUR', '-4.88', 'lars.acou@telenet.be', '8FP47977YF2401717', 'Lars, Acou', 'T0000',
        'BE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Jean-Alexandre Anglès d\'Auriac', 'Completed', 'EUR', '-4.79', 'jagw40k@free.fr', '9J663139LD2104335',
        'Jean-Alexandre, Anglès d\'Auriac', 'T0000', 'FR', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('James Hardy', 'Completed', 'GBP', '-4.05', 'jameshardy88@gmail.com', '0TJ05858BP1667740', 'James, Hardy',
        'T0000', 'GB', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Stefano Rosselli', 'Completed', 'USD', '-5.29', 'bloomind.studio@gmail.com', '43P939541K426654C',
        'Stefano, Rosselli', 'T0000', 'CH', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Daniel Fore', 'Completed', 'USD', '-5.29', 'Daniel.P.Fore@gmail.com', '8XX1253526902043D', 'Daniel, Fore',
        'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Leszek Lesner', 'Pending', 'USD', '-5.04', 'leszek.lesner@web.de', '23V003349H478045E', 'Leszek, Lesner',
        'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Robert Bosak', 'Completed', 'USD', '-4.97', 'BBOSAK2143@gmail.com', '5G716149E3425380F', 'Robert, Bosak',
        'T0000', 'US', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Piotr Arłukowicz', 'Completed', 'EUR', '-4.03', 'piotao@gmail.com', '5P191039R5161262M', 'Piotr, Arłukowicz',
        'T0000', 'PL', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Stefan Siegl', 'Pending', 'USD', '-4.58', 'sis@soulinsadness.de', '1KM71136EH2876453', 'Stefan, Siegl',
        'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Leszek Lesner', 'Completed', 'USD', '-5.04', 'leszek.lesner@web.de', '23V003349H478045E', 'Leszek, Lesner',
        'T0000', 'DE', NOW());
INSERT INTO `member_paypal` (`name`, `last_payment_status`, `currency`, `last_payment_amount`, `paypal_address`,
                             `last_transaction_id`, `address`, `last_transaction_event_code`, `country_code`,
                             `created_at`)
VALUES ('Tobias Kaiser', 'Completed', 'USD', '-6.76', 'tobi012@gmx.de', '7YT33817RJ175681T', 'Tobias, Kaiser', 'T0000',
        'DE', NOW());


#select * from member_paypal p
UPDATE `member_paypal` `p`
    JOIN `member` `m` ON `m`.`paypal_mail` = `p`.`paypal_address`
SET `p`.`member_id` = `m`.`member_id`
WHERE `p`.`member_id` IS NULL;

