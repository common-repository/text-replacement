<?php
/*
Plugin Name: Text Replacement
Plugin URI: http://www.horttcore.de/wordpress/Text-Replacement/
Description: Replaces some texts
Version: 1.1.2
Author: Ralf Hortt
Author URI: http://www.horttcore.de/


	Thank you ciaran29d and JEG2 from #textmate
	I really hate regex ;-)

*/

load_plugin_textdomain('text-replacement', '' , 'text-replacement');

//======================================
// Description: Management Page
function tr_management(){
global $wpdb;
	if ($_POST){
		tr_save_rules();
	}
	$rules = get_replacement_rules();
	$i = 1;
	?>
	<div class="wrap">
		<h2><?php _e('Text Replacement')?></h2>
		<form method="post">
			<table class="form-table">
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col"><?php _e('Search','text-replacement')?></th>
						<th scope="col"><?php _e('Replace','text-replacement')?></th>					
						<th scope="col"><?php _e('the_content','text-replacement')?></th>					
						<th scope="col"><?php _e('the_excerpt','text-replacement')?></th>					
						<th scope="col"><?php _e('the_title','text-replacement')?></th>					
					</tr>
				</thead>
				<tbody class="sort">
			<?php
				if (is_array($rules)){
					foreach($rules as $rule) {?>
						<tr class="drag">
							<th scope="row"><?php echo $i; ?></th>
							<td><input name="replace_<?php echo $i; ?>[]" id="replace_<?php echo $i; ?>" type="text" size="35" value="<?php echo stripslashes($rule['search']) ?>" /></td>
							<td><input name="replace_<?php echo $i; ?>[]" id="rule_<?php echo $i; ?>" type="text" size="35" value="<?php echo stripslashes($rule['replace']) ?>" /></td>
							<td style="text-align: center;"><input name="replace_<?php echo $i; ?>[]" id="rule_<?php echo $i; ?>" type="checkbox" value="the_content" <?php if ($rule['the_content']) echo 'checked="checked"' ?> /></td>
							<td style="text-align: center;"><input name="replace_<?php echo $i; ?>[]" id="rule_<?php echo $i; ?>" type="checkbox" value="the_excerpt" <?php if ($rule['the_excerpt']) echo 'checked="checked"' ?> /></td>
							<td style="text-align: center;"><input name="replace_<?php echo $i; ?>[]" id="rule_<?php echo $i; ?>" type="checkbox" value="the_title" <?php if ($rule['the_title']) echo 'checked="checked"' ?> /></td>
						</tr>
						<?php
						$i++;
					}
				}
			?>
			<tr style="padding: 0 0 0 50px;">
				<th scope="row"><?php echo $i; ?></th>
				<td><input name="replace_<?php echo $i; ?>[]" id="replace_<?php echo $i; ?>" type="text" size="35" /></td>
				<td><input name="replace_<?php echo $i; ?>[]" id="rule_<?php echo $i; ?>" type="text" size="35" /></td>
				<td style="text-align: center;"><input name="replace_<?php echo $i; ?>[]" id="rule_<?php echo $i; ?>" checked="checked" type="checkbox" value="the_content" /></td>
				<td style="text-align: center;"><input name="replace_<?php echo $i; ?>[]" id="rule_<?php echo $i; ?>" type="checkbox" value="the_excerpt" /></td>
				<td style="text-align: center;"><input name="replace_<?php echo $i; ?>[]" id="rule_<?php echo $i; ?>" type="checkbox" value="the_title" /></td>
			</tr>
			</tbody>
			</table>
			<p class="submit"><button class="button" type="submit"><?php _e('Save')?></button></p>
		</form>
		<p><?php _e('<strong>Constants:</strong> ','text-replacement');
		$constants = tr_constant();
		foreach($constants as $constant) echo $constant." "; ?>
		</p>
	</div>
	<?php
}

//======================================
// Description: Saving Text-Replacement Rules
function tr_save_rules(){
	$i = 0;
	foreach($_POST as $rule) {
		if ($rule[0]) {
			$search = str_replace('"',"'",$rule[0]);
			$replace = str_replace('"',"'",$rule[1]);
			for($o=2;$o<5;$o++) {
				if ($rule[$o] == 'the_content') $the_content = 'the_content'; 
				if ($rule[$o] == 'the_excerpt') $the_excerpt = 'the_excerpt';  			
				if ($rule[$o] == 'the_title') $the_title = 'the_title';
			}
			$rules[$i] = array('search' => $search, 'replace' => $replace, 'the_content' => $the_content, 'the_excerpt' => $the_excerpt, 'the_title' => $the_title);
			unset($the_title);unset($the_content);unset($the_excerpt);
			$i++;
		}
	}
	#$rules = serialize($rules);
	update_option('tr_rules', $rules);
	?><div class="updated"><p><?php _e('Saved Replacement Rules','text-replacement')?></p></div><?php
}

//======================================
// Description: Beschreibung
// Return: $rules obj Object with all replacement rules
function get_replacement_rules(){
	$rules = get_option('tr_rules');
	$version = apply_filters( 'update_footer', '');
	if (preg_match('&2.5&',$version)) { // deprected in WP 2.6
		$rules = unserialize($rules); 
	}
	$return = ($rules) ? $rules : $return = array();
	return $return;
}

//======================================
// Description: Run the replacement rules 
// Require: $text
function tr_filter_content($text){
	$replace = tr_constant('key');
	$replace_by = tr_constant('value');
	
	$rules = get_replacement_rules();
	foreach($rules as $rule) {
		
		if (in_array('the_content',$rule)) {
			$rule['replace'] = str_replace($replace, $replace_by, $rule['replace']);
			$text = preg_replace("/".$rule['search']."(?![^<]*>)/", stripslashes($rule['replace']), $text);
		}
	}
	return $text;
}

//======================================
// Description: Run the replacement rules 
// Require: $text
function tr_filter_excerpt($text){
	$replace = tr_constant('key');
	$replace_by = tr_constant('value');
	
	$rules = get_replacement_rules();
	foreach($rules as $rule) {
		if (in_array('the_excerpt',$rule)) {
			$rule['replace'] = str_replace($replace, $replace_by, $rule['replace']);
			$text = preg_replace("/".$rule['search']."(?![^<]*>)/", stripslashes($rule['replace']), $text);
		}
	}
	return $text;
}

//======================================
// Description: Run the replacement rules 
// Require: $text
function tr_filter_title($text){
	$replace = tr_constant('key');
	$replace_by = tr_constant('value');
	
	$rules = get_replacement_rules();
	foreach($rules as $rule) {
		if (in_array('the_title',$rule)) {
			$rule['replace'] = str_replace($replace, $replace_by, $rule['replace']);
			$text = preg_replace("/".$rule['search']."(?![^<]*>)/", stripslashes($rule['replace']), $text);
		}
	}
	return $text;
}

//======================================
// Description: Default constants
// Param: $return str 'key' / 'value' 
function tr_constant($return = 'key'){
	$constants['SITEURL'] = get_option('siteurl');	
	$constants['TEMPLATE_URL'] = get_bloginfo('stylesheet_directory');	
	$constants['UPLOAD_PATH'] = get_option('upload_path');
	$constant = array();
	
	foreach ($constants as $key => $value) {
		#$value = str_replace('../', '', $value);
		if ($return == 'value') {
			array_push($constant, $value);
		}
		else {
			array_push($constant, $key);
		}
	}
	return $constant;
}

//======================================
// Description: Beschreibung
// Require: 
// Param: 
function tr_adminhead(){?>
	<style type="text/css">
		.form-table tbody th:hover {
			cursor: move;
		}
	</style>
	<script type="text/javascript" src="<?php bloginfo('url') ?>/<?php echo PLUGINDIR ?>/text-replacement/jquery-ui-personalized-1.5.2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function(){
		    jQuery(".sort").sortable({});
		  });		
	</script><?php
}


//======================================
// Description: Install Plugin
function tr_install(){
	
}

//======================================
// Description: Install Plugin
function tr_deinstall(){
	delete_option('tr_rules');
}

//======================================
// Description: Adding Menu
function tr_adminmenu(){
	add_management_page(__('Text-Replacement','text-replacement'), __('Text-Replacement','text-replacement'), '7', __('Text-Replacement','text-replacement'), 'tr_management');
}

add_action('admin_menu', 'tr_adminmenu');
#if ($_GET['page'] == 'Text-Replacement') {add_action('admin_head', 'tr_adminhead');}
add_filter('the_content','tr_filter_content');
add_filter('the_excerpt','tr_filter_excerpt');
add_filter('the_title','tr_filter_title');
#register_activation_hook(__FILE__, 'tr_install');
#register_deactivation_hook(__FILE__, 'tr_deinstall');
?>