<?php
namespace app\components;

use Yii;
use yii\base\Component;
use MeiliSearch\Client;
use GuzzleHttp\Client as GuzzleHttpClient;
use yii\base\InvalidConfigException;

class Meili extends Component
{
    public $host = 'http://127.0.0.1:7700';
    public $masterKey = 'masterKey';
    public $timeout = 2;
    private $_client;

    public function init()
    {
        parent::init();

        if (empty($this->host)) {
            throw new InvalidConfigException('Meilisearch host must be set.');
        }
    }

    // public function connect($content = null)
    // {
    //     //$client = new Client('http://127.0.0.1:7700', 'uQGbv6nq-EZh2ke3HRMS6Wpu-dSLlo618BV6-Qal0jM');
    //     $client = new Client('http://127.0.0.1:7700', 'masterKey', new GuzzleHttpClient(['timeout' => 2]));
    //     return $client;
    // }

    public function connect($content = null)
    {
        $server = strtolower($_SERVER['HTTP_HOST'] ?? php_uname('n'));

        try {
            if ($server === 'partner.dingo.kg') {
                $client = new Client(
                    'https://ms.dingo.kg',
                    'XYgN9akmwIizAMP6Z1zRAE8qryqcjZY1XQBT-qLUG3g',
                    new GuzzleHttpClient([
                        'timeout' => 5,
                        'verify' => false
                    ])
                );
            } elseif ($server === 'dev.dingo.kg') {
                $client = new Client(
                    'https://meili.selva.kg',
                    'NGY2YzkxZDhiZjA5MGIzODg1Y2MwNDU5',
                    new GuzzleHttpClient([
                        'timeout' => 5,
                        'verify' => false
                    ])
                );
            } else {
                $client = new Client(
                    'http://host.docker.internal:7700',
                    'masterKey',
                    new GuzzleHttpClient([
                        'timeout' => 5,
                        'verify' => false
                    ])
                );
            }

            return $client;

        } catch (\Exception $e) {
            Yii::error("Meilisearch connection error: " . $e->getMessage());
            throw $e;
        }
    }


    public function getClient()
    {
        if ($this->_client === null) {
            $httpClient = new GuzzleHttpClient([
                'timeout' => $this->timeout,
                'connect_timeout' => 2,
            ]);

            $this->_client = new Client(
                $this->host,
                $this->masterKey,
                $httpClient
            );
        }

        return $this->_client;
    }
}
