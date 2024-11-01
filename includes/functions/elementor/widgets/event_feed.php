<?php
class Zig_notch_event_feed extends \Elementor\Widget_Base
{

	public function get_name()
	{
		return 'Zig_notch_event_feed';
	}

	public function get_title()
	{
		return esc_html__('Event feed', 'elementor-addon');
	}

	public function get_icon()
	{
		return 'eicon-welcome';
	}

	public function get_categories()
	{
		return ['zig_notch'];
	}

	protected function register_controls()
	{
		global $apiBaseUrl;

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__('Feed from event', 'zig-notch-events'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$url = $apiBaseUrl . "Events/list";
		$headers = array('headers' => array(
			'x-api-key' => get_option('zig_notch_api_key'),
			'Cache-Control' => 'max-age=31536000',
		));
		$api_response = wp_remote_get($url, $headers);
		$api_response_body = wp_remote_retrieve_body($api_response);
		$eventsList = json_decode($api_response_body);

		foreach ($eventsList as $event) {
			$eventID = $event->eventID;
			$eventName = $event->name;

			$option[$eventID] = $eventID . ": " . $eventName;
		}
		$this->add_control(

			'evenrID',
			[
				'label' => esc_html__('Events', 'zig-notch-events'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '830',
				'options' => $option,
			]
		);
		$this->add_control(
			'hr',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);
		$this->add_control(
			'show_products',
			[
				'label' => esc_html__('Display products', 'zig-notch-events'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'your-plugin'),
				'label_off' => esc_html__('Hide', 'your-plugin'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		$this->add_control(
			'show_sponsors',
			[
				'label' => esc_html__('Display sponsors', 'zig-notch-events'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'your-plugin'),
				'label_off' => esc_html__('Hide', 'your-plugin'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		$this->add_control(
			'show_speakers',
			[
				'label' => esc_html__('Display speakers', 'zig-notch-events'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'your-plugin'),
				'label_off' => esc_html__('Hide', 'your-plugin'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		$this->add_control(
			'show_sessions',
			[
				'label' => esc_html__('Display sessions', 'zig-notch-events'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'your-plugin'),
				'label_off' => esc_html__('Hide', 'your-plugin'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function render()
	{
		global $apiBaseUrl;
		global $post;

		$settings = $this->get_settings_for_display();
		$eventID = esc_attr($settings['evenrID']);
		$dispalyEventProducts = esc_attr($settings['show_products']);
		if ($dispalyEventProducts === 'yes') {
			$display_products = "true";
		} else {
			$display_products = "false";
		}

		$dispalyEventSponsors = esc_attr($settings['show_sponsors']);
		if ($dispalyEventSponsors === 'yes') {
			$display_sponsors = "true";
		} else {
			$display_sponsors = "false";
		}

		$dispalyEventSpeakers = esc_attr($settings['show_speakers']);
		if ($dispalyEventSpeakers === 'yes') {
			$display_speakers = "true";
		} else {
			$display_speakers = "false";
		}

		$dispalyEventSessions = esc_attr($settings['show_sessions']);
		if ($dispalyEventSessions === 'yes') {
			$display_sessions = "true";
		} else {
			$display_sessions = "false";
		}



		$page_slug = $post->post_name;


?>

		<div class='zig-container' id='zig-container-event_feed' data-content='event_feed' data-page='<?php echo esc_attr($page_slug) ?>' data-eventId='<?php echo esc_attr($eventID) ?>'>
			<div class='overlay'></div>
			<div class='feed_cover_container_outer' id='feed_cover_container_outer'></div>
			<div class='details-backdrop' id='details-backdrop'></div>
			<div class='feed_container'>
				<div class='item-container' id='item-container-feed'>
				</div>
				<div class='feed_side_bar'>
					<div class='products' id='products' data-dispaly='<?php echo esc_attr($display_products) ?>'>

					</div>
					<div class='sponsors' id='sponsors' data-dispaly='<?php echo esc_attr($display_sponsors) ?>'>

					</div>
					<div class='speakers' id='speakers' data-dispaly='<?php echo esc_attr($display_speakers) ?>'>

					</div>
					<div class='sessions' id='sessions' data-dispaly='<?php echo esc_attr($display_sessions) ?>'>

					</div>
				</div>
			</div>

		</div>

		<div class='feed_read_more'>
			<p>For more details on out amazing event please visit us at ZINGNothc.com</p>
			<a href='https://www.app.zignotch.com/event/$eventID'>ZINGNothc.com</a>
		</div>
		</div>
		<script>
			var items_per_page = 5
			var baseUrl = window.location.hostname
			var protocol = window.location.protocol
			var page_selected = 1
			var project = ''
			var container_event_feed = document.querySelector("#zig-container-event_feed")
			if (container_event_feed) {
				var event_id_event_feed = container_event_feed.dataset.eventid

				document.body.classList.add('loading')
				fetch(protocol + '//' + baseUrl + project + '/wp-json/get/Events/GetEventFeed/' + event_id_event_feed + '/' + page_selected + '/5')
					.then((response) => {
						return response.json();
					})
					.then(function(data) {
						document.querySelector('#feed_cover_container_outer').innerHTML = data.cover
						document.querySelector('#item-container-feed').innerHTML = data.content_feed
						if (document.querySelector('#sponsors').dataset.dispaly === 'true') {
							document.querySelector("#sponsors").innerHTML = '<p>Proudly supported by</p>' + data.content_sponsors
						} else {
							document.querySelector("#sponsors").classList.add('hide')
						}
						if (document.querySelector('#speakers').dataset.dispaly === 'true') {
							document.querySelector('#speakers').innerHTML = '<p>Featured speakers' + data.content_speakers
						} else {
							document.querySelector("#speakers").classList.add('hide')
						}
						if (document.querySelector('#products').dataset.dispaly === 'true') {
							document.querySelector('#products').innerHTML = '<p>Products you may be interested in<p>' + data.content_products
						} else {
							document.querySelector("#products").classList.add('hide')
						}
						if (document.querySelector('#sessions').dataset.dispaly === 'true') {
							document.querySelector('#sessions').innerHTML = '<p>Sessions you may wish to attend</p>' + data.content_sessions
						} else {
							document.querySelector("#sessions").classList.add('hide')
						}
						document.body.classList.remove('loading')
					})
					.catch(function(error) {
						console.log(error);
					});
			}
		</script>
<?php
	}
}
