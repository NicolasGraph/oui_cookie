<?php

$plugin['name'] = 'oui_cookie';

$plugin['allow_html_help'] = 0;

$plugin['version'] = '0.1.3-dev';
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

Set, read, reset or delete cookies through url variables.

This plugin allows to catch defined url variables and check their values. It can store a valid value in a cookie, reset this one or delete it with only one tag. The same tag can also returns the current valid value of the url variable or of the cookie set.
Moreover, you can check the status or the value of a defined url variable or cookie with a conditional tag.

h2. Table of contents

* "Plugin requirements":#requirements
* "Installation":#installation
* "Tags":#tags
** "oui_cookie":#oui_cookie
** "oui_if_cookie":#oui_if_cookie
* "Examples":#examples
** "Front end articles sorting":#sort_by
** "EU cookies warning":#eu_cookies
* "Author":#author
* "Licence":#licence

h2(#requirements). Plugin requirements

oui_instagram’s minimum requirements:

* Textpattern 4.5+

h2(#installation). Installation

# Paste the content of the plugin file under the *Admin > Plugins*, upload, install and enable it;
# Set your tags…

h2(#tags). tags

h3(#oui_cookie). oui_cookie

bc. <txp:oui_cookie name="…" values="…" />

This tag catches the named url variable if its value is one of the accepted values defined via the @values@ attribute. Once catched, this value can be displayed, used for conditional output and is stored in a cookie. This cookie can be deleted by calling the value of the @delete@ attribute as the value of the url variable.

h4. Attributes

h5. Required

* @name@ - _default: unset_ - The name of the url variable used and of the cookie set by it.
* @values@ - _default: unset_ - A comma separated list of accepted values for the url variable and its cookie.

h5. Optional

* @default@ - _default: unset_ - A default value.
* @display@ - _default: 1_ - If set to _0_, the url variable and/or the cookie will be read and set or reset, but no value will be displayed;
* @duration@ - _default: +1 day_ - The cookie duration.
* @delete@ - _default: 0_ - A value which removes the cookie when called through the url variable.

h3(#oui_cookie). oui_if_cookie

bc.. <txp:oui_if_cookie name="…">
    […]
<txp:else />
    […]
</txp:oui_if_cookie>

p. This tag checks the url variable or the cookie defined by the @name@ attribute and its related cookie. if the url variable is used and its value is the value defined via the @value@ attribute or if a related cookie is set to this value, the condition is true. If the @value@ attribute is not set, the plugin looks for one of the accepted values previoulsy set in the @<txp:oui_cookie />@ tag.

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

p. The first part of the code is a simple select element which is submited on change. Each selectable option value sets a different value to an url variable.
In the second part, we used @<txp:oui_cookie />@ as the value of the @sort@ attribute of @<txp:article />@. The plugin do its job by catching the @sort_by@ variable and its value. If it is equal to one of the @values@, it returns and stores it in a cookie named _sort_by_ to keep the selected order.

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

function oui_cookie($atts) {
    global $oui_cookies;

    extract(lAtts(array(
        'name'    => '',
        'values'  => '',
        'default' => '',
        'duration' => '+1 day',
        'delete'   => '0',
        'display' => '1',
    ),$atts));

    $errors = '';

    $oui_cookies = $oui_cookies ?: array();

    if ($name) {
        $gps = strval(gps($name));
        $cs = cs($name);
    } else {
        $errors .= trigger_error('oui_cookie requires an name attribute.');
    }

    if ($values) {
        $values = array_map('trim', explode(",", $values));
    } else {
        $errors .= trigger_error('oui_cookie requires a values attribute.');
    }

    if (!$errors) {
        if ($gps && in_array($gps, $values, true)) {
            setcookie($name, $gps, strtotime($duration), '/');
            $oui_cookies[$name] = true;
            return ($display ? $gps : '');
        } else if ($cs) {
            if ($gps == $delete) {
                setcookie($name, '', -1, '/');
                $oui_cookies[$name] = false;
                return ($display ? $default : '');
            } else {
                $oui_cookies[$name] = true;
                return ($display ? $cs : '');
            }
        } else {
            $oui_cookies[$name] = false;
            return ($display ? ($default ? $default : '') : '');
        }
    } else {
        return;
    }
}

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

    if (isset($oui_cookies[$name])) {
        if ($value) {
            $out = ($oui_cookies[$name] && ($gps == $value || !$gps && $cs == $value)) ? true : false;
        } else {
            $out = $oui_cookies[$name];
        }
    } else {
        $errors .= trigger_error('oui_cookie was unable to find your '.$name.' cookie settings');
    }

    // PREF_PLUGIN is a Txp4.6 feature, so if it is defined we can use the new parser.
    return $errors ?: defined('PREF_PLUGIN') ? parse($thing, $out) : parse(EvalElse($thing, $out));
}
# --- END PLUGIN CODE ---

?>
