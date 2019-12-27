<?php
  require_once($_SERVER['DOCUMENT_ROOT'].'/plugin_construction/wp-load.php');
  // header("Content-type: application/json; charset=utf-8");
  // header('Content-Disposition: attachment; filename="manifest.json"');

  $background_color = $_POST['bg_color'];
  $theme_color = $_POST['th_color'];
  $short_name = $_POST['short_name'];
  $manifest = array(
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
  file_put_contents("../../../manifest.json", $jsonManifest);

  header("Location: {$_SERVER['HTTP_REFERER']}");
  exit;
?>
