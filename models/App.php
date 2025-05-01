<?php

namespace app\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user This property is read-only.
 *
 */
class App
{
    public static function getStyle($action){
        switch($action){
            case 'view':return 'hotel';break;
            case 'comfort':return 'hotel';break;
            case 'payment':return 'hotel';break;
            case 'terms':return 'hotel';break;
            case 'crew':return 'hotel';break;
            case 'feedback':return 'hotel';break;


            case 'prices':return 'prices';break;
            case 'booking':return 'booking';break;

            case 'room-list':return 'rooms';break;
            case 'tariff-list':return 'rooms';break;
            case 'room':return 'rooms';break;
            case 'add-room':return 'rooms';break;
            case 'pictures':return 'rooms';break;
            case 'room-beds':return 'rooms';break;
            case 'room-comfort':return 'rooms';break;
            case 'edit-tariff':return 'rooms';break;
            case 'add-tariff':return 'rooms';break;

            case 'finance':return 'finance';break;
            default:return '';
        }
    }

    public static function getPersonSvg()
    {
        return '<svg width="13" class="person-svg" height="13" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0.666626 11.3333C0.666626 9.33332 3.33329 9.33332 4.66663 7.99999C5.33329 7.33332 3.33329 7.33332 3.33329 3.99999C3.33329 1.77799 4.22196 0.666656 5.99996 0.666656C7.77796 0.666656 8.66663 1.77799 8.66663 3.99999C8.66663 7.33332 6.66663 7.33332 7.33329 7.99999C8.66663 9.33332 11.3333 9.33332 11.3333 11.3333" stroke="black" stroke-linecap="round"/>
        </svg>';
    }
    public static function getStarSvg()
    {
        return '<svg width="14" style="margin-right:2px;" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.99995 10.896L3.12212 12.9347L3.86272 8.61671L0.725486 5.55867L5.06103 4.92867L6.99995 1L8.93887 4.92867L13.2744 5.55867L10.1372 8.61671L10.8778 12.9347L6.99995 10.896Z" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        ';
    }
    public static function getGlobeSvg()
    {
        return '<svg width="14" height="14" class="svg-globe" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g id="language">
        <path id="Oval" fill-rule="evenodd" clip-rule="evenodd" d="M6.99996 12.8333C10.2216 12.8333 12.8333 10.2217 12.8333 6.99999C12.8333 3.77833 10.2216 1.16666 6.99996 1.16666C3.7783 1.16666 1.16663 3.77833 1.16663 6.99999C1.16663 10.2217 3.7783 12.8333 6.99996 12.8333Z" stroke="black"/>
        <path id="Oval_2" fill-rule="evenodd" clip-rule="evenodd" d="M6.99996 12.25C6.99996 12.25 9.33329 10.3409 9.33329 7C9.33329 3.65909 6.99996 1.75 6.99996 1.75C6.99996 1.75 4.66663 3.65909 4.66663 7C4.66663 10.3409 6.99996 12.25 6.99996 12.25Z" stroke="black"/>
        <path id="Line" d="M1.45829 5.24999H12.5416" stroke="black" stroke-linecap="round"/>
        <path id="Line_2" d="M1.45829 8.74999H12.5416" stroke="black" stroke-linecap="round"/>
        </g>
        </svg>';
    }
    public static function getBagSvg()
    {
        return '<svg width="14" height="14" style="margin-right:5px;" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g id="bag">
        <rect id="Rectangle 2" x="3.33337" y="4.66666" width="9.33333" height="8" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
        <path id="Rectangle 2_2" d="M5.33337 4.66667C5.33337 3.19391 6.52728 2 8.00004 2C9.4728 2 10.6667 3.19391 10.6667 4.66667" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
        </g>
        </svg>';
    }

    public static function getHeart()
    {
        return '<svg class="svg-heart" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M15.9998 28L14.0665 26.2736C7.19984 20.1657 2.6665 16.1373 2.6665 11.1935C2.6665 7.16512 5.89317 4 9.99984 4C12.3198 4 14.5465 5.0594 15.9998 6.73352C17.4532 5.0594 19.6798 4 21.9998 4C26.1065 4 29.3332 7.16512 29.3332 11.1935C29.3332 16.1373 24.7998 20.1657 17.9332 26.2866L15.9998 28Z" stroke="#A6A6A6"/>
        </svg>';
    }

    public static function favs()
    {
        $count = 0;
        if (isset($_COOKIE['fav'])) {
            $count = count(unserialize($_COOKIE['fav']));
        }
        return $count;
    }

    public static function getLanguageLabel()
    {
        $arr = [
            'label' => Html::img(Url::base() . "/images/site/language.svg") . "<span>En</span>",
            'url' => "?language=en",
            'options' => ['class' => 'language-item'],
        ];
        switch (Yii::$app->language) {
            case 'en':
                $arr = [
                    'label' => Html::img(Url::base() . "/images/site/language.svg") . "<span>Рус</span>",
                    'url' => "?language=ru",
                    'options' => ['class' => 'language-item'],
                ];
                break;
        }
        return $arr;
    }
}