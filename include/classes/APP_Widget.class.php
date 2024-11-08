<?php
/*
  Widget Class
  http://codex.wordpress.org/Widgets_API
*/
require_once(dirname( __FILE__ ) . '/HeyPublisher/Log.class.php');

class AmazonPostPurchase extends WP_Widget {
  var $public_key = 'AKIAJUTYNEJVYJQYBQBQ';
  var $private_key = 'Y4VMdiLPD5XEkrDTIM53ktFptmLuhEtik5JSqvqm';
  var $title_spec = '#_AUTHORNAME_#';
  var $title = 'More by %s';
  var $admin_width = 250;
  var $options = array();

  function __construct() {
    $this->logger = new \AMZNPP\HeyPublisher\Log('amznpp-widget.log');
    $control_ops = array( 'id_base' => 'amznpp','width'=>$this->admin_width );
		$widget_ops = array('description' => __('Display Amazon products related to your POST in the side-bar.','amznpp'));
		$this->options = get_option(AMZNPP_PLUGIN_OPTTIONS)[options];
	  parent::__construct('amznpp', __('Amazon Post Purchase','amznpp'), $widget_ops,$control_ops );
  }

  /** @see WP_Widget::widget */
  function widget($args, $instance) {
    global $post;
    // This widget does NOT execute on the homepage
    if (is_home()) { return false; }
  	// outputs the content of the widget
    extract($args);
    $tag = $this->options[tag];
    if (!$tag) { return false; }    // no defined ASIN tag - that's weird
  	$asin = get_post_meta($post->ID,$tag,true);
    // safety checks and biz rule checks
    $this->logger->debug("ASIN = '{$asin}'");
    // Logic:
    $asin = $this->should_widget_display($asin);
    if (!$asin) { return false; }

    // If we made it this far, show the block
    $title = apply_filters('widget_title', $instance['title']);
    $aff_id = $this->options[aff_id];
    $aff_cc = $this->options[aff_cc];
    $widget_content = $this->getSingleAmazonProduct($asin,$aff_id,$aff_cc,$title);
    if ($widget_content) {
      // start the output
      echo $before_widget;
      if ( $title ) { echo $before_title . $title . $after_title; }
      echo $widget_content;
      echo $after_widget;
    }
    return;
	}

  /** @see WP_Widget::update */
  function update($new_instance, $old_instance) {
    if (!$new_instance['title']) { $new_instance['title'] = sprintf($this->title,$this->title_spec); }
    return $new_instance;
  }

  // outputs the options form on admin
  /** @see WP_Widget::form */
  function form($instance) {
    $title = esc_attr($instance['title']);
?>
    <p>
      <code><?php echo $this->title_spec; ?></code> will be replaced with author/artist's name.
    </p>
    <p>
      <label class="amazon-post-purchase" for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
<?php
	}

	//Single Product API Call - Returns One Product Data
  function getSingleAmazonProduct($asin='',$aws_partner_id, $aws_partner_locale, &$title){
		$target=' target="_blank" ';
		$a_err_msg='Product Unavailable.';
		$a_hidden_msg='Visit Amazon for Price.';
    $pop_new_window = true;
    $public_key = $this->public_key;
    $private_key = $this->private_key;
    if (!$asin) { return false; }
			$ASIN = $asin; //valid ASIN
			$errors='';
			// Amazon API Call
			$pxml = aws_signed_request($aws_partner_locale, array(
  	    "Operation"=>"ItemLookup",
  	    "ItemId"=>"$ASIN",
  	    "ResponseGroup"=>"ItemAttributes,Images,Offers",
  	    "IdType"=>"ASIN",
  	    "AssociateTag"=>"$aws_partner_id"), $public_key, $private_key
			);
      $this->logger->debug("getSingleAmazonProduct()");
      $this->logger->debug(sprintf("=> ASIN: : %s",print_r($ASIN,1)));
      $this->logger->debug(sprintf("=> parnter locale: : %s",print_r($aws_partner_locale,1)));
      $this->logger->debug(sprintf("PXML : %s",print_r($pxml,1)));

      if (!$pxml) {
        return false;
      }
			if(isset($pxml["ItemLookupResponse"]["Items"]["Request"]["Errors"]["Error"]["Message"])){
				$errors = $pxml["ItemLookupResponse"]["Items"]["Request"]["Errors"]["Error"]["Message"];
        return false;
			}
      // get our result data formatted into a meaningful hash
      // TODO: Move this FormatASINResult to AmazonPostPurchase class
			$result = FormatASINResult($pxml);
      $this->logger->debug(sprintf("FormatASINResult : %s",print_r($result,1)));
            // build up our HTML

            // Test for a large image - if we have it, provide a link
            $largeimage = false;
            if($result['LargeImage']!=''){
            $largeimage = <<< EOF
        <div id="amazon-post-purchase-large-image-link">
            <a target="amazon-image" href="javascript: void(0)"
                onclick="artwindow=window.open('{$result['LargeImage']}','art','directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=no, toolbar=no, width=400,height=525');artwindow.focus();return false;">
            <span>See larger image</span></a>
        </div>
EOF;
             }

      //  Buy Button
      $buy_button = $this->getBuyButton($aws_partner_locale);
      // Test for byline - set it appropriately
      $byline = $this->byline($result);
      // Hidden price
      if($result["ListPrice"]!='0') {
		    $list_price = $result["ListPrice"];
	    }
			else {
		    $list_price = $a_hidden_msg;
			}

            // lowest price
            $lowest_new_price = false;
			if (isset($result["LowestNewPrice"])){
				if($result["LowestNewPrice"]=='Too low to display'){
					$newPrice = 'Check Amazon For Pricing';
				}
				else{
					$newPrice = $result["LowestNewPrice"];
				}
				if($result["TotalNew"]>0){
				    $instock = " <span class='instock'>In Stock</span>";
				}
				else {
				    $instock = " <span class='outofstock'>Out of Stock</span>";
                }
                $lowest_new_price = "<tr><td>New From:</td><td>{$newPrice}{$instock}</td></tr>";
			}
            //  check for used prices
            $lowest_used_price = false;
			if(isset($result["LowestUsedPrice"])){
				if($result["TotalUsed"]>0){
				    $lowest_used_price = "<tr><td>Used From:</td><td>{$result["LowestUsedPrice"]} <span class='instock'>In Stock</span></td></tr>";
				}
			}
      // test for special key in the title
      $spec = $this->title_spec;
      if (preg_match("/$this->title_spec/", $title)) {
        if (isset($result['Author']) && FALSE != $result['Author']) {
          $title = preg_replace("/$this->title_spec/",$result['Author'],$title);
        }
        elseif (isset($result['Director']) && FALSE != $result['Director']) {
          $title = preg_replace("/$this->title_spec/",$result['Director'],$title);
        }
        elseif (isset($result['Publisher']) && FALSE != $result['Publisher']) {
          $title = preg_replace("/$this->title_spec/",$result['Publisher'],$title);
        }
        elseif (isset($result['ProductGroup']) && FALSE != $result['ProductGroup']) {
          $title = preg_replace("/$this->title_spec/",$result['ProductGroup'],$title);
        }
        else {  # if we can't format it properly, don't push special chars to the screen
          $title = '';
        }
      }

// the whole kit-n-kaboodle
    $med_image = $this->awsImageGrabber($result['MediumImage'],'amazon-image');
			$returnval  =  <<< EOF
<div id="amazon-post-purchase-container">
	<div id="amazon-post-purchase-image">
	    <a href="{$result["URL"]}" {$target}>{$med_image}</a>
        {$largeimage}
    </div>
    <div id="amazon-post-purchase-byline">
        <h2><a href='{$result['URL']}'>{$result['Title']}</a></h2>
        {$byline}
    </div>
    <table id="amazon-post-purchase-publication">
        <tr><td>{$result['Binding']}</td></tr>
        <tr><td>Released {$result['PublicationDate']}</td></tr>
        <tr><td>{$result['Publisher']}</td></tr>
    </table>

    <table id="amazon-post-purchase-price">
        <tr><td>List Price:</td><td>{$list_price}</td></tr>
        {$lowest_new_price}
        {$lowest_used_price}
    </table>

    <div id="amazon-post-purchase-button">
        <a style="display:block;margin-top:8px;width:165px;" {$target} href="{$result["URL"]}">
          {$buy_button}
        </a>
    </div>
</div>

EOF;
  // Buy buttons found at https://affiliate-program.amazon.com/help/node/topic/202094750
	    return $returnval;
	}
  // Get the buy button for the country in question
  public function getBuyButton($cc) {
    $images = array(
      'ca' => array('w' => 90, 'h' => 28),
      'fr' => array('w' => 120, 'h' => 28),
      'de' => array('w' => 120, 'h' => 28),
      'it' => array('w' => 137, 'h' => 28),
      'es' => array('w' => 137, 'h' => 28),
      'co.uk' => array('w' => 137, 'h' => 28),
      'com' => array('w' => 120, 'h' => 28)
    );
    if (!$images[$cc]) {
      $cc = 'com'; // default to US
    }

    $file = sprintf('%s/%s/amazon-post-purchase/images/buy-button/buy-from-%s.gif',get_bloginfo('url'),PLUGINDIR,$cc);
    $img = <<< EOF
    <img src="{$file}" width="{$images[$cc][w]}" height="{$images[$cc][h]}" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/>
EOF;
    return $img;
  }


	//Amazon Product Image from ASIN function - Returns HTML Image Code
	function awsImageGrabber($imgurl, $class=""){

	    $base_url0 = '<'.'img src="';
	    $base_url = $imgurl;
	    $base_url1 = '"';
	    $base_url1 = $base_url1.' class="amazon-image '.$class.'"';
	    $base_url1 = $base_url1.' rel="image-'.$asin.'" />';

		if($base_url!=''){
	    	return $base_url0.$base_url.$base_url1;
		}else{
			$base_url = get_bloginfo('url').'/'.PLUGINDIR.'/amazon-product-in-a-post-plugin/images/noimage.jpg';
	    	return $base_url0.$base_url.$base_url1;
		}
	}

	//Amazon Product Image from ASIN function - Returns URL only
	function awsImageGrabberURL($asin, $size="M"){
	    $base_url = 'http://images.amazon.com/images/P/'.$asin.'.03.';
	    if (strcasecmp($size, 'S') == 0){
	      $base_url .= 'THUMBZZZ';
	    }
	    else if (strcasecmp($size, 'L') == 0){
	      $base_url .= 'LZZZZZZZ';
	    }
	    else{
	      $base_url .= 'MZZZZZZZ';
	    }
	    $base_url .= '.jpg';
	    return $base_url;
	}
  // Take in the options array and pull default from that.
  private function get_default_asin() {
    $list = $this->options[def_asin];
    $asins = [];
    if ($list) {
      $asins = split(',',$list);
      shuffle($asins);
    }
    $asin = array_shift($asins);
    return $asin;
  }

	private function block_div($val,$prefix='',$class='') {
	  $klass = 'amazon-post-purchase';
	  if ($class) { $klass = sprintf('%s-%s',$klass,$class); }
	  if ($prefix) { $val = $prefix . ' ' . $val; }
	  $block = sprintf("<div class='amznpp-desc' id='%s'>%s</div>", $klass,$val);
	  return $block;
  }
	private function byline($obj) {
	  $byline = '';
		if(isset($obj["Author"])){
		    $byline .= $this->block_div($obj['Author'], 'By','author');
		}
		if(isset($obj["Director"])){
	    $byline .= $this->block_div($obj['Director'], 'Directed by','director');
		}
		if(isset($obj["Actors"])){
	    $byline .= $this->block_div($obj['Actors'], 'Starring:','starring');
		}
		if(isset($obj["Rating"])){
	    $byline .= $this->block_div($obj['Rating'], 'Rating:','rating');
		}
    return $byline;
  }

  private function should_widget_display($asin){
    $all_pages = $this->options[all_pages];
    $all_screens = $this->options[all_screens];
    $default_asin = $this->get_default_asin();
    if ( is_single() || is_page() ) {
      if ($asin) {
        return $asin;
      } elseif ($default_asin && $all_pages ) {
        return $default_asin;
      }
    } else {
      if ($default_asin && $all_screens ) {
        return $default_asin;
      }
    }
    return false; // default is to not display
  }

} // end class

?>
