<?php

$plugin['name'] = 'oui_cookie';

$plugin['allow_html_help'] = 0;

$plugin['version'] = '0.2.3';
$plugin['author'] = 'Nicolas Morand';
$plugin['author_uri'] = 'http://github.com/NicolasGraph';
$plugin['description'] = 'Set, read, reset or delete cookies';

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
It is also possible to check the status or the value of a defined cookie, on setting or once set.

*Warning:* According to the EU legislation you must warn your users if your website set cookies.

h2. Table of contents

* "Plugin requirements":#requirements
* "Installation":#installation
* "Tags":#tags
** "oui_cookie":#oui_cookie
** "oui_if_cookie":#oui_if_cookie
* "Examples":#examples
** "Front end articles sorting":#sort_by
** "Last viewed article":#last_viewed
** "EU cookies warning":#eu_cookies
* "Author":#author
* "Licence":#licence

h2(#requirements). Plugin requirements

oui_cookie requires Textpattern 4.5+.

h2(#installation). Installation

# Paste the content of the plugin file under the *Admin > Plugins* tab, upload, install and enable it;
# Set your tags…

h2(#tags). tags

h3(#oui_cookie). oui_cookie

bc. <txp:oui_cookie name="…" />

or

bc.. <txp:oui_cookie name="…">
    […]
</txp:oui_cookie>

h4. Attributes

h5. Required

* @name@ - _default: unset_ - The cookie (and HTTP variable) name you want to use. If no other attibutes are defined, the tag will read and display the related value.

h5. Manually set a cookie

* @value@ - _default: unset_ - A value to manually set the named cookie.
You can also set the cookie value by using a continer tag like you would for a variable.

h5. Set a cookie through a HTTP variable

* @values@ - _default: unset_ - A comma separated list of accepted values for the url variable and its cookie.
* @default@ - _default: unset_ - A default value.
If set, the plugin conditional tag will always be true if not check against a defined value.

h5. Optional cookie settings

* @duration@ - _default: +1 day_ - A "strtotime":http://php.net/manual/fr/function.strtotime.php value to set the cookie duration.

h5. Delete a cookie

* @delete@ - _default: 0_ - If set to _1_ this attribute will delete the named cookie.

h3(#oui_cookie). oui_if_cookie

bc.. <txp:oui_if_cookie name="…">
    […]
<txp:else />
    […]
</txp:oui_if_cookie>

p. This tag checks the status or the value of the cookie (and/or the related HTTP variable) defined by the @name@ attribute.

h4. Attributes

h5. Required

* @name@ - _default: unset_ - The cookie (and HTTP variable) name you want to use.

h5. Optional

* @value@ - _default: unset_ - A value to check against the cookie (and/or the HTTP variable) value.

h2(#examples). Examples

h3(#sort_by). Front end articles sorting

List the sort options you want to use:

bc.. <select onchange="window.location.href=this.value">
    <option value="" disabled selected>Sort by</option>
    <option value="?sort_by=custom_1">Size</option>
    <option value="?sort_by=custom_2">Weight</option>
</select>

p. Then, catch the HTTP variable sent by this list to store it (useful to keep the sort order pages after pages).

bc. <txp:oui_cookie name="sort_by" values="custom_1, custom_2" default="custom_1" />

Now use the new value as the value of the @sort@ attribute of your article tag.

bc. <txp:article sort='<txp:oui_cookie name="sort_by" />' />

h3(#last_viewed). Last viewed article

Store the current article id in a cookie:

bc. <txp:if_individual_article>
    <txp:oui_cookie name="last_article" value='<txp:article_id />' />
</txp:if_individual_article>

Now, use the following code anywhere you want to display the last viewed article.

bc. <txp:if_cookie name="last_article">
    <txp:article_custom id='<txp:oui_cookie name="last_article" />' />
</txp:if_cookie>

h3(#eu_cookies). EU cookies Warning

bc.. <txp:oui_cookie name="accept_cookies" values="yes" />

<txp:oui_if_cookie name="accept_cookies">
<txp:else />
    This website uses cookies. <a href="?accept_cookies=yes">I accept!</a>
</txp:oui_if_cookie>

h2(#author). Author

"Nicolas Morand":http://github.com/NicolasGraph, inspired by a "Jukka Svahn":http://rahforum.biz/ "tip":http://textpattern.tips/setting-cookies-for-eu-legislation.

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
# --- END PLUGIN CODE ---

?>
