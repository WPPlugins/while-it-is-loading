<?php
/**
 * @package While Loading
 * @version 2.0
 */
/*
Plugin Name: While Loading
Plugin URI: http://wordpress.org/extend/plugins/while-it-is-loading/
Description: It shows a screen while all content page is being loaded. After the content has been rendered, it disappears.
Author: Garmur
Version: 2.0
Author URI: https://google.com/+GeorgeGarro
Tags: loading, gear, screen, personalization
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JVGHP29EWE85G
Text Domain: while-loading
Domain Path: /i18n/
*/

/*	Copyright 2014 Garmur

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!defined('ABSPATH'))
	die("You don't have access.");

// Make sure we don't expose any info if called directly
if(!function_exists('add_action')) {
	_e('Hi there! You cannot access here. We are sorry.','while-loading');
	exit;
}

if(!defined('WL_PLUGIN_DIR_IMG'))
	define('WL_PLUGIN_DIR_IMG',WP_PLUGIN_DIR . '/' .trim(dirname(plugin_basename(__FILE__) ), '/') . '/img');
if(!defined('WL_PLUGIN_URL_IMG'))
	define('WL_PLUGIN_URL_IMG', plugins_url( '/img' , __FILE__ ));

function wl_gear(){
	do_action('wl_gear');
}
add_action('after_theme_setup','wl_gear');

if(!class_exists('WhileIsLoading')){
	final class WhileIsLoading{
		public function __construct(){
			add_action('admin_menu',array($this,'addSubPage'));
			add_action('plugins_loaded',array($this,'addI18n'));
			add_action('wp_head',array($this,'writeOnHeadTheme'));
			add_action('plugin_action_links_'.basename( dirname( __FILE__ ) ).'/'.basename( __FILE__ ), array($this,'wlPluginSettings'), 10, 4);
			add_action('admin_enqueue_scripts',array($this,'enqueueColorPicker'));
			add_action('wl_gear',array($this,'writeOnBodyTheme'));
			add_action('wp_print_styles',array($this,'enqueueFeStyles'));
		}

		public function enqueueColorPicker(){
			wp_enqueue_script('color-handler',plugins_url('js/color-handler.js', __FILE__ ),array('wp-color-picker'),false,true);
			wp_enqueue_style('wp-color-picker');
		}

		public function enqueueFeStyles(){
			wp_enqueue_style('wl-style', plugins_url('/css/wl-style.css' , __FILE__ ));
		}

		public function checkColor($value){
			if(preg_match('/^#[a-f0-9]{6}$/i',$value)){
				return true;
			}
			return false;
		}

		public function addSubPage(){
			add_submenu_page('options-general.php',__('While the page is loading','while-loading'),__('While Loading','while-loading'),'manage_options','loading-settings',array($this,'adminForm'));
		}

		public function addI18n(){
			load_plugin_textdomain('while-loading',false,basename(dirname(__FILE__)).'/i18n');
		}

		public function wlPluginSettings($links){
			$settings_link = '<a href="options-general.php?page=loading-settings">'.__('Settings','while-loading').'</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		public function adminForm(){
			if(isset($_POST['enviar'])){
				$color = $_POST['colorFondo'];
				$transparencia = $_POST['transparencia'];
				$titulo = $_POST['titulo'];
				$dibujo = $_POST['imagenDeCarga'];

				$color = $this->checkColor($color) ? $color : '#000000';
				$transparencia = (intval($transparencia) > 1 || intval($transparencia) < 0) ? 1 : $transparencia;

				update_option('el_color',$color);
				update_option('la_transparencia',$transparencia);
				update_option('el_titulo',$titulo);
				update_option('el_dibujo',$dibujo);

				echo '<div class="updated settings-error"><p><strong>'.__('Options have been saved.','while-loading').'</strong></p></div>';
			}
			$color = get_option('el_color','#000000');
			$transparencia = get_option('la_transparencia','0.95');
			$titulo = get_option('el_titulo',__('Page loading','while-loading'));
			$dibujo = get_option('el_dibujo','2');

		?>
			<div class="wrap">
				<h2><?php _e( 'While Loading Options', 'while-loading'); ?></h2>
				<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table class="form-table">
						<tr>
							<th scope="row"><label for="select_background"><?php _e('Background Color','while-loading'); ?></label></th>
							<td><input class="wl-color-picker" type="text" id="select_background" name="colorFondo" value="<?php echo $color; ?>" />
							<p class="description"><?php _e('Hexadecimal color.','while-loading');?></p></td>
						</tr>
						<tr>
							<th scope="row"><label for="select_transparency"><?php _e('Opacity','while-loading'); ?></label></th>
							<td><input type="range" min="0" max="1" step="0.05" id="select_transparency" name="transparencia" value="<?php echo $transparencia; ?>" />
							<p class="description"><?php _e('The opacity for background is from 0 to 1.','while-loading');?></p></td>
						</tr>
						<tr>
							<th scope="row"><label for="select_titulo"><?php _e('Title','while-loading'); ?></label></th>
							<td><input class="regular-text" type="text" id="select_titulo" name="titulo" value="<?php echo $titulo; ?>" placeholder="Your Title" />
							<p class="description"><?php _e('A little title.','while-loading');?></p></td>
						</tr>
						<tr>
							<th scope="row"><label for="select_picture"><?php _e('Graphic', 'while-loading'); ?></label></th>
							<td>
								<?php echo '<select name="imagenDeCarga">';
								$index = -1;
								foreach($this->listImages(WL_PLUGIN_DIR_IMG) as $image){
									echo '<option value="'.++$index.'"'. ($index == get_option('el_dibujo') ? ' selected' : '') .'>'.$image['name'].'</option>';
								}
								echo '</select>';
								?>
							</td>
						</tr>
					</table>
					<p class="submit"><input class="button button-primary" type="submit" name="enviar" value="<?php _e('Save Settings','while-loading'); ?>" /></p>
				</form>
				<div id="postimagediv">
					<h3><?php _e('Note:','while-loading'); ?></h3>
					<p><?php printf(__('Please, write this code %s just after body tag of your WordPress theme.','while-loading'), '<code>&lt;?php wl_gear(); ?&gt;</code>'); ?></p>
					<p style="color:red;"><?php printf(__('Most times the %1$s tag is in %2$s of your theme and you can modify it from the %3$s.','while-loading'),'<code>&lt;body&gt;</code>','<b>header.php</b>','<a href="'.get_site_url().'/wp-admin' .(is_multisite() ? '/network' : '') .'/theme-editor.php?file=header.php&theme='.get_template('').'">'.__('theme editor','while-loading').'</a>'); ?></p>
					<div class="inside">
						<img src="<?php echo plugins_url('/ejemplo.jpg',__FILE__); ?>" />
					</div>
					<small>You can show your appreciation.</small>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="JVGHP29EWE85G">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="Donate for Garmur">
						<img alt="Donate for this plugin" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
			</div>
		<?php
		}

		private function listImages($f){
			$folder = opendir($f);
			$files = array();
			while($current = readdir($folder)){
				if($current != '.' && $current != '..' && $current != '.htaccess')
					/* if(is_dir($f.$current))
						$this->listImages($f.$current.'/'); */
					if(preg_match('/^.*\.(gif|jpg|png)$/', $f.$current))
						$files[] = array('name'=>$current,'type'=>'image');
					if(preg_match('/^.*\.(svg)$/', $f.$current))
						$files[] = array('name'=>$current,'type'=>'svg');
			}
			return $files;
		}

		public function writeOnHeadTheme(){
		?>
			<script>
			function desvanecer(){
				var dibujo = document.getElementById('display');
				dibujo.style.opacity -= 0.03;
				if(dibujo.style.opacity < 0.0)
					dibujo.parentNode.removeChild(dibujo);
				else
					setTimeout(function(){desvanecer()}, 30);
			}
			window.onload = desvanecer; // as <body onload="">
			</script>
		<?php
		}

		public function writeOnBodyTheme(){
			$style = 'background-color:'. get_option('el_color') . ';opacity:'. get_option('la_transparencia').';';
			$ggm = -1;?>
			<div id="display" style="<?php echo $style; ?>">
				<h1 id="loading"><?php echo get_option('el_titulo'); ?></h1>
			<?php
			foreach($this->listImages(WL_PLUGIN_DIR_IMG) as $image){
				if(get_option('el_dibujo') != ++$ggm){
					if(count($image) == $ggm){?>
						<!-- Show the main image, the gear and it is by default -->
						<svg id="engranaje" xmlns="http://www.w3.org/2000/svg" version="1.1"><!--First gear-->
						<g>
						<rect height="20" width="240" y="110" x="0" fill="grey" id="svg3"/>
						<rect height="240" width="20" y="0" x="110" fill="grey" id="svg4"/>
						<rect height="20" width="240" y="110" x="0" transform="rotate(22.5 120 120)" fill="grey" id="svg5"/>
						<rect height="20" width="240" y="110" x="0" transform="rotate(45 120 120)" fill="grey" id="svg6"/>
						<rect height="20" width="240" y="110" x="0" transform="rotate(67.5 120 120)" fill="grey" id="svg7"/>
						<rect height="20" width="240" y="110" x="0" transform="rotate(113 120 120)" fill="grey" id="svg8"/>
						<rect height="20" width="240" y="110" x="0" transform="rotate(135.5 120 120)" fill="grey" id="svg9"/>
						<rect height="20" width="240" y="110" x="0" transform="rotate(160 120 120)" fill="grey" id="svg10"/>
						<circle r="90" cy="120" cx="120" fill="#7f7f7f" id="cover"/>
						<circle r="70" cy="120" cx="120" fill="white" id="cover2"/>
						<circle r="60" cy="120" cx="120" fill="url(#garmur)" id="svg2"/>
						</g>
						<defs>
						<radialGradient spreadMethod="pad" id="garmur">
						<stop offset="0.6" stop-color="#7f7f7f"/>
						<stop offset="1" stop-opacity="0.9" stop-color="white"/>
						</radialGradient>
						</defs>
						</svg>
					<?php
					}
					continue;
				}
				switch($image['type']){
					case 'image':?>
						<img src="<?php echo WL_PLUGIN_URL_IMG.'/'.$image['name']; ?>" alt="BiG circle" />
					<?php
						break;
					case 'svg':?>
						<object data="<?php echo WL_PLUGIN_URL_IMG.'/'.$image['name']; ?>" type="image/svg+xml"></object>
					<?php
						break;
				}
				break;
			}?>
			</div>
			<?php
		}
	}
}

if(class_exists('WhileIsLoading')){
	global $WhileIsLoading;
	$WhileIsLoading = new WhileIsLoading();
}

/*Agradezco a los que me dieron el tiempo. - Thank you, GBSF.*/
?>