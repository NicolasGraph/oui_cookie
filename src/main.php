<?php

/*
 * oui_cookie - Set, check, read, reset or delete cookies
 *              manually or through HTTP variables.
 *
 * https://github.com/NicolasGraph/oui_cookie
 *
 * Copyright (C) 2016 Nicolas Morand
 *
 * This file is part of oui_cookie.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

# --- BEGIN PLUGIN CODE ---
Txp::get('\Textpattern\Tag\Registry')
    ->register('oui_cookie')
    ->register('oui_if_cookie');

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
        setcookie($name, $thing, strtotime($duration), '/', null, false, true);
        $oui_cookies[$name] = $thing;
        return;
    } elseif ($value) {
        // Manually set a cookie.
        setcookie($name, $value, strtotime($duration), '/', null, false, true);
        $oui_cookies[$name] = $value;
        return;
    } elseif ($values) {
        // Set a cookie through HTTP variables.
        // Get the current values of the named HTTP variable or cookie
        $gps = strval(gps($name));

        if ($gps !== '' && in_list($gps, $values, $delim = ',')) {
            // The HTTP variable is set to one of the valid 'values'.
            setcookie($name, $gps, strtotime($duration), '/', null, false, true);
            $oui_cookies[$name] = $gps;
        } elseif ($gps !== '' && $gps === $delete) {
            // The HTTP variable is set to the 'delete' value.
            setcookie($name, '', -1, '/');
            $oui_cookies[$name] = false;
        } elseif ($cs) {
            // The cookie is alredy set.
            $oui_cookies[$name] = $cs;
        } elseif ($default) {
            // Default setting.
            setcookie($name, $default, strtotime($duration), '/', null, false, true);
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
    return parse($thing, $out);
}
