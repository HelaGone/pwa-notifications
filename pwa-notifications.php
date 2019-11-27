<?php
  /**
  * Plugin Name:       PWA & Notifications
  * Plugin URI:        https://github.com/HelaGone
  * Description:       Handle configuration for OneSignal Notifications and Progressive Web Application
  * Version:           1.0.0
  * Author:            Holkan Luna
  * Author URI:        https://hela.dev/
  * License:           GPL v2 or later
  * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
  * Text Domain:       pwa-notifications
  * Domain Path:       /languages
  */

  function pwa_plugin_activation(){
    /*
     * Plugin activation actions
     * Should place necessaary files in root
    */
  }
  register_activation_hook(__FILE__, 'pwa_plugin_activation');

  function pwa_plugin_deactivation(){
    //Plugin deactivation actions

    //Remove metabox on plugin deactivation;
    remove_meta_box('meta_push_signal', 'post', 'side');
  }
  register_deactivation_hook(__FILE__, 'pwa_plugin_deactivation');

  function pwa_plugin_uninstall(){
    /*
     * Plugin deactivation actions
     * Should delete files pladed in root
    */
  }
  register_uninstall_hook(__FILE__, 'pwa_plugin_uninstall');

  if(!class_exists('PwaNotifications')){
    class PwaNotifications{
      public function __construct(){
        add_action('admin_menu', array($this, 'pwa_add_admin_menu'));
        add_action('admin_init', array($this, 'pwa_settings_init'));
      }//End constructor

      public function pwa_add_admin_menu(){
        add_menu_page('PWA Notifications', 'PWA Notifications', 'manage_options', 'pwa_notifications', array($this, 'pwa_notifications_page'));
      }

      public function pwa_settings_init(){
        //OneSignal Options
        register_setting('pwaOptionsPage', 'pwa_onesignal_option');
        add_settings_section('pwa_one_signal_section', 'OneSignal App ID & Rest API Key', array($this, 'pwa_appid_section_callback'), 'pwaOptionsPage');
        add_settings_field('pwa_appid_input_field', 'App ID', array($this, 'pwa_input_apikey_render'), 'pwaOptionsPage', 'pwa_one_signal_section');
        add_settings_field('pwa_restapikey_input_field', 'Rest API Key', array($this, 'pwa_input_restapikey_render'), 'pwaOptionsPage', 'pwa_one_signal_section');

        register_setting('pwaOptionsPage', 'pwa_manifest_option');
        add_settings_section('pwa_manifest_section', 'Menifest Values', array($this, 'pwa_menifest_section_callback'), 'pwaOptionsPage');
        add_settings_field('pwa_manifest_bg_color_field', 'Background Color', array($this, 'pwa_render_input_manifest_bg_color'), 'pwaOptionsPage', 'pwa_manifest_section');
        add_settings_field('pwa_manifest_theme_color_field', 'Theme Color', array($this, 'pwa_render_input_manifest_theme_color'), 'pwaOptionsPage', 'pwa_manifest_section');
        add_settings_field('pwa_manifest_short_name_field', 'Short Name', array($this, 'pwa_render_input_manifest_short_name'), 'pwaOptionsPage', 'pwa_manifest_section');
        add_settings_field('pwa_manifest_fcm_sender_id_field', 'FCM Sender ID', array($this, 'pwa_render_input_manifest_fcm_sender_id'), 'pwaOptionsPage', 'pwa_manifest_section');
      }

      //Section callbacks
      public function pwa_appid_section_callback(){
        echo 'Paste the App ID & Rest API Key from OneSignal';
      }

      public function pwa_menifest_section_callback(){
        echo 'Paste the Background Color, Theme Color, Short Name and FCM Sender ID for the app manifest';
      }

      //Input Fields for OneSignal Configuration
      public function pwa_input_apikey_render(){
        $options = get_option('pwa_onesignal_option'); ?>
        <input type="text" name="pwa_onesignal_option[pwa_appid_input_field]" value="<?php echo $options['pwa_appid_input_field']; ?>" class="custom_input"/>
        <?php
      }
      public function pwa_input_restapikey_render(){
        $options = get_option('pwa_onesignal_option'); ?>
        <input type="text" name="pwa_onesignal_option[pwa_restapikey_input_field]" value="<?php echo $options['pwa_restapikey_input_field']; ?>" class="custom_input"/>
        <?php
      }

      //Input fields for app Manifest configuration
      public function pwa_render_input_manifest_bg_color(){
        $options = get_option('pwa_manifest_option'); ?>
        <input type="text" name="pwa_manifest_option[pwa_manifest_bg_color_field]" value="<?php echo $options['pwa_manifest_bg_color_field']; ?>" class="custom_input"/>
        <?php
      }
      public function pwa_render_input_manifest_theme_color(){
        $options = get_option('pwa_manifest_option'); ?>
        <input type="text" name="pwa_manifest_option[pwa_manifest_theme_color_field]" value="<?php echo $options['pwa_manifest_theme_color_field']; ?>" class="custom_input" />
        <?php
      }
      public function pwa_render_input_manifest_short_name(){
        $options = get_option('pwa_manifest_option'); ?>
        <input type="text" name="pwa_manifest_option[pwa_manifest_short_name_field]" value="<?php echo $options['pwa_manifest_short_name_field']; ?>" class="custom_input" />
        <?php
      }
      public function pwa_render_input_manifest_fcm_sender_id(){
        $options = get_option('pwa_manifest_option'); ?>
        <input type="text" name="pwa_manifest_option[pwa_manifest_fcm_sender_id_field]" value="<?php echo $options['pwa_manifest_fcm_sender_id_field']; ?>" class="custom_input"/>
        <?php
      }

      //Plugin Form
      public function pwa_notifications_page(){ ?>
        <form id="pwa_form" action="options.php" method="post" class="">
          <h2>PWA & Notifications</h2>
          <?php
            settings_fields('pwaOptionsPage');
            do_settings_sections('pwaOptionsPage');
            submit_button();
          ?>
        </form>
        <?php
      }//End pwa_notifications_page

    }//End class definition
  }//End if class exist

  if(is_admin()):
  	$options_page = new PwaNotifications();
    include_once dirname(__FILE__).'/helper-functions.php';
  endif;

?>
