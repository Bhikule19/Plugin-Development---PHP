<?php

/*
Plugin Name: Our Custom Plugin
Description: This is a custom made plugin
Version: 1.0
Author: Abhishek
Author URI: https://github.com/Bhikule19
Text Domain: wcpdomain
Domain Path: /Languages
*/


class WordCountAndTimePlugin {
    function __construct(){
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifApplicable'));
        add_action('init', array($this, 'languages'));
    }



    function settings(){
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

        //Display Location. 
        add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

        //Headline Text
        add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

        //Word Count
        add_settings_field('wcp_wordcount', 'Word Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
        register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        //Character Count
        add_settings_field('wcp_charactercount', 'Character Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_charactercount'));
        register_setting('wordcountplugin', 'wcp_charactercount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        //Read Time
        add_settings_field('wcp_readtime', 'Read Time', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
        register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    }

    function languages() {
      load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }


    function ifApplicable($content){ // returns true and show the content info if true

      if(is_main_query() && is_single() && (get_option('wcp_wordcount', '1') || get_option('wcp_charactercount', '1') || get_option('wcp_readtime', '1'))){
           return $this->createHTML($content);
      }
      return $content;

  }

  function createHTML($content){
      $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . '</h3><p>';

     
      if(get_option('wcp_wordcount', '1') || get_option('wcp_readtime', '1')){
          $wordCount = str_word_count(strip_tags($content));
      }

      if (get_option('wcp_wordcount', '1')) {
          $html.= esc_html__('This Post has Word Count:', 'wcpdomain') . ' ' . $wordCount. '<br>';
        }
    
        if (get_option('wcp_charactercount', '1')) {
          $html .= '<strong>This Post has :</strong> ' . strlen(strip_tags($content)) . ' <strong>characters</strong>.<br>';
        }
    
        if(round($wordCount/225) == 0){
          if (get_option('wcp_readtime', '1')) {
              $html.= '<strong>This post is very short.</strong><br>';
            }
        } 

        if(round($wordCount/225) >= 1){
          if (get_option('wcp_readtime', '1')) {
              $html .= '<strong>This post will take about </strong>' . round($wordCount/225) . ' <strong>minute(s) to read.</strong><br>';
            }
        }

       
    
        $html .= '</p>';

      if(get_option('wcp_location', '0') === '0'){ // Will put the post statisctis above the content area
          return $html . $content ;
      }
      
      return $content . $html;
  }

    function sanitizeLocation($input) { // sanitize the display location value to be only one or zero and outputing error message if not
        if ($input != '0' AND $input != '1') {
          add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either beginning or end.');
          return get_option('wcp_location');
        }
        return $input;
      }

    /*
    Create a single function for each check box
    function wordcountHTML() { ?>
        <input type="checkbox" name="wcp_wordcount" value="1" <?php checked(get_option('wcp_wordcount'), '1') ?>>
      <?php }

    */

      // just create a single function for all three checkboxes

      function checkboxHTML($args){ ?>
        <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked(get_option($args['theName']), '1') ?> >
        <?php } 

    function headlineHTML(){ ?>

    <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">

   <?php }

    function locationHTML() { ?>
        <select name="wcp_location">
          <option value="0" <?php selected(get_option('wcp_location'), '0') ?> >Beginning of post</option>
          <option value="1" <?php selected(get_option('wcp_location'), '1') ?> >End of post</option>
        </select>
      <?php }

    function adminPage(){
        add_options_page('Word Count Settings', __('Word Count', 'wcpdomain'), 'manage_options', 'word-count-settings-page', array($this, 'adminPageContent'));
    }

    function adminPageContent(){ ?>

    <div class="wrap" >
        <h1>Word Count Settings</h1>
        <form action="options.php" method="POST">
      <?php
        settings_fields('wordcountplugin');
        do_settings_sections('word-count-settings-page');
        submit_button();
      ?>
      </form>
    </div>

  <?php  }

}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();


?>  
