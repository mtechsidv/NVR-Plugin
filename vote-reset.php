<?php
/*
Plugin Name: Vote Reset
Description: Resets Negative Votes to 0...
Version:     1.0
Author:      Sid V
*/

function nvr_plugin() {
    //add an item to the menu
    add_submenu_page('edit.php?post_type=coupon', 
        'Vote Reset',
        'Reset All Negative Votes',
        'manage_options',
        'vote-reset',
		'negativevotesreset'
    );
}
add_action( 'admin_menu', 'nvr_plugin' );

function negativevotesreset () {
	global $wpdb;
	$reset_records_array = array();

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	?>
	
	<form id = "myform" action = "" method = "post">
 
    <h2>What Do You Want To Do?</h2>
	    <div style="margin-bottom: 8px;font-size: medium;"><input class="nvr_radio" type="radio" name="nvr-radio" value="yes"/><span style="margin-left: 5px;">Reset Negative Votes</span><br></div>
	    <div style="margin-bottom: 8px;font-size: medium;"><input class="nvr_radio" type="radio" name="nvr-radio" value="show"/><span style="margin-left: 5px;">Show History</span><br></div>
	    <div style="margin-bottom: 8px;font-size: medium;"><input type="submit" class="button-primary" value="Submit"/></div>
	</form>
	
	<?php 
    
    if (isset($_POST['nvr-radio'])) { 
    	if ($_POST['nvr-radio']=="yes") { 
			$query1 = $wpdb->prepare( "UPDATE $wpdb->clpr_votes_total SET votes_down = 0, votes_total = votes_up WHERE votes_down > 0");
			$query2 = $wpdb->prepare( "UPDATE $wpdb->clpr_votes_total SET votes_up = 1, votes_total = 1 WHERE votes_up = 0");
			$result1 = $wpdb->query( $query1 );
			$result2 = $wpdb->query( $query2 );
			
			$metaquery = "UPDATE $wpdb->postmeta SET meta_value = '0' WHERE meta_key = 'clpr_votes_down'";
			$result3 = $wpdb->query( $metaquery );
			
			$metaquery1 = "UPDATE $wpdb->postmeta SET meta_value = '1' WHERE meta_key = 'clpr_votes_up' AND meta_value = '0' ";
			$result4 = $wpdb->query( $metaquery1 );

			// update clpr_votes_percent to 100%
			$metaquery2 = "UPDATE $wpdb->postmeta SET meta_value = '100' WHERE meta_key = 'clpr_votes_percent'";
			$result5 = $wpdb->query( $metaquery2 ); 

			if ($result1 && $result2 && $result3 && $result4 && $result5) { 
				echo '<h3>All Negative Votes Cleared</h3>';

				$todays_date = date('d M Y');

				$reset_records = get_option( 'vote_reset_record' );
				if($reset_records) {
					$reset_records_array = explode(',', $reset_records);
				}

				array_unshift( $reset_records_array, $todays_date );
				
				echo '<table style="width:15%;" class="wp-list-table widefat fixed posts">
				<thead>
				<tr>
			    <th>Votes Reset Dates</th>
			  	</tr>
			  	</thead>';

				foreach( $reset_records_array as $record ) {
					echo '<tr>';
					echo '<td>' . $record . '</td>';
					echo '</tr>';
				}
				echo '</table>';

				$reset_records_string = implode( ",", $reset_records_array );

				update_option( 'vote_reset_record', $reset_records_string );
			}
			else{
				echo '<h3>No Negative Votes Found.</h3>';	
			}
    	}
    	
    	elseif($_POST['nvr-radio']=="show") {

    		//$get_records = $wpdb->get_results( "SELECT * FROM `wp_nvr_records` ORDER BY date_time DESC;" );
				
			$reset_records = get_option( 'vote_reset_record' );
			
			if($reset_records) {
				$reset_records_array = explode(',', $reset_records);
			}

			echo '<table style="width:15%;" class="wp-list-table widefat fixed posts">
			<thead>
			<tr>
		    <th>Votes Reset Dates</th>
		  	</tr>
		  	</thead>';

			foreach($reset_records_array as $record) {

				echo '<tr>';
				echo '<td>' . $record . '</td>';
				echo '</tr>';
			}
			echo '</table>';
    	}
	}	
}
