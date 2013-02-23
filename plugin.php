<?php
/*
Plugin Name: WP Posting Average
Plugin URI: http://www.gravitywiz.com
Description: Display average time between posts in seconds for all posts or individual Author's posts.
Version: 1.0.beta1
Author: Ounce Of Talent
Author URI: http://www.gravitywiz.com
Author Email: david@ounceoftalent.com
License:

  Copyright 2013 - Ounce Of Talent (david@ounceoftalent.com)

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

class WPPostingAverage
{

	//---------------------------------------------------------------------------------------
	//								WP Posting Average Core
	//---------------------------------------------------------------------------------------
	 
	/**
	 * WPPostingAverage::__construct()
	 * 
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 *
	 * @return void
	 */
	function __construct()
	{
		// Load plugin text domain
		add_action( 'init', array( $this, 'plugin_textdomain' ) );

		// Register admin actions
		if (is_admin())
			add_action('wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
	}

	/**
	 * WPPostingAverage::plugin_textdomain()
	 * 
	 * Loads the plugin text domain for translation
	 *
	 * @return void
	 */
	public function plugin_textdomain()
	{
		$domain = 'wp-posting-average-locale';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
        load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	//---------------------------------------------------------------------------------------
	//							   End WP Posting Average Core
	//---------------------------------------------------------------------------------------
	//							    WP Posting Avarage Methods
	//---------------------------------------------------------------------------------------
	
	/**
	 * WPPostingAverage::dashboard_widget_function()
	 *
	 * The dashboard widget's content
	 * 
	 * @return void
	 */
	public function dashboard_widget_function()
	{
		$total_posting_average = $this->get_posting_average();
		$author_posting_average = $this->get_posting_average( array( 'author' => get_current_user_id() ) );

		?>
			<h4>Site Wide Posting Average</h4>
			<em><?php echo $this->format_seconds( $total_posting_average ); ?></em>

			<h4 style="padding-top:15px;">Your Posting Average</h4>
			<em><?php echo $this->format_seconds( $author_posting_average ); ?></em>
		<?php
	}

	/**
	 * WPPostingAverage::add_dashboard_widgets()
	 *
	 * Add the WP Posting Average dashboard widget
	 *
	 * @return void
	 */
	public function add_dashboard_widgets()
	{
		wp_add_dashboard_widget(
			'wp_posting_average_widget',
			'WP Posting Average',
			array( $this, 'dashboard_widget_function' )
		);
	}
	
	/**
	 * WPPostingAverage::get_post_average()
	 * 
	 * Retrieve average time between posts in seconds.
	 *
	 * @param $args array
	 * @return int
	 */
	public function get_posting_average( $args = array() )
	{
		$posts = get_posts( wp_parse_args( $args, array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'order' => 'desc'
			) ) );

		$total = 0;

		for( $i = 1; $i < count( $posts ); $i++ ) {
			$total += strtotime( $posts[$i]->post_date_gmt ) - strtotime( $posts[$i - 1]->post_date_gmt );
		}

		$total = abs( $total ) / count( $posts );

		return $total;
	}

	/**
	 * WPPostingAverage::format_seconds()
	 *
	 * Convert Seconds to Larger Units
	 *  
	 * @param  $secs int
	 * @return string
	 */
	public function format_seconds($secs) {
	    $secs = (int)$secs;

	    if ( $secs === 0 )
	        return '0 secs';

	    $mins   = 0;
	    $hours  = 0;
	    $days   = 0;
	    $weeks  = 0;
	    $result = '';

	    if ( $secs >= 60 ) {
	        $mins = (int)($secs / 60);
	        $secs = $secs % 60;
	    }

	    if ( $mins >= 60 ) {
	        $hours = (int)($mins / 60);
	        $mins = $mins % 60;
	    }

	    if ( $hours >= 24 ) {
	        $days = (int)($hours / 24);
	        $hours = $hours % 60;
	    }

	    if ( $days >= 7 ) {
	        $weeks = (int)($days / 7);
	        $days = $days % 7;
	    }

	    if ( $weeks )
	        $result .= "{$weeks} week(s) ";

	    if ( $days )
	        $result .= "{$days} day(s) ";

	    if ( $hours )
	        $result .= "{$hours} hour(s) ";

	    if ( $mins )
	        $result .= "{$mins} min(s) ";

	    if ( $secs )
	        $result .= "{$secs} sec(s) ";

	    $result = rtrim($result);

	    return $result;
	}

	//---------------------------------------------------------------------------------------
	//							   End WP Posting Average Core
	//---------------------------------------------------------------------------------------

}

$plugin_name = new WPPostingAverage();
