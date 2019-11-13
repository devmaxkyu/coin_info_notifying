<?php
/**
 * Class for reading email
 * @author Zemin W.
 * Created at 2019-11-12
 */

class Email_reader {

    // imap server connection
    public $conn;

    // inbox storage and inbox message count
    private $inbox;
    private $msg_cnt;

    // email login credentials
    private $server = null;
    private $user   = null;
    private $pass   = null;
    private $port   = 143; // adjust according to server settings

    /**
     * connect to the server and get the inbox emails
     * @param
     * $config_params = array(
     *         'server' => <EMAIL SERVER URL or IP>,
     *         'user' => <YOUR EMAIL ADDRESS>,
     *         'pass' => <YOUR EMAIL PASSWORD>,
     *         'port' => <EMAIL SERVER PORT>  // this is optional parameter
     * )
     *  */ 
    function __construct($config_params) {

        // config params validation
        if(!array_key_exists('server', $config_params))
        {
            throw new Exception('missing config param: server');
        }

        if(!array_key_exists('user', $config_params))
        {
            throw new Exception('missing config param: user');
        }

        if(!array_key_exists('pass', $config_params))
        {
            throw new Exception('missing config param: pass');
        }

        foreach ($config_params as $key => $value) {
            $this->{$key} = $value;
        }

        $this->connect();
        $this->inbox();
    }

    // close the server connection
    function close() {
        $this->inbox = array();
        $this->msg_cnt = 0;

        imap_close($this->conn);
    }

    // delete email
    function delete($msg_number)
    {
        # code...
        return imap_delete($this->conn, $msg_number);
    }

    // open the server connection
    // the imap_open function parameters will need to be changed for the particular server
    // these are laid out to connect to a Dreamhost IMAP server
    function connect() {
        $this->conn = imap_open('{'.$this->server.'/notls}', $this->user, $this->pass);
    }

    // move the message to a new folder
    function move($msg_index, $folder='INBOX.Processed') {
        // move on server
        imap_mail_move($this->conn, $msg_index, $folder);
        imap_expunge($this->conn);

        // re-read the inbox
        $this->inbox();
    }

    // get a specific message (1 = first email, 2 = second email, etc.)
    function get($msg_index=NULL) {
        if (count($this->inbox) <= 0) {
            return array();
        }
        elseif ( ! is_null($msg_index) && isset($this->inbox[$msg_index])) {
            return $this->inbox[$msg_index];
        }

        return $this->inbox[0];
    }

    // get all message
    function getAll(){
        return $this->inbox;
    }

    // read the inbox
    function inbox() {
        $this->msg_cnt = imap_num_msg($this->conn);

        $in = array();
        for($i = 1; $i <= $this->msg_cnt; $i++) {

            $headers = imap_headerinfo($this->conn, $i);
            $body = imap_body($this->conn, $i);
            $structure = imap_fetchstructure($this->conn, $i);

            if($headers->Deleted != 'D'){
                $in[] = array(
                    'index'     => $i,
                    'header'    => $headers,
                    'body'      => $body,
                    'structure' => $structure
                );
            }
        }

        $this->inbox = $in;
    }

}