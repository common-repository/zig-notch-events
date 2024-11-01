<?php
class Zig_notch_agenda extends \Elementor\Widget_Base
{

	public function get_name()
	{
		return 'Zig_notch_agenda';
	}

	public function get_title()
	{
		return esc_html__('Agenda', 'elementor-addon');
	}

	public function get_icon()
	{
		return 'eicon-calendar';
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
				'label' => esc_html__('Agenda from event', 'plugin-name'),
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
				'label' => esc_html__('Events', 'plugin-name'),
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
?>
		<div class='zig-container' id='zig-container-agenda' style='min-height: 500px' data-content='agenda' data-page='<?php echo esc_attr($page_slug) ?>' data-eventId='<?php echo esc_attr($eventID) ?>'>

			<div class='overlay'></div>
			<div class='container' style='margin-top: 0!important;'>
				<div class='row item-container '>
					<div class='agenda_day_container col-2' id='agenda_day_container'>

					</div>
					<div class='col-10'>
						<p id='session_day_selected'></p>
						<div id='session_container'>

						</div>
					</div>
				</div>
			</div>
		</div>
		<script>
			var container_agenda = document.querySelector("#zig-container-agenda")
			var items_per_page = 5
			var baseUrl = window.location.hostname
			var protocol = window.location.protocol
			var page_selected = 1
			var project = ''

			// SESSIONS
			if (container_agenda) {
				var event_id_agenda = container_agenda.dataset.eventid

				document.body.classList.add('loading');

				function get_agenda_data(selected_date) {
					fetch(protocol + '//' + baseUrl + project + '/wp-json/get/Session/daydata/' + event_id_agenda + '/' + selected_date)
						.then((response) => {
							return response.json();
						})
						.then(function(data) {
							document.querySelector('#session_container').innerHTML = ''
							document.querySelector('#session_container').innerHTML = data.content
							document.body.classList.remove('loading')
						})
						.catch(function(error) {
							console.log(error);
						});
				}

				fetch(protocol + '//' + baseUrl + project + '/wp-json/get/Sessions/days/' + event_id_agenda)
					.then((response) => {
						return response.json();
					})
					.then(function(data) {
						document.body.classList.remove('loading')
						document.querySelector('#agenda_day_container').innerHTML = data.content
						document.querySelector('#session_day_selected').innerHTML = data.first_date_formated
						document.querySelector('#session_day_selected').dataset.date = data.first_date


						document.querySelector('#day_selector li:first-child').classList.add('selected_date')

						var selected_date = data.first_date
						get_agenda_data(selected_date)

						var day_selector = document.querySelectorAll('.agenda_day')

						for (let i = 0; i < day_selector.length; i++) {
							day_selector[i].addEventListener("click", function() {
								document.body.classList.add('loading');
								selected_date = day_selector[i].dataset.agenda_day
								document.querySelector('.selected_date').classList.remove('selected_date')
								day_selector[i].classList.add('selected_date')
								get_agenda_data(selected_date)
							});
						}
					})
					.catch(function(error) {
						console.log(error);
					});
			}
		</script>

<?php
	}
}
