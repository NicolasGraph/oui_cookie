<?php

$plugin['name'] = 'oui_cookie';

$plugin['allow_html_help'] = 0;

$plugin['version'] = '0.2.2-dev';
$plugin['author'] = 'Nicolas Morand';
$plugin['author_uri'] = 'http://github.com/NicolasGraph';
$plugin['description'] = 'Set, read, reset or delete cookies through url variables';

$plugin['order'] = 5;

$plugin['type'] = 0;

// Plugin 'flags' signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use.
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

// $plugin['flags'] = PLUGIN_HAS_PREFS | PLUGIN_LIFECYCLE_NOTIFY;
$plugin['flags'] = '0';

if (!defined('txpinterface'))
    @include_once('zem_tpl.php');

if (0) {

?>
# --- BEGIN PLUGIN HELP ---

h1. oui_cookie

This plugin allows to set, read, reset or delete a cookie manually or through a HTTP variable.
It is also possible to check the status or the value of a defined cookie.

*Warning:* According to the EU legislation you need to warn your users if your website set cookies.

h2. Table of contents

* "Plugin requirements":#requirements
* "Installation":#installation
* "Tags":#tags
** "oui_cookie":#oui_cookie
** "oui_if_cookie":#oui_if_cookie
* "Examples":#examples
** "Front end articles sorting":#sort_by
** "Recently viewed articles":#recently_viewed
** "EU cookies warning":#eu_cookies
* "Author":#author
* "Licence":#licence

h2(#requirements). Plugin requirements

oui_cookie requires Textpattern 4.6+ from the version 0.2.0.

h2(#installation). Installation

# Paste the content of the plugin file under the *Admin > Plugins* tab, upload, install and enable it;
# Set your tags…

h2(#tags). tags

h3(#oui_cookie). oui_cookie

bc. <txp:oui_cookie name="…" />

This tag is able to set a cookie manually with the @value@ attribute or through an HTTP variable thanks to the @values@ attribute which list accepted values. It is also able to read and return the current valid value of a HTTP variable or of the cookie set.

h4. Attributes

h5. Required

* @name@ - _default: unset_ - The name of the url variable used and of the cookie set by it.

h5. Set a cookie

* @duration@ - _default: +1 day_ - A "strtotime":http://php.net/manual/fr/function.strtotime.php value to set the cookie duration.
* @display@ - _default: 0_ - By default the url variable and/or the cookie will be read and set or reset, but no value will be displayed;

h6. Manually set a cookie

* @value@ - _default: unset_ - A value to manually set the named cookie.

h6. Set a cookie through a HTTP variable

* @values@ - _default: unset_ - A comma separated list of accepted values for the url variable and its cookie. If not set the tag will only read the cookie value.
* @default@ - _default: unset_ - A default value. If set, the plugin conditional tag will always be true if not check against a defined value.
* @delete@ - _default: any value which is not one of the accepted values_ - A defined value which removes the cookie when called through the url variable.

h5. Read a cookie (also works while setting)

* @limit@ - _default: 1_ - Allows to add each found value to the cookie value untill.
* @offset@ - _default: 0_ - When a cookie is read you could occasionaly need to use this attribute to skip a defined number of values when the cookie contains a list.

h3(#oui_cookie). oui_if_cookie

bc.. <txp:oui_if_cookie name="…">
    […]
<txp:else />
    […]
</txp:oui_if_cookie>

p. This tag checks the status or the value of the HTTP variable defined by the @name@ attribute and its related cookie.

h4. Attributes

h5. Required

* @name@ - _default: unset_ - The name of the url variable used and of the cookie set by it.

h5. Optional

* @value@ - _default: unset_ - A value to check against the url variable or the cookie value.

h2(#examples). Examples

h3(#sort_by). Front end articles sorting

bc.. <select onchange="window.location.href=this.value">
    <option value="" disabled selected>Sort by</option>
    <option value="?sort_by=custom_1">Size</option>
    <option value="?sort_by=custom_2">Weight</option>
</select>

<txp:article sort='<txp:oui_cookie name="sort_by" values="custom_1, custom_2" default="custom_1" />' />

p. The first part of the code is a simple select element which is submited on change. Each selectable option value sets a diferent value to an url variable.

In the second part, we used @<txp:oui_cookie />@ as the value of the @sort@ attribute of @<txp:article />@. The plugin do its job by catching the @sort_by@ variable and its value. If it is equal to one of the @values@, it returns and stores it in a cookie named _sort_by_ to keep the selected order.

h3(#recently_viewed). Recently viewed articles

If you use raw url's, you can catch and store articles id's by using the following code in your article form:

bc. <txp:if_individual_article>
    <txp:oui_cookie name="id" values='<txp:article_id />' limit="5" display="0" />
</txp:if_individual_article>

This works because Textpattern raw url's use a HTTP variable named _id_ and set to the article id.

If you use an url rewrite rule, you need to store the article id like so:

bc. <txp:if_individual_article>
    <txp:oui_cookie name="id" value='<txp:article_id />' limit="5" display="0" />
</txp:if_individual_article>

Here, we are setting the value manually, so we could change the @name@.

Now, to display a list of up to five recently viewed articles, use the following code anywhere you want…

bc. <txp:if_cookie name="id">
    <txp:article_custom id='<txp:oui_cookie name="id" />' />
</txp:if_cookie>

h3(#eu_cookies). EU cookies Warning

bc.. <txp:oui_cookie name="accept_cookies" values="1" display="0" />

<txp:oui_if_cookie name="accept_cookies">
<txp:else />
    This website uses cookies. <a href="?accept_cookies=1">Ok!</a>
</txp:oui_if_cookie>

h2(#author). Author

"Nicolas Morand":http://github.com/NicolasGraph, from a "Jukka Svahn":http://rahforum.biz/ "tip":http://textpattern.tips/setting-cookies-for-eu-legislation.

h2(#licence). Licence

This plugin is distributed under "GPLv2":http://www.gnu.org/licenses/gpl-2.0.fr.html.


# --- END PLUGIN HELP ---
<?php
}

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
function oui_cookie($atts) {
    global $oui_cookies;

    extract(lAtts(array(
        'name'     => '',
        'values'   => '',
        'value'    => '',
        'default'  => '',
        'duration' => '+1 day',
        'delete'   => '',
        'display'  => '0',
        'limit'    => '1',
        'offset'   => '0',
    ),$atts));

    $oui_cookies = $oui_cookies ?: array();

    if ($name) {
        $gps = strval(gps($name));
        $cs = cs($name);
    } else {
        trigger_error('oui_cookie requires an name attribute.');
        return;
    }

    /**
     * Manually set a cookie
     */
    if ($value) {

        if ($value == $delete) {
            setcookie($name, '', -1, '/');
            $oui_cookies[$name] = false;
            return;
        } else {
            $cs = $value.', '.$cs;
            $cs = implode(', ', array_slice(do_list_unique($cs), $offset, $limit));
            setcookie($name, $cs, strtotime($duration), '/');
            $oui_cookies[$name] = $value;
            return $display ? $cs : '';
        }
    }
    /**
     * Set a cookie through HTTP variables
     */
    else if ($values) {

        $valid = in_list($gps, $values, $delim = ',');
        /**
         * HTTP variable is set to one of the valid 'values';
         * set the related cookie.
         */
        if ($valid) {
            $cs = $gps.', '.$cs;
            $cs = implode(', ', array_slice(do_list_unique($cs), $offset, $limit));
            setcookie($name, $cs, strtotime($duration), '/');
            $oui_cookies[$name] = $values;
            return $display ? $cs : '';
        }
        /**
         * HTTP variable is set to the delete value if defined or to a non valid value;
         * delete the cookie or set it to the 'default' value.
         */
        else if ($delete ? $gps == $delete : $gps && !$valid) {
            if ($default) {
                setcookie($name, $default, strtotime($duration), '/');
                $oui_cookies[$name] = $default;
                return ($display ? $default : '');
            } else {
                setcookie($name, '', -1, '/');
                $oui_cookies[$name] = false;
                return;
            }
        }
        /**
         * A non valid HTTP variable is found but the 'delete' attribute is set;
         * do nothing.
         */
        else if ($cs || ($gps && !$valid && $delete)) {
            $oui_cookies[$name] = $values ? $values : $default;
            return $display ? $cs : '';
        }
        /**
         * No valid/delete HTTP variable, no cookie set;
         * set the cookie to the 'value' or 'default' attribute value.
         */
        else {
            if ($default) {
                setcookie($name, $default, strtotime($duration), '/');
                $oui_cookies[$name] = $default;
                return $display ? $default : '';
            } else {
                $oui_cookies[$name] = false;
                return;
            }
        }
    }
    /**
     * Read a cookie
     */
    else  {

        if ($delete) {
            setcookie($name, '', -1, '/');
            $oui_cookies[$name] = false;
            return;
        } else {
            return $cs;
        }
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

    $errors = '';

    if ($name) {
        $gps = strval(gps($name));
        $cs = cs($name);
    } else {
        $errors .= trigger_error('oui_cookie requires an cookie attribute.');
    }

    /**
     * A 'named' HTTP variable exists;
     * Check it against a defined 'value' or return the status.
     */
    if ($gps) {
        if ($value) {
            $valid = in_list($gps, $oui_cookies[$name]);
            $out = ($oui_cookies[$name] && ($gps == $value || !$valid && in_list($value, $cs))) ? true : false;
        } else {
            $out = $oui_cookies[$name] ? true : false;
        }
    }
    /**
     * No 'named' HTTP variable but a cookie already exists;
     * Check it against a defined 'value' or return the status.
     */
    else if ($cs) {
        $out = $value ? in_list($value, $cs) : true;
    }
    /**
     * No 'named' HTTP variable nor cookie found;
     * Check the oui_cookie 'value' or 'default' value against a defined 'value' or return the status.
     */
    else {
        $out = $value ? in_list($value, $oui_cookies[$name]) : $oui_cookies[$name] ? true : false;
    }

    return parse($thing, $out);
}
# --- END PLUGIN CODE ---

?>
