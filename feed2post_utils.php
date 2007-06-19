<?

define('FEED2POST_AUTOPUBLISH_DELAY', 3600);

function feed2post_fixitems() {
  global $table_prefix, $wpdb;
	
	$sql = "SELECT * FROM wp_posts WHERE post_name RLIKE '-[[:digit:]]+$' ";
	$posts = $wpdb->get_results($sql);
	
	foreach($posts as $post) {
		wp_delete_post($post->ID);
	}
	
}

function feed2post_autopublish() {
	if ( get_option('feed2post_lastautopublish') + constant('FEED2POST_AUTOPUBLISH_DELAY') < time() ) {
		update_option('feed2post_lastautopublish', time(), "time of the last auto publish");
		
		feed2post_parsefeed();
		
	  $items = feed2post_getitems();
	  feed2post_convertitems($items, false);
	}
}

function feed2post_convertitems($items, $verbose = true) {
  global $table_prefix, $wpdb;
  
  if ( ! is_array($items) )
  	return false;
  	
  if ($verbose)
	  echo "<div style='width:100%;height:100%;'>"; 	
  
  foreach($items as $itemID) {
  	feed2post_convertitem($itemID);
  } 
  
  if ($verbose)
	  echo "</div>";
}

function feed2post_convertitem($item) {
  global $table_prefix, $wpdb;

	if ( is_numeric($item) ) {
		$sql = "SELECT * FROM ".$table_prefix.FEED2POST_TABLENAME." WHERE id = ".$item." AND postid IS NULL";
		$item = $wpdb->get_row($sql);
	}
	
	if ( $item->id && ! $item->postid ) {
		
		$item_data = unserialize($item->data);
		
		/* TODO :
		 *        format content
		 * 				meta
		 */
		$aUserURL = parse_url($item->guid);
		$sUserURL = $aUserURL['scheme'].'://'.$aUserURL['host'].'/';

		$sql = "SELECT * FROM ".$table_prefix."usermeta um LEFT JOIN ".$table_prefix."users u ON um.user_id = u.ID WHERE user_url = '".$sUserURL."'"; 
		$user = $wpdb->get_row($sql);
		
	  $post_data = array(
	  										'blog_ID' => null,
	  										'post_author' => (($user->ID)?$user->ID:1),
	  										'post_date' => date('Y-m-d H:i:s', strtotime($item->pubdate)),
	  										'post_date_gmt' => date('Y-m-d H:i:s', strtotime($item->pubdate)),
	  										'post_content' => $wpdb->escape(feed2post_cleancontent($item_data['CONTENT:ENCODED'])),
//		  										'post_content' => $wpdb->escape(feed2post_cleancontent($item->description)),
	  										'post_title' => $wpdb->escape(feed2post_cleancontent($item->title)),
	  										'post_category' => array(),
	  										'post_status' => 'publish'
	  									);

		if ( preg_match("/dailymotion|youtube/", $item->link) )
			$post_data['post_content'] = "[video]".$item->link."[/video]";

	  $post_ID = wp_insert_post($post_data);
	  
	  // update wp_feed2post table
	  $sql = "UPDATE ".$table_prefix.FEED2POST_TABLENAME." SET postid = ".$post_ID." WHERE id = ".$item->id;
	  $wpdb->query($sql);
	  
		// set Tags (as jerome's tags plugins way
		add_post_meta($post_ID, 'keywords', preg_replace("/\s+/", ',', $item_data['MEDIA:CATEGORY']));

		// set other metas
		add_post_meta($post_ID, 'source', $item->link);

		// grab images
		log_r($item_data['CONTENT:ENCODED']);

		preg_match_all("/<img [^>]*src=[\'\"]([\w\d\-\_\.\/\:]+)[\'\"][^>]*>/m", stripslashes($item_data['CONTENT:ENCODED']), $aMatches);
		foreach($aMatches[1] as $img) {
	
			log_r($img);
	
			$wp_filetype = wp_check_filetype( $img );
			$filename = basename($img);
	
			$uploads = wp_upload_dir();
			wp_mkdir_p( $uploads['path'] . "/" . $user->user_nicename  );
	
			$number = '';
			while ( file_exists( $uploads['path'] . "/" . $filename ) ) {
				if ( '' == $number.".".$wp_filetype['ext'] )
					$filename = $filename . ++$number . ".".$wp_filetype['ext'];
				else
					$filename = str_replace( $number.".".$wp_filetype['ext'], ++$number . ".".$wp_filetype['ext'], $filename );
			}
			$new_file = $uploads['path'] . "/" . $user->user_nicename . "/" . $filename;
			if ( ! $uploads['url'] )
				$uploads['url'] = $sUserURL;
			$url = $uploads['url'] . "/" . $user->user_nicename . "/" . $filename;
	
			file_put_contents($new_file, file_get_contents($img));
	
			// Set correct file permissions
			$stat = stat( dirname( $new_file ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $new_file, $perms );
		
			// Construct the attachment array
			$attachment = array(
				'post_title' => $post_data['post_title'] ? $post_data['post_title'] : $filename,
				'post_content' => '',
				'post_author' => (($user->ID)?$user->ID:1),
				'post_type' => 'attachment',
				'post_parent' => $post_ID,
				'post_mime_type' => $wp_filetype['type'],
				'guid' => $url
			);
	
			// Save the data
			$id = wp_insert_attachment($attachment, $new_file, $post_ID);
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $new_file ) );
			
			$post_data['post_content'] = str_replace($img, $url, $post_data['post_content']);
			$sql = " UPDATE $wpdb->posts SET post_content = '".$post_data['post_content']."' WHERE ID = $post_ID ";
			$wpdb->Query($sql);
			
		}

	}
	  
}


function feed2post_cleancontent($content) {
	$content = str_replace("", "'", $content);
	return $content;
}

function feed2post_parsefeed() {
  global $table_prefix, $wpdb;

	require_once('class.RSS.php');

	foreach( preg_split("/[\s,]+/", get_option('feed2post_url')) as $url) {

	  $rss = new RSS;
	  $rss -> Parser($url);
	  $rss -> Parse();

	  foreach( $rss->item as $item) {
	  	$sql = "SELECT guid FROM ".$table_prefix.FEED2POST_TABLENAME." WHERE guid = '".$item['GUID']."'";
	  	$guid = $wpdb->get_var($sql);
	  	if ( $guid != $item['GUID']) {
	  		// insert item
	  		$sql = "INSERT INTO ".$table_prefix.FEED2POST_TABLENAME." (pubdate, title, link, guid, description, feedname, data) VALUES ('".date('Y-m-d H:i:s', strtotime($item['PUBDATE']))."', '".$wpdb->escape($item['TITLE'])."', '".$item['LINK']."', '".$item['GUID']."', '".$wpdb->escape($item['DESCRIPTION'])."', '".$wpdb->escape($item['HUBRSS:FEEDNAME'])."', '".$wpdb->escape(serialize($item))."')";
	  		$wpdb->query($sql);
	  		feed2post_convertitem(mysql_insert_id());
	  	}
	  }
	}
}

function feed2post_getitems($blnFullList = false) {
  global $table_prefix, $wpdb;

	$sql = "SELECT * FROM ".$table_prefix.FEED2POST_TABLENAME." ORDER BY pubdate DESC ".(($blnFullList)?'':'LIMIT 0, 10');
	$items = $wpdb->get_results($sql);
	return $items;
}

function feed2post_install () {
   global $table_prefix, $wpdb;

   $table_name = $table_prefix . FEED2POST_TABLENAME;
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE ".$table_name." (
	      id int NOT NULL AUTO_INCREMENT,
	      pubdate datetime NULL,
	      title VARCHAR(255) NULL,
	      link VARCHAR(255) NULL,
	      guid VARCHAR(255) NULL,
	      description TEXT NULL,
	      feedname VARCHAR(255) NULL,
	      postid VARCHAR(255) NULL,
				data TEXT NULL,
	      UNIQUE KEY id (id)
	     );";

      require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
      dbDelta($sql);
  
      update_option('shoutbox_fade_from', "666666");
      update_option('shoutbox_fade_to', "FFFFFF");
      update_option('shoutbox_update_seconds', 4000);
   }
}


if ( ! function_exists('log_r') ):
	function log_r($s) {
		if ( is_array($s) or is_object($s) )
			$s = print_r($s, true);
		error_log($s);
	}
endif;

?>
