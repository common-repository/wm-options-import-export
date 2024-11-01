<?php

if(!class_exists('wm_wpoie_admin_options_import')){
	class wm_wpoie_admin_options_import{
		
		var $vars	= array();
		
		function __construct($vars = array()){
			$this->vars	= $vars;
			
			
		}//End Construct
		
		function get_options_import_pages(){
			$steps = isset($_GET['step']) ? $_GET['step'] : 0;
			if($steps == 0){
				$this->first_step();
			}
			
			if($steps == 1){
				check_admin_referer( 'import-upload' );
				if ( $this->handle_upload() ) {
					$this->show_options();
				} else {
					$import = isset($_GET['import']) ? $_GET['import'] : '';
					echo '<p><a href="' . esc_url( admin_url( 'admin.php?import='.$import ) ) . '">' . __( 'Return to File Upload', 'wm_wpoie' ) . '</a></p>';
				}
			}
			
			if($steps == 2){
				check_admin_referer( 'import-wordpress-options' );
				$this->file_id = intval( $_POST['import_id'] );
				if ( false !== ( $this->import_data = get_transient( $this->transient_key() ) ) ) {
					$this->import();
				}
			}
		}
		
		private function first_step() {
			$import = isset($_GET['import']) ? $_GET['import'] : '';
			echo '<div class="narrow">';
			echo '<p>'.__( 'Choose a JSON (.json) file to upload, then click Upload file and import.', 'wm_wpoie' ).'</p>';
				wp_import_upload_form( 'admin.php?import='.$import.'&amp;step=1' );
			echo '</div>';
		}
		
		private function handle_upload() {
			$file = wp_import_handle_upload();
	
			if ( isset( $file['error'] ) ) {
				return $this->error_message(
					__( 'Sorry, there has been an error.', 'wm_wpoie' ),
					esc_html( $file['error'] )
				);
			}
	
			if ( ! isset( $file['file'], $file['id'] ) ) {
				return $this->error_message(
					__( 'Sorry, there has been an error.', 'wm_wpoie' ),
					__( 'The file did not upload properly. Please try again.', 'wm_wpoie' )
				);
			}
	
			$this->file_id = intval( $file['id'] );
	
			if ( ! file_exists( $file['file'] ) ) {
				wp_import_cleanup( $this->file_id );
				return $this->error_message(
					__( 'Sorry, there has been an error.', 'wm_wpoie' ),
					sprintf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'wm_wpoie' ), esc_html( $file['file'] ) )
				);
			}
	
			if ( ! is_file( $file['file'] ) ) {
				wp_import_cleanup( $this->file_id );
				return $this->error_message(
					__( 'Sorry, there has been an error.', 'wordpress-importer' ),
					__( 'The path is not a file, please try again.', 'wordpress-importer' )
				);
			}
	
			$file_contents = file_get_contents( $file['file'] );
			$this->import_data = json_decode( $file_contents, true );
			set_transient( $this->transient_key(), $this->import_data, DAY_IN_SECONDS );
			wp_import_cleanup( $this->file_id );
	
			return $this->run_data_check();
		}
		
		var $import_data = NULL;
		
		var $transient_key = 'wp-options-import-%d';
		
		var $file_id = NULL;
		
		private function run_data_check() {
			if ( empty( $this->import_data['options'] ) ) {
				$this->clean_up();
				return $this->error_message( __( 'Sorry, there has been an error. This file appears valid, but does not seem to have any options.', 'wm_wpoie' ) );
			}
			return true;
		}
		
		private function transient_key() {
			return sprintf( $this->transient_key, $this->file_id );
		}
	
	
		private function clean_up() {
			delete_transient( $this->transient_key() );
		}
		
		private function error_message( $message, $details = '' ) {
			echo '<div class="error"><p><strong>' . $message . '</strong>';
			if ( ! empty( $details ) ) {
				echo '<br />' . $details;
			}
			echo '</p></div>';
			return false;
		}
		
		function show_options(){
			$whitelist 	= array();
			$blacklist 	= array();
			$import 	= isset($_GET['import']) ? $_GET['import'] : '';
			?>
                <style type="text/css">
					#importing_options {
						border-collapse: collapse;
					}
					#importing_options th {
						text-align: left;
					}
					#importing_options td, #importing_options th {
						padding: 5px 10px;
						border-bottom: 1px solid #dfdfdf;
					}
					#importing_options pre {
						white-space: pre-wrap;
						max-height: 200px;
						overflow-y: auto;
						background: #fff;
						padding: 5px;
					}
					div.error#import_all_warning {
						margin: 25px 0 5px;
					}
				</style>
                <form action="<?php echo admin_url( 'admin.php?import='.$import.'&amp;step=2' ); ?>" method="post">
					<?php wp_nonce_field( 'import-wordpress-options' ); ?>
                    <input type="hidden" name="import_id" value="<?php echo absint( $this->file_id ); ?>" />
                    <input type="hidden" class="which-options" name="settings[which_options]" value="specific" />
                        <div id="option_importer_details">
                        	<table id="importing_options" class="wp-list-table widefat striped">
                            <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th><?php _e( 'Option Name', 'wm_wpoie' ); ?></th>
                                    <th><?php _e( 'New Value', 'wm_wpoie' ) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $this->import_data['options'] as $option_name => $option_value ) : $optionname = esc_attr( $option_name );?>
                                    <tr>
                                        <td><input type="checkbox" name="options[]" value="<?php echo $optionname; ?>" <?php checked( in_array( $option_name, $whitelist ) ) ?> id="<?php echo $optionname; ?>" /></td>
                                        <td><label for="<?php echo $optionname; ?>"><?php echo esc_html( $option_name ) ?></label></td>
                                        <?php if ( null === $option_value ) : ?>
                                            <td><em>null</em></td>
                                        <?php elseif ( '' === $option_value ) : ?>
                                            <td><em>empty string</em></td>
                                        <?php elseif ( false === $option_value ) : ?>
                                            <td><em>false</em></td>
                                        <?php else : ?>
                                            <td><pre><?php echo esc_html( $option_value ) ?></pre></td>
                                        <?php endif ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                        <h3><?php _e( 'Additional Settings', 'wm_wpoie' ); ?></h3>
                        <p>
                            <input type="checkbox" value="1" name="settings[override]" id="override_current" checked="checked" />
                            <label for="override_current"><?php _e( 'Override existing options', 'wm_wpoie' ); ?></label>
                        </p>
                        <p class="description"><?php _e( 'If you uncheck this box, options will be skipped if they currently exist.', 'wm_wpoie' ); ?></p>
                    	<?php submit_button( __( 'Import Selected Options', 'wm_wpoie' ) ); ?>
                </form>
            <?php
		}
		
		private function import() {
			if ( $this->run_data_check() ) {
				if ( empty( $_POST['settings']['which_options'] ) ) {
					$this->error_message( __( 'The posted data does not appear intact. Please try again.', 'wm_wpoie' ) );
					$this->show_options();
					return;
				}
	
				$options_to_import = array();
				if ( 'all' == $_POST['settings']['which_options'] ) {
					$options_to_import = array_keys( $this->import_data['options'] );
				} elseif ( 'default' == $_POST['settings']['which_options'] ) {
					$options_to_import = $this->get_whitelist_options();
				} elseif ( 'specific' == $_POST['settings']['which_options'] ) {
					if ( empty( $_POST['options'] ) ) {
						$this->error_message( __( 'There do not appear to be any options to import. Did you select any?', 'wm_wpoie' ) );
						$this->show_options();
						return;
					}
	
					$options_to_import = $_POST['options'];
				}
	
				$override = ( ! empty( $_POST['settings']['override'] ) && '1' === $_POST['settings']['override'] );
	
				$hash = '1adea3d3bf202a7bab8a388cfce650e6722fa18e';
	
				// Allow others to prevent their options from importing
				$blacklist = $this->get_blacklist_options();
	
				foreach ( (array) $options_to_import as $option_name ) {
					if ( isset( $this->import_data['options'][ $option_name ] ) ) {
						if ( in_array( $option_name, $blacklist ) ) {
							echo "\n<p>" . sprintf( __( 'Skipped option `%s` because a plugin or theme does not allow it to be imported.', 'wm_wpoie' ), esc_html( $option_name ) ) . '</p>';
							continue;
						}
	
						// As an absolute last resort for security purposes, allow an installation to define a regular expression
						// blacklist. For instance, if you run a multsite installation, you could add in an mu-plugin:
						// 		define( 'WP_OPTION_IMPORT_BLACKLIST_REGEX', '/^(home|siteurl)$/' );
						// to ensure that none of your sites could change their own url using this tool.
						if ( defined( 'WP_OPTION_IMPORT_BLACKLIST_REGEX' ) && preg_match( WP_OPTION_IMPORT_BLACKLIST_REGEX, $option_name ) ) {
							echo "\n<p>" . sprintf( __( 'Skipped option `%s` because this WordPress installation does not allow it.', 'wm_wpoie' ), esc_html( $option_name ) ) . '</p>';
							continue;
						}
	
						if ( ! $override ) {
							// we're going to use a random hash as our default, to know if something is set or not
							$old_value = get_option( $option_name, $hash );
	
							// only import the setting if it's not present
							if ( $old_value !== $hash ) {
								echo "\n<p>" . sprintf( __( 'Skipped option `%s` because it currently exists.', 'wm_wpoie' ), esc_html( $option_name ) ) . '</p>';
								continue;
							}
						}
	
						$option_value = maybe_unserialize( $this->import_data['options'][ $option_name ] );
						if ( in_array( $option_name, $this->import_data['no_autoload'] ) ) {
							delete_option( $option_name );
							add_option( $option_name, $option_value, '', 'no' );
						} else {
							update_option( $option_name, $option_value );
						}
					} elseif ( 'specific' == $_POST['settings']['which_options'] ) {
						echo "\n<p>" . sprintf( __( 'Failed to import option `%s`; it does not appear to be in the import file.', 'wm_wpoie' ), esc_html( $option_name ) ) . '</p>';
					}
				}
	
				$this->clean_up();
				echo '<p>' . __( 'All done. That was easy.', 'wm_wpoie' ) . ' <a href="' . admin_url() . '">' . __( 'Have fun!', 'wm_wpoie' ) . '</a>' . '</p>';
			}
		}
		
		function get_blacklist_options(){
			return array();
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