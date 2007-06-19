<?

/* Admin Panel */
function feed2post_subpanel() {
	global $wpdb, $table_prefix;

  if (isset($_POST['info_update'])) {
  	update_option('feed2post_url', $_POST['feed2post_url'], "the url of the feeds to transform");
  	update_option('feed2post_auto', $_POST['feed2post_auto'], "set automatic publishing");
    ?><div class="updated"><p><strong><?php 
			_e('Process completed fields in this if-block, and then print warnings, errors or success information.', 'Localization name')
    ?></strong></p></div><?php
	}
	
  if (isset($_POST['feed_update'])) {
  	feed2post_parsefeed();
    ?><div class="updated"><p><strong><?php 
			_e('Process completed fields in this if-block, and then print warnings, errors or success information.', 'Localization name')
    ?></strong></p></div><?php
	}
	
  if (isset($_POST['convert_items'])) {
  	feed2post_convertitems($_POST['items']);
    ?><div class="updated"><p><strong><?php 
			_e('Process completed fields in this if-block, and then print warnings, errors or success information.', 'Localization name')
    ?></strong></p></div><?php
	}
	
  if (isset($_POST['fix_items'])) {
  	feed2post_fixitems();
    ?><div class="updated"><p><strong><?php 
			_e('Process completed fields in this if-block, and then print warnings, errors or success information.', 'Localization name')
    ?></strong></p></div><?php
	}
	
	// Set Feed URL
	?>
	<div class=wrap>
		<form method="post">
	  	<h2>Feed to Post</h2>
	    	<fieldset name="set1">
					<legend><?php _e('Select the feeds to watch : ', 'Localization name') ?></legend>
					<label for='feed2post_url' style='align:top;'>Urls (comma separated) : </label><textarea name='feed2post_url' id='feed2post_url' style='width:600px'><?php echo get_option('feed2post_url') ?></textarea>&nbsp;
	     	</fieldset>
	    	<fieldset name="set2">
					<label for='feed2post_url' style='align:top;'>Automatic publishing : </label><input type='checkbox' name='feed2post_auto' id='feed2post_auto' value='1' <?=((get_option('feed2post_auto'))?'CHECKED':'')?>'>&nbsp;
	     	</fieldset>
			<div class="submit"><input type="submit" name="info_update" value="<?php _e('Update options', 'Localization name') ?> »" /></div>
	  </form>
	</div>

	<div class=wrap>
		<form method="post">
	  	<h2>Fix Items</h2>
			<div class="submit"><input type="submit" name="fix_items" value="<?php _e('Fix Items', 'Localization name') ?> »" /></div>
	  </form>
	</div>

	<?
	
	// Conversion
	// get feed content
	// put in f2p_items table
	// show list of last items
	// convert checked items
	?>
	<div class=wrap>
		<form method="post">
	  	<h2>Items to convert</h2>
			<div class="submit"><input type="submit" name="feed_update" value="<?php _e('re-Parse feed', 'Localization name') ?> »" /></div>
	  </form>
			<?
			$items = feed2post_getitems();
//			print_r($items);
			?>
		<style>
			.items { list-style:none; }
			.items li { background: #ccc; padding: 7px; text-align: right; }
			.items li.hover span { font-weight: bold; }
			.items li.converted { background: #eee; }
			.items li span { float: left; }
			.items li div { width:100%; text-align:left; display:none;}
		</style>
		<form method="post">
			<ul class='items'>
				<?foreach($items as $item) {?>
					<li class='<?=(($item->postid)?'converted':'new')?>'>
						<span>
							<?if (!$item->postid) {?><input type='checkbox' name='items[]' value='<?=$item->id?>'><?}?>
							<?=$item->feedname?> - <?=$item->title?>
						</span>
						<?=$item->pubdate?>
						<div>
							<?=$item->description?>
						</div>
					</li>
				<?}?>
			</ul>
			<div class="submit" style='text-align:left;'><input type="submit" name="convert_items" value="<?php _e('Convert Items', 'Localization name') ?> »" /></div>
	  </form>
	  <script>
	  $(document).ready(function(){
	  	$("ul.items li input").click(function(){
	  		$(this).attr('checked', !$(this).attr('checked'))
	  	});
	  	$("ul.items li.new").click(function(){
	  		$(this).find('input').attr('checked', !$(this).find('input').attr('checked'))
	  	});
	  	$("ul.items li.new").hover(
	  		function(){
	  			$(this).addClass('hover');
	  			$(this).find('div').show();
	  		},
	  		function(){
	  			$(this).removeClass('hover');
	  			$(this).find('div').hide();
	  		}
	  	);
	  });
	  </script>
	</div>
	<?
	
	// others options
	// flush feed2post table (remove all items)
	
}

function feed2post_panel() {
	if (function_exists('add_options_page')) {
		add_options_page('feed2post', 'Feed to Post', 8, basename(__FILE__), 'feed2post_subpanel');
	}
}
 
 
?>