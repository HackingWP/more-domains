<?php

/*
Plugin Name:    More Domains
Plugin URI:     http://www.attitude.sk
Description:    Allows WordPress installation to run on multiple domains other than installed
Version:        v0.1.0
Author:         Martin Adamko
Author URI:     http://www.attitude.sk
License:        The MIT License (MIT)

Copyright (c) 2013 Mgr. art. Martin Adamko

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

class DevDomain
{
    static  $instance = null;

    private $host     = null;
    private $dev_host = null;
    private $https    = false;

    /**
     * Constructor
     *
     * @param void
     *
     */
    private function __construct()
    {
        add_filter('home_url', array($this,'dev_url'), 0);
        add_filter('site_url', array($this,'dev_url'), 0);
        add_filter('plugins_url', array($this,'dev_url'), 0);
        add_filter('theme_root_uri', array($this,'dev_url'), 0);
        add_filter('query',    array($this,'filter_query'));

        $this->setHttpsIfSecure();
    }

    /**
     * Returns singleton instance of this class
     *
     * @return this
     * 
     */
    static function instance()
    {
        if(static::$instance===null) {
            $instance = new DevDomain();
            return $instance;
        }

        return static::$instance;
    }

    /**
     * Ensures that this plugin is the first plugin to run from all
     *
     * Build upon:      http://wordpress.org/support/topic/how-to-change-plugins-load-order
     * Original author: http://profiles.wordpress.org/jsdalton/
     *
     * @param   void
     * @return  void
     *
     */
    public static function first_in_order() {
        $this_plugin     = plugin_basename(trim(__FILE__));
        $active_plugins  = get_option('active_plugins');
        $this_plugin_key = array_search($this_plugin, $active_plugins);

        if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
            array_splice($active_plugins, $this_plugin_key, 1);
            array_unshift($active_plugins, $this_plugin);

            update_option('active_plugins', $active_plugins);
        }
    }

    /**
     * Change home_url() and site_url() calls
     *
     * Modifies passed $url string by lookin at the current $_SERVER['HOST_NAME']
     * or adding `.dev` at the end
     *
     * @param   $url    string  URL string
     * @return          string  Modified URL string
     *
     */
    public function dev_url($url)
    {
        // Unify
        $url = $this->https ? str_replace('http://', 'https://', $url) : str_replace('https://', 'http://', $url);

        if($this->host===null) {
            if(preg_match('|https?://(.*?)/|', $url, $matches)) {
                $this->host = $matches[1];
            }

            // Skips entire change when running on original domain
            if($_SERVER['HTTP_HOST']!==$this->host) {
                if(!!apply_filters('dev-domain/is_chameleon', true)) {
                    $this->dev_host = apply_filters('dev-domain/new_domain', $_SERVER['HTTP_HOST']);
                } else {
                    $this->dev_host = apply_filters('dev-domain/new_domain', $this->host.'.dev');
                }
            }
        }

        if($this->dev_host!==null) {
            $url = str_replace($this->host, $this->dev_host, $url);
        }

        return $url;
    }

    /**
     * Set Bool for HTTPS
     *
     * Temporary, to fix som odds
     *
     */
    protected function setHttpsIfSecure()
    {
        $this->https = isset($_SERVER['HTTPS']) ? !! $_SERVER['HTTPS'] : false;
    }

    /**
     * Filter query
     *
     * Make sure none of .dev domain is saved into database
     *
     * @param   $sql string SQL query passed from every DB query
     * @return       string Modified SQL query
     *
     */
    public function filter_query($sql)
    {
        // Handle writes to table
        if($this->dev_host!==null) {
            $sql = str_replace($this->dev_host, $this->host, $sql);
        }

        return $sql;
    }

    /**
     * Checks if current host matches set of allowed hosts
     *
     * Define `MORE_DOMAINS_HOSTS` constant as a string where
     * hosts are divided by a pipe character.
     *
     * @param  $allowedHost array   List of hosts
     * @return              boolean True or false
     *
     */
    public static function hostMatches(array $allowedHosts)
    {
        $host = array_shift(explode(':', $_SERVER['HTTP_HOST']));

        foreach ($allowedHosts as $allowedHost) {
            if ($host === $allowedHost) {
                return true;
            }
        }

        return false;
    }
}

if (!defined('MORE_DOMAINS_HOSTS')) {
    define('MORE_DOMAINS_HOSTS', $_SERVER['HTTP_HOST']);
}

if (DevDomain::hostMatches(explode('|', MORE_DOMAINS_HOSTS)))  {
    // Runs on every other plugin's activation
    add_action("activated_plugin", array('DevDomain','first_in_order'));

    global $wp_dev_domain;
    $wp_dev_domain = DevDomain::instance();
}
