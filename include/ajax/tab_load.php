<?php
/*
 * Load tab information over AJAX
 */
NConf_DEBUG::set($_POST, 'DEBUG', "data send over POST");
if (!empty($_POST["id"])){
  // tab id
  $tab_id = $_POST["id"];
  $tab_info = db_templates("load_tab_info", $tab_id);
  if (!empty($tab_info)){
    echo '<div id="load_data">';
      echo json_encode($tab_info);
    echo '</div>';
  }
  
}