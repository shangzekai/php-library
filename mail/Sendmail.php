<?php
class Mail_Sendmail {

	public $title;
	public $body;
	public $email_to;

	public function __construct($title,$body,$email_to) {

		$this->title = $title;
		$this->body = $body;
		$this->email_to = $email_to;

		return $this->send();
	}

	public function send() {
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->SMTPDebug  = true;                     // enables SMTP debug information (for testing)
		$mail->SMTPAuth   = true;                  // enable SMTP authentication

		$body = $this->body ? $this->body : NULL;
		$title = $this->title ? $this->title : NULL;
		$to = $this->email_to ? $this->email_to : NULL;

		$mail->SMTPSecure = 'tls';                   // sets the prefix to the servier
		$mail->Host       = 'smtp.dynect.net';                  // sets GMAIL as the SMTP server
		$mail->Port       = 25;                    // set the SMTP port for the GMAIL server
		$mail->Username   = 'admin@yeahmails.com';                 // GMAIL username
		$mail->Password   = 'lnJXhYlbIbWMw1NZ';                // GMAIL password
		//$mail->SetFrom('service@yeahmobi.com');
		$mail->SetFrom('yeahmobi_team_noreply@yeahmails.com', 'YeahMobi Team');               //PHP Mailer要求发送的From 与 mail account为同一主机名

		$mail->ClearReplyTos();
		$mail->ClearAddresses();

		$mail->AddReplyTo('yeahmobi_team_noreply@yeahmails.com', 'YeahMobi Team');
		$mail->Subject    = "=?utf-8?B?" . base64_encode($title) . "?=";
		//$mail->AltBody    = $this->_contentReplace($v['title'], $v);                        // optional, comment out and test
		$mail->MsgHTML($body);
		$mail->AddAddress($to);

		$mail->Send();
	}
}
