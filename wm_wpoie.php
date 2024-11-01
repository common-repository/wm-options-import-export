<?php
/**
 * Plugin Name: WM Options Import Export
 * Plugin URI: http://plugins.web-mumbai.com/
 * Description: Wordpress Options Import Export.
 * Version: 1.0.1
 * Author: Web Mumbai
 * Author URI: http://plugins.web-mumbai.com/
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Last Update Date: 3, January 2016
 */ 

if(!class_exists('wm_wpoie')){
	class wm_wpoie{
		
		var $vars	= array();
		
		function __construct($vars = array()){
			
			$this->vars					= $vars;
			
			$this->vars['is_admin'] 	= is_admin();
			add_action('plugins_loaded', array($this, 'plugins_loaded'));
			
		}//End Construct
		
		function plugins_loaded(){
			if($this->vars['is_admin']){
				$this->vars['__FILE__'] 	= __FILE__;
				$this->vars['plugin_role'] 	= 'manage_options';
				$this->vars['plugin_key'] 	= 'wmwpoie';
				$this->vars['admin_page'] 	= isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
				require_once('includes/wm_wpoie_admin.php');
				$o = new wm_wpoie_admin($this->vars);
				add_filter( 'plugin_action_links_wm-options-import-export/wm_wpoie.php', array( $this, 'plugin_action_links' ), 51, 2 );
			}
		}
		
		function plugin_action_links($plugin_links = array(), $file = ""){
			$plugin_links[] = '<a href="'.admin_url('tools.php?page='.$this->vars['plugin_key'].'_export').'" title="'.__('Export Options','wm_wpoie').'">'.__('Export Options','wm_wpoie').'</a>';
			return $plugin_links;
		}
		
	}//End Class
}

$wm_wpoie = new wm_wpoie();