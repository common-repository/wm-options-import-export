<?php

if(!class_exists('wm_wpoie_admin')){
	class wm_wpoie_admin{
		
		var $vars	= array();
		
		function __construct($vars = array()){
			
			$this->vars	= $vars;
			add_action('admin_menu', 				array($this, 'admin_menu'));
			add_action( 'admin_init', 				array( $this, 'register_importer' ) );
			$admin_page = $this->vars['admin_page'];
			if($admin_page == $this->vars['plugin_key']."_export"){
				add_action('admin_init', 				array($this, 'admin_init'));
				add_action('admin_enqueue_scripts', 	array($this, 'admin_enqueue_scripts'));
			}
			
			
			
		}//End Construct
		
		function admin_menu(){
			add_management_page(__( 'Export Wordpress Options', 'wm_wpoie' ),	__( 'Export Options','wm_wpoie' ),$this->vars['plugin_role'],$this->vars['plugin_key'].'_export',array( $this, 'admin_pages' ));
		}
		
		function admin_init(){
			if(isset($_REQUEST['export_options'])){
				require_once("wm_wpoie_admin_options_export.php");
				$export = new wm_wpoie_admin_options_export($this->vars);
				$export->get_export_options($_REQUEST['export_format']);
			}		
		}
		
		function admin_enqueue_scripts(){
			$this->vars['plugin_url'] 	= plugins_url("", $this->vars['__FILE__']);
			wp_enqueue_script( $this->vars['plugin_key'].'_ajax-script', $this->vars['plugin_url'].'/assets/js/scripts.js',array('jquery'));
		}
		
		function admin_pages(){
			$admin_page = $this->vars['admin_page'];
			echo "<div class=\"wrap\">";
				if($admin_page == $this->vars['plugin_key']."_export"){
					echo "<h2>".__('Export Wordpress Options','wm_wpoie')."</h2>";
					
					$this->get_tabs();
					
					require_once("wm_wpoie_admin_options_export.php");
					$export = new wm_wpoie_admin_options_export($this->vars);
					$export->get_options_export_page();
				}
			echo "</div>";
		}
		
		function admin_imports(){
			$admin_page = isset($_REQUEST['import']) ? $_REQUEST['import'] : '';
			echo "<div class=\"wrap\">";
				if($admin_page == $this->vars['plugin_key']."_import"){
					
					echo "<h2>".__('Import Wordpress Options','wm_wpoie')."</h2>";
					$this->get_tabs();
					require_once("wm_wpoie_admin_options_import.php");
					$export = new wm_wpoie_admin_options_import($this->vars);
					$export->get_options_import_pages();
				}
			echo "</div>";
		}
		
		function get_tabs(){
			$import_page = isset($_REQUEST['import']) ? $_REQUEST['import'] : '';
			$admin_page = $this->vars['admin_page'];
			
			$import_tab = $import_page == 'wmwpoie_import' ? ' nav-tab-active' : '';
			$export_tab = $admin_page == 'wmwpoie_export' ? ' nav-tab-active' : '';
			
			$tool_url = admin_url("tools.php?page=wmwpoie_export");
			$admin_url = admin_url("admin.php?import=wmwpoie_import");
			
			echo '<div id="icon-themes" class="icon32"><br></div>';
			echo '<h2 class="nav-tab-wrapper">';
				echo "<a class='nav-tab$export_tab' href='{$tool_url}'>".__( 'Export', 'wm_wpoie' )."</a>";
				echo "<a class='nav-tab$import_tab' href='{$admin_url}'>".__( 'Import', 'wm_wpoie' )."</a>";
			echo '</h2>';
		}
		
		public function register_importer() {
			if ( function_exists( 'register_importer' ) ) {
				register_importer( $this->vars['plugin_key'].'_import', __( 'Wordpress Options', 'wm_wpoie' ), __( 'Import Wordpress Options from a JSON file', 'wm_wpoie' ), array( $this, 'admin_imports' ) );
			}
		}
		
		function print_list($ar = NULL,$display = true){
			if($ar){
				$output = "<pre>";
				$output .= print_r($ar,true);
				$output .= "</pre>";
				
				if($display){
					echo $output;
				}else{
					return $output;
				}
			}
		}
		
	}//End Class
}