<?php 
/*
Section: Followers Social Bar
Author: Enrique Chavez
Author URI: http://tmeister.net
Version: 1.0
Description: The Followers Social Bar section allows you to show the followers/subscribers from your social networks.
Class Name: TmFollowersBar
Cloning: false
Workswith: main, morefoot
*/

class TmFollowersBar extends PageLinesSection {

	var $domain = 'tm_followers_social_bar';
	var $prefix = 'tm_sf_';
	var $accounts;
	var $cache_expiration = 3600; //1 hour

	function section_persistent(){
		add_filter('pagelines_options_array', array($this, 'get_options'));
		$this->set_accounts_apis();
	} 

	function section_head(){
	?>
		<!--[if IE]>
		<style>
			.social_followers.sf_bubble ul li span.sf_count{
				border-radius: 0px;
			}
		</style>
		<![endif]-->
		<!--[if IE 7]>
		<style>
			.social_followers ul li{
				float:left;
			}
		</style>
		<![endif]-->
	<?
	} 
	function section_template( $clone_id = null ) { 
		global $post, $pagelines_ID;
		$oset            = array('post_id' => $pagelines_ID, 'clone_id' => $clone_id);
		$tm_sf_theme     = ( ploption('tm_sf_theme', $oset) ) ? ploption('tm_sf_theme', $oset) : 'sf_cloud';
		$tm_sf_align     = ( ploption('tm_sf_align', $oset) ) ? ploption('tm_sf_align', $oset) : 'left';
		$tm_sf_icons_set = ( ploption('tm_sf_icons_set', $oset) ) ? ploption('tm_sf_icons_set', $oset) : 'normal_icons';
		$found = false;
		foreach ($this->accounts as $network) {
			$var = $network['account'];
			$$var = ( ploption($var) ) ? ploption($var, $oset) : null;
			if( !$found && $$var != null ){
				$found = true;
			}
		}
		if( !$found ){
			echo setup_section_notify($this, __('Please, Enter at least one social network user.', $this->domain), '/wp-admin/admin.php?page=pagelines', 'Followers Social Bar Options' );
			return;
		}
	?>
		<div class="social_followers <?php echo $tm_sf_theme ?> <?php echo $tm_sf_align ?>">
			<ul class="<?php echo $tm_sf_icons_set; ?>">
				<?php foreach ($this->accounts as $network): $user_id = $$network['account'];?>
					<?php if ( $tm_sf_theme != 'sf_big_text' && $tm_sf_theme != 'sf_small_text' ): ?>
						<?php if ($user_id != null && $count = $this->get_social_data($network, $user_id)): ?>
							<li class="<?php echo $network['class'] ?>">
								<span class="sf_count"><?php echo $count;?></span>
								<span class="sf_icon">
									<a href="<?php echo $network['url'].$user_id ?>" target="_blank"></a>
								</span>
								<span class="sf_label"><?php echo $network['label'] ?></span>
							</li>		
						<?php endif ?>
					<?php else: ?>
						<?php if ($user_id != null && $count = $this->get_social_data($network, $user_id)): ?>
							<li class="<?php echo $network['class'] ?>">
								<span class="sf_icon">
									<a href="<?php echo $network['url'].$user_id ?>" target="_blank" title="<?php echo $user_id ?>"></a>
								</span>
								<div class="sf_text">
									<span class="sf_count"><?php echo $count;?></span>
									<span class="sf_label"><?php echo $network['label'] ?></span>
								</div>
							</li>		
						<?php endif ?>
					<?php endif ?>
				<?php endforeach ?>
			</ul>
		</div>
	<?php
	}

	function section_scripts(){
		return array();
	}

	function get_options($options){
		$options['followers_social_bar'] = array(
			'icon' => $this->icon,
			'tm_sf_theme' => array(
				'title'			=> __( 'Theme - Layout', $this->domain),
				'type'         	=> 'select',
				'selectvalues' 	=> array(
					'sf_cloud'      => array('name' => __( 'Cloud', $this->domain) ),
					'sf_bubble'     => array('name' => __( 'Bubble', $this->domain) ),
					'sf_big_text'   => array('name' => __( 'Big Numbers', $this->domain) ),
					'sf_small_text' => array('name' => __( 'Small Numbers', $this->domain) ),
				),
				'inputlabel'   	=> __( 'Select theme', $this->domain ),
				'shortexp' 		=> __( 'Default value: Cloud', $this->domain ),
				'exp'      		=> __( 'Indicates which theme to use when the section shown', $this->domain )
			),
			'tm_sf_icons_set' => array(
				'title'			=> __( 'Icon Set', $this->domain ),
				'type'         	=> 'select',
				'selectvalues' 	=> array(
					'normal_icons'  => array('name' => __( 'Normal', $this->domain) ),
					'cloud_icons'   => array('name' => __( 'Cloud', $this->domain) ),
					'elegant_icons' => array('name' => __( 'Elegant', $this->domain) ),
					'circle_icons'  => array('name' => __( 'Circle', $this->domain) ),
				),
				'inputlabel'   	=> __( 'Select icons', $this->domain ),
				'shortexp' 		=> __( 'Default value: Normal', $this->domain ),
				'exp'      		=> __( 'Indicates which icon set use when the section shown.', $this->domain )
			),
			'tm_sf_align' => array(
				'title'			=> __( 'Alignment', $this->domain ),
				'type'         	=> 'select',
				'selectvalues' 	=> array(
					'left'   => array('name' => __( 'Left', $this->domain) ),
					'center' => array('name' => __( 'Center', $this->domain) ),
					'right'  => array('name' => __( 'Right', $this->domain) ),
				),
				'inputlabel'   	=> __( 'Alignment', $this->domain ),
				'shortexp' 		=> __( 'Default value: Left', $this->domain ),
				'exp'      		=> __( 'The alignment of the bar\'s container.', $this->domain )
			),
			'tm_sf_accoounts' 	=> array(
				'type' 			=> 'text_multi',
				'title' 		=> __( 'Social Networks', $this->domain),
				'shortexp'		=> __( 'Enter your Social Network usernames', $this->domain),
				'inputlabel'	=> '',
				'exp' 			=> __( 'Leave blank to not show the account.', $this->domain),
				'selectvalues'	=> array(
					'tm_sf_twitter'    => array('inputlabel'=> __( 'Twitter - Username', $this->domain )),
					'tm_sf_facebook'   => array('inputlabel'=> __( 'Facebook - FB Page ID/Name', $this->domain )),
					'tm_sf_digg'       => array('inputlabel'=> __( 'Digg - Username', $this->domain )),
					'tm_sf_feedburner' => array('inputlabel'=> __( 'Feedburner - Username', $this->domain )),
					'tm_sf_youtube'    => array('inputlabel'=> __( 'Youtube - Username', $this->domain )),
					'tm_sf_vimeo'      => array('inputlabel'=> __( 'Vimeo - Username', $this->domain ))
				)
			)
		);
		return $options;
	}

	/**************************************************************************
	**
	**************************************************************************/

	function set_accounts_apis()
	{
		$this->accounts['facebook'] = array(
			'url'              => 'https://facebook.com/',
			'api_base'         => 'http://graph.facebook.com/',
			'account'          => $this->prefix.'facebook',
			'label'            => __( 'fans', $this->domain ),
			'class'            => 'sf_facebook_network',
			'social_count_key' => 'likes',
			'name'             => 'facebook'
		);
		$this->accounts['twitter'] = array(
			'url'              => 'http://twitter.com/',
			'api_base'         => 'http://api.twitter.com/1/users/show.json?skip_status=true&screen_name=',
			'account'          => $this->prefix.'twitter',
			'label'            => __( 'followers', $this->domain ),
			'class'            => 'sf_twitter_network',
			'social_count_key' => 'followers_count',
			'name'             => 'twitter'
		);
		$this->accounts['feedburner'] = array(
			'url'              => 'http://feeds.feedburner.com/',
			'api_base'         => 'http://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=',
			'account'          => $this->prefix.'feedburner',
			'label'            => __( 'subscribers', $this->domain ),
			'class'            => 'sf_feedburner_network',
			'social_count_key' => '',
			'name'             => 'feedburner'
		);
		$this->accounts['youtube'] = array(
			'url'              => 'http://youtube.com/',
			'api_base'         => 'http://gdata.youtube.com/feeds/api/users/',
			'account'          => $this->prefix.'youtube',
			'label'            => __( 'subscribers', $this->domain ),
			'class'            => 'sf_youtube_network',
			'social_count_key' => '',
			'name'             => 'youtube'
		);
		$this->accounts['vimeo'] = array(
			'url'              => 'http://vimeo.com/',
			'api_base'         => 'http://vimeo.com/api/v2/%s/info.json',
			'account'          => $this->prefix.'vimeo',
			'label'            => __( 'likes', $this->domain ),
			'class'            => 'sf_vimeo_network',
			'social_count_key' => 'total_videos_liked',
			'name'             => 'vimeo'
		);
		$this->accounts['digg'] = array(
			'url'              => 'http://digg.com/',
			'api_base'         => 'http://services.digg.com/2.0/user.getInfo?usernames=',
			'account'          => $this->prefix.'digg',
			'label'            => __( 'followers', $this->domain ),
			'class'            => 'sf_digg_network',
			'social_count_key' => '',
			'name'             => 'digg'
		);
	}

	function get_social_data($network, $network_user_id)
	{
		$network_cached = get_transient($network['account']);
		if(!$network_cached || ($network_cached->id != $network_user_id)) {
			switch ($network['name']) {
				case 'vimeo':
					$url_api = sprintf( $network['api_base'], $network_user_id );
					break;
				default:
					$url_api = $network['api_base'] . $network_user_id;
					break;
			}
			$response = wp_remote_get( $url_api );

			if(is_wp_error($response) or (wp_remote_retrieve_response_code($response) != 200)){
			    return false;
			}

			switch ($network['name']) {
				case 'facebook':
				case 'twitter':
				case 'vimeo':
					$api_data = json_decode(wp_remote_retrieve_body($response), true);		
					if(!is_array($api_data) or isset($api_data['error']) or !isset($api_data[$network['social_count_key']])){
					    return false;
					}
					$count = $this->convert_count($api_data[$network['social_count_key']]);
					break;
				case 'feedburner':
					$api_data = simplexml_load_string(wp_remote_retrieve_body($response));
					if(!$api_data or isset($api_data->err) or !isset($api_data->feed->entry['circulation'])){
						return false;
					}

					$count = $this->convert_count($api_data->feed->entry['circulation']);
					break;
				case 'digg':
					$api_data = json_decode(wp_remote_retrieve_body($response), true);
					if(!is_array($api_data) or !isset($api_data['users'][$network_user_id]['followers'])){
					    return false;
					}
								
					$count = $this->convert_count($api_data['users'][$network_user_id]['followers']);
					break;
				case 'youtube':
					$api_data = simplexml_load_string(wp_remote_retrieve_body($response));
					if(!$api_data or isset($api_data->err) or !isset($api_data->children('http://gdata.youtube.com/schemas/2007')->statistics->attributes()->subscriberCount)){
						return false;
					}
					$count = $this->convert_count((int) $api_data->children('http://gdata.youtube.com/schemas/2007')->statistics->attributes()->subscriberCount);			
					break;
			}
			
			$data = new stdClass();
			$data->id = $network_user_id;
			$data->count = $count;
			set_transient($network['account'], $data, $this->cache_expiration);
			return $count;
		}

		return $network_cached->count;
	}

	function convert_count($number=0){
	
		$number = (int)$number; // make sure the number is a integer
		
		switch($number) {
		
			case ($number == 0):
				return $number;
			break;
			
			case ($number < 1000):
				return $number;	// the number is not changed 	
			break;
			
			case ($number > 999 && $number < 10000):
				$number = (string)$number; // convert into a string
				$number = substr($number, 0, 1).'k';
				return $number;
			break;
			
			case ($number > 9999 && $number < 100000):
				$number = (string)$number; // convert into a string
				$number = substr($number, 0, 2).'k';
				return $number;
			break;
			
			case ($number > 99999 && $number < 1000000):
				$number = (string)$number; // convert into a string
				$number = substr($number, 0, 3).'k';
				return $number;
			break;
			
			case ($number > 999999 && $number < 10000000):
				$number = (string)$number; // convert into a string
				$number = substr($number, 0, 1).'m';
				return $number;
			break;
			
			case ($number > 9999999 && $number < 100000000):
				$number = (string)$number; // convert into a string 
				$number = substr($number, 0, 2).'m';
				return $number;
			break;
			
			case ($number > 99999999 && $number < 1000000000):
				$number = (string)$number; // convert into a string
				$number = substr($number, 0, 3).'m';
				return $number;
			break;
			
			default:
				return $number;
			break;
			
		}
		
	}

}
			