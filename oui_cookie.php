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
function oui_cookie($atts, $thing = null) {
    global $oui_cookies;

    extract(lAtts(array(
        'name'     => '',
        'value'    => '',
        'values'   => '',
        'default'  => '',
        'duration' => '+1 day',
        'delete'   => '0',
    ),$atts));

    if ($name) {
        $cs = cs($name);
    } else {
        trigger_error('oui_cookie requires an "name" attribute.');
        return;
    }

    $oui_cookies ?: $oui_cookies = array();

    /**
     * Manually set a cookie.
     */
    if ($thing) {
        $thing = parse($thing);
        setcookie($name, $thing, strtotime($duration), '/');
        $oui_cookies[$name] = $thing;
        return;
    }
    /**
     * Manually set a cookie.
     */
    else if ($value) {
        setcookie($name, $value, strtotime($duration), '/');
        $oui_cookies[$name] = $value;
        return;
    }
    /**
     * Set a cookie through HTTP variables.
     */
    else if ($values) {
        /**
         * Get the current values of the named HTTP variable or cookie
         */
        $gps = strval(gps($name));
        /**
         * The HTTP variable is set to one of the valid 'values';
         */
        if (in_list($gps, $values, $delim = ',')) {
            setcookie($name, $gps, strtotime($duration), '/');
            $oui_cookies[$name] = $gps;
        }
        /**
         * The cookie is alredy set.
         */
        else if ($cs) {
            $oui_cookies[$name] = $cs;
        }
        /**
         * Default setting.
         */
        else if ($default) {
            setcookie($name, $default, strtotime($duration), '/');
            $oui_cookies[$name] = $default;
        }
        /**
         * Else?
         */
        else {
            $oui_cookies[$name] = false;
        }
        return;
    }
    /**
     * Deletion.
     */
    else if ($delete) {
        setcookie($name, '', -1, '/');
        $oui_cookies[$name] = false;
        return;
    }
    /**
     * Reading from the related variable if it exists and is not false…
     */
    else if (isset($oui_cookies[$name]) && $oui_cookies[$name]) {
        return $oui_cookies[$name];
    }
    /**
     * …or, from the cookie itself.
     */
    else if ($cs) {
        return $cs;
    }
}

/**
 * Checks the status or the value of a HTTP variable or a cookie.
 */
function oui_if_cookie($atts, $thing = NULL) {
    global $oui_cookies;

    extract(lAtts(array(
        'name'  => '',
        'value' => '',
    ),$atts));

    if ($name) {
        $cs = cs($name);
    } else {
        trigger_error('oui_cookie requires a name attribute.');
        return;
    }

    /**
     * The cookie setting or deletion is in process;
     */
    if (isset($oui_cookies[$name])) {
        if ($oui_cookies[$name] === false) {
            $out = false;
        } else {
            $out = $value ? ($value === $oui_cookies[$name] ? true : false) : true;
        }
    }
    /**
     * The cookie already exists.
     */
    else if ($cs) {
        $out = $value ? ($value === $cs ? true : false) : true;
    }
    /**
     * No cookie set nor in setting.
     */
    else {
        $out = false;
    }
    /**
     * TO DO:
     * in the future, drop Txp 4.5 support by using parse($thing, $out) only.
     */
    return class_exists('\Textpattern\Tag\Registry') ? parse($thing, $out) : parse(EvalElse($thing, $out));
}
