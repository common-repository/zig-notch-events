<?php
class Zig_notch_exhibitors extends \Elementor\Widget_Base
{

	public function get_name()
	{
		return 'Zig_notch_exhibitors';
	}

	public function get_title()
	{
		return esc_html__('Exhibitors', 'elementor-addon');
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
				'label' => esc_html__('Exhibitors from event', 'plugin-name'),
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
		<div class='zig-container' id='zig-container-exhibitors' data-content='exhibitors' data-page='<?php echo esc_attr($page_slug) ?>' data-eventId='<?php echo esc_attr($eventID) ?>'>
			<div class='overlay'></div>
			<div class='details-backdrop' id='details-backdrop'></div>

			<div class='container mt-4'>
				<div class='row'>
					<div class='mb-3 col-6'>
						<label for='name_search' class='form-label' id='name_search_label'>Search</label>
						<input type='text' class='form-control' id='name_search' value=''>
						<button type='submit' class='btn btn-primary' id='name_search_button'>Search</button>
					</div>
					<div class='mb-3 col-3'>
						<label for='category_filter' class='form-label'>Category</label>
						<select name='category_filter' id='category_filter'>
							<option value='0'>All</option>
						</select>
					</div>


				</div>
			</div>
			<div class='item-container row' id='item-container-exhibitors'>
			</div>
			<div id='pages'>
				<p></p>
			</div>

		</div>
		<script>
			var container_exhibitors = document.querySelector("#zig-container-exhibitors")
			var items_per_page = 5
			var baseUrl = window.location.hostname
			var protocol = window.location.protocol
			var page_selected = 1
			var project = ''
			var paganation_container = document.getElementById("pages")


			if (container_exhibitors) {
				var event_id_exhibitors = container_exhibitors.dataset.eventid

				function get_and_reder_exhibitors() {
					document.querySelector('#item-container-exhibitors').innerHTML = ''
					document.body.classList.add('loading');


					fetch(protocol + '//' + baseUrl + project + '/wp-json/get/Exhibitors/all/' + event_id_exhibitors + '/' + page_selected + '/' + items_per_page + '/' + searchPhrase + '/' + category_filter_val)
						.then((response) => {
							return response.json();
						})
						.then(function(data) {
							document.body.classList.remove('loading')

							document.querySelector('#category_filter').innerHTML = "<option value='0'>All</option>" + data.filters

							document.querySelector('#item-container-exhibitors').innerHTML = data.content

							document.body.classList.remove('loading')

						})
						.catch(function(error) {
							console.log(error);
						});
				}
				var category_filter_val = 0
				var page = 1

				document.body.classList.add("loading")

				var searchPhrase = document.getElementById('name_search').value
				if (searchPhrase == "") {
					searchPhrase = "no_search_phrase"
				}

				document.body.classList.add('loading');

				fetch(protocol + '//' + baseUrl + project + '/wp-json/get/Exhibitors/all/' + event_id_exhibitors + '/' + page_selected + '/' + items_per_page + '/' + searchPhrase + '/' + category_filter_val)
					.then((response) => {
						return response.json();
					})
					.then(function(data) {
						document.body.classList.remove('loading')

						// paganation
						if (data.hasnext == true) {
							var number_of_pages = Math.ceil(data.total / items_per_page)
							var page = 1
							var paganation = ""
							var number

							while (page <= number_of_pages) {
								paganation = document.createElement("button")
								number = document.createTextNode(page)

								paganation.appendChild(number)
								paganation.classList.add("pagantion_number_button")
								paganation.id = "pagantion_number_button_" + page
								paganation.dataset.page = page
								paganation.href = "#" + page

								document.getElementById("pages").appendChild(paganation)
								page++
							}
						}

						// frst contetn render

						document.querySelector('#category_filter').innerHTML = "<option value='0'>All</option>" + data.filters
						document.querySelector('#item-container-exhibitors').innerHTML = data.content
						// page change
						var paga_button = document.querySelectorAll('.pagantion_number_button')

						for (let i = 0; i < paga_button.length; i++) {
							paga_button[i].addEventListener("click", function() {
								document.body.classList.add('loading');
								var page_selected = paga_button[i].dataset.page

								document.querySelector('#item-container-exhibitors').innerHTML = ''
								document.body.classList.add('loading');

								fetch(protocol + '//' + baseUrl + project + '/wp-json/get/Exhibitors/all/' + event_id_exhibitors + '/' + page_selected + '/' + items_per_page + '/' + searchPhrase + '/' + category_filter_val)
									.then((response) => {
										return response.json();
									})
									.then(function(data) {
										document.body.classList.remove('loading')

										document.querySelector('#category_filter').innerHTML = "<option value='0'>All</option>" + data.filters

										document.querySelector('#item-container-exhibitors').innerHTML = data.content

										document.body.classList.remove('loading')

									})
									.catch(function(error) {
										console.log(error);
									});
							});
						}
						// category filter
						const category_filter = document.querySelector('#category_filter');
						category_filter.addEventListener('change', (event) => {
							category_filter_val = event.target.value

							get_and_reder_exhibitors()

						});
					})
					.catch(function(error) {
						console.log(error);
					});

				document.querySelector('#name_search_button').addEventListener('click', () => {
					searchPhrase = document.querySelector('#name_search').value
					if (searchPhrase == "") {
						searchPhrase = "no_search_phrase"
					}
					get_and_reder_exhibitors()

				})
			}
		</script>
<?php
	}
}
