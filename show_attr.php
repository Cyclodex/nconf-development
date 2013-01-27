<?php
require_once 'include/head.php';

?>
<!-- jQuery part -->
<script type="text/javascript">
    $(document).ready(function(){
        /* Sortable tabs */
        $("#tabs_sortable").sortable({
          update: function(event, ui) {
            $("#reorder_info").show("slow");
          },
          placeholder: "ui-state-highlight",
          forcePlaceholderSize: true,
          forceHelperSize: true,
          revert: true,
          items: "li",
          handle: "a.handle",
          sort: function(event, ui) {
                // change the height of the placeholder according to the moving tab
                ui.placeholder.height($("li.ui-sortable-helper").height());
            }
        });
       /* sortable attributes */
        $("#reorder_form table.sortable").sortable({
          items: 'tbody > tr',
          receive: function(e, ui){
            $(this).find("tbody").append(ui.item);   
          },
          update: function(event, ui) {
            $("#reorder_info").show("slow");
          },
          placeholder: "ui-state-highlight",
          handle: 'div.handle',
          forcePlaceholderSize: true,
          revert: true,
          connectWith: "#reorder_form table.sortable"
        });
        
        /* save reordering */
        $("#reorder").click( function() {
          $.post("call_file.php?ajax_file=tab_reorder.php&debug=yes", $("#reorder_form").serialize()
          , function(data){
              // this will move the debug information to current page
              $(data).nconf_ajax_debug();
              var service_id = $(data).filter("#clone_success").html();
              $(data).filter("#save_success").appendTo( $("#reordered_info_feedback") );
              $("#reorder_info").hide("slow");
              $(data).filter("#save_failed").appendTo( $("#reordered_info_feedback") );
          });
          return false;
        });
        
        
        /* DIALOG */
        var tab_title_input = $("#tab_title");
    
        // modal dialog init: custom buttons and a "close" callback reseting the form
        // make a copy for the modify, its the same as add with some differences
        var $dialog = $( "#dialog" ).dialog({
          autoOpen: false,
          title: 'Add Tab',
          modal: true,
          width: "350",
          buttons: {
            Add: function() {
              handleTab( $("#tab_add").serialize() );
            },
            Cancel: function() {
              $( this ).dialog( "close" );
            }
          },
          open: function() {
            tab_title_input.focus();
          },
          close: function(test) {
            var Form = $( "form", $dialog );
            $( "form", $dialog )[0].reset();
            $( ".modal_feedback_area", $dialog ).empty();
          }
        });
        // Update tab
        var $modifyDialog = $( "#modifydialog" ).dialog({
          autoOpen: false,
          title: 'Modify Tab',
          modal: true,
          width: "350",
          buttons: {
            Delete: function() {
              $("#action").val("delete");
              handleTab( $("#tab_update > input:hidden").serialize() );
            },
            Cancel: function() {
              $( this ).dialog( "close" );
            },
            Update: function() {
              $("#action").val("modify");
              handleTab( $("#tab_update").serialize() );
            }
          },
          open: function() {
            tab_title_input.focus();
          },
          close: function() {
            var modifyForm = $( "form", $modifyDialog );
            $( "form", $modifyDialog )[0].reset();
            $( ".modal_feedback_area", $modifyDialog ).empty();
          }
        });
        // Delete all tabs
        var $removetabsdialog = $( "#removedialog" ).dialog({
          autoOpen: false,
          title: 'Remove all Tabs',
          modal: true,
          width: "350",
          buttons: {
            Confirm: function() {
              handleTab( $("#tabs_delete").serialize() );
            },
            Cancel: function() {
              $( this ).dialog( "close" );
            }
          },
          open: function() {
            //tab_title_input.focus();
          },
          close: function(test) {
            var Form = $( "form", $dialog );
            $( "form", $dialog )[0].reset();
            $( ".remove_modal_feedback_area", $dialog ).empty();
          }
        });
        
        /* prevent sending form with ENTER */
        $("form").submit(function() {
          return false;
        })
    
        // add new tab or update existing tab
        // function will call a file over AJAX which saves the data into the DB
        function handleTab(data) {
          var modal_feedback_area = "div.ui-dialog-content:visible .modal_feedback_area";
          $(modal_feedback_area).children().hide("drop", 'slow', function() {
              $(this).remove();
          });
          $.post("call_file.php?ajax_file=tab_handle.php&debug=yes", data
          , function(data){
              $(data).filter("#ajax_error").appendTo( $(modal_feedback_area) ).addClass("ui-state-error ui-corner-all fg-error").hide().show("slow");
              $(modal_feedback_area).show("slow");
              // this will move the debug information to current page
              $(data).nconf_ajax_debug();
              $(data).filter("#save_success").appendTo( $(modal_feedback_area) ).addClass("ui-state-highlight ui-corner-all fg-error");
              // check if it there were no errors before reloading
              if (!$(data).is("#ajax_error")){
                location.reload();
              }
          });
        }
    
        /* TAB ADD */
        $( "#add_tab" )
          .button()
          .click(function() {
            $dialog.dialog( "open" );
          });
          
        /* REMOVE ALL TABS */
        $( "#remove_all_tabs" )
          .button()
          .click(function() {
            $removetabsdialog.dialog( "open" );
          });
        
        /* TAB MODIFY */
        $("#tabs_sortable .tab_modify").click(function() {
          var tab_id = $(this).parent().next('input:hidden[name="tab_ordering[]"]').val();
          $.post("call_file.php?ajax_file=tab_load.php&debug=yes", { id: tab_id}
          , function(data){
              var tab_info = jQuery.parseJSON($(data).filter("#load_data").html());
              
              $(data).filter("#ajax_error").appendTo( $("#modify_modal_feedback_area") ).addClass("ui-state-error ui-corner-all fg-error").hide().show("slow");
              $("#modify_modal_feedback_area").show("slow");
              // this will move the debug information to current page
              $(data).nconf_ajax_debug();
              $(data).filter("#save_success").appendTo( $("#modify_modal_feedback_area") ).addClass("ui-state-highlight ui-corner-all fg-error");
              
              // add tab id info
              $("#tab_id").val(tab_info.id_tab);
              // fill the input fields with loaded data
              $("#tab_name_update").val(tab_info.tab_name);
              $modifyDialog.dialog( "option", "title", 'Modify Tab "' + tab_info.tab_name + '"' );
              $("#tab_description_update").val(tab_info.description);
              if (tab_info.visible == "no"){
                $("#tab_visible_update").removeAttr("checked");
              }else{
                $("#tab_visible_update").attr('checked','checked');
              }
              
              // change title of dialog
              $modifyDialog.dialog( "open" );
          });
        });
    
       /* scroll to position */
      /* perhaps a later improvement
       $('html, body').animate({
            scrollTop: $('a.handle[name="7"]').position().top
       }, 2000);
       */
    });

</script>

<?php

// Form action and url handling
$request_url = set_page();

// Delete Cache of modify (if still exist)
if ( isset($_SESSION["cache"]["modify_attr"]) ) unset($_SESSION["cache"]["modify_attr"]);

// set info in the footer when naming attribute changes have done (from modify attribute)
if ( isset($_GET["naming_attr"]) ){
    if ($_GET["naming_attr"] == "changed"){
        message($info, TXT_NAMING_ATTR_CHANGED);
    }elseif ($_GET["naming_attr"] == "last"){
        message($info, TXT_NAMING_ATTR_LAST);
    }

    // Remove it from url, so that formular dont takes this get variable all around
    $request_url = preg_replace("/&naming_attr=last/", "", $request_url);
    $request_url = preg_replace("/&naming_attr=changed/", "", $request_url);
}




# Filters


if ( isset($_POST["os"]) ) {
    $filter_os = $_POST["os"];
}else{
    $filter_os = "";
}



# Show class selection
$show_class_select = "yes";


if ( isset($_GET["class"]) ) {
    $class = $_GET["class"];
}else{
    $class = "host";
}



// Page output begin

echo NConf_HTML::title('Show attributes: '.$class);

$content = 'This mask allows administrators to modify the data schema of the NConf application.
            There is no need to make any changes to the schema for ordinary operation.
            Users are strictly discouraged from changing any attribute names, datatypes, from modifying classes in any way, 
            and from any other changes to the schema.
            Disregarding this may result in unexpected behavour of the application, failure to generate the Nagios configuration properly 
            and may under certain circumstances <b>result in data corruption or loss!</b>';

$reorder_info =
  '<div id="reorder_info" style="display: none">'
    .NConf_HTML::show_highlight('Reordered List', 'The list order has changed, please save if you are finished moving items.'
      .'<br><button id="reorder">Save reordering</button>'
      .'<div id="reordered_info_feedback"></div>'
    )
 .'</div>';
echo NConf_HTML::limit_space(
    NConf_HTML::show_error('WARNING', $content).$reorder_info
    , 'style="float: right; width: 555px;"'
);

echo '<form name="filter" action="'.$request_url.'" method="get">
<fieldset class="inline">
<legend>Select class</legend>
      <table>';


// Class Filter
if ( isset($show_class_select) ){
    echo '<tr>';
        $result = db_handler('SELECT config_class FROM ConfigClasses ORDER BY config_class', "result", "Get Config Classes");

    echo '</tr>';
    echo '<tr>';
        echo '<td><select name="class" style="width:192px" onchange="document.filter.submit()">';
        //echo '<option value="">'.SELECT_EMPTY_FIELD.'</option>';

        while($row = mysql_fetch_row($result)){
            echo "<option value=$row[0]";
            if ( (isset($class) ) AND ($row[0] == $class) ) echo " SELECTED";
            echo ">$row[0]</option>";
        }

        echo '</select>
            </td>';
    echo '</tr>';
}


echo '
</table>
</fieldset>';

echo '</form>';

$class_ID   = db_templates('get_id_of_class', $class);

/* Create new tab */
echo '
  <div id="dialog" title="Add new tab">
    <form id="tab_add">';
        echo '<input type="hidden" name="class_id" value="'.$class_ID.'">';
        echo '
        <label for="tab_name">Title</label>
        <input type="text" name="tab_name" id="tab_name" value="" class="span4 ui-widget-content ui-corner-all" /><span class="help-inline mark_as_mandatory">*</span>
        <label for="tab_description">Description</label>
        <textarea name="tab_description" id="tab_description" class="span4 ui-widget-content ui-corner-all"></textarea>
        <label class="checkbox" for="tab_visible">Visible
          <input type="checkbox" id="tab_visible" name="tab_visible" checked=checked>
        </label>
        <div id="modal_feedback_area" class="modal_feedback_area"></div>
    </form>
  </div>';

/* Modify tab */
echo '
  <div id="modifydialog" title="Modify tab">
    <form id="tab_update">';
        echo '<input type="hidden" name="class_id" value="'.$class_ID.'">';
        echo '
        <label for="tab_name">Title</label>
        <input type="text" name="tab_name" id="tab_name_update" value="" class="span4 ui-widget-content ui-corner-all" /><span class="help-inline mark_as_mandatory">*</span>
        <label for="tab_description">Description</label>
        <textarea name="tab_description" id="tab_description_update" class="span4 ui-widget-content ui-corner-all"></textarea>
        <label class="checkbox" for="tab_visible">Visible
          <input type="checkbox" id="tab_visible_update" name="tab_visible" checked=checked>
        </label>
        <input type="hidden" name="tab_id" id="tab_id" value="">
        <input type="hidden" name="action" id="action" value="modify">
        <div id="modify_modal_feedback_area" class="modal_feedback_area"></div>
    </form>
  </div>';
/* Delete all tabs */
echo '
  <div id="removedialog" title="Remove all tabs">
    <form id="tabs_delete">';
        echo 'Are you sure you want to remove all tabs?<br>The attributes will NOT be removed.';
        echo '<input type="hidden" name="class_id" value="'.$class_ID.'">';
        echo '<input type="hidden" name="action" id="action" value="deletealltabs">
        <div id="delete_modal_feedback_area" class="modal_feedback_area"></div>
    </form>
  </div>';  
  
echo '<br>';  
echo '<button id="add_tab">Add Tab</button>';
echo '<button id="remove_all_tabs">Remove all Tabs</button>';
  

echo '<form id="reorder_form" action="#" method="get">';
echo '<input type="hidden" name="class_id" value="'.$class_ID.'">';
echo '<div class="clearer"></div>';



// Attr manipulation
if ( !empty($_GET["do"]) AND !empty($_GET["id"]) ){
    if ($_GET["do"] == "up"){
        attr_order($_GET["id"], "up");
    }elseif($_GET["do"] == "down"){
        attr_order($_GET["id"], "down");
    }
        
}
    
    $result = db_templates('get_attributes_from_class_with_tab_info', $class_ID);
    
    // Table beginning will be added in the output function
    $table_header  = '';
    $tabs_header   = '';
    

    if ($result != "") {

        // Create table header template, which is used for each tab
        $table_header .= '<thead class="ui-widget-header">';
        $table_header .= '<tr><th>';
            $table_header .= '<div style="width:30px">&nbsp;</div>';
            $table_header .= '<div style="width:170px">Attribute Name</div>';
            $table_header .= '<div style="width:170px">Friendly Name</div>';
            $table_header .= '<div style="width:100px">Datatype</div>';
            $table_header .= '<div style="width:70px" class="center">Mandatory</div>';
            $table_header .= '<div style="width:60px" class="center">Ordering</div>';
            $table_header .= '<div style="width:50px" class="center">PK</div>';
            $table_header .= '<div style="width:40px" class="center">Edit</div>';
            $table_header .= '<div style="width:40px" class="center">Delete</div>';

        $table_header .= "</th></tr>";
        $table_header .= '</thead>';

        $table_header .= '<tbody class="ui-widget-content">';


        $count = 1;
        $naming_attr_count = 0;
        
        $last_tab_id = '';
        while($entry = mysql_fetch_assoc($result)){
          
          $tab_disabled = ($entry["visible"] == "no" ) ? 'class="disabled"' : '';
          // Check tabs!
          if (!empty($entry["id_tab"]) ){
            if (empty($last_tab_id)){
              // create first tab if no tab was done yet
              $tabs_header .= '<ul id="tabs_sortable"><li '.$tab_disabled.'>';
              $tabs_header .= '<h3 class="ui-widget-header ui-corner-top fg_tab">';
                $tabs_header .= '<a href="#" class="handle" name="'.$entry["id_tab"].'" title="Drag to re-order">'
                    .'<div class="draggable"></div>'
                    .'</a>';
                $tabs_header .= '<span class="jQ_tooltip lighten tab_modify" title="Click to modify tab">';
                $tabs_header .= !empty($entry["tab_name"]) ? $entry["tab_name"] : 'undefined';
                $tabs_header .= '</span>';
                //$tabs_header .= '<div id="ui-nconf-icon-bar">'.ICON_EDIT.'</div>';
              $tabs_header .= '</h3>';
              // remove button for later 
              // $tabs_header .= '<span class="ui-icon ui-icon-close">Remove Tab</span>';
              $tabs_header .= '<input type="hidden" name="tab_ordering[]" value="'.$entry["id_tab"].'">';
              $tabs_header .= '<input type="hidden" name="ordering[][tab]" value="'.$entry["id_tab"].'">';
              
              $last_tab_id = $entry["id_tab"];
            }elseif ( $last_tab_id != $entry["id_tab"]){
              // create new tab if it differs from the previous attribute
              $table .= '</tbody>';
              $tab_content_table = NConf_HTML::ui_table($table_header . $table, 'ui-nconf-max-width sortable');
              echo $tabs_header . $tab_content_table;
              
              $last_tab_id = $entry["id_tab"];
              
              // reset table
              $table = '';
              $tabs_header = '';
              
              // Create new tab
              $tabs_header .= '</li><li '.$tab_disabled.'>';
              $tabs_header .= '<h3 class="ui-widget-header ui-corner-top fg_tab">';
                $tabs_header .= '<a href="#" class="handle" name="'.$entry["id_tab"].'" title="Drag to re-order">'
                    .'<div class="draggable"></div>'
                    .'</a>';
                $tabs_header .= '<span class="jQ_tooltip lighten tab_modify" title="click to modify tab">';
                $tabs_header .= !empty($entry["tab_name"]) ? $entry["tab_name"] : 'undefined';
                $tabs_header .= '</span>';
              $tabs_header .= '</h3>';
              $tabs_header .= '<input type="hidden" name="tab_ordering[]" value="'.$entry["id_tab"].'">';
              $tabs_header .= '<input type="hidden" name="ordering[][tab]" value="'.$entry["id_tab"].'">';
            }
          }
          
          
          $row_warn = 0;
          if ($entry["naming_attr"] == "yes"){
            $naming_attr_count++;
            $pre = "<b>";
            $fin = "</b>";
            $naming_attr_cell = SHOW_ATTR_NAMING_ATTR;
            if ($naming_attr_count > 1){
                $row_warn = 1;
                message($info, TXT_NAMING_ATTR_CONFLICT);
                $naming_attr_cell .= SHOW_ATTR_NAMING_ATTR_CONFLICT;
            }
			      $additional_class = " color_nomon";
          }else{
            $pre = "";
            $fin = "";
            $naming_attr_cell = "&nbsp;";
				    $additional_class = "";
          }

          // Show datatype icons 
          switch ($entry["datatype"]){
            case "text":
                $ICON_datatype = SHOW_ATTR_TEXT;
            break;
            case "password":
                $ICON_datatype = SHOW_ATTR_PASSWORD;
            break;
            case "select":
                $ICON_datatype = SHOW_ATTR_SELECT;
            break;
            case "assign_one":
                $ICON_datatype = SHOW_ATTR_ASSIGN_ONE;
            break;
            case "assign_many":
                $ICON_datatype = SHOW_ATTR_ASSIGN_MANY;
            break;
				  case "assign_cust_order":
                    $ICON_datatype = SHOW_ATTR_ASSIGN_CUST_ORDER;
  				break;
  				default:
  					$ICON_datatype = '';
  				break;
              }
  
          // Show mandatory icons 
          switch ($entry["mandatory"]){
              case "yes":
                  $ICON_mandatory = ICON_TRUE_RED;
              break;
              case "no":
              default:
                  $ICON_mandatory = ICON_FALSE_SMALL;
              break;
          }
  			
    			// highlight moved row
    			if ( !empty($_GET["do"]) AND !empty($_GET["id"]) ){
    				if ( $entry["id_attr"] == $_GET["id"]){
    					$additional_class .= " ui-state-highlight";
    				}
    			}

          if ( !empty($entry["id_attr"]) ){

            // set list color
            if ($row_warn == 1){
                $table .= '<tr class="ui-state-error highlight">';
            }elseif((1 & $count) == 1){
                $table .= '<tr class="color_list1 highlight '.$additional_class.'">';
            }else{
                $table .= '<tr class="color_list2 highlight '.$additional_class.'">';
            }
            
            $table .= '<td>';
                $table .= '<div style="width:30px">'.$ICON_datatype.'</div>';
                $table .= '<div style="width:170px" class="table_text">'.$pre.'<a href="detail_admin_items.php?type=attr&class='.$class.'&id='.$entry["id_attr"].'">'.$entry["attr_name"].'</a>'.$fin.'</div>';
                $table .= '<div style="width:170px" class="table_text">'.$pre.$entry["friendly_name"].$fin.'</div>';
                $table .= '<div style="width:100px" class="table_text">'.$pre.$entry["datatype"].$fin.'</div>';
                $table .= '<div style="width:70px" align="center"><div align=center>'.$ICON_mandatory.'</div></div>';
                // Ordering is good for debbuging
                //$table .= '<div>'.$pre.$entry["ordering"].$fin.'</div>';
                $table .= '<div style="width: 60px" class="center table_icon">
                            <div class="handle center">
                              <span class="ui-icon handle ui-icon-arrowthick-2-n-s" style="margin: 0 auto"></span>
                              <input type="hidden" name="ordering[]" value="'.$entry["id_attr"].'">
                              </div>
                            </div>';
                $table .= '</div>';
                $table .= '<div style="width:50px" class="center">'.$naming_attr_cell.'</div>';
                $table .= '<div style="width:40px" class="center table_icon"><a href="modify_attr.php?id='.$entry["id_attr"].'">'.ICON_EDIT.'</a></div>';
                $table .= '<div style="width:40px" class="center table_icon"><a href="delete_attr.php?id='.$entry["id_attr"].'">'.ICON_DELETE.'</a></div>';
            $table .= "</td></tr>\n";
          }

          $count++;
          
          
          
        } // END of while
        
        // Warn if there is no naming attribute
        if ($naming_attr_count == 0){
            message($info, TXT_NAMING_ATTR_MISSED);
        }

        // Close last table
        $table .= '</tbody>';
        
        // print last tab with last table
        echo $tabs_header;
        echo NConf_HTML::ui_table($table_header . $table, 'ui-nconf-max-width sortable');
        echo '</li>';
        echo '</ul>';
        
        
    } // End of if results != ''





echo '</form>';

mysql_close($dbh);
require_once 'include/foot.php';

?>
