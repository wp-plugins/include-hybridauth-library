<?php
/**
* Plugin Name: HybridAuth lib
* Author URI: http://pravdomil.cz
*/

include "prettyPrint.php";

class hybridauth_lib
{
	var $capability = 'manage_options';
	var $slug = 'hybridauth_lib';
	var $menu_title = 'HybridAuth';
	var $provider_option_slug = 'hybridauth_lib_config';
	
	var $providers = array();
	var $callback = "";
	var $config = array();
	
	function __construct()
	{
		// set vars
		$this->loadProviders();
		$this->callback = $this->getCallback();
		$this->config = $this->getConfig();
		
		// load HybridAuth
		require_once( __DIR__ . "/hybridauth/Hybrid/Auth.php" );
		
		//set HybridAuth var
		global $HybridAuth;
		$HybridAuth = new Hybrid_Auth( $this->config );
		
		// admin page
		add_action( 'admin_menu', array($this, "menu"));
	}
	
	function menu()
	{
		add_options_page($this->menu_title, $this->menu_title, $this->capability, $this->slug, array($this, "page"));
	}
	
	function page()
	{
		if ( !current_user_can( $this->capability ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		$this->savePost();
		
		?>
<style type="text/css">
table { width: 100%; }
td { vertical-align: top; width: 50%; }

td:nth-child(2) { padding: 0 2em; }
td:nth-child(2) textarea { display: block; width: 100%; height: 439px; resize: vertical; }
td:nth-child(2) input { width: 100%; margin: 1em; display: block; }

a { text-decoration: none; }
code { display: block; margin: 1em 0; }
</style>
<table>
	<tr>
		<td>
			<h1>HybridAuth has been included</h1>
			<ol>
				<li>Set your provider app keys on the right</li>
				<li>
					Use the API instantly as:<br>
					<code>
						global $HybridAuth;<br>
						$adapter = $HybridAuth->authenticate("Twitter");<br>
						<br>
						$params = array("count" => 1000);<br>
						$response = $adapter->api()->api( 'statuses/user_timeline.json', $params);<br>
						<br>
						if($response->errors) var_dump($response);<br>
						else var_dump($response);<br>
					</code>
				</li>
			</ol>
			<h1>Documentation</h1>
			<p>
				<a href="http://hybridauth.sourceforge.net/userguide.html">Learn about API possibilities: OpenID, Google, Yahoo, Windows Live, LinkedIn, Foursquare, Additional Providers, Github, LastFM, Vimeo , Viadeo , Identica, Tumblr, Goodreads, QQ, Sina, Murmur, Pixnet, Plurk, Skyrock, Geni, FamilySearch, MyHeritage, 500px, Vkontakte, Mail.ru, Yandex, Odnoklassniki, Instagram, Twitch.tv NEW, Steam Community</a>
			</p>
			<p>
				Facebook: <a href="http://hybridauth.sourceforge.net/userguide/IDProvider_info_Facebook.html">documentation</a>, 
				<a href="https://developers.facebook.com/apps">manage apps</a>
			</p>
			<p>
				Twitter: <a href="http://hybridauth.sourceforge.net/userguide/IDProvider_info_Twitter.html">documentation</a>,
				<a href="https://dev.twitter.com/apps">manage apps</a>
			</p>
			<p>BTW your API callback URL is: &nbsp; (check if it's working)<br>
				<a href="<?php echo $this->callback; ?>" style="font-size: 10px;"><?php echo $this->callback; ?></a></p>
		</td>
		<td>
			<h1>Edit providers</h1>
			<form action="?page=<?php echo $this->slug; ?>" method="post">
				<textarea name="hybridauth_set_providers"><?php echo $this->getProvidersJson(); ?></textarea>
				<input type="submit" value="Submit changes" class="button-primary">
				<span style="font-size: 10px;">Leave empty for reset</span>
			</form>
		</td>
	</tr>
</form>
<?php
	}
	
	function getCallback()
	{
		return plugins_url('hybridauth', __FILE__) . "/";
	}
	
	function getConfig()
	{
		return array(
			"base_url" => $this->callback,
			"debug_mode" => false,
			"debug_file" => "",
			"providers" => $this->providers,
		);
	}
	
	function savePost()
	{
		$req = @$_POST["hybridauth_set_providers"];
		
		if($req === null) return;
		
		if(get_magic_quotes_gpc()) $req = stripslashes($req);
		
		if($req) $this->providers = json_decode($req, true);
		else $this->providers = $this->getDefaultConf();
		
		$this->saveProviders();
	}
	
	function saveProviders()
	{
		update_option($this->provider_option_slug, $this->providers);
	}
	
	function loadProviders()
	{
		$this->providers = get_option($this->provider_option_slug);
		
		if(!$this->providers) $this->providers = $this->getDefaultConf();
	}
	
	function getDefaultConf()
	{
		return include("default_config.php");
	}
	
	function getProvidersJson()
	{
		return prettyPrint(json_encode($this->providers));
	}
}

$HybridAuth = null;
new hybridauth_lib();
