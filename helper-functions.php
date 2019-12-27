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
    wp_nonce_field(__FILE__, '_meta_pwa_notifications_nonce');
    echo "<label><input type='checkbox' name='_meta_pwa_notifications' value='send' $push_notification_check />Send Notification</label>";
  }

  /**
   * In this action the metabox for push notifications gets saved
  */
  add_action('save_post', function($post_id){
    if(!current_user_can('edit_page', $post_id)){
      return $post_id;
    }
    if(defined('DOING_AUTOSAVE' && DOING_AUTOSAVE)){
      return $post_id;
    }
    if(wp_is_post_revision($post_id)||wp_is_post_autosave($post_id)){
      return $post_id;
    }

    if(array_key_exists('_meta_pwa_notifications', $_POST) && check_admin_referer(__FILE__, '_meta_pwa_notifications_nonce')){
      update_post_meta($post_id, '_meta_pwa_notifications', $_POST['_meta_pwa_notifications']);
    }else if(!defined('DOING_AJAX')){
      delete_post_meta($post_id, '_meta_pwa_notifications');
    }
  });

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
   * add manifest link to header
  */
  function pwa_add_manifest_link(){
    $siteUrl = get_site_url();
    echo '<link rel="manifest" href="manifest.json">';
  }
  add_action('wp_head', 'pwa_add_manifest_link');

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
      try{
        if(!function_exists('curl_init')){
          return;
        }
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
      }catch(Exception $e){
        return $e;
      }
  	}//End pwa_sendMessage function

    /**
     * [pwa_post_published_notification] This function gets the post objet's info and send the notification by calling
     * @see pwa_sendMessage
     * @param [null]
     * @return [void]
     * This function is executed in publish post
    */
    function pwa_post_published_notification($post_id, $post){
      if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE){
        return;
      }
      if('auto-draft' === $post->post_status){
        return;
      }
      if('publish' === $post->post_status){
        $doSendPush = (get_post_meta($post->ID, '_meta_pwa_notifications', true)) ? true : false;
        if(array_key_exists('_meta_pwa_notifications', $_POST) && $doSendPush){
          $id = $post->ID;
          $title = $post->post_title;
          $excerpt = (has_excerpt($id)) ? $post->post_excerpt : null;
          $link = get_the_permalink($post->ID);
          $thumbnail = get_the_post_thumbnail_url($id, 'middle');
          pwa_sendMessage($title, $link, $id, $excerpt, $thumbnail);
        }
      }
	  }
	  add_action( 'publish_post', 'pwa_post_published_notification', 10, 2 );
    add_action( 'save_post', 'pwa_post_published_notification', 10, 3 );
?>
