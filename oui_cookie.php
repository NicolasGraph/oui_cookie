<?php

/*
 * This file is part of oui_cookie,
 * a plugin allowing easy cookies management in Textpattern CMS.
 *
 * https://github.com/NicolasGraph/oui_cookie
 *
 * Copyright (C) 2018 Nicolas Morand
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA..
 */

namespace Oui {

    class cookie {

        /**
         * Cookie name.
         *
         * @var string
         * @see        setName(), getName().
         */

        protected $name;

        /**
         * Cookie related GET/POST parameter.
         *
         * @var string
         * @see        setParam(), getParam().
         */

        protected $param;

        /**
         * In process cookie setting value.
         *
         * @var array The provided value as a string or FALSE.
         * @see       setProcessing(), getProcessing().
         */

        protected static $processing = array();

        /**
         * Cookie value set.
         *
         * @var string
         * @see        setCookie(), getCookie().
         */

        protected $cookie;

        /**
         * $name property setter.
         *
         * @return object $this.
         */

        protected function setName($value) {
            $this->name = $value;

            return $this;
        }

        /**
         * $name property getter.
         *
         * @return string $this->name.
         */

        protected function getName() {
            return $this->name;
        }

        /**
         * $param property setter.
         *
         * @return object $this.
         */

        protected function setParam() {
            $this->param = strval(gps($this->getName()));

            return $this;
        }

        /**
         * $param property getter.
         *
         * @return string $this->param.
         */

        protected function getParam() {
            $this->param or $this->setParam();

            return $this->param;
        }

        /**
         * $processing property setter.
         *
         * @return object $this.
         */

        protected function setProcessing($value) {
            static::$processing[$this->getName()] = $value;

            return $this;
        }

        /**
         * $processing property getter.
         *
         * @return mixed
         */

        protected function getProcessing() {
            if (array_key_exists($this->getName(), static::$processing)) {
                return static::$processing[$this->getName()];
            }

            return null;
        }

        /**
         * $cookie property setter.
         *
         * @return object $this.
         */

        protected function setCookie() {
            $this->cookie = cs($this->getName());

            return $this;
        }

        /**
         * $cookie property getter.
         *
         * @return mixed $this->cookie
         */

        protected function getCookie() {
            $this->cookie or $this->setCookie();

            return $this->cookie;
        }

        /**
         * Delete a cookie.
         * Set the related $processing property value to false.
         *
         * @return bool TRUE if the cookie unsetting succeeded.
         */

        protected function delete() {
            $this->setProcessing(false);

            return setcookie($this->getName(), '', -1, '/');
        }

        /**
         * Set a cookie.
         * Set the related $processing property value.
         *
         * @return bool TRUE if the cookie setting succeeded.
         */

        public function set($value, $duration) {
            $this->setProcessing($value);

            return setcookie($this->getName(), $value, strtotime($duration), '/', null, false, true);
        }

        /**
         * Check the status/value of a defined cookie name against its cookie/URL-parameter related value.
         *
         * @param  string $value A value to check against the set one;
         * @return bool   TRUE if the defined cookie is set or in setting
         *                and, optionally, if its value match the one provided.
         */

        public function isSet($value = null) {
            $cs = $this->getCookie();
            $processing = $this->getProcessing();
            $out = false;

            if ($processing === false) {
                $out = false;
            } elseif ($processing) {
                $out = $value ? ($value === $processing ? true : false) : true;
            } elseif ($cs) {
                $out = $value ? ($value === $cs ? true : false) : true;
            }

            return $out;
        }

        /**
         * oui_cookie callback method.
         * Set, read or delete a cookie, manually or from a GET/POST parameter.
         *
         * @param  array  $atts Attributes in use;
         * @param  string $thing Tag content when used as a container.
         * @return string The cookie value on reading.
         */

        public static function renderCookie($atts, $thing = null)
        {
            $instance = \Txp::get('Oui\Cookie');

            extract(lAtts(array(
                'name'     => '',
                'value'    => '',
                'values'   => '',
                'default'  => '',
                'duration' => '+1 day',
                'delete'   => '',
            ), $atts));

            if ($name) {
                $cs = $instance->setName($name)->getCookie();
                $gps = $instance->getParam();
            } else {
                trigger_error('oui_cookie requires an "name" attribute.');
                return;
            }

            if ($thing) {
                $value = parse($thing);
            } elseif ($values && in_list($gps, $values, $delim = ',')) {
                $value = $gps;
            } elseif (!$cs && $default) {
                $value = $default;
            }

            if ($value) {
                $instance->set($value, $duration);
            } elseif ($delete && !$values || $values && $gps && $gps === $delete) {
                $instance->delete();
            } elseif (!$values) {
                $processing = $instance->getProcessing();

                return $cs && $processing !== false ? $cs : $processing;
            }
        }

        /**
         * oui_if_cookie callback method.
         * Switch between two defined contents/behaviours depending on the $isSet() result.
         *
         * @param  array  $atts  Attributes in use;
         * @param  string $thing Tag content.
         * @return mixed  The default contents/behaviour if $isSet returns TRUE, the else part otherwise.
         */

        public static function renderIfCookie($atts, $thing = null)
        {
            $instance = \Txp::get('Oui\Cookie');

            extract(lAtts(array(
                'name'  => '',
                'value' => '',
            ), $atts));

            if ($name) {
                $instance->setName($name);
            } else {
                trigger_error('oui_cookie requires a name attribute.');
                return;
            }

            return parse($thing, $instance->isSet($value));
        }
    }
}

namespace {
    Txp::get('\Textpattern\Tag\Registry')
        ->register('Oui\Cookie::renderCookie', 'oui_cookie')
        ->register('Oui\Cookie::renderIfCookie', 'oui_if_cookie');
}
