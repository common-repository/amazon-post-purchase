<?php
//  Admin Class
namespace AMZNPP;

require_once(dirname( __FILE__ ) . '/HeyPublisher/Log.class.php');
require_once(dirname( __FILE__ ) . '/HeyPublisher/Base.class.php');

class APP_Admin extends \AMZNPP\HeyPublisher\Base {
  var $wp_filter_id = 1;
  var $help = false;
  var $options = array();
  var $defaults = array();

  public function __construct() {
    parent::__construct();
    $this->logger = new \AMZNPP\HeyPublisher\Log('amznpp.log');

    // default settings to prevent plugin from breaking when no value provided.
    $this->defaults = $this->init_defaults();
    $this->options = get_option(AMZNPP_PLUGIN_OPTTIONS);
    $this->logger->debug(sprintf("APP_Admin constructor\nopts = %s",print_r($this->options,true)));
    $this->check_plugin_version();
    $this->slug = AMZNPP_ADMIN_PAGE;
    // Sidebar configs
    $this->plugin['home'] = 'https://wordpress.org/plugins/amazon-post-purchase/';
    $this->plugin['support'] = 'https://wordpress.org/support/plugin/amazon-post-purchase';
    $this->plugin['contact'] = 'mailto:wordpress@heypublisher.com';
  }

  public function __destruct() {
    parent::__destruct();
    // nothing to see yet
  }

  public function activate_plugin() {
    $this->logger->debug("in the activate_plugin()");
    // $this->check_plugin_version();
  }
  public function deactivate_plugin() {
    $this->logger->debug("in the deactivate_plugin()");
    $this->options = false;
    delete_option(AMZNPP_PLUGIN_OPTTIONS);  // remove the default options
	  return;
  }

  public function configuration_screen_help($contextual_help, $screen_id, $screen) {
    if ($screen_id == $this->help) {
      $contextual_help = <<<EOF
<h3>Affiliate Country</h3>
<p>Select the country which matches your Amazon Affiliate account.  </p>
<h3>Amazon Affiliate ID</h3>
<p>Use the Affiliate ID associated with your Affiliate account.  The Affiliate ID looks something like <code>foobar-20</code>.</p>
<h3>Custom Field Name</h3>
<p>This is the name of the Custom Field you will use in Posts and Pages to store the ASIN.  You should give this a memorable name.  We recommend <code>ASIN</code>.</p>
<h3>Default ASIN(s)</h3>
<p>This field is optional and is only helpful if you want to display products in the side-bar of each Post without having to add individual ASIN ids to each Post.  This field takes a comma-separated list of one or more ASINs.  The plugin will pull a random ASIN out of this list for use when displayed.</p>
<h3>Display On All Posts/Pages?</h3>
<p>If checked <i>and</i> you have defined Default ASINs to use for display, you will see the side-bar widget displayed on every Post and Page.  When a custom field is defined for an individial Post or Page, that individual ASIN will be used.  When a custom field is not defined, then the plugin will fall back to using one of the Default ASINs.</p>
<h3>Display On Other Views?</h3>
<p>Similar to above, if checked <i>and</i> you have defined Default ASINs, the plugin will display in the sidebar of your homepage, search results page, category page, etc.</p>
EOF;
    }
  	return $contextual_help;
  }

  // Default settings to be used if not defined
  private function init_defaults() {
    $def = array();
    $def['tag']       = 'ASIN';
    $def['aff_id']    = 'amznpp-20';
    $def['aff_cc']    = 'com';
    $def['def_asin']  = ''; // empty string will ensure that it doesn't display by default
    $def['all_pages'] = 1;
    $def['all_screens'] = 0;
    return $def;
  }

  private function plugin_admin_url() {
    $url = 'options-general.php?page='.AMZNPP_ADMIN_PAGE;
    return $url;
  }
  // Filter for creating the link to settings
  public function plugin_filter() {
    return sprintf('plugin_action_links_%s',AMZNPP_PLUGIN_FILE);
  }
  // Called by plugin filter to create the link to settings
  public function plugin_link($links) {
    $url = $this->plugin_admin_url();
    $settings = '<a href="'. $url . '">'.__("Settings", "amznpp").'</a>';
    array_unshift($links, $settings);  // push to left side
    return $links;
  }

  public function register_admin_page() {
    $this->logger->debug("in the register_admin_page()");
    // ensure our js and style sheet only get loaded on our admin page
    $this->help = add_options_page('Amazon Post Purchase', 'Amazon Post Pur...', 'manage_options', AMZNPP_ADMIN_PAGE, array(&$this,'action_handler'));
    add_action("admin_print_scripts-". $this->help, array(&$this,'admin_js'));
    add_action("admin_print_styles-". $this->help, array(&$this,'admin_stylesheet') );
  }
  function admin_js() {
    $this->logger->debug("in the admin_js()");
    wp_enqueue_script('amznpp', plugins_url($this->slug . '/include/js/amznpp.js'), array('jquery'));
  }
  function admin_stylesheet() {
    $this->logger->debug("in the admin_stylesheet()");
    wp_register_style( 'amznpp-heypublisher', plugins_url($this->slug . '/include/css/heypublisher.css' ) );
    wp_register_style( 'amznpp-admin', plugins_url($this->slug . '/include/css/amznpp_admin.css' ), array('amznpp-heypublisher') );
    wp_enqueue_style('amznpp-heypublisher');
    wp_enqueue_style('amznpp-admin');
  }
  // Primary action handler for page
  function action_handler() {
    parent::page('Amazon Post Purchase Settings', '', array($this,'content'));
  }

  public function content() {
    $html = '';
    if (is_user_logged_in() && is_admin() ){
      $this->logger->debug("config screen settings");
      $this->logger->debug(sprintf("POST = %s",print_r($_POST,1)));
      // update then refetch
      $message = $this->update_options($_POST);
      $opts = get_option(AMZNPP_PLUGIN_OPTTIONS)[options];
      $aff_id = $opts[aff_id];
      // if ($aff_id == $this->defaults['aff_id']) { $aff_id = ''; }  // don't display default

      if ($message) {
        printf('<div id="message" class="updated fade"><p>%s</p></div>',$message);
      } elseif ($this->error) { // reload the form post in this form
        // set the defaults
        // $opts['default'] =  $_POST['AMZNPP_opt']['default'];
        // // restructure the posts hash
        // foreach ($posts as $x=>$hash) {
        //   $id = $hash['ID'];
        //   if (isset($_POST['AMZNPP_opt']['posts'][$id])) {
        //     $hash['meta_value'] = $_POST['AMZNPP_opt']['posts'][$id];
        //     $posts[$x] = $hash;
        //   }
        // }
      }

      $nonce = wp_nonce_field(AMZNPP_ADMIN_PAGE_NONCE);
      $action = $this->form_action();
      $countries = $this->supported_countries();
      $select= '';
      foreach ($countries as $key=>$val) {
        $sel = '';
        if ($opts['aff_cc']==$key) { $sel = 'selected="selected"'; }
        $select .= sprintf("<option value='%s' %s>%s</option>",$key,$sel,$val);
      }
      $checked_all_screens = ($opts[all_screens] == 1) ? 'checked' : '';
      $checked_all_pages = ($opts[all_pages] == 1) ? 'checked' : '';

      $html =<<<EOF
        <form method="post" action="{$action}">
          {$nonce}
				  <h2>Add the widget to your side-bar after configuring your settings below.</h2>
          <p>Click on the Help link above for an explanation of the input fields.</p>
          <ul>
          <li>
            <label class='amznpp_label' for='amznpp_aff_cc'>Affiliate Country:</label>
            <select name="amznpp_opt[aff_cc]" id="amznpp_aff_cc" class='amznpp_input'>
            {$select}
            </select>
            <a id='amznpp_domain' class='amznpp' href='#' title='Signup for an Amazon Affiliate account' target='_blank'>
            <span class="dashicons dashicons-external"></span>
            </a>
            <br/>
            <small class="amznpp_small">* Prices will be displayed in the default currency of this store.</small>
          </li>
          <li>
            <label class='amznpp_label' for='amznpp_aff_id'>Amazon Affiliate ID:</label>
            <input type="text" name="amznpp_opt[aff_id]" id="amznpp_aff_id" class='amznpp_input' value="{$aff_id}" />
          </li>
          <li>
            <label class="amznpp_label" for='amznpp_tag'>Custom Field Name:</label>
            <input type="text" name="amznpp_opt[tag]" id="amznpp_tag" class='amznpp_input' value="{$opts['tag']}" />
          </li>
          <li>
            <label class="amznpp_label" for='amznpp_def_asin'>Default ASIN(s):</label>
            <input type="text" name="amznpp_opt[def_asin]" id="amznpp_def_asin" class='amznpp_input' value="{$opts['def_asin']}" />
          </li>
          <li>
            <input type='hidden' name="amznpp_opt[all_pages]" value='0'/>
            <input type="checkbox" name="amznpp_opt[all_pages]" id="amznpp_all_pages" class='amznpp_input' value="1" {$checked_all_pages} />
            <label class='amznpp_label' for='amznpp_all_pages'>Display On All Posts/Pages?</label>
          </li>
          <li>
            <input type='hidden' name="amznpp_opt[all_screens]" value='0'/>
            <input type="checkbox" name="amznpp_opt[all_screens]" id="amznpp_all_screens" class='amznpp_input' value="1" {$checked_all_screens} />
            <label class='amznpp_label' for='amznpp_all_screens'>Display On Other Views?</label>
          </li>
          </ul>
          <input type="hidden" name="save_settings" value="1" />
          <input type="submit" class="button-primary" name="save_button" value="Update Settings" />
        </form>
EOF;
    } // end conditional '
    return $html;
  }

  public function update_options($form) {
    $message = null;
    if(isset($_POST['save_settings'])) {
      check_admin_referer(AMZNPP_ADMIN_PAGE_NONCE);
      if (isset($_POST['amznpp_opt'])) {
        $message = 'Your updates have been saved.';
        $opts = $_POST['amznpp_opt'];
        // biz rule, if aff_id is blank, set country to 'com'
        // if tag is blank, set to default
        if ($opts[aff_id] && $opts[aff_cc]) {
          $this->options[options][aff_id] = $opts[aff_id];
          $this->options[options][aff_cc] = $opts[aff_cc];
        } else {
          $this->options[options][aff_id] = $this->defaults[aff_id];
          $this->options[options][aff_cc] = $this->defaults[aff_cc];
        }
        if ($opts[tag]) {
          $this->options[options][tag]    = $opts[tag];
        } else {
          $this->options[options][tag]    = $this->defaults[tag];
          $message = "Custom field was not set - using default of: {$this->defaults[tag]}";
        }
        $this->options[options][def_asin]    = $opts[def_asin];
        $this->options[options][all_pages]   = $opts[all_pages];
        $this->options[options][all_screens] = $opts[all_screens];
        update_option(AMZNPP_PLUGIN_OPTTIONS,$this->options);
      }
      return $message;
    }
  }
  /*
    Private Functions
  */
  private function check_plugin_version() {
    $opts = $this->options;
    $this->logger->debug(sprintf("in check_plugin_version()\nPLUGIN VERSION = %s\nopts = %s",AMZNPP_PLUGIN_VERSION,print_r($opts,1)));
    if (!$opts || !$opts[plugin] || $opts[plugin][version_current] == false) {
      $this->logger->debug("no old version - initializing");
      $this->init_plugin();
      return;
    }
    // check for upgrade option here
    if ($opts[plugin][version_current] != AMZNPP_PLUGIN_VERSION) {
      $this->logger->debug("need to upgrade version");
      $this->upgrade_plugin($opts);
      return;
    }
    $this->logger->debug('-Returning from check_plugin_version()');
  }

  private function get_version_as_int($str) {
    $var = intval(preg_replace("/[^0-9 ]/", '', $str));
    return $var;
  }
  private function init_install_options() {
    $this->options = array(
      'plugin' => array(
        'version_last'    => AMZNPP_PLUGIN_VERSION,
        'version_current' => AMZNPP_PLUGIN_VERSION,
        'install_date'    => Date('Y-m-d'),
        'upgrade_date'    => Date('Y-m-d')
      ),
      'options' => $this->defaults
    );
    return;
  }
  private function init_plugin() {
    $this->init_install_options();
    add_option(AMZNPP_PLUGIN_OPTTIONS,$this->options);
    return;
  }
  private function supported_countries() {
    $countries = array(
      'ca' => 'Canada (amazon.ca)',
      'fr' => 'France (amazon.fr)',
      'de' => 'Germany (amazon.de)',
      'it' => 'Italy (amazon.it)',
      'es' => 'Spain (amazon.es)',
      'co.uk' => 'United Kingdon (amazon.co.uk)',
      'com' => 'United States (amazon.com)'
    );
    return $countries;
  }
  private function upgrade_plugin($opts) {
    $ver = $this->get_version_as_int($this->options['plugin']['version_current']);
    $this->logger->debug("Version = $ver");
    if ($ver < 200) {
      // set as defaults, as we're no longer able to pull from the widget
      $this->options[options][aff_id] = $this->defaults[aff_id];
      $this->options[options][aff_cc] = $this->defaults[aff_cc];
      $this->options[options][tag]    = $this->defaults[tag];
    }
    if ($ver < 230) {
      // seed the new option keys
      $this->options[options][def_asin]    = $this->defaults[def_asin];
      $this->options[options][all_pages]   = $this->defaults[all_pages];
      $this->options[options][all_screens] = $this->defaults[all_screens];
    }
    $this->options[plugin][version_last] = $this->options[plugin][version_current];
    $this->options[plugin][version_current] = AMZNPP_PLUGIN_VERSION;
    $this->options[plugin][upgrade_date] = Date('Y-m-d');
    $this->logger->debug(sprintf("upgrading plugin with opts %s",print_r($this->options,1)));
    update_option(AMZNPP_PLUGIN_OPTTIONS,$this->options);
  }
}
?>
