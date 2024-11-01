<?php
class Zig_notch_products extends \Elementor\Widget_Base
{

	public function get_name()
	{
		return 'Zig_notch_products';
	}

	public function get_title()
	{
		return esc_html__('Products', 'elementor-addon');
	}

	public function get_icon()
	{
		return 'eicon-products';
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
				'label' => esc_html__('Products from event', 'zig-notch-events'),
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

		$this->end_controls_section();
	}

	protected function render()
	{
		global $apiBaseUrl;
		global $post;

		$settings = $this->get_settings_for_display();
		$eventID = esc_attr($settings['evenrID']);

		$page_slug = $post->post_name;

		echo "";
?>
		<div class='zig-container' id='zig-container-products' style='min-height: 500px' data-content='products' data-page='<?php echo esc_attr($page_slug) ?>' data-eventId='<?php echo esc_attr($eventID) ?>'>
			<div class='overlay'></div>
			<div class='details-backdrop' id='details-backdrop'></div>
			<div class='container-fluid' style='margin-top: 0!important; max-width: 100%;'>
				<div class='item-container' id='item-container-products'>

				</div>
			</div>
		</div>
		<script>
			var container_products = document.querySelector("#zig-container-products")
			var items_per_page = 5
			var baseUrl = window.location.hostname
			var protocol = window.location.protocol
			var page_selected = 1
			var project = ''
			var paganation_container = document.getElementById("pages")

			if (container_products) {
				var event_id_products = container_products.dataset.eventid

				function get_and_reder_products() {
					document.querySelector('#item-container-products').innerHTML = ''
					document.body.classList.add('loading');

					fetch(protocol + '//' + baseUrl + project + '/wp-json/get/Companies/eventProductsList/' + event_id_products)
						.then((response) => {
							return response.json();
						})
						.then(function(data) {
							document.body.classList.remove('loading')
							document.querySelector('#item-container-products').innerHTML = data.content
							document.body.classList.remove('loading')
						})
						.catch(function(error) {
							console.log(error);
						});
				}
				get_and_reder_products()
			}
		</script>
<?php
	}
}
