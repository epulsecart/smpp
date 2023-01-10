<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use smpp\{Address, Client as SmppClient, Smpp, transport\Socket};


class SMPPController extends Controller{
    public function send(Request $request){
        $validator = Validator::make($request->all(), [
            'to' => 'required|min:9',
            'message' =>'required'
        ], [
            'phone.required' => 'The phone field is required and must be minimum of 9 digits.',
            'message.required' => 'the message field is required'

        ]);
        (new SmsBuilder('82.114.166.86', 5016, 'United', 'u@3n2', 10000))
            ->setRecipient("771221030", \smpp\SMPP::TON_INTERNATIONAL) //msisdn of recipient
            ->sendMessage("test message");
            return $request;
    }
}




class SmsBuilder
{
    /** @var string 11 chars limit */
    public const DEFAULT_SENDER = 'example';

    protected Socket $transport;

    protected SmppClient $smppClient;

    protected bool $debug = false;

    protected Address $from;

    protected Address $to;

    protected string $login;

    protected string $password;

    /**
     * SmsBuilder constructor.
     *
     * @param string $address SMSC IP
     * @param int $port SMSC port
     * @param string $login
     * @param string $password
     * @param int $timeout timeout of reading PDU in milliseconds
     * @param bool $debug - debug flag when true output additional info
     */
    public function __construct(
        string $address,
        int $port,
        string $login,
        string $password,
        int $timeout = 10000,
        bool $debug = false,
    ) {
       // place to add your logger to Socket constructor
        $this->transport = new Socket([$address], $port, false, );
        // Activate binary hex-output of server interaction
        $this->transport->debug = $debug;
        $this->transport->setRecvTimeout($timeout);
        $this->smppClient = new SmppClient($this->transport);

        $this->login = $login;
        $this->password = $password;

        $this->from = new Address(self::DEFAULT_SENDER, SMPP::TON_ALPHANUMERIC);
    }

    /**
     * @param string $sender
     * @param int $ton
     *
     * @return $this
     * @throws Exception
     */
    public function setSender(string $sender, int $ton): SmsBuilder
    {
        return $this->setAddress($sender, 'from', $ton);
    }

    /**
     * @param string $address
     * @param string $type
     * @param int $ton
     * @param int $npi
     *
     * @return $this
     * @throws Exception
     */
    protected function setAddress(
        string $address,
        string $type,
        int $ton = SMPP::TON_UNKNOWN,
        int $npi = SMPP::NPI_UNKNOWN
    ): SmsBuilder {
        // some example of data preparation
        if ($ton === SMPP::TON_INTERNATIONAL) {
            $npi = SMPP::NPI_E164;
        }
        $this->$type = new Address($address, $ton, $npi);

        return $this;
    }

    /**
     * @param string $address
     * @param int $ton
     *
     * @return $this
     * @throws Exception
     */
    public function setRecipient(string $address, int $ton): SmsBuilder
    {
        return $this->setAddress($address, 'to', $ton);
    }

    /**
     * @param string $message
     *
     * @throws Exception
     */
    public function sendMessage(string $message): void
    {
        $this->transport->open();
        $this->smppClient->bindTransceiver($this->login, $this->password);
        // strongly recommend use SMPP::DATA_CODING_UCS2 as default encoding in project to prevent problems with non latin symbols
        $this->smppClient->sendSMS($this->from, $this->to, $message, null, SMPP::DATA_CODING_UCS2);
        $this->smppClient->close();
    }
}

