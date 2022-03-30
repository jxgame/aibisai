<?php


if (!function_exists('isChinaMobile')) {
    /** 是否是中国手机号码 */
    function isChinaMobile($value){
        return mb_ereg_match('^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|191|198|199|(147))\d{8}$', $value);
    }    
}

