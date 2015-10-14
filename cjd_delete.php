<?php
/*
Plugin Name: CJD Spam Nuke
Plugin URI: http://chrisjdavis.org/category/wp-hacks/
Description: This plugin allows you to interact with the comments flagged as 'spam' in WordPress 1.5. Click <a href="../wp-content/plugins/cjd_delete.php?action=changelog" title="changelog">here for the changelog</a> to see what's new.
Version: 1.5.1
Author: Chris J. Davis
Author URI: http://chrisjdavis.org/
*/

/*
Version 1.5.1 Changes:
	* Changed Name to CJD Spam Nuke.
	* Added number of spam to menu item ala the Awaiting Moderation menu item.
	* Move to a friendlier UI for Selective Nuking.
	* Added ability to "unspam" comments marked as spam, just in case.
*/

if( $_GET[action] == 'changelog' && (strpos( $_SERVER['HTTP_REFERER'],
'/wp-admin/plugins.php' ) || strpos( $_SERVER['HTTP_REFERER'], 'cjd_delete.php' ) )) {

echo "<title>WordPress > Spam Nuker Changelog</title>\n";

echo "<style type=\"text/css\">\np\n{\nmargin: 10px;\n}\nbody {\nbackground: #f2f2f2;\ncolor: #000;\nmargin: 0px;\npadding: 0px;\n}\n\n.wrap {\nheight: 50%;\nborder-top: 0px;\nborder-right: 1px;\nborder-left: 1px;\nborder-bottom: 1px;\nborder-style: solid;\nborder-color: #999;\nbackground-color: #fff;\nmargin: 0px 10% 0 10%;\npadding: 1em 1em;\n}\nh2\n{\nborder-bottom: 2px\nsolid #cc3300;\ncolor: #333;\nfont: normal 22px/18px normal;\nmargin: 0px 10px;\n}\n</style>\n";
echo "<div class=\"wrap\">\n<h2>Spam Nuker Changelog</h2>\n<h3>Version 1.5.1 <small>march 5rd 2005</small></h3><ol><li>Changed Name to CJD Spam Nuke.</li>\n<li>Added number of spam to menu item ala the Awaiting Moderation menu item.</li>\n<li>Moved to a friendlier UI for Selective Nuking.</li>\n<li>Added ability to \"unspam\" comments marked as spam, just in case.</li>\n</ol>\n";
echo "<h3>Version 1.5.0 <small>march 3rd 2005</small></h3>\n<ol>\n<li>Brought CJD Mass Delete into line with WordPress 1.5</li>\n<li>Moved CJD Mass Delete to the new spam system in WordPress 1.5.</li>\n<li>Added the ability to delete all comments flagged as \"spam\" with one click.</li>\n<li>Moved the menu item to the Manage page and renamed it to Spam.</li>\n</ol>\n";
echo "<p>\n<a href=\"../../wp-admin/plugins.php\">return to plugins page</a> | <a href=\"http://www.chrisjdavis.org/donate/\" title=\"Donate\">donate to the cause</a> | <a href=\"http://www.chrisjdavis.org/index.php/category/wp-hacks/\" title=\"more plugins\">get more plugins</a>\n</p>\n</div>";
	die();
}

$waiting_spam = $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = 'spam'");

if (! function_exists('cjd_delete_add_manage_page'))
{
    function cjd_delete_add_manage_page()
        {
                global $waiting_spam;
                $spam_count = "Spam (" . $waiting_spam . ")";
        if (function_exists('add_management_page'))
            add_management_page("Spam", $spam_count, 1, __FILE__);
    }
}
	if (is_plugin_page())
{
?>
<?php
switch($_POST['submit']) {
		case 'Nuke em!':
			$i = 0;
	foreach ($delete_spam as $comment) :
		$comment = (int) $comment;
		$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_ID = $comment AND comment_approved = 'spam'");
			++$i;
	endforeach;
	echo '<div class="updated"><p>' . sprintf(__('%s comments deleted.'), $i) . "</p></div>";
	break;
		case 'Unspam me!':
			$i = 0;
	foreach ($not_spam as $comment) :
		$comment = (int) $comment;
		$wpdb->query("UPDATE $wpdb->comments SET comment_approved = '1' WHERE comment_ID = $comment AND comment_approved = 'spam'");
			++$i;
	endforeach;
	echo '<div class="updated"><p>' . sprintf(__('%s comments unspammed.'), $i) . "</p></div>";
}
switch($action) {
		case 'nuked':
		$nuked = $wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'");
if (isset($nuked)){
			echo '<div class="updated"><p>';
		if ($nuked) {
				echo __("All spam Nuked! Rowr!");
					}
					echo "</p></div>";
				}
}
?>
<div class="wrap">
<h2><?php _e('Mass Spam Nuke') ?></h2>
		<?php _e('<p>Mass Spam Nuke allows you to delete every comment in your database that is flagged as spam with one click. Be warned this is undoable, if you are sure you don\'t have some legitimate comments flagged as spam then go for it.</p>')?>
<?php
function get_count(){
global $wpdb, $comments;
$comments = $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = 'spam'");
echo $comments;
}
?>
<form method="post" action="admin.php?page=cjd_delete.php&action=nuked" name="form1">
<input type="hidden" name="action" value="nuked" />
There are currently <?php get_count(); ?> comments identified as spam.&nbsp; &nbsp; <input type="submit" name="Submit" value="Nuke em, nuke em all!">
</form>
</div>
<div class="wrap">
<h2><?php _e('Selective Spam Nuke') ?></h2>
		<?php _e('<p>Selective Spam Nuke allows you to choose the spam you are nuking by clicking the checkbox at the beginning of each row.  When you have checked all the spam you want for deletion simply click the Nuke em! button at the bottom of the screen and that\'s it.</p>')?>
<?php
		$comments = $wpdb->get_results("SELECT *, COUNT(*) AS count FROM $wpdb->comments WHERE comment_approved = 'spam' GROUP BY comment_author_IP");
		if ($comments) {
?>
<table width="100%" cellpadding="3" cellspacing="3">
  <tr>
	<th scope="col"><?php _e('Nuke?') ?></th>
	<th scope="col"><?php _e('Unspam?') ?></th>
    <th scope="col"><?php _e('Name') ?></th>
    <th scope="col"><?php _e('Email') ?></th>
    <th scope="col"><?php _e('URI & Message') ?></th>
	<th scope="col"><?php _e('IP') ?></th>
  </tr>
<?php
    foreach($comments as $comment) {
			$comment_date = mysql2date(get_settings("date_format") . " @ " . get_settings("time_format"), $comment->comment_date);
			$post_title = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID='$comment->comment_post_ID'");
?>
<form method="post" action="admin.php?page=cjd_delete.php" name="form2">
<input type="hidden" name="action" value="deleted" />
<?php
$bgcolor = '';
$class = ('alternate' == $class) ? '' : 'alternate';
?>
<tr class='<?php echo $class; ?>' valign="top">

    <td align="center"><input type="checkbox" name="delete_spam[]" value="<?php echo $comment->comment_ID; ?>" /></td>

   	<td align="center"><input type="checkbox" name="not_spam[]" value="<?php echo $comment->comment_ID; ?>" /></td>



    <td><a href="<?php comment_author_url() ?>"><?php comment_author() ?></a></td>



    <td><?php comment_author_email_link( 'email' ) ?></td>



    <td><?php echo trim_string( $comment->comment_content, 50 ); ?></td>



    <td><a href="http://ws.arin.net/cgi-bin/whois.pl?queryinput=<?php comment_author_IP() ?>" title="<?php comment_author_IP() ?>"><?php echo $comment->count ?></a></td>

  </tr>
	<?php
		}
			} else {
    // nothing to approve
    echo __("<p>Currently there are no comments in the queue.</p>") . "\n";
		}
	?>
	</table>
	<input type="submit" name="submit" value="Nuke em!">  or <input type="submit" name="submit" value="Unspam me!">
	</form>
	</div>
<?php
}
add_action('admin_menu', 'cjd_delete_add_manage_page');


function trim_string( $trimString, $length ) {

	$blah = explode( ' ', $trimString );

	if ( count( $blah ) > $length) {
		$k = $length;
	} else {
		return nl2br( $trimString );
	}

	$trimString = '';

	for ( $i = 0; $i < $k; $i++ ) {
		$trimString .= $blah[ $i ] . ' ';
	}

	return nl2br( $trimString );

}

?>