INSERT INTO `pling`.`mail_template`
(`mail_template_id`,
`name`,
`subject`,
`text`,
`created_at`,
`changed_at`,
`deleted_at`)
VALUES
(19,
'tpl_user_comment_note_30',
'Moderation Email [%product_title%] opendesktop.org - You Received A New Comment',
'<h2>Hey %username%,</h2>
<p><br />you received a new comment on <b>%product_title%</b></p>
<p><br />Here is what %username_sender% wrote:</p>
<div><br />%comment_text%</div>
<p><br /><br />Please do not reply to the email, but use the comment system for this product instead:<br />
<a href="https://www.opendesktop.org/p/%product_id%/#tab-moderation">%product_title%</a></p>
<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href="mailto:contact@opendesktop.org" target="_blank">contact@opendesktop.org</a><br /><br /></p>',
now(),
now(),
null);



INSERT INTO `pling`.`mail_template`
(`mail_template_id`,
`name`,
`subject`,
`text`,
`created_at`,
`changed_at`,
`deleted_at`)
VALUES
(20,
'tpl_user_comment_reply_note_30',
'Moderation Email [%product_title%] opendesktop.org - You received a new reply to your comment',
'<h2>Hey %username%,</h2>
<p><br />you received a new reply to your comment on <b>%product_title%</b></p>
<p><br />Here is what %username_sender% wrote:</p>
<div><br />%comment_text%</div>
<p><br /><br />Please do not reply to the email, but use the comment system for this product instead:<br />
<a href="https://www.opendesktop.org/p/%product_id%/#tab-moderation">%product_title%</a></p>
<p><br /><br />Kind regards,<br />Your openDesktop Team <br /><a href="mailto:contact@opendesktop.org" target="_blank">contact@opendesktop.org</a><br /><br /></p>',
now(),
now(),
null);
