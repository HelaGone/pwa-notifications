<?php
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
  add_action('wp_footer', 'pwa_add_footer_tags');

  /**
   * Create metabox for notifications
  */
  add_action('add_meta_boxes', function(){
    add_meta_box('meta_push_signal', 'Notificación', 'pwa_push_notification_definition', 'post', 'side', 'high');
  });
  function pwa_push_notification_definition($post){
    $push_notification_check = (get_post_meta($post->ID, '_meta_pwa_notifications', true)) ? 'checked' : '';
    wp_nonce_field(__FILE__, '_articulo_push_nonce');
    echo "<label><input type='checkbox' name='_meta_pwa_notifications' value='true' $push_notification_check />Send Web Push Notification</label>";
  }

  function pwa_insert_manifest($option, $value="test"){
    $options = get_option('pwa_manifest_option');
    $fcm_sender_id = $options['pwa_manifest_fcm_sender_id_field'];
    $background_color = $options['pwa_manifest_bg_color_field'];
    $theme_color = $options['pwa_manifest_theme_color_field'];
    $short_name = $options['pwa_manifest_short_name_field'];
    $manifest = array(
      "gcm_sender_id_comment" => "For OneSignal web push notifications, Do not change ID",
      "gcm_sender_id" => $fcm_sender_id,
      "name" => get_bloginfo('name'),
      "short_name" => $short_name,
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
      "background_color" => $background_color,
      "theme_color" => $theme_color,
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

  add_action('added_option', 'pwa_insert_manifest', 10, 2);
  add_action('updated_option', 'pwa_insert_manifest', 10, 3);

  add_action('wp_footer', 'pwa_insert_manifest', 10, 4);
  // function pwa_add_rewrite_rules(){
  //   $manifest_filename = "manifest.json";
  //   add_rewrite_rule("^/{$manifest_filename}$", "index.php?{$manifest_filename}=1");
  // }
  //
  // function pwa_generate_manifest_on_the_fly($query){
  //   if(!property_exists($query, 'query_vars') || !is_array($query->query_vars)){
  //     return;
  //   }
  //   $query_vars_as_string = implode(',', $query->query_vars);
  //   $manifest_filename = "manifest.json";
  //
  //   if(strpos($query_vars_as_string, $manifest_filename) !== false){
  //     header('Content-Type: application/json');
  //     echo pwa_insert_manifest();
  //     exit();
  //   }
  // }
  //
  // function pwa_setup_hooks(){
  //   add_action('init', 'pwa_add_rewrite_rules');
  //   add_action('parse_request', 'pwa_generate_manifest_on_the_fly');
  // }
  // add_action('plugins_loaded', 'pwa_setup_hooks');

?>
