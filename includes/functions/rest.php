<?php
class Zig_event_feed_widgets extends WP_REST_Controller
{
    public function register_routes()
    {
        register_rest_route("get", "/Sessions/days/(?P<eventId>\d+)", array( //(?P<eventId>\d+) (?P<eventId>[\S]+)
            'methodes' => WP_REST_Server::READABLE,
            'callback' => array($this, 'zig_get_agenda')
        ));
        register_rest_route("get", "/Session/daydata/(?P<eventId>\d+)/(?P<selected_date>[\S]+)", array(
            'methodes' => WP_REST_Server::READABLE,
            'callback' => array($this, 'zig_get_agenda_details')
        ));
        register_rest_route("get", "Exhibitors/all/(?P<eventId>\d+)/(?P<pageNumber>\d+)/(?P<pageSize>\d+)/(?P<searchPhrase>[\S]+)/(?P<categoryID>\d+)", array(
            'methodes' => WP_REST_Server::READABLE,
            'callback' => array($this, 'zig_get_exhibitors')
        ));
        register_rest_route("get", "companies/companyDetails/(?P<exhibitorUsername>[\S]+)/(?P<eventId>\d+)", array(
            'methodes' => WP_REST_Server::READABLE,
            'callback' => array($this, 'zig_get_exhibitor_details')
        ));
        register_rest_route("get", "Events/GetEventFeed/(?P<eventId>\d+)/(?P<pageNumber>\d+)/(?P<pageSize>\d+)/", array(
            'methodes' => WP_REST_Server::READABLE,
            'callback' => array($this, 'zig_get_event_feed')
        ));
        register_rest_route("get", "Companies/eventProductsList/(?P<eventId>\d+)", array(
            'methodes' => WP_REST_Server::READABLE,
            'callback' => array($this, 'zig_get_products')
        ));
    }
// EXHIBITORS
    public function zig_get_exhibitors($request)
    {

        global $apiBaseUrl;
        $event_id = $request['eventId'];
        $pageNumber = intval($request['pageNumber']);
        $pageSize = intval($request['pageSize']);
        if ($request['searchPhrase'] != "no_search_phrase") {
            $searchPhrase = $request['searchPhrase'];
        } else {
            $searchPhrase = "";
        }
        $categoryID = intval($request['categoryID']);
        if ($categoryID == 0) {
            $categoryID = "";
        } else {
            $categoryID = intval($categoryID);
        }

        $html = "";

        $url = $apiBaseUrl . "Companies/list?eventID=$event_id&pageNumber=$pageNumber&pageSize=$pageSize&searchPhrase=$searchPhrase&categoryID=$categoryID";
        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));

        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $allExhibitorsInformation = json_decode($api_response_body);
        $allExhibitorsInformationArray = $allExhibitorsInformation->companies;

        foreach ($allExhibitorsInformationArray as $exhibitor) {

            $exhibitorID = $exhibitor->companyID;
            $exhibitorName = $exhibitor->name;
            $exhibitorCompanyLogo = zignotch_chek_if_logo_image_exist($exhibitor->logoPath);
            $exhibitorCategories = zignotch_categories_array_expload($exhibitor->categories);
            $categoryName = "";
            $vendorCategoryID = "";
            $filters_array = [];
            $filters = "";

            foreach ($allExhibitorsInformationArray as $categoryArray) {
                foreach ($categoryArray->categories as $category) {

                    $vendorCategoryID = $category->vendorCategoryID;
                    $categoryName = $category->name;
                    $filters_array[] = "<option value='$vendorCategoryID'>$categoryName</option>";
                }
            }

            $filters = implode("", array_unique($filters_array));

            $exhibitorCountryName = $exhibitor->countryName;
            $exhibitorUsername = $exhibitor->username;
            if (!empty($exhibitorUsername)) {
                $exhibitorUsername = $exhibitor->username;
            }

            $html .= "<article class='item exhibitor-item all' data-name='strval($exhibitorName)'>
                        <div>
                            <img src='$exhibitorCompanyLogo' width='80' alt='company logo' loading='lazy'> 
                        </div>
                        <div class='title'>
                            <a href='https://app.zignotch.com/event/$event_id/company/$exhibitorUsername/home/' data-exhibitor='$exhibitorUsername' class='companyLinkDetails' target='_blank'><h3>$exhibitorName</h3></a>
                            <p>$exhibitorCountryName</p>
                        </div>
                    </article>";
        }

        $response = new WP_REST_Response(['content' => $html, 'hasnext' => $allExhibitorsInformation->hasNext, 'total' => $allExhibitorsInformation->total, 'page' => $pageNumber, 'filters' => $filters, 'categories' => $exhibitorCategories]);

        $response->set_status(200);

        return $response;
    }
    // EXHIBITOR DETAILS
    public function zig_get_exhibitor_details($request)
    {
        global $apiBaseUrl;

        $event_id = $request['eventId'];
        $exhibitorUsername = $request['exhibitorUsername'];

        $url = $apiBaseUrl . "companies/companyDetails?username=$exhibitorUsername&eventId=$event_id";
        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));
        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $detailsArray = json_decode($api_response_body);

        $contactDetails = $detailsArray->contactDetails;
        $detailsAddress = $contactDetails->address;
        $detailsCity = $contactDetails->city;
        $detailsPostalCode = $contactDetails->postalCode;
        $detailsPhone = $contactDetails->officePhone;


        if ($detailsPhone == null || $detailsPhone == "") {
            $detailsPhoneField = "";
        } else {
            $detailsPhoneField = "<div class='d-flex align-items-center'><span class='dashicons dashicons-phone mr-1'></span> <a href=tel:'$detailsPhone' target='_blank'> $detailsPhone </a> </div>";
        }

        $detailsEmail = $detailsArray->email;

        if ($detailsEmail == null || $detailsEmail == "") {
            $detailsEmailField = "";
        } else {
            $detailsEmailField = "<div class='d-flex align-items-center'><span class='dashicons dashicons-email mr-1'></span> <a href='mailto:$detailsEmail' target='_blank'> $detailsEmail </a> </div>";
        }

        $detailsSite = $detailsArray->websiteLink;

        if ($detailsSite == null || $detailsSite == "") {
            $detailsSiteField = "";
        } else {
            $detailsSiteField = "<div class='d-flex align-items-center'> <span class='dashicons dashicons-admin-site-alt3 mr-1'></span><a class='ellipses' href=' $detailsSite ' target='_blank'> $detailsSite</a> </div>";
        }
        //social media information
        $socialDetails = $detailsArray->socialDetails;

        $detailslinkedInLink = $socialDetails->linkedInLink;
        if ($detailslinkedInLink == null || $detailslinkedInLink == "") {
            $detailslinkedInLinkField = "";
        } else {
            $detailslinkedInLinkField = "<div class='social-wrapper'><a href='$detailslinkedInLink' target='_blank'><span class='dashicons dashicons-linkedin'></span></a></div>";
        }

        $detailsfacebookLink = $socialDetails->facebookLink;
        if ($detailsfacebookLink == null || $detailsfacebookLink == "") {
            $detailsfacebookLinkField = "";
        } else {
            $detailsfacebookLinkField = "<div class='social-wrapper'><a href='$detailsfacebookLink' target='_blank'><span class='dashicons dashicons-facebook'></span></a></div>";
        }

        $detailstwitterLink = $socialDetails->twitterLink;
        if ($detailstwitterLink == null || $detailstwitterLink == "") {
            $detailstwitterLinkField = "";
        } else {
            $detailstwitterLinkField = "<div class='social-wrapper'><a href='$detailstwitterLink' target='_blank'><span class='dashicons dashicons-twitter'></span></a></div>";
        }

        $detailsXingLink = $socialDetails->xingLink;
        if ($detailsXingLink == null || $detailsXingLink == "") {
            $detailsXingLinkField = "";
        } else {
            $detailsXingLinkField = "<div class='social-wrapper'><a href='$detailsXingLink' target='_blank'><span class='dashicons dashicons-xing'></span></a></div>";
        }

        $detailsInstagramLink = $socialDetails->instagramLink;
        if ($detailsInstagramLink == null || $detailsInstagramLink == "") {
            $detailsInstagramLinkField = "";
        } else {
            $detailsInstagramLinkField = "<div class='social-wrapper'><a href='$detailsInstagramLink' target='_blank'><span class='dashicons dashicons-instagram'></span></a></div>";
        }

        $detailsDescription = $detailsArray->about;

        // sessions the company is a part of



        $html = "<div class='details-dialog'>
                    <span class='close-button' id='exhibitorID ?>'> <span class='dashicons dashicons-no'></span></span>
                    <div class='row'>
                        <div class='col-md-12 company-profile-header'>
                            <div class='logo-container'>
                                <img width=150 src='" . zignotch_chek_if_logo_image_exist($detailsArray->profilePhoto) . "' alt='$detailsArray->companyName'>
                            </div>
                            <span class='details-company-name'>$detailsArray->companyName</span>
                                 $detailsPhoneField
                                 $detailsEmailField
                                 $detailsSiteField
                            <div class='gap'></div>
                        </div>
                        <div class='col-md-12 company-profile-sessions'>
                            <h3 style='color: #696969'>Sessions</h3>
                        </div>
                        <div class='col-md-12 company-profile-products'>
                            <h3 style='color: #696969'>Products</h3>
                        </div>
                    </div>
                </div>";
        $response = new WP_REST_Response(['content' => $html, 'array' => $detailsArray]);

        $response->set_status(200);

        return $response;
    }

    // SESSIONS
    public function zig_get_agenda($request)
    {
        global $apiBaseUrl;

        $event_id = $request['eventId'];


        $html = "";
        $filter = "";
        $categoriesClasses = "";

        $url = $apiBaseUrl . "Session/days/$event_id";

        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));

        $api_response = wp_remote_get($url, $headers);

        $api_response_body = wp_remote_retrieve_body($api_response);

        $session_days = json_decode($api_response_body);
        $days_from_api = $session_days->days;
        $html = "<ul id='day_selector'>";

        foreach ($session_days->days as $days) if ($days->count != 0) {
            $date = $days->date;
            $name = $days->name;
            $dates_available[] = $days->date;


            $html .= "<li id='agenda_day' class='agenda_day' data-agenda_day='$date'>
                        <span id='day'>" . zignotch_format_date($date, 'l') . "<br>
                        <span id='date'>" . zignotch_format_date($date, 'd F') . zignotch_format_date($date, ', Y') . "<br>
                    </li>";
        }

        $first_date = $dates_available[0];
        $first_date_formated = zignotch_format_date($dates_available[0], 'l d F, Y');


        $response = new WP_REST_Response(['content' => $html, 'array' => $session_days, 'first_date' => $first_date, 'first_date_formated' => $first_date_formated]);

        $response->set_status(200);

        return $response;
    }

    public function zig_get_agenda_details($request)
    {
        global $apiBaseUrl;

        $event_id = $request['eventId'];
        $selected_date = $request['selected_date'];
        $html = "";
        $speakers = "";
        $sponsors = "";

        $url = $apiBaseUrl . "Session/daydata/$event_id?date=$selected_date";

        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));

        $api_response = wp_remote_get($url, $headers);

        $api_response_body = wp_remote_retrieve_body($api_response);

        $session_day_details = json_decode($api_response_body);

        foreach ($session_day_details->sessions as $day_details) {
            foreach ($day_details->speakers as $speaker) {
                $speakers .= "
                    <div class='speaker-single'>
                        <div class = 'speaker-single-image'>
                            <img src = '$speaker->userPhoto'>
                        </div>
                        <div class = 'speaker-single-info'>
                            $speaker->fullName<br>$speaker->jobTitle<br>$speaker->companyName
                        </div>
                    </div>
                ";
            }
            foreach ($day_details->companies as $sponsor) {
                $sponsors .= "<div><img src='$sponsor->companyLogo'></div>";
            }

            $html .= "<div class='agenda_sigle_item'>
                        <div class='agenda_item_header'>
                            <div class='agenda_item_header_time'>
                                <div>
                                    <span class='dashicons dashicons-clock'></span>
                                </div>
                                <div>
                                    " .
                zignotch_format_date($day_details->timeFrom, "g-i a") . " - " .
                zignotch_format_date($day_details->timeTo, "g-i a")
                . "
                                    <br>
                                    $day_details->location
                                </div>
                            </div>
                            <div class='agenda_item_header_title'>
                                $day_details->title
                            </div>
                        </div>
                        <div class='agenda_item_body'>
                            <h5>Speakers</h5>
                            <div class='speakers-container'>
                                $speakers  
                            </div>
                            <h5>Sponsors</h5>
                            <div class='sponsor-container'>
                                $sponsors
                            </div>

                        </div>
                    </div>";
            $speakers = "";
            $sponsors = "";
        }

        $response = new WP_REST_Response(['content' => $html, 'array' => $session_day_details]);

        $response->set_status(200);

        return $response;
    }
    // EVENT FEED
    public function zig_get_event_feed($request)
    {
        global $apiBaseUrl;
        $event_id = $request['eventId'];
        $pageNumber = intval($request['pageNumber']);
        $pageSize = intval($request['pageSize']);

        $html_plane = "";
        $html_pinned = "";
        $post_images = "";
        $products_side_bar = "";
        $feed_sponsors = "";
        $html_fetures_speakers = "";
        $html_sessions = "";
        $html_sessions_array = [];
        $sessions_speakers = "";
        $image_count = 0;
        // FEED
        $url = $apiBaseUrl . "Events/GetEventFeed?eventId=$event_id&$pageNumber=1&pageSize=$pageSize";
        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));

        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $event_feed = json_decode($api_response_body);
        $feed = $event_feed->feed;
        $vendorId = $event_feed->vendorId;

        foreach ($feed->activities as $post_info) if ($post_info->isPinned) {

            $post_user = $post_info->user;
            $post_content = $post_info->content;

            foreach ($post_content as $post_image) if ($image_count < 4) {
                $post_images .= "<div class='event_feed_images_container'><a href='https://app.zignotch.com/public/post?id=$post_info->previewToken&vendorId=$vendorId' target='_blank'><img src = '$post_image->imageSrc' alt='$post_image->title' width=$post_image->width height=$post_image->height; class='event_feed_images'></a></div>";
                $image_count++;
            }

            $html_pinned .= "
                <span class='dashicons dashicons-sticky'></span><span>Pinned</span>
                <div class='single_event_post'>
                    <div class='single_event_post_header'>
                        <div class='user_cont'>
                            <img class='single_event_post_user_image' src='" . zignotch_chek_if_user_image_exist($post_user->userPhoto) . "'>
                            <span class='single_event_post_name'>$post_user->fullName</span>
                        </div>
                        <br>
                        <span>" . zignotch_format_date($post_info->time, "j M") . " at " . zignotch_format_date($post_info->time, 'g:i a') . "</span>
                    </div>
                    <div class='single_event_post_body'>
                        <p>$post_info->postText </p>
                        <div class='single_event_post_content_image_container'>
                            $post_images
                        </div>
                    </div>
                    <div class='engagement_bar'>";
            if ($post_info->likesCount > 0) {
                $html_pinned .= " 
                            <div class='like_count'>
                                <span>$post_info->likesCount Like this</span>
                            </div>
                            
                        ";
            }
            if ($post_info->commentsCount == 1) {
                $html_pinned .= " 
                            <div class='comment_count'>
                                <span>
                                    $post_info->commentsCount Comment
                                </span>
                            </div>";
            } elseif ($post_info->commentsCount > 1) {
                $html_pinned .= " 
                            <div class='comment_count'>
                                <span>
                                    $post_info->commentsCount Comments
                                </span>
                            </div>";
            }
            $html_pinned .= "
                </div>
                <div class='engagement_buttons'>
                <a href='https://app.zignotch.com/public/post?id=$post_info->previewToken&vendorId=$vendorId' target='_blank'><span class='dashicons dashicons-thumbs-up'></span></a>
                <a href='https://app.zignotch.com/public/post?id=$post_info->previewToken&vendorId=$vendorId' target='_blank'><span class='dashicons dashicons-admin-comments'></span></a>
                <a href='https://app.zignotch.com/public/post?id=$post_info->previewToken&vendorId=$vendorId' target='_blank'><span class='dashicons dashicons-share'></span></a>
                </div>
                </div>";
            $post_images = "";
        }
        foreach ($feed->activities as $post_info) if (!$post_info->isPinned) {
            $post_user = $post_info->user;
            $post_content = $post_info->content;

            foreach ($post_content as $post_image) if ($image_count < 4) {
                $post_images .= "<div class='event_feed_images_container'><a href='https://app.zignotch.com/public/post?id=$post_info->previewToken&vendorId=$vendorId' target='_blank'><img src = '$post_image->imageSrc' alt='$post_image->title' width=$post_image->width height=$post_image->height; class='event_feed_images'></a></div>";
                $image_count++;
            }

            $html_plane .= "
                <div class='single_event_post'>
                    <div class='single_event_post_header'>
                        <div class='user_cont'>
                            <img class='single_event_post_user_image' src='" . zignotch_chek_if_user_image_exist($post_user->userPhoto) . "'>
                            <span class='single_event_post_name'>$post_user->fullName</span>
                        </div>
                        <br>
                        <span>" . zignotch_format_date($post_info->time, "j M") . " at " . zignotch_format_date($post_info->time, 'g:i a') . "</span>
                    </div>
                    <div class='single_event_post_body'>
                        <p>$post_info->postText </p>
                        <div class='single_event_post_content_image_container'>
                            $post_images
                        </div>
                    </div>
                    <div class='engagement_bar'>";
            if ($post_info->likesCount > 0) {
                $html_plane .= " 
                            <div class='like_count'>
                                <span>$post_info->likesCount Like this</span>
                            </div>
                            
                        ";
            }
            if ($post_info->commentsCount == 1) {
                $html_plane .= " 
                            <div class='comment_count'>
                                <span>
                                    $post_info->commentsCount Comment
                                </span>
                            </div>";
            } elseif ($post_info->commentsCount > 1) {
                $html_plane .= " 
                            <div class='comment_count'>
                                <span>
                                    $post_info->commentsCount Comments
                                </span>
                            </div>";
            }
            $html_plane .= "</div>
                <div class='engagement_buttons'>
                <a href='https://app.zignotch.com/public/post?id=$post_info->previewToken&vendorId=$vendorId' target='_blank'><span class='dashicons dashicons-thumbs-up'></span></a>
                <a href='https://app.zignotch.com/public/post?id=$post_info->previewToken&vendorId=$vendorId' target='_blank'><span class='dashicons dashicons-admin-comments'></span></a>
                <a href='https://app.zignotch.com/public/post?id=$post_info->previewToken&vendorId=$vendorId' target='_blank'><span class='dashicons dashicons-share'></span></a>
                </div>
                </div>";
            $post_images = "";
        }
        $html = $html_pinned . $html_plane;

        $url = $apiBaseUrl . "Events/GetEventDetails?eventId=$event_id&numOfRep=5";

        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));

        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $event_feed_details = json_decode($api_response_body);

        foreach ($event_feed_details as $details) {
            $det = $details->eventDetailsInfo;
        }
        $cover = $det->coverPhotoPath;
        $name = $det->name;

        $cover_html = "
            <img src='https://d1kglcy9tturzq.cloudfront.net/" . $cover . "'.>
            <div class='feed_header'>
                <span>$name</span>
                <span>" . zignotch_format_date($det->dateFrom, 'j - ') . zignotch_format_date($det->dateTo, 'j M, Y') . "</span>
            </div>";

        // PRODUCTS
        $url = $apiBaseUrl . "Companies/eventProductsList?EventID=$event_id&PageNumber=1&PageSize=20000";
        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));
        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $details = json_decode($api_response_body);

        $upper_limit_for_random_number = ceil(count($details) / 2);
        $page_to_display = rand(1, $upper_limit_for_random_number);

        $url = $apiBaseUrl . "Companies/eventProductsList?EventID=$event_id&PageNumber=1&PageSize=$page_to_display";
        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));
        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $details = json_decode($api_response_body);
        $i = 0;
        foreach ($details as $detailsArray) if ($i < 2) {
            if (strlen($detailsArray->productName) > 40) {
                $dots = " ...";
            }
            $products_side_bar .= "<article class='item product-item-feed all'>

                <div class='product_image_container_feed'>
                    <img src='$detailsArray->image' width='80' alt='product image' loading='lazy' class='product_image_feed'> 
                </div>
                <div class='producttitle'>
                <a href='https://app.zignotch.com/event/$event_id/company/$detailsArray->username/products/$detailsArray->companyProductID?page=eventproducts' data-exhibitor='exhibitorUsername' class='companyLinkDetails' target='_blank'>
                <p>" . substr($detailsArray->productName, 0, 40) . $dots . "</p>
                
                    </a>
                    <a href='https://app.zignotch.com/event/$event_id/company/$detailsArray->username/home' target='_blank'><span>$detailsArray->companyName</span></a>

                </div>
                <div class='product_footer_feed'>
                    <p> <a href='https://app.zignotch.com/event/$event_id/company/$detailsArray->username/products/$detailsArray->companyProductID?page=eventproducts' target='_blank'><span class='dashicons dashicons-heart'></span> Bookmark</a></p>
                    <p> <a href='https://app.zignotch.com/event/$event_id/company/$detailsArray->username/products/$detailsArray->companyProductID?page=eventproducts' target='_blank'><span class='dashicons dashicons-cart'></span> Interested</a></p>
                </div>
            </article>";
            $i++;
            $dots = "";
        }
        // SPONSORS
        $url = $apiBaseUrl . "Companies/sponsorsList?eventID=$event_id";

        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));
        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $sponsors = json_decode($api_response_body);

        foreach ($sponsors as $sponsor) {
            foreach ($sponsor->companies as $company) {
                $feed_sponsors = "<img src = '$company->logoPath' class='feed_sposnosr_image'>";
            }
        }
        // SPEAKERS
        $url = $apiBaseUrl . "Speakers/list?eventID=$event_id&pageNumber=1&pageSize=50000";

        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));
        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $speakers = json_decode($api_response_body);
        $i = 0;

        foreach ($speakers->speakers as $speaker) if ($i < 4) {
            $html_fetures_speakers .= "<div class='feed_single_speaker'>
                                        <img src='$speaker->userPhoto' class='feed_single_speaker_image'>
                                        <div class='feed_single_speaker_name_and_tile' >
                                            <span class='feed_single_speaker_name'>$speaker->fullName</span><br>
                                            <span class='feed_single_speaker_title'>$speaker->jobTitle at $speaker->companyName</span>
                                        </div>
                                    </div>";
            $i++;
        }
        // SESSIONS
        $url = $apiBaseUrl . "Session/all/$event_id";
        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));
        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $sessions_feed = json_decode($api_response_body);
        $sessions = $sessions_feed->agendaDaysWithSessions;
        $i = 0;
        foreach ($sessions as $session) {
            foreach ($session->dayAgendaSessions as $session_item) if ($i < 2) {

                foreach ($session_item->speakers as $speakers) {
                    $sessions_speakers .= "
                    <div class='agenda_single_item_feed_speakers'>
                            <img class='agenda_single_item_feed_speakers_image' src ='$speakers->userPhoto'>
                            <span>$speakers->fullName</span>
                            </div>
                    ";
                }


                $html_sessions .= "
                        <div class='agenda_single_item_feed'>
                            <p class='agenda_single_item_feed_title'>
                                <strong>" . $session_item->title . "</strong>
                            </p>
                            <p class='rickets'>
                                    ";
                if ($session_item->seats > 0) {
                    $html_sessions .= "<span class='dashicons dashicons-tickets-alt'></span> Only " . $session_item->seats . " left";
                }
                $html_sessions .=  "</p>
                            <p class='agenda_single_item_feed_time'>
                            <span class='dashicons dashicons-clock'></span>" .
                    zignotch_format_date($session_item->timeFrom, 'j M, Y, h:i A') . " - " . zignotch_format_date($session_item->timeTo, 'h:i A')
                    . "</p>
                            <p class='agenda_single_item_feed_location'>
                                <span class='dashicons dashicons-location'></span> " . $session_item->location . " 
                                <span class='dashicons dashicons-layout'></span> " . $session_item->typeName . "
                            </p>
                            <p>Speakers</p>
                            $sessions_speakers
                        </div>
                ";
                $html_sessions_array[] = $session_item;
                $i++;
            }
            $sessions_speakers = "";
        }




        $response = new WP_REST_Response([
            'cover' => $cover_html,
            'event_feed_details' => $det,
            'vendor_id' => $vendorId,
            'content_feed' => $html,
            'array_feed' => $event_feed->feed,
            'content_products' => $products_side_bar,
            'array_products' => $details,
            'content_sponsors' => $feed_sponsors,
            'array_sponsors' => $sponsors,
            'content_speakers' => $html_fetures_speakers,
            'array_speakers' => $speakers,
            'content_sessions' => $html_sessions,
            'array_sessions' => $html_sessions_array
        ]);

        $response->set_status(200);

        return $response;
    }
    // PRODUCTS
    public function zig_get_products($request)
    {

        global $apiBaseUrl;
        $html = "";

        $event_id = $request['eventId'];
        $exhibitorUsername = $request['exhibitorUsername'];

        $url = $apiBaseUrl . "Companies/eventProductsList?EventID=$event_id&PageNumber=1&PageSize=20";
        $headers = array('headers' => array(
            'x-api-key' => get_option('zig_notch_api_key'),
            'Cache-Control' => 'max-age=31536000',
        ));
        $api_response = wp_remote_get($url, $headers);
        $api_response_body = wp_remote_retrieve_body($api_response);
        $details = json_decode($api_response_body);
        foreach ($details as $detailsArray) {
            if (strlen($detailsArray->productName) > 40) {
                $dots = " ...";
            }
            $html .= "<article class='product-item all'>
                <div>
                    <img src='$detailsArray->image' width='80' alt='product image' loading='lazy'> 
                </div>
                <div class='title'>
                    <a href='https://app.zignotch.com/event/$event_id/company/$detailsArray->username/products/$detailsArray->companyProductID?page=eventproducts' data-exhibitor='exhibitorUsername' class='companyLinkDetails' target='_blank'>
                        <p>" . substr($detailsArray->productName, 0, 40) . $dots . "</p>
                        
                    </a>
                    <a href='https://app.zignotch.com/event/$event_id/company/$detailsArray->username/home' target='_blank'><span>$detailsArray->companyName</span></a>
                </div>
            </article>";
            $dots = "";
        }
        $response = new WP_REST_Response(['content' => $html, 'array' => $details]);

        $response->set_status(200);

        return $response;
    }
}

// REST
add_action('rest_api_init', function () {
    if (class_exists('Zig_event_feed_widgets')) {
        $controller = new Zig_event_feed_widgets();
        $controller->register_routes();
    }
});
