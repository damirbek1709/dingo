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
    public static function getLogo()
    {
        return Html::img(Url::base() . '/images/site/logo.svg');
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

    public static function activeLinkUser($action)
    {
        switch ($action) {
            case "profile":
                return "profile-active";
                break;
            case "favorites":
                return "favorites-active";
                break;
            case "purchaises":
                return "purchaises-active";
                break;
            case "account":
                return "account-active";
                break;
            case "delivery":
                return "delivery-active";
                break;
            case "payment-method":
                return "payment-method-active";
                break;
            case "orders":
                return "orders-active";
                break;
            default:
                return "profile-active";
        }
    }

    public static function isFav($id)
    {
        $isFav = Favorites::find()->where(['user_id' => Yii::$app->user->id, 'product_id' => $id])->one();
        if ($isFav) return "heart-reversed";
        else return "";
    }

    public static function isCookieAccepted(){
        $cookie = Yii::$app->request->cookies->getValue('cookie_terms');
        if ($cookie && $cookie == "accepted") {
            return true;
        }
        else{
            return false;
        }
    }
}
