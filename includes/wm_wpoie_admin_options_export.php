<?php

if(!class_exists('wm_wpoie_admin_options_export')){
	class wm_wpoie_admin_options_export{
		
		var $vars	= array();
		
		function __construct($vars = array()){
			
			$this->vars	= $vars;
			
		}//End Construct
		
		function get_options_export_page(){
			$this->get_options_list();
		}
		
		function get_options_list(){
			global $wpdb;
			
			$whitelist_options = $this->get_whitelist_options();
			
			$whitelist_options_exclude = '' ;
			if(count($whitelist_options)>0){
				$whitelist_options_string = implode("', '", $whitelist_options);
				$whitelist_options_exclude = "AND `option_name` NOT IN ('{$whitelist_options_string}')";
			}
			
			
			$multisite_exclude = '';
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				$multisite_exclude = $wpdb->prepare( "AND `option_name` NOT LIKE 'wp_%d_%%'", get_current_blog_id() );
			}
			
			$options = $wpdb->get_results( "SELECT option_id, option_name FROM $wpdb->options WHERE `option_name` NOT LIKE '_transient_%' {$multisite_exclude} {$whitelist_options_exclude} ORDER BY option_name ASC" );
			
			//$this->print_list($wpdb);
			
			/*
			$sql = "SELECT * FROM {$wpdb->options}";
			$sql .= " ORDER BY option_name ASC";
			$sql .= " LIMIT 20";
			
			$options = $wpdb->get_results($sql);
				<th><?php _e('Auto Load')?></th>
				<td><input type="checkbox" name="autoload[<?php echo $option->option_id;?>]" id="autoload_<?php echo $option->option_id;?>" value="yes" /></td>
				<td><label for="option_id_<?php echo $option->option_id;?>"><?php echo $option->option_value;?></label></td>
				 <th><?php _e('Option value')?></th>
			*/
			//echo $hash = hash('ripemd160', 'The Options API is a simple and standardized way of storing data in the database.');
			?>
            	<form name="form_export_optons" id="form_export_optons" class="form_export_optons" method="post">
                
                    <div style="text-align:right; margin-bottom:5px;">
                        <input type="submit" name="submit_export_options" id="submit_export_options" value="<?php _e('Download Export File','wm_wpoie')?>" class="button button-primary" />
                    </div>
                    
                    <table class="wp-list-table widefat striped ">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="chkselecctall" id="chkselecctall_top" class="chkselecctall" value="1"/></th>
                                <th><?php _e('Option Name','wm_wpoie')?></th>
                            </tr>
                        </thead>
                        <tbody class="the-list">
                        <?php foreach($options as $key => $option):?>
                        <tr>
                            <th><input type="checkbox" name="option_ids[]" id="option_id_<?php echo $option->option_id;?>" value="<?php echo $option->option_id;?>" class="checkbox1" /></th>
                            <td><label for="option_id_<?php echo $option->option_id;?>"><?php echo $option->option_name;?></label></td>
                        </tr>
                        <?php endforeach;?>
                        </tbody>
                        
                    </table>
                	<div style="text-align:right; margin-top:5px;">
                        <input type="submit" name="submit_export_options" id="submit_export_options" value="<?php _e('Download Export File','wm_wpoie')?>" class="button button-primary" />
                    </div>
                    <input type="hidden" name="export_options" value="json" />
                    <input type="hidden" name="export_format" value="json" />
                    <input type="hidden" name="page" value="<?php echo (isset($_REQUEST['page']) ? $_REQUEST['page'] : '')?>" />
                </form>
            	
            <?php
			//$this->print_list($options);
		}
		
		private function get_whitelist_options() {
			
			$import_whitelist = array(
				'active_plugins', 'admin_email', 'advanced_edit', 'avatar_default', 'avatar_rating', 'blacklist_keys', 'blogdescription', 'blogname', 'blog_charset', 'blog_public', 'blog_upload_space', 'category_base', 'category_children', 'close_comments_days_old', 'close_comments_for_old_posts', 'comments_notify', 'comments_per_page', 'comment_max_links', 'comment_moderation', 'comment_order', 'comment_registration', 'comment_whitelist', 'cron', 'current_theme', 'date_format', 'default_category', 'default_comments_page', 'default_comment_status', 'default_email_category', 'default_link_category', 'default_pingback_flag', 'default_ping_status', 'default_post_format', 'default_role', 'gmt_offset', 'gzipcompression', 'hack_file', 'html_type', 'image_default_align', 'image_default_link_type', 'image_default_size', 'large_size_h', 'large_size_w', 'links_recently_updated_append', 'links_recently_updated_prepend', 'links_recently_updated_time', 'links_updated_date_format', 'link_manager_enabled', 
				'mailserver_login', 'mailserver_pass', 'mailserver_port', 'mailserver_url', 'medium_size_h', 'medium_size_w', 'moderation_keys', 'moderation_notify', 'ms_robotstxt', 'ms_robotstxt_sitemap', 'nav_menu_options', 'page_comments', 'page_for_posts', 'page_on_front', 'permalink_structure', 'ping_sites', 'posts_per_page', 'posts_per_rss', 'recently_activated', 'recently_edited', 'require_name_email', 'rss_use_excerpt', 'show_avatars', 'show_on_front', 'sidebars_widgets', 'start_of_week', 'sticky_posts', 'stylesheet', 'subscription_options', 'tag_base', 'template', 'theme_switched', 'thread_comments', 'thread_comments_depth', 'thumbnail_crop', 'thumbnail_size_h', 'thumbnail_size_w', 'timezone_string', 'time_format', 'uninstall_plugins', 'uploads_use_yearmonth_folders', 'upload_path', 'upload_url_path', 'users_can_register', 'use_balanceTags', 'use_smilies', 'use_trackback', 'widget_archives', 'widget_categories', 'widget_image', 'widget_meta', 'widget_nav_menu', 'widget_recent-comments', 
				'widget_recent-posts', 'widget_rss', 'widget_rss_links', 'widget_search', 'widget_text', 'widget_top-posts', 'WPLANG'
			);
			
			return apply_filters( 'wordpress_options_import_whitelist', $import_whitelist, 'wm_wpoie');
		}
		
		function get_export_options($get_export_options = 'json'){
			if($get_export_options == "json"){
				global $wpdb;
				
				$option_ids = isset($_REQUEST['option_ids']) ? $_REQUEST['option_ids'] : array();
				
				if(count($option_ids)<=0) return '';
				
				$option_ids = implode(",", $option_ids);
				
				$option_names = $wpdb->get_col( "SELECT DISTINCT `option_name` FROM $wpdb->options WHERE `option_id` IN($option_ids)" );
				
				//$this->print_list($wpdb);
				
				if ( ! empty( $option_names ) ) {
	
					$export_options = array();
					$hash 			= '1adea3d3bf202a7bab8a388cfce650e6722fa18e';
					foreach ( $option_names as $option_name ) {
						
						$option_value = get_option( $option_name, $hash );
						
						if ( $option_value !== $hash ) {
							$export_options[ $option_name ] = maybe_serialize( $option_value );
						}
					}
	
					$no_autoload = $wpdb->get_col( "SELECT DISTINCT `option_name` FROM $wpdb->options WHERE `option_id` IN($option_ids) AND `autoload`='no'" );
					
					if ( empty( $no_autoload ) ) {
						$no_autoload = array();
					}
					
					if(count($export_options)>0){
						$sitename = sanitize_key(str_replace(" ",'_', get_bloginfo( 'name' )));
						if ( ! empty( $sitename ) ) {
							$sitename .= '_';
						}
						$filename = $sitename . 'wp_options_' . date_i18n( 'Y_m_d_H_i_s' ) . '.json';
						
						header( 'Content-Description: File Transfer' );
						header( 'Content-Disposition: attachment; filename=' . $filename );
						header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
		
						$JSON_PRETTY_PRINT = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : NULL;
						echo json_encode( array('options' => $export_options, 'no_autoload' => $no_autoload ), $JSON_PRETTY_PRINT );
						exit;die;
					}
				}
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