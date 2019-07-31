<?php
/**
 * Created by PhpStorm.
 * User: hungna
 * Date: 10/19/2017
 * Time: 5:11 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Happy_birthday
 *
 * @property object config
 * @property object input
 * @property object output
 * @property object requests
 * @property object email
 */
class Happy_birthday extends CI_Controller
{
    protected $sendSms;
    protected $sendEmail;
    protected $happy_birthday;

    /**
     * Happy_birthday constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->config->load('config_happy_birth_day');
        $this->sendSms        = config_item('send_sms');
        $this->sendEmail      = config_item('send_email');
        $this->happy_birthday = config_item('happy_birthday');
        $this->load->library(array('requests', 'email'));
        $this->load->helper(array('url', 'string'));
    }

    /**
     * Send SMS Chúc mừng sinh nhật Boss
     *
     * @command 45 5 20 10 * cd /home/hungna/Scripts/Happy-Birthday && php index.php happy_birthday send_sms >/dev/null 2>&1
     */
    public function send_sms()
    {
        if (is_cli()) {
            // Xác định webservices Send SMS
            $url    = $this->sendSms['url'];
            $token  = $this->sendSms['token'];
            $prefix = $this->sendSms['prefix'];
            // Xác định tham số gửi tin
            $phone  = $this->happy_birthday['msisdn'];
            $mo     = 'CMSN BOSS';
            $msg    = $this->happy_birthday['msg'];
            $note   = 'CMSN|BOSS|2017-10-20';
            $params = array(
                'msisdn'    => $phone,
                'mo'        => $mo,
                'mt'        => $msg,
                'note'      => $note,
                'signature' => md5($phone . $prefix . $msg . $prefix . $token)
            );
            // Send SMS
            $send_sms = $this->requests->sendRequest($url, $params);
            log_message('debug', 'Send SMS to URL: ' . $url);
            log_message('debug', 'Send SMS to DATA: ' . json_encode($params));
            log_message('debug', 'Response from Request: ' . $send_sms);
            // Response
            $this->output->set_status_header(200)->set_content_type('application/json', 'utf-8')->set_output($send_sms)->_display();
            exit;
        } else {
            show_404();
        }
    }

    /**
     * Send Email chúc mừng sinh nhật Boss
     *
     * @command 45 5 20 10 * cd /home/hungna/Scripts/Happy-Birthday && php index.php happy_birthday send_email >/dev/null 2>&1
     */
    public function send_email()
    {
        if (is_cli()) {
            // Cấu hình Email
            $this->email->initialize($this->sendEmail);
            // Email content
            $file_content  = APPPATH . 'files/email_content.txt';
            $email_content = file_get_contents($file_content);
            // Gửi email
            $this->email->from($this->sendEmail['smtp_user'], $this->sendEmail['useragent']);
            $this->email->to($this->happy_birthday['email']['to']);
            $this->email->cc($this->happy_birthday['email']['cc']);
            //
            $this->email->subject('[Kỹ thuật PKD] - Chúc mừng sinh nhật Sếp :)');
            $this->email->message($email_content);
            if (!$this->email->send()) {
                log_message('debug', $this->email->print_debugger());
                $this->output->set_status_header(200)->set_content_type('application/json', 'utf-8')->set_output('Gui email that bai!')->_display();
                exit;
            } else {
                $this->output->set_status_header(200)->set_content_type('application/json', 'utf-8')->set_output('Gui email thanh cong!')->_display();
                exit;
            }
        } else {
            show_404();
        }
    }
}
