INSERT INTO `mail_template` (`name`, `subject`, `text`, `created_at`, `changed_at`)
VALUES ('tpl_user_comment_reply_note', 'opendesktop.org - You received a new reply to your comment',
        '<h2>Hey %username%,</h2>\r\n<p><br />you received a new reply to your comment on %product_title%</p>\r\n<p><br />Here is what the user wrote:</p>\r\n<div><br />%comment_text%</div>\r\n<p><br /><br /></p>\r\n<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href="mailto:contact@opendesktop.org" target="_blank">contact@opendesktop.org</a><br /><br /></p>',
        '2016-09-15 12:32:48', '2016-09-15 12:32:48');
