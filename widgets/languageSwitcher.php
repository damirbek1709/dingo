<?php
/**
 * Created by PhpStorm.
 * User: Damir
 * Date: 10/28/15
 * Time: 11:51 AM
 */

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Url;
use yii\web\Cookie;

class languageSwitcher extends Widget
{
    public $languages = [
        'ru' => 'Русский',
        'en'=>'English'
    ];

    public function init()
    {

        if (php_sapi_name() === 'cli') {
            return true;
        }

        parent::init();

        $cookies = Yii::$app->request->cookies;
        $languageNew = Yii::$app->request->getQueryParam('language');
        if ($languageNew) {
            if (isset($this->languages[$languageNew])) {
                Yii::$app->language = $languageNew;
                \Yii::$app->getResponse()->getCookies()->add(new Cookie([
                    'name' => 'language',
                    'value' => $languageNew,
                ]));
            }
        } elseif (($cookie = $cookies->get('language')) !== null) {
            Yii::$app->language = $cookies->getValue('language');
        }
    }

    public function run()
    {
        $languages = $this->languages;
        $current = $languages[Yii::$app->language];
        unset($languages[Yii::$app->language]);

        $items = [];
        foreach ($languages as $code => $language) {
            $temp = [];
            $temp['label'] = $language;
            $temp['url'] = Url::current(['language' => $code]);
            array_push($items, $temp);
        }
        echo ButtonDropdown::widget([
            'label' => $current,
            'dropdown' => [
                'items' => $items,
            ],
        ]);
    }

}