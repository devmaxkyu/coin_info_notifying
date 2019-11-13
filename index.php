<?php
// import classes
require_once('email_reader.php');
require_once('coin_market_api.php');
require_once('command_parser.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

//===============CONFIG SECTION===================          
$email_config = array(                           
	'server' => 'mail.onefathom.com',            
	'user' => 'zemin@onefathom.com',
	'pass' => 'MNCNm;$W-v{[',
	'port' => 143
);

$api_key = 'ba5abd61-6329-4ecc-9190-3174d463c041';
//=================================================

//reading email      
$email_reader = new Email_Reader($email_config);
$emails = $email_reader->getAll();

//get data from CoinMarketCap API
$coin_market = new CoinMarket($api_key);
$result = $coin_market->getListingLatest();

foreach($emails as $e){

    // parse content
    $parser = new Parsing($e['body'], $result);
    $parser->extract_cmds();

    $response = $parser->generate();

    // sending response

    $header = $e['header'];
    $from = $header->from;
    foreach ($from as $id => $object) {
        $fromname = $object->personal;
        $fromaddress = $object->mailbox . "@" . $object->host;
    }

    // Instantiation and passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = $email_config['server'];                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $email_config['user'];                     // SMTP username
        $mail->Password   = $email_config['pass'];                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom($email_config['user'], 'onefathom');
        $mail->addAddress($fromaddress);     // Add a recipient
        $mail->addReplyTo($email_config['user'], 'Information');

        // Content
        $mail->isHTML(false);                                  // Set email format to HTML
        $mail->Subject = 'Answer for your question';
        $mail->Body    = $response;
        $mail->AltBody = $response;

        $mail->send();
        $email_reader->delete($e['index']);
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

$email_reader->close();

?>