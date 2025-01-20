<?php

namespace app\commands;

use Yii;
use DateTime;
use yii\console\Controller;
use yii\httpclient\Client;
use app\models\Product;

class ManageProductController extends Controller
{
     private function login()
     {
         $url = 'https://node.dilbar.style/login';
         //$product_url = 'https://node.dilbar.style/login';
         $data = [
             'username' => 'admin',
             'password' => 'd26b650cd3a0dc61a5a56',
         ];
 
         $client = new Client();
         $response = $client->createRequest()
             ->setMethod('POST')
             ->setUrl($url)
             ->setData($data)
             ->setFormat(Client::FORMAT_JSON)
             ->send();
 
         if ($response->isOk) {
             $content = json_decode($response->getContent());
             return $content->token;
         }
         return null;
     }
 
     public function actionSyncStock()
     {
         $token = $this->login();
         if ($token) {
             $url = 'https://node.dilbar.style/product';
             $client = new Client();
             $response = $client->createRequest()
                 ->setMethod('POST')
                 ->setUrl($url)
                 ->addHeaders(['Authorization' => 'Bearer ' . $token])
                 ->send();
 
             $counter = 0;
             if ($response->isOk) {
                 $content = json_decode($response->getContent());
                 foreach ($content->result as $item) {
                     $product = Product::find()->where(['product_code' => $item->id])->one();
                     
                     if ($product) {
                         if ($item->quantity) {
                             $product->in_stock = 1;
                         } else {
                             $product->in_stock = 0;
                         }

                         if($item->price){
                            $product->price = $item->price;
                         }
                         $product->save(false);
                     }
                 }
             }
         }
     }
}
