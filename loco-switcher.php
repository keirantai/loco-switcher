<?php
/**
 * @package TAI
 */
/*
Plugin Name: Loco Switcher by T.A.I.
Plugin URI: http://taicreation.com/
Description: Use this with Loco Translate. This is a language switcher for Loco Translate plugin.
Version: 0.0.1
Author: T.A.I.
Author URI: http://taicreation.com
License: Private
*/

if ( ! class_exists( 'LocoSwitcher' ) ) {

  class LocoSwitcher {

    const OPT_ACTIVATED = 'activate-loco-switcher'; // cookies name of plugin activation status
    const C_LOCALE = 'loco-switcher-locale'; // cookies name of current locale
    const Q_LOCALE = 'locale'; // query string parameter name

    protected static $_instance = null;

    public static function instance() {
      if ( is_null( self::$_instance ) ) {
        self::$_instance = new self();
      }
      return self::$_instance;
    }

    public function __construct() {
      $this->hooks();
    }

    /**
     * setup wp hooks
     */
    public function hooks() {
      add_action( 'init', array( $this, 'init' ) );
      add_filter( 'locale', array( $this, 'updateLocale' ) );
    }

    /**
     * Init script when loading wp page
     */
    public function init() {
      if ( get_option( self::OPT_ACTIVATED ) !== 'yes') {
        return;
      }

      if ( ! isset( $_COOKIE[self::C_LOCALE] ) ) {
        setcookie( self::C_LOCALE, $this->getLocale(), strtotime('+1 year') );
      }
      
      $newLocale = @$_GET[self::Q_LOCALE];
      if ( ! is_null( $newLocale ) ) {
        setcookie( self::C_LOCALE, $newLocale, strtotime('+1 year') );
      }     
    }

    /**
     * Return current locale if plugin is activated
     */
    public function updateLocale() {
      if ( get_option( self::OPT_ACTIVATED ) !== 'yes') {
        return;
      }

      return $this->applyLocale();
    }

    /**
     * Return current locale to update WP
     */
    public function applyLocale() {      
      $newLocale = @$_GET[self::Q_LOCALE];
      if ( ! is_null( $newLocale ) ) {
        return $newLocale;
      }
      return @$_COOKIE[self::C_LOCALE];
    }

    /**
     * Predefined available locales
     */
    public function getAvailableLocales() {
      return array(
        'en' => 'en',
        'zh' => 'zh_HK',
      );
    }

    /**
     * Get a new locale by detecting browser locale or return default as `en`
     */
    public function getLocale() {
      $avail = $this->getAvailableLocales();
      $browerLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
      if ( ! isset( $avail[$browerLang] ) ) {
        $browserLang = 'en';
      }
      return $avail[$browerLang];
    }

    /**
     * Execute when activate this plugin
     */
    public function activatePlugin() {
      update_option( self::OPT_ACTIVATED, 'yes' );
    }

    /**
     * Execute when deactivate this plugin
     */
    public function deactivatePlugin() {
      delete_option( self::OPT_ACTIVATED );
    }

  }

  function loco_switcher() {
    return LocoSwitcher::instance();
  }

  add_action( 'plugins_loaded', 'loco_switcher', 10);
  register_activation_hook( __FILE__, array( 'LocoSwitcher', 'activatePlugin' ) );
  register_deactivation_hook( __FILE__, array( 'LocoSwitcher', 'deactivatePlugin' ) );
}