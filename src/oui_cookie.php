<?php

$plugin['name'] = 'oui_cookie';

$plugin['allow_html_help'] = 0;

$plugin['version'] = '0.1.0';
$plugin['author'] = 'Nicolas Morand';
$plugin['author_uri'] = 'http://github.com/NicolasGraph';
$plugin['description'] = 'Set, read, reset cookies through url variables';

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

h1. oui_cookie (in process!)

Set, reset, read cookies through url variables.

h2(#tags). tags

h3(#oui_cookie). oui_cookie

bc. <txp:oui_cookie />

Set and reset cookies.

h4. Attributes

h3(#oui_cookie). oui_if_cookie

bc.. <txp:oui_if_cookie>
    […]
<txp:else />
    […]
</txp:oui_if_cookie>

h4. Attributes

p. Check the context.

h2(#examples). Examples

h3(#sort_by). Front end articles sorting

bc.. <select onchange="window.location.href=this.value">
    <option value="?sort_by=custom_1">Size</option>
    <option value="?sort_by=custom_2">Weight</option>
</select>

<txp:article sort='<txp:oui_cookie urlvar="sort_by" values="custom_1, custom_2" default="custom_1" />' />

h3(#eu_cookies). EU cookies Warning

bc.. <txp:oui_cookie urlvar="accept_cookies" values="1, 0" default="0" display="0" />

<txp:oui_if_cookie cookie="accept_cookies" value="1">
<txp:else />
    This website uses cookies. <a href="?accept_cookies">Ok!</a>
</txp:oui_if_cookie>

h3(#switch_output). Output switching

bc.. <ul>
    <li><a href="?switch_css=2-cols">2 columns</a></li>
    <li><a href="?switch_css=3-cols">3 columns</a></li>
    <li><a href="?switch_css=4-cols">4 columns</a></li>
</ul>

<txp:css name='<txp:oui_cookie urlvar="switch_css" values="2-cols, 3-cols, 4-cols" default="4-cols" />' />

h2(#author). Author

"Nicolas Morand":http://github.com/NicolasGraph, from a "Jukka Svahn":http://rahforum.biz/ "tip":http://textpattern.tips/setting-cookies-for-eu-legislation.

h2(#licence). Licence

This plugin is distributed under "GPLv2":http://www.gnu.org/licenses/gpl-2.0.fr.html.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
if (class_exists('\Textpattern\Tag\Registry')) {
    // Register Textpattern tags for TXP 4.6+.
    Txp::get('\Textpattern\Tag\Registry')
        ->register('oui_cookie')
        ->register('oui_if_cookie');
}

function oui_cookie($atts) {

    extract(lAtts(array(
        'urlvar'  => '',
        'values'  => '',
        'default'  => '',
        'expires' => '+1 day',
        'reset'   => '0',
        'display' => '1',
    ),$atts));

    $values = array_map('trim', explode(",", $values));
    $gps = strval(gps($urlvar));
    $cs = cs($urlvar);

    if ($gps && in_array($gps, $values, true)) {
        setcookie($urlvar, $gps, strtotime(''.$expires.''), '/');
        return ($display ? $gps : '');
    } else if ($cs) {
        if ($gps == $reset) {
            setcookie($urlvar, '', -1, '/');
            return ($display ? $default : '');
        } else {
            return ($display ? $cs : '');
        }
    } else {
        return ($display ? $default : '');
    }
}

function oui_if_cookie($atts, $thing = NULL) {

    extract(lAtts(array(
        'cookie'  => '',
        'value'  => '',
    ),$atts));

    $gps = strval(gps($cookie));
    $cs = cs($cookie);

    $out = ($gps == $value || !$gps && $cs == $value) ? true : false;

    return parse(EvalElse($thing, $out));
}
# --- END PLUGIN CODE ---

?>
