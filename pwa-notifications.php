<?php
  /**
  * Plugin Name:       PWA & Notifications
  * Plugin URI:        https://github.com/HelaGone
  * Description:       Handle configuration for OneSignal Notifications and Progressive Web Application. Only for Wordpress-AMP sites
  * Version:           1.0.0
  * Author:            Holkan Luna
  * Author URI:        https://hela.dev/
  * License:           GPL v2 or later
  * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
  * Text Domain:       pwa-notifications
  * Domain Path:       /languages
  */

  /*
   * OPTIONS
   * pwa_onesignal_option   --> OPTION FOR ONESIGNAL'S APP ID AND REST API KEY
  */

  //Including helper functions
  require_once dirname(__FILE__).'/helper-functions.php';

  function pwa_plugin_activation(){
    /*
     * Plugin activation actions
     * Should place necessaary files in root
    */
    copy($_SERVER['DOCUMENT_ROOT'].'/plugin_construction/wp-content/plugins/pwa-notifications/root-files/amp-helper-frame.html', $_SERVER['DOCUMENT_ROOT'].'/plugin_construction/amp-helper-frame.html');
    copy($_SERVER['DOCUMENT_ROOT'].'/plugin_construction/wp-content/plugins/pwa-notifications/root-files/amp-permission-dialog.html', $_SERVER['DOCUMENT_ROOT'].'/plugin_construction/amp-permission-dialog.html');
    copy($_SERVER['DOCUMENT_ROOT'].'/plugin_construction/wp-content/plugins/pwa-notifications/root-files/install_sw.html', $_SERVER['DOCUMENT_ROOT'].'/plugin_construction/install_sw.html');
    copy($_SERVER['DOCUMENT_ROOT'].'/plugin_construction/wp-content/plugins/pwa-notifications/root-files/OneSignalSDKUpdaterWorker.js', $_SERVER['DOCUMENT_ROOT'].'/plugin_construction/OneSignalSDKUpdaterWorker.js');
    copy($_SERVER['DOCUMENT_ROOT'].'/plugin_construction/wp-content/plugins/pwa-notifications/root-files/OneSignalSDKWorker.js', $_SERVER['DOCUMENT_ROOT'].'/plugin_construction/OneSignalSDKWorker.js');
    copy($_SERVER['DOCUMENT_ROOT'].'/plugin_construction/wp-content/plugins/pwa-notifications/root-files/service_worker.js', $_SERVER['DOCUMENT_ROOT'].'/plugin_construction/service_worker.js');
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

  add_action('admin_print_styles', function(){
    wp_register_style('plugin-style', plugin_dir_url(__FILE__).'css/style.css' );
    wp_enqueue_style('plugin-style');
  });


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
      }

      //Section callbacks
      public function pwa_appid_section_callback(){
        echo 'Paste the App ID & Rest API Key from OneSignal';
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

      //Plugin Forms
      public function pwa_notifications_page(){ ?>
        <form id="pwa_form" action="options.php" method="post" class="">
          <h2>PWA & Notifications</h2>
          <?php
            settings_fields('pwaOptionsPage');
            do_settings_sections('pwaOptionsPage');
            submit_button();
          ?>
        </form>

        <!-- THIS FORM HANDLES THE MANIFEST JSON GENERATION -->
        <form class="pwa_manifest_gen" action="<?php echo plugin_dir_url(__FILE__);?>gen-manifest.php" method="post">
          <h2>Manifest Values</h2>
          <label for="short_name">Short Name</label>
          <input type="text" name="short_name" value="" placeholder="Short Name"><br/>
          <label for="bg_color">Background Color</label>
          <input type="text" name="bg_color" value="" placeholder="#012345"><br/>
          <label for="th_color">Theme Color</label>
          <input type="text" name="th_color" value="" placeholder="#A1B2C3"><br/>
          <input type="submit" name="Submit" value="Save Manifest" class="button button-primary">
        </form>
        <?php
      }//End pwa_notifications_page

    }//End class definition
  }//End if class exist

  if(is_admin()):
  	$options_page = new PwaNotifications();
  endif;

?>
