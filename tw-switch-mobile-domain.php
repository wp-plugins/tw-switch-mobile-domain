<?php
/*
Plugin Name: TW Switch Mobile Domain
Plugin URI: http://thucdem.mobi
Description: Redirect to Mobile domain and switch mobile theme
Version: 1.1
Author: MrTaiw
Author URI: https://www.facebook.com/taiw96
*/
class tw_switch_mobile_domain
{

	private $mobile_domain;
	private $mobile_theme;
	private $site_url;
    private $request;
    private $current_domain;

	public function __construct(){
		
		$current_domain  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
        $current_domain .= '://'.$_SERVER['HTTP_HOST'];
        $this->mobile_domain  = get_option('tw_mobile_domain');
		$this->mobile_theme   = get_option('tw_mobile_theme');
		$this->site_url       = site_url();
        $this->request        = $_SERVER['REQUEST_URI'];
        $this->current_domain = $current_domain;
		if(is_admin()){
			add_action('admin_init', array($this, 'tw_register_settings'));
			add_action('admin_menu', array($this, 'tw_add_menu_admin'));
		}
		else
		{
			add_action('init', array($this, 'tw_switch_mobile_domain'));
			if($this->mobile_theme != '' && ($this->current_domain == $this->mobile_domain || wp_is_mobile()))
				$this->tw_switch_theme();
		}

    }


    public function tw_register_settings() {

    	register_setting('tw-settings', 'tw_mobile_domain');
    	register_setting('tw-settings', 'tw_mobile_theme');

    }

    public function tw_switch_mobile_domain(){

    	
    	if((wp_is_mobile() && $this->mobile_domain != '') || $this->current_domain == $this->mobile_domain)
    	{
            
            if($this->current_domain != $this->mobile_domain){
                wp_redirect($this->mobile_domain . ':80' . $this->request);
                exit;
    		}
    		define('WP_HOME',    $this->mobile_domain);
    		define('WP_SITEURL', $this->mobile_domain);
    	}
    }

    public function tw_switch_theme(){
    	
    	add_filter('stylesheet', array($this, 'tw_add_filter_switch_theme'), 0);
    	add_filter('template',   array($this, 'tw_add_filter_switch_theme'), 0);
        add_filter('template_directory',       array($this, 'tw_change_path'));
        add_filter('template_directory_uri',   array($this, 'tw_change_path'));
        add_filter('stylesheet_directory',     array($this, 'tw_change_path'));
        add_filter('stylesheet_directory_uri', array($this, 'tw_change_path'));
    }

    public function tw_change_path($path){

        $path = str_replace($this->site_url, $this->mobile_domain, $path);
        return $path;
    }

    public function tw_add_filter_switch_theme(){
	
        $themes = get_themes();
        foreach($themes as $theme_data){
        	if($theme_data['Name'] == $this->mobile_theme){
                return $theme_data['Stylesheet'];
        	}
        }
    }

    public function tw_add_menu_admin(){

    	add_theme_page("TW Mobile Domain", "TW Mobile Domain", 'manage_options', 'tw-switch-mobile-domain', array($this, 'tw_show_admin_page'));
    }

    public function tw_show_admin_page(){
    	?>
    	<div class="wrap">
    		<h2>TW Switch Mobile Domain</h2>
    		<form method="post" action="options.php">
    			<input type="hidden" name="option_page" value="tw_show_admin_page"/>
    			<input type="hidden" name="action" value="update" />
    			<?php settings_fields('tw-settings');?>
    			<label><b>Mobile domain</b></label><br/>
    			<input type="text" name="tw_mobile_domain" style="width:98%" value="<?php echo $this->mobile_domain;?>" placeholder="http://m.thucdem.mobi"/><br/>
    			<label><b>Mobile Theme</b></label><br/>
    			<?php
    			$themes = get_themes();
    			$current_theme = get_current_theme();
    			$theme_names = array_keys($themes);
    			?>
    			<select name="tw_mobile_theme">
    				<?php foreach($theme_names as $theme_name):?>
    				<?php if(($this->mobile_theme == $theme_name) || (($this->mobile_theme == '') && ($theme_name == $current_theme))):?>
    				<option value="<?php echo $theme_name?>" selected="selected"><?php echo $theme_name?></option>
    			    <?php else:?>
    			    <option value="<?php echo $theme_name?>"><?php echo $theme_name?></option>
    			    <?php endif;?>
    			    <?php endforeach;?>
    			</select>
    			<?php submit_button(); ?>
    		</form>
    	</div>
    	<?php
    }
}

new tw_switch_mobile_domain();
?>