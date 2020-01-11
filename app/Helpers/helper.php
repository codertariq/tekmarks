<?php

function gv($params, $key, $default = null) {
    return (isset($params[$key]) && $params[$key]) ? $params[$key] : $default;
}

function gbv($params, $key) {
    return (isset($params[$key]) && $params[$key]) ? 1 : 0;
}

function getVar($list) {
    $file = resource_path('var/' . $list . '.json');

    return (\File::exists($file)) ? json_decode(file_get_contents($file), true) : [];
}

/*
 *  Used to get IP address of visitor
 *  @return date
 */

function getRemoteIPAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null;
}


/*
 *  Used to get IP address of visitor
 *  @return IP address
 */

function getClientIp() {
    $ips = getRemoteIPAddress();
    $ips = explode(',', $ips);
    return !empty($ips[0]) ? $ips[0] : \Request::getClientIp();
}


/*
 *  Used to convert time in desired format
 *  @param
 *  $time as time
 *  @return time
 */

function showTime($time = '') {
    if (!$time) {
        return;
    }

    if (config('config.time_format') === 'H:mm') {
        return date('H:i', strtotime($time));
    } else {
        return date('h:i a', strtotime($time));
    }
}


/*
 *  Used to get Default Currency
 *  @return array
 */

function getDefaultCurrency($prop = null) {
    $default_currency_key = array_search(config('config.currency'), array_column(getVar('currency'), 'name'));
    $currency = ($default_currency_key !== false) ? getVar('currency')[$default_currency_key] : null;

    if (!$prop) {
        return $currency;
    }

    return ($currency && isset($currency[$prop])) ? $currency[$prop] : null;
}

/*
 *  Used to format amount in given currency
 *  @param
 *  $amount as numeric
 *  $symbol as boolean, 1 for with currency symbol or 0 for without currency symbol
 *  @return string
 */
function currency($amount, $symbol = 0) {
    $currency = getDefaultCurrency();

    if (!$currency) {
        return round($amount, 2);
    }

    $decimal_value = $currency['decimal_place'];

    if (!$symbol) {
        return round($amount, $decimal_value);
    }

    $position = $currency['position'];
    $currency_symbol = $currency['symbol'];

    $amount = round($amount, $decimal_value);

    if ($position === 'suffix') {
        return $amount . '' . $currency_symbol;
    } else {
        return $currency_symbol . '' . $amount;
    }
}


/*
 * get Maximum post size of server
 */

function getPostMaxSize() {
    if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
        return (int) $postMaxSize;
    }

    $metric = strtoupper(substr($postMaxSize, -1));
    $postMaxSize = (int) $postMaxSize;

    switch ($metric) {
        case 'K':
            return $postMaxSize * 1024;
        case 'M':
            return $postMaxSize * 1048576;
        case 'G':
            return $postMaxSize * 1073741824;
        default:
            return $postMaxSize;
    }
}
