<?php
/*
 * Handle tab over AJAX
 * - Add new tabs
 * - Update / modify existing tabs
 * - Delete tabs
 */
// some helpers
$failed = 0;
$successfull = FALSE;

NConf_DEBUG::set($_POST, 'DEBUG', "data send over POST");
// update the ordering
$name = escape_string($_POST["tab_name"]);
$description = ( !empty($_POST["tab_description"]) ) ? escape_string($_POST["tab_description"]) : NULL;
$visible = ( !empty($_POST["tab_visible"]) ) ? "yes" : "no";
$class_ID = escape_string($_POST["class_id"]);
$tab_ID = ( !empty($_POST["tab_id"]) ) ? escape_string($_POST["tab_id"]) : NULL;
$action = ( !empty($_POST["action"]) ) ? $_POST["action"] : NULL;

if ( !empty($_POST["tab_name"]) ){
  if ( empty($_POST["class_id"]) ){
    NConf_DEBUG::set("Missing class", 'ERROR', "Error");
  }
  
  // update tab
  if ( $action == "modify" AND !empty($tab_ID) ){
    // modify & tab ID exists, save it
    $tab_update_query = 'UPDATE ConfigTabs '
                        .'SET tab_name = "'.$name.'", '
                            .'description = "'.$description.'", '
                            .'visible = "'.$visible.'" '
                        .'WHERE id_tab = "'.$tab_ID.'"'
                        .' AND fk_id_class = "'.$class_ID.'"';
    if (!db_handler($tab_update_query, "insert", "Update Tab $name") ){
      NConf_DEBUG::set("Could not update tab", 'ERROR', "Failed");
    }
  }
  // add tab
  elseif (db_templates('class_has_tabs', $class_ID)){
    // Check if class has existing tabs
    // get last tab id of this class
    $tab_max_ordering_query = 'SELECT MAX(ordering) FROM ConfigTabs WHERE fk_id_class = '.$class_ID;
    $tab_max_ordering = db_handler($tab_max_ordering_query, "getOne", "Select the last ordering of this class ($class_ID)");
    if ($tab_max_ordering){
      // Found ID, increase one and add new tab
      $ordering = $tab_max_ordering + 1;
      // Save new tab
      $tab_add_query = 'INSERT INTO `ConfigTabs` (`tab_name`, `description`, `ordering`, `visible`, `fk_id_class`) VALUES ("'.$name.'", "'.$description.'", "'.$ordering.'", "'.$visible.'", "'.$class_ID.'")';
      if (!db_handler($tab_add_query, "insert", "INSERT Tab $name") ){
        NConf_DEBUG::set("Could not add new tab", 'ERROR', "Failed");
      }
      
    }else{
      // No max ordering found looks like class has no 
      NConf_DEBUG::set("Class seems to have tabs but could not check ordering", 'ERROR', "Error");
    }
  }else{
    // No tabs yet for this class. Create first and move all arguments into it.
    $tab_ordering = 1;
    
    // Get list of attributes before creating first tab
    $class_attributes_result = db_templates('get_attributes_from_class_with_tab_info', $class_ID);
    
    // Save new tab for this class
    $tab_add_query = 'INSERT INTO `ConfigTabs` (`tab_name`, `description`, `ordering`, `visible`, `fk_id_class`) VALUES ("'.$name.'", "'.$description.'", "'.$tab_ordering.'", "'.$visible.'", "'.$class_ID.'")';
    NConf_DEBUG::set($tab_add_query, 'DEBUG', "executing add tab query");
    if (!db_handler($tab_add_query, "insert", "INSERT Tab $name") ){
      NConf_DEBUG::set("Could not add new tab", 'ERROR', "Failed");
    }else{
      $tab_id = mysql_insert_id();
      // Update all attributes to be part of this 1st tab
      $attr_order = 0;
      while($attr = mysql_fetch_assoc($class_attributes_result)){
        $attr_order++;
        NConf_DEBUG::set($attr, 'DEBUG', "Attribute info");
        $query = 'UPDATE ConfigAttrs SET ordering="'.($attr_order).'", fk_id_tab="'.$tab_id.'" WHERE id_attr = '.$attr["id_attr"].' AND fk_id_class='.$class_ID;
        NConf_DEBUG::set($query, 'DEBUG', "executing query");
        if (!db_handler($query, "update", 'UPDATE: save attribute '.$attr["friendly_name"].' ('.$attr["attr_name"].') on position '.$attr_order.' in new tab with id'.$tab_id) ){
          NConf_DEBUG::set("save attributes in tab", 'ERROR', "Failed");
          $failed++;
        }else{
          $successfull = TRUE;
        }
               
      }
      NConf_DEBUG::set($successfull, 'DEBUG', "Successfull check");
      NConf_DEBUG::set($failed, 'DEBUG', "Amount of failed queries");
      // Check if everything was fine and give feedback
      if ($successfull AND $failed === 0){
        echo '<div id="save_success">';
          echo "Saved successfully...";
        echo '</div>';
      }else{
        NConf_DEBUG::set("check previous error messages for reason", 'ERROR', "Error");
      }
           
         
    } 
      
  }
  
}elseif( ($action == "delete") AND !empty($tab_ID) ){
  // No tab name but ID, means delete it
  // Count amount of attributes still linked with this tab
  $attributes_on_tab = 'SELECT COUNT(id_attr)
                          FROM ConfigAttrs
                          WHERE `fk_id_tab` ="'.$tab_ID.'"';
  $attributes_on_tab_count = db_handler($attributes_on_tab, "getOne", "Count still linked attributes on this tab");
  if ($attributes_on_tab_count != 0){
    // ask and move the other attributes
    NConf_DEBUG::set("You have to remove all attributes from this tab before you can delete the tab!", 'ERROR', "Failed");
  }else{
    // delete tab
    $del_tab_query = 'DELETE FROM ConfigTabs WHERE id_tab = "'.$tab_ID.'"';
    if (!db_handler($del_tab_query, "delete", "DELETE Tab $name") ){
      NConf_DEBUG::set("Could not delete tab $name", 'ERROR', "Failed");
    }
  }
  
}else{
  NConf_DEBUG::set("Missing tab title", 'ERROR', "Error");
}
            
