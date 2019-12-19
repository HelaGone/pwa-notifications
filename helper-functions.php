<?php
  /**
   * Create metabox for notifications
  */
  add_action('add_meta_boxes', function(){
    add_meta_box('meta_push_signal', 'Notifications', 'pwa_push_notification_metabox', 'post', 'side', 'high');
  });

  /**
   * [pwa_push_notification_metabox] This function define the markup for the metabox
   * @param [object] $post The post object
   * @return [void]
   */
  function pwa_push_notification_metabox($post){
    $push_notification_check = (get_post_meta($post->ID, '_meta_pwa_notifications', true)) ? 'checked' : '';
    wp_nonce_field(__FILE__, '_articulo_push_nonce');
    echo "<label><input type='checkbox' name='_meta_pwa_notifications' value='true' $push_notification_check />Send Web Push Notification</label>";
  }

  /**
  * [pwa_add_footer_tags] Add amp tags to footer
  * @param [null]
  * @return [void]
  */
  function pwa_add_footer_tags() {
    $siteUrl = get_site_url();
    $options = get_option('pwa_onesignal_option');
    $appId = ($options) ? $options['pwa_appid_input_field'] : '';
    echo '<amp-web-push id="amp-web-push" layout="nodislay" helper-iframe-url="'.$siteUrl.'/amp-helper-frame.html?appId='.$appId.'" permission-dialog-url="'.$siteUrl.'/amp-permission-dialog.html?appId='.$appId.'" service-worker-url="'.$siteUrl.'/OneSignalSDKWorker.js?appId='.$appId.'" class="i-amphtml-element i-amphtml-layout-nodisplay" hidden i-amphtml-layout="nodisplay"></amp-web-push>';
    echo '<amp-install-serviceworker src="'.$siteUrl.'/OneSignalSDKWorker.js?appId='.$appId.'" data-iframe-src="'.$siteUrl.'/install_sw.html" layout="nodisplay" class="i-amphtml-element i-amphtml-layout-nodisplay" hidden i-amphtml-layout="nodisplay">';
  }
  add_action('wp_footer', 'pwa_add_footer_tags');

  /**
   * [pwa_insert_manifest] This function create the manifest template in json format
   * and tries to save the json file in root
  */
  function pwa_insert_manifest(){
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
    // $fp = fopen('manifest.json', 'w');
    // fwrite($fp, $jsonManifest);
    // fclose($fp);
  }

  /**
  	 * [pwa_sendMessage] Esta función se encarga de enviar una push notification cuando la casilla
  	 * de enviar push es activada en la creación de una publicación.
  	 * Solo se ejecuta en publish post
  	 * @param [string] $title The post title
     * @param [string] $link The post permalink
     * @param [string] $id The post ID
     * @param [string] $excet The post $excerpt
     * @param [string] $thumb The post thumbnail
   	 * @return [boolean] $response wheter or not the notification was sent
  	*/
  	function pwa_sendMessage($title, $link, $id, $excer, $thumb){
        $options = get_option('pwa_onesignal_option');
        $app_id = $options['pwa_appid_input_field'];
        $rest_api_key = $options['pwa_restapikey_input_field'];
  	    $mTitle = array("en" => $title);
  	    $fields = array(
  	        'app_id' => $app_id,
  	        'included_segments' => array('Testers'),
  	        'url' => $link,
  	        'contents' => $mTitle,
  	        'chrome_web_image' => $thumb
  	    );
  			if($excer!=null){
  				$mExcerpt = array("en" => $excer);
  				$fields['headings'] = $mTitle;
  				$fields['contents'] = $mExcerpt;
  			}
  	    $fields = json_encode($fields);
  	    $ch = curl_init();
  	    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
  	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  	        "Content-Type: application/json; charset=utf-8",
  	        "Authorization: Basic $rest_api_key"
  	    ));
  	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  	    curl_setopt($ch, CURLOPT_HEADER, FALSE);
  	    curl_setopt($ch, CURLOPT_POST, TRUE);
  	    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
  	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  	    $response = curl_exec($ch);
  	    curl_close($ch);
  	    return $response;
  	}//End pwa_sendMessage function

    /**
     * [pwa_post_published_notification] This function gets the post objet's info and send the notification by calling
     * @see pwa_sendMessage
     * @param [null]
     * @return [void]
     * This function is executed in publish post
    */
    function pwa_post_published_notification(){
		  global $post;
		  $doSendPush = (get_post_meta($post->ID, '_meta_eza_notifications', true)) ? get_post_meta($post->ID, '_meta_eza_notifications', true) : $_POST['_meta_eza_notifications'];
  		if($doSendPush){
  			$id = $post->ID;
  			$title = $post->post_title;
  			$excerpt = (has_excerpt($id)) ? $post->post_excerpt : null;
  			$link = get_the_permalink($post->ID);
  			$thumbnail = get_the_post_thumbnail_url($id, 'middle');
  			pwa_sendMessage($title, $link, $id, $excerpt, $thumbnail);
  		}
	  }
	  add_action( 'publish_post', 'post_published_notification', 10, 2 );
?>
