<?php

# --- BEGIN PLUGIN CODE ---
if (class_exists('\Textpattern\Tag\Registry')) {
    Txp::get('\Textpattern\Tag\Registry')
        ->register('oui_cookie')
        ->register('oui_if_cookie');
}

/**
 * Reads a HTTP variable, checks its value,
 * returns it and stores it in a cookie.
 */
function oui_cookie($atts, $thing = null)
{
    global $oui_cookies;

    extract(lAtts(array(
        'name'     => '',
        'value'    => '',
        'values'   => '',
        'default'  => '',
        'duration' => '+1 day',
        'delete'   => '0',
    ), $atts));

    if ($name) {
        $cs = cs($name);
    } else {
        trigger_error('oui_cookie requires an "name" attribute.');
        return;
    }

    $oui_cookies ?: $oui_cookies = array();

    if ($thing) {
        // Manually set a cookie.
        $thing = parse($thing);
        setcookie($name, $thing, strtotime($duration), '/');
        $oui_cookies[$name] = $thing;
        return;
    } elseif ($value) {
        // Manually set a cookie.
        setcookie($name, $value, strtotime($duration), '/');
        $oui_cookies[$name] = $value;
        return;
    } elseif ($values) {
        // Set a cookie through HTTP variables.
        // Get the current values of the named HTTP variable or cookie
        $gps = strval(gps($name));

        if (in_list($gps, $values, $delim = ',')) {
            //The HTTP variable is set to one of the valid 'values'.
            setcookie($name, $gps, strtotime($duration), '/');
            $oui_cookies[$name] = $gps;
        } elseif ($cs) {
            // The cookie is alredy set.
            $oui_cookies[$name] = $cs;
        } elseif ($default) {
            // Default setting.
            setcookie($name, $default, strtotime($duration), '/');
            $oui_cookies[$name] = $default;
        } else {
            // Else?
            $oui_cookies[$name] = false;
        }
        return;
    } elseif ($delete) {
        // Deletion.
        setcookie($name, '', -1, '/');
        $oui_cookies[$name] = false;
        return;
    } elseif (isset($oui_cookies[$name]) && $oui_cookies[$name]) {
        // Reading from the related variable if it exists and is not false…
        return $oui_cookies[$name];
    } elseif ($cs) {
        // …or, from the cookie itself.
        return $cs;
    }
}

/**
 * Checks the status or the value of a HTTP variable or a cookie.
 */
function oui_if_cookie($atts, $thing = null)
{
    global $oui_cookies;

    extract(lAtts(array(
        'name'  => '',
        'value' => '',
    ), $atts));

    if ($name) {
        $cs = cs($name);
    } else {
        trigger_error('oui_cookie requires a name attribute.');
        return;
    }

    if (isset($oui_cookies[$name])) {
        // The cookie setting or deletion is in process
        if ($oui_cookies[$name] === false) {
            $out = false;
        } else {
            $out = $value ? ($value === $oui_cookies[$name] ? true : false) : true;
        }
    } elseif ($cs) {
        // The cookie already exists.
        $out = $value ? ($value === $cs ? true : false) : true;
    } else {
        // No cookie set nor in setting
        $out = false;
    }
    // TO DO: in the future, drop Txp 4.5 support by using parse($thing, $out) only.
    return class_exists('\Textpattern\Tag\Registry') ? parse($thing, $out) : parse(EvalElse($thing, $out));
}
