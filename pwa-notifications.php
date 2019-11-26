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
    }

    public function pwa_appid_section_callback(){
      echo 'Paste the App ID & Rest API Key from OneSignal';
    }

    //Input Fields
    public function pwa_input_apikey_render(){
      $options = get_option('pwa_onesignal_option'); ?>
      <input type="text" name="pwa_onesignal_option[pwa_appid_input_field]" value="<?php echo $options['pwa_appid_input_field']?>" class="custom_input"/>
      <?php
    }
    public function pwa_input_restapikey_render(){
      $options = get_option('pwa_onesignal_option'); ?>
      <input type="text" name="pwa_onesignal_option[pwa_restapikey_input_field]" value="<?php echo $options['pwa_restapikey_input_field'] ?>" class="custom_input"/>
      <?php
    }

    public function pwa_restapikey_section_callback(){
      echo 'Paste the Rest API Key from OneSignal';
    }

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
    }

  }//End class definition
}//End if class exist

if(is_admin()):
	$options_page = new PwaNotifications();
endif;

/**
 * [pwa_add_footer_tags] Add amp tags to footer
 * @param [null]
 * @return [void]
*/
function pwa_add_footer_tags() {
  $siteUrl = get_site_url();
  $options = get_option('pwa_onesignal_option');
  $appId = $options['pwa_appid_input_field'];
  echo '<amp-web-push id="amp-web-push" layout="nodislay" helper-iframe-url="'.$siteUrl.'/amp-helper-frame.html?appId='.$appId.'" permission-dialog-url="'.$siteUrl.'/amp-permission-dialog.html?appId='.$appId.'" service-worker-url="'.$siteUrl.'/OneSignalSDKWorker.js?appId='.$appId.'" class="i-amphtml-element i-amphtml-layout-nodisplay" hidden i-amphtml-layout="nodisplay"></amp-web-push>';
  echo '<amp-install-serviceworker src="'.$siteUrl.'/OneSignalSDKWorker.js?appId='.$appId.'" data-iframe-src="'.$siteUrl.'/install_sw.html" layout="nodisplay" class="i-amphtml-element i-amphtml-layout-nodisplay" hidden i-amphtml-layout="nodisplay">';
}
add_action( 'wp_footer', 'pwa_add_footer_tags' );

/**
 * [pwa_insert_manifest]
*/
function pwa_insert_manifest(){
  $manifest = array(
    "gcm_sender_id_comment" => "For OneSignal web push notifications, Do not change ID",
    "gcm_sender_id" => "",
    "name" => get_bloginfo('name'),
    "short_name" => "",
    "description" => get_bloginfo('description'),
    "icons" => array(
      array(
        "src" => get_template_directory_uri().'/images/icon/192.png',
        "sizes" => "192x192",
        "type" => "image/png"
      ),
      array(
        "src" => get_template_directory_uri().'/images/icon/152.png',
        "sizes" => "152x152",
        "type" => "image/png"
      )
    ),
    "background_color" => "#000000",
    "theme_color" => "#000000",
    "display" => "standalone",
    "orientation" => "portrait",
    "start_url" => "./",
    "scope" => "./"
  );
  $jsonManifest = json_encode($manifest);
  $fp = fopen('manifest.json', 'w');
  fwrite($fp, $jsonManifest);
  fclose($fp);
}
pwa_insert_manifest();

?>
