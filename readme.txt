=== Amazon Post Purchase ===
Contributors: heypublisher, aguywithanidea, loudlever
Donate link: https://literary-arts.org/donate/
Tags: affiliate sales, Amazon, ASIN, Amazon Associate, monetize, heypublisher
Requires at least: 4.0
Tested up to: 4.7.5
Stable tag: 2.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily display Amazon Affiliate products related to a your post or page in a side-bar widget.

== Description ==
Amazon Post Purchase Plugin is based upon the [Amazon Product In a Post](https://wordpress.org/plugins/amazon-product-in-a-post-plugin/) plugin developed by [Don Fischer](http://fischercreativemedia.com/).

While that plugin is intended for displaying products within a Post, this plugin will display products as a side-bar widget in themes that support dynamic side-bars.

This plugin is useful for when you want to display different Amazon products in the side-bar for individual Posts.  The plugin will only display when the Post or Page has a custom field set.

**How it Works:**
The plugin uses the newest Amazon Product Advertising API, ensuring full security on all transaction calls.

To use the plugin, you should first get an [Amazon Affiliate Account](https://affiliate-program.amazon.com/).  Once you have an account, install the plugin, enter your Amazon Associate ID, and the keyname you will use in your post's Custom Field to track the ASIN.

== Installation ==
1. Upload the *amazon-post-purchase* folder (inside the zip file) to your */wp-content/plugins/* directory
2. Activate the plugin through the 'Plugins' menu in WordPress

After you have installed the plugin and loaded the widget into your side-bar, you will need to configure it.

Drag the "AmazonPostPurchase" widget to the appropriate side-bar container.  Where prompted, set the following fields:

* "Title" - this will be the title displayed above the widget in the side-bar.
- You can make this simple text, like "You Might Also Like", or
- You can make the title dynamically display the Author/Artist's name by using the keyword *#_AUTHORNAME_#*, like this: "Recently by #_AUTHORNAME_#".

* "Amazon Affiliate ID" - this should be _your_ Affiliate ID.  If you do not set this field, all referral $$ from Amazon will go to us.

* "Custom Field Name" - this is the 'name' you will use when you create a custom field in your post.  By default this value is set to 'ASIN'.

No additional adjustments are needed unless you want to configure your own CSS styles.

**Usage**

Once installed, adding a product to your post is a simple process:

*  Go into the full edit mode for the post (Post/Edit then select the post).
*  Under the Custom Fields, click on the link 'Enter New' (after you've done this the first time, you will simply select the value from the drop-down list).
*  Input the value you set in the widget "Custom Field Name" in the "Name" field.  (we recommend the word "ASIN").
*  Input the ASIN for the product you want displayed in the "Value" field.
*  Save or Publish the post.

*Yes -- it's that easy!*

== Styling ==

You can customize the look and feel of the displayed widget, including turning on or off the display of certain data elements, through CSS.  The following CSS describes the various data elements displayed in the Widget:

<pre>
#amazon-post-purchase-container {
  /* This is the container DIV for the displayed widget */
}
#amazon-post-purchase-image  {
  /* This is the container DIV for the product IMAGE.  Defaults are: */
  text-align:center;
}
#amazon-post-purchase-large-image-link {
  /* This controls the display of the text "See larger image"  Defaults are: */
  text-align:center;
}
#amazon-post-purchase-byline {
  /* This DIV contains the product 'title' and 'author' information.  Two sub elements are contained within this DIV: */
}
#amazon-post-purchase-byline H2 {
    /* This is where the product 'title' is contained */
}
#amazon-post-purchase-byline #amazon-post-purchase-author {
    /* This is where the product 'author' is contained */
}
#amazon-post-purchase-publication {
  /*  This Container displays the Format (ie: 'paperback'), Release Date, and Publisher information.
      If you turn 'off' display of this id, all 3 of those elements will not be displayed */
}
#amazon-post-purchase-price {
  /*  This is where the 'List Price', 'New Price' and 'Used Price' are displayed
      If you turn 'off' display of this id, all 3 of those elements will not be displayed */
}
#amazon-post-purchase-button {
  /*  This controls the display of the 'Buy Now at Amazon' button.
      By default, this displays the image "images/buyamzon-button.png" in the plugin directory.
      If you want to use a different 'buy' button, simply save a new image with the same name to this location. */
}
</pre>

If you have any questions email us at [wordpress@heypublisher.com](mailto:wordpress@heypublisher.com).

== Frequently Asked Questions ==

= How Much Does This Plugin Cost? =
Nothing.  Nada.  Zero.  This widget is absolutely FREE for you to use in your blog, online magazine, or other Wordpress-powered website.  Have fun!

= Can I Make a Donation to HeyPublisher for Use of this Plugin? =
If you wish to make a donation please consider donating instead to the [Literary Arts](https://literary-arts.org/donate/) organization.  Literary Arts introduces high school students to the craft of writing.  They are our kind of organization and could use the money.

= Can I Use This Widget More Than Once In My Sidebar? =
The plugin is activated off of a Custom Field in the post.  There will only be one custom field matching the configuration of the plugin, so if you include it multiple times in your side bar you will see the same product displayed multiple times.

= The Widget is Not Displaying on my Homepage? =
This is by design.  Since the widget acts upon an ASIN being set at the POST level, a homepage with more than one POST can yield inconsistent results.

== Screenshots ==

1. Widget as it will appear in the Widget manager.
2. Control screen for configuring the widget.
3. Custom Field in the Post Edit screen.  This is where you set the ASIN for the product you want to display.
4. How the widget will display on your website.

== Changelog ==

= 2.3.1 =
* Released 2017-08-01
* Move debug logging to it's own class and ensured that it's can't easily accidentally get turned on and fill up your disk.

= 2.3.0 =
* Released 2017-07-11
* Ensure plugin works with latest version of PA API.
* Changed the donation URL for Literary Arts organization.  You really should consider donating to them!
* Added ability to define default ASIN list that can be used to display the widget on all Page/Posts without having to define a custom ASIN for each of those pages.
* Added ability to extend this functionality to all other screens (except the HOME page)
* And, just to help explain things, added a Help screen on the Settings page that will answer all of your questions.
* Lastly, we received complaints that the buy button was displaying in English even though the Amazon store being used was Italian.  We've fixed that.  The widget now displays the buy button in the proper language.

= 2.2.0 =
* Released 2017-04-06
* Added support for Italy and Spain
* Added external link to Affiliate Stores soyou can quickly get your account set up and validated

= 2.1.0 =
* Released 2017-04-05
* Tested up through WordPress 4.7.3
* This plugin is now owned and maintained by [HeyPublisher](https://www.heypublisher.com)
* Removed a bunch of kruft from the codebase.
* Prepping for a rewrite of the product search component for faster rendering.
* Updated stylesheet and enqueueing.

= 2.0.0 =
* WordPress 4.x compliant.
* Updated the query to Amazon to return default currency of store being queried.
* Added a Settings page and side-bar navigation to settings.
* All widget configuration except title is now in a Settings page.

= 1.1.3 =
* Fixed a bug regarding products that don't have sale data.

= 1.1.2 =
* Fixed a bug that was preventing activation on some hosting servers.
* Updating documentation to clarify that widget will not display on Homepage by default.

= 1.1.1 =
* Better documentation, including adding section on how to customize look of widget through CSS.
* Cleaned out some of the kruft in the code.
* This plugin is now owned and maintained by [Loudlever, Inc.](http://www.loudlever.com)

= 1.0.1 =
* Simplified the Admin configuration screen.
* Added ability to create dynamic widget titles through use of reserved word *#_AUTHORNAME_#*
* Cleaned up some cluttered parts of code.
* Updated readme.txt file to better explain installation process.

= 1.0 =
* Plugin Release (10/23/2009)
