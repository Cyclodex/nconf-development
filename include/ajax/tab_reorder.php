<?php
/*
 * Save ordering of items called over AJAX
 */
NConf_DEBUG::set($_POST, 'DEBUG', "data send over POST");
if (!empty($_POST["ordering"]) AND !empty($_POST["class_id"])){
  // update the ordering
  $class_ID = $_POST["class_id"];
  
  $tab_index = 0;
  $tab_id = '';
  // check if list contains tabs
  if ( !empty($_POST["tab_ordering"]) ){
    // go thru each tab
    foreach ($_POST["ordering"] as $attr_order => $attr_id) {
      if ( is_array($attr_id) AND isset($attr_id["tab"]) ){
        NConf_DEBUG::set($attr_id["tab"], 'DEBUG', 'Tab with id');
        $tab_id = $attr_id["tab"];
        $tab_index++;
        
        // Save tab order
        $tab_query = 'UPDATE ConfigTabs SET ordering='.$tab_index.' WHERE id_tab = '.$tab_id.' AND fk_id_class='.$class_ID;
        if (!db_handler($tab_query, "update", "UPDATE: tab order") ){
          NConf_DEBUG::set("save order of tab", 'ERROR', "Failed");
        }
        continue;
      }elseif ( is_numeric($attr_id) ){
      // go thru the list of containing attrs
        $query = 'UPDATE ConfigAttrs SET ordering="'.($attr_order + 1).'", fk_id_tab="'.$tab_id.'" WHERE id_attr = '.$attr_id.' AND fk_id_class='.$class_ID;
        if (!db_handler($query, "update", "UPDATE: save ordering of item $attr_id") ){
          NConf_DEBUG::set("save reordered items", 'ERROR', "Failed");
          NConf_DEBUG::set($query, 'DEBUG', "udpate query");
        }
      }
    }
  }else{
    // save reordering without tabs
    db_handler('SELECT id_attr, ordering, fk_id_class FROM ConfigAttrs WHERE fk_id_class='.$class_ID, "array", "GET order list");
    foreach ($_POST["ordering"] as $attr_order => $attr_id) {
      if ( is_numeric($attr_id) ){
        // go thru the list of containing attrs
        $query = 'UPDATE ConfigAttrs SET ordering="'.($attr_order + 1).'" WHERE id_attr = '.$attr_id.' AND fk_id_class='.$class_ID;
        if (!db_handler($query, "update", "UPDATE: save ordering of item $attr_id") ){
          NConf_DEBUG::set("save reordered items", 'ERROR', "Failed");
          NConf_DEBUG::set($query, 'DEBUG', "udpate query");
        }
      }
    }
    
  }
  // OK
  echo '<div id="save_success">';
  echo '</div>';
  
}