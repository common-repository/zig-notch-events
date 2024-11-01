<?php
// Admin actions and fillters
add_action('admin_menu', 'zignotch_nav_settings');
add_action('admin_init', 'zignotch_api_settings');
add_action('admin_post_zignotch_revoke_key', 'zignotch_revoke_key');
add_action('admin_post_revokepages', 'revokepages');
add_action('admin_head', 'zignotch_admin_custom_style');
add_action('elementor/elements/categories_registered', 'zignotch_add_elementor_widget_categories');

$apiBaseUrl = "https://extapi.zignotch.com/";

function zignotch_admin_custom_style()
{
    $zig_allowed_style_atributs = [
        'style' => []
    ];
    $style = "<style>
                    #toplevel_page_zig-notch-admin-page a div.wp-menu-image.dashicons-before img{width:20px !important; height: auto;}
                    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button {opacity: 1;}
                </style>";


    echo wp_kses($style, $zig_allowed_style_atributs);
}

function zignotch_nav_settings()
{
    $iconUrl = plugin_dir_url(__DIR__) . "images/icon.png";
    add_menu_page('Zig Notch settings', __('Zig Notch', 'Zig Nothc Domain'), 'manage_options', 'zig-notch-admin-page', 'zignotch_admin_settings', $iconUrl, 6);
}

function zignotch_admin_settings()
{
    include(plugin_dir_path(__DIR__) .  "templates/admin-templates/admin-settings.php");
}

function zignotch_api_settings()
{
    add_settings_section('zignotch_api_settings', 'Api settings', NULL, 'api-settings');

    add_settings_field('zig_notch_api_key', 'Api key: ', 'zignotch_api_key_html', 'api-settings', 'zignotch_api_settings');
    register_setting('apisettings', 'zig_notch_api_key', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
}

function zignotch_api_key_html()
{
    $zig_allowed_input_atributes = [
        'input' => array(
            'type'      => array(),
            'name'      => array(),
            'value'     => array(),
            'checked'   => array(),
            'style'     => array()
        ),
    ];

    $zig_api_key_input = "<input type='text' name='zig_notch_api_key' value='" .
        esc_html(get_option('zig_notch_api_key')) . "' style = 'width: 300px; text-align: center'>";

    echo wp_kses($zig_api_key_input, $zig_allowed_input_atributes);
}

function zignotch_revoke_key()
{
    update_option('zig_notch_api_key', '');

    wp_redirect(admin_url('?page=zig-notch-admin-page'));
}
// Gerneral use funcions

function zignotch_format_date($date, $format)
{
    $dateFormated = date_create($date);
    $dateFormated = date_format($dateFormated, $format);

    return $dateFormated;
}

function zignotch_nclude_template_part($section, $templatePart)
{
    return $template = plugin_dir_path(__DIR__) . "templates/template-parts/$section/$templatePart.php";
}

function zignotch_chek_if_cover_image_exist($cover)
{
    if ($cover == NULL) {
        return plugin_dir_url(__DIR__) . "images/place-holder-cover.jpg";
    } else {
        return $cover;
    }
}

function zignotch_chek_if_logo_image_exist($logo)
{
    if ($logo == "") {
        return plugin_dir_url(__DIR__) . "images/place-holder-logo.png";
    } else {
        return $logo;
    }
}

function zignotch_chek_if_product_image_exist($logo)
{
    if ($logo == "") {
        return plugin_dir_url(__DIR__) . "images/product_placeholder.png";
    } else {
        return $logo;
    }
}

function zignotch_chek_if_user_image_exist($logo)
{
    if ($logo == "") {
        return plugin_dir_url(__DIR__) . "images/no-user-photo.png";
    } else {
        return $logo;
    }
}
function zignotch_chek_if_company_logo_exist($logo)
{
    if ($logo == "") {
        return plugin_dir_url(__DIR__) . "images/place-holder-logo.png";
    } else {
        return $logo;
    }
}

function zignotch_alphabetic_sort_multydimentional_array($array, $key, $order)
{ //$array  - array to sort || $key - key from the multidimentional array to sort by || $order - order type "SORT_ASC or SORT_DESC"
    array_multisort(array_column($array, $key), $order, $array);
    return $array;
}

function zignotch_nice_print_r($array)
{
    $array_return = "<pre>" . print_r($array) . "</pre>";
    return $array_return;
}

function zignotch_variable_check($var)
{
    $variable = "";
    if ($var == null || $var == "") {
        $var = "";
    } else {
        $variable = $var;
    }
    return $variable;
}

function zignotch_categories_array_expload_tag($categoiresArray)
{
    $categories = "";
    $categoriesJoindArray = "";
    $filters = "";

    foreach ($categoiresArray as $category) {
        $categoriesJoindArray .= str_replace(" ", "-", preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($category->name))) . " ";
    }
    return $categoriesJoindArray;
}

function zignotch_categories_array_expload($categoiresArray)
{
    $categoriesDisplay = "";

    foreach ($categoiresArray as $category) {

        $categoriesDisplay .= "<div class='category-wraper'>$category->name</div>";
    }
    if ($categoriesDisplay == "") {
        $categoriesDisplay = "";
    }
    return $categoriesDisplay;
}
// Agenda

function zignotch_session_speakers($speakersArray)
{
    $speakers = "";

    foreach ($speakersArray as $speaker) {

        $fullName = $speaker->fullName;
        $userPhoto = zignotch_chek_if_user_image_exist($speaker->userPhoto);
        $jobTitle = $speaker->jobTitle;
        $companyName = $speaker->companyName;

        $speakers .= "<div class='d-flex speaker-details flex-wrap mr-3'>";
        if (count($speakersArray) > 1) {
            $speakers .= "<img src=" . $userPhoto . " alt='' class='speaker-image'>";
        }
        $speakers .= "<p class='speaker-name'>$fullName</p>";
        if ($jobTitle != "" && $companyName != "") {
            $speakers .= ", <p class='ml-1'>" . $jobTitle . ", " . $companyName . "</p>";
        } elseif ($jobTitle == "" && $companyName != "") {
            $speakers .=  ", <p class='ml-1'>" . $companyName . "</p>";
        } elseif ($companyName == "" && $jobTitle != "") {
            $speakers .=  ", <p class='ml-1'>" . $jobTitle . "</p>";
        }
        $speakers .= " </div>";
    }
    if ($speakers == "") {
        $speakers = "No speakers.";
    }
    return $speakers;
}

function zignotch_session_sponsors($sponsorsArray)
{
    $sponsors = "";

    foreach ($sponsorsArray as $sponsor) {

        $companyLogo = $sponsor->companyLogo;
        $companyName = $sponsor->name;

        if ($companyLogo == null) {
            $sponsors .= "<div class='no-logo-sponsor'> <img src=" . zignotch_chek_if_company_logo_exist($companyLogo) . " alt='' class=''> 
                                    <h5 class='align-self-center mr-2'>" . $companyName . "</h5> </div>";
        } else {
            $sponsors .= "<img src=" . $companyLogo . " alt='' class='sponsor-img'>";
        }
    }
    if ($sponsors == "") {
        $sponsors = "No sponsors.";
    }
    return $sponsors;
}

function zignotch_speaker_comp($companyName, $companyLogo)
{
    $sponsors = "";
    if ($companyLogo == null) {
        $sponsors .= "<div class='no-logo-sponsor'> <img src=" . zignotch_chek_if_company_logo_exist($companyLogo) . " alt='' class=''> 
                                <h5 class='align-self-center mr-2'>" . $companyName . "</h5> </div>";
    } else {
        $sponsors .= "<img src=" . $companyLogo . " alt='' class='sponsor-img'>";
    }

    if ($sponsors == "") {
        $sponsors = "No sponsors.";
    }
    return $sponsors;
}



function zignotch_empty_list($message)
{
    $empty = "";
    $empty .= "<div class='empty-list'> <h5 class='align-self-center mr-2'>" . $message . "</h5>  </div>";

    return $empty;
}

function zignotch_error()
{
    $empty = "";
    $empty .= '<div id="notfound">
        <div class="notfound">
          <div class="notfound-404">
            <h3>Oops! Something went wrong</h3>
            <h1><span>4</span><span>0</span><span>4</span></h1>
          </div>
          <span class="msg">we are sorry, but the page you requested was not found</span>
        </div>
      </div>';

    return $empty;
}
// Elementor
function zignotch_extract_data_from_array_elementor($array, $key)
{
    foreach ($array["$key"] as $items) {
        $item = $items;
    }
    return $item;
}

function zignotch_add_elementor_widget_categories($elements_manager)
{

    $elements_manager->add_category(
        'zig_notch',
        [
            'title' => esc_html__('Zig Notch', 'zig_notch'),
            'icon' => 'fa fa-plug',
        ]
    );
}
