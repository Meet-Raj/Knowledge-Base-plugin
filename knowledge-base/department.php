<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

function create_kb_post_type() {
    $labels = array(
        'name' => __('Knowledge Base'),
        'singular_name' => __('Knowledge Base Article'),
        'add_new' => __('Add New Article'),
        'add_new_item' => __('Add New Article'),
        'edit_item' => __('Edit Article'),
        'new_item' => __('New Article'),
        'view_item' => __('View Article'),
        'search_items' => __('Search Knowledge Base'),
        'not_found' => __('No articles found'),
        'not_found_in_trash' => __('No articles found in Trash'),
        'parent_item_colon' => '',
        'menu_name' => __('Knowledge Base'),
    );

    register_post_type('knowledge_base', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'query_var' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'taxonomies' => array('department', 'category'), 
        'menu_icon' => 'dashicons-book-alt',
    ));
}
add_action('init', 'create_kb_post_type');

// Register custom taxonomy for departments
function register_department_taxonomy() {
    $labels = array(
        'name' => _x('Departments', 'taxonomy general name'),
        'singular_name' => _x('Department', 'taxonomy singular name'),
        'search_items' => __('Search Departments'),
        'all_items' => __('All Departments'),
        'parent_item' => __('Parent Department'),
        'parent_item_colon' => __('Parent Department:'),
        'edit_item' => __('Edit Department'),
        'update_item' => __('Update Department'),
        'add_new_item' => __('Add New Department'),
        'new_item_name' => __('New Department Name'),
        'menu_name' => __('Departments'),
    );

    register_taxonomy('department', 'knowledge_base', array(
        'labels' => $labels,
        'hierarchical' => true,
        'show_in_rest' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
    ));
}
add_action('init', 'register_department_taxonomy');

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
        'key' => 'group_60b6b8a2cdd0c',
        'title' => 'Knowledge Base Fields',
        'fields' => array(
            array(
                'key' => 'field_60b6b8b06ef15',
                'label' => 'Document (PDF/Doc)',
                'name' => 'document',
                'type' => 'file',
                'instructions' => 'Upload a PDF or Doc file.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'library' => 'all',
                'min_size' => '',
                'max_size' => '',
                'mime_types' => 'pdf,doc,docx',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'knowledge_base',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));}



function knowledge_tab($user) {
    $support_active = (isset($_GET['action']) && $_GET['action'] == 'knowledge-base') ? 'mepr-active-nav-tab' : '';
    ?>
    <span class="mepr-nav-item knowledge-base  <?php echo $support_active; ?>">
        <a href="<?php echo esc_url(home_url('/account/?action=knowledge-base')); ?>">Knowledge Base</a>
    </span>
    <?php
}
add_action('mepr_account_nav', 'knowledge_tab', 150);

function singe_post_tab($user) {
    $singlepost_active = (isset($_GET['action']) && $_GET['action'] == 'knowledge-post') ? 'mepr-active-nav-tab' : '';
    ?>
    <span class="mepr-nav-item knowledge-base  <?php echo $singlepost_active; ?>">
        <a href="<?php echo esc_url(home_url('/account/?action=knowledge-post')); ?> " style="display: none;">knowledge-post</a>
    </span>
    <?php
}
add_action('mepr_account_nav', 'singe_post_tab');

function knowledge_post_content($action) {
    if ($action == 'knowledge-post') {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

  if ($post_id > 0) {
            $post = get_post($post_id);

            if ($post instanceof WP_Post) {
                echo '<a href="' . esc_url(home_url('/account/?action=knowledge-base')) . '" class="back-button btn-green mb-4"><i class="fas fa-arrow-left-long"></i> Back to Knowledge Base</a>';
                echo '<div class="knowledge-post-content">';
                echo '<h2 class="post-title">' . esc_html($post->post_title) . '</h2>';
                echo '<ul class="post-info">';
                echo '<li><i class="fas fa-clock"></i> <b>Created On:</b> ' . esc_html(get_the_time('F j, Y', $post_id)) . '</li>';

                $categories = get_the_category($post_id);
                if (!empty($categories)) {
                    echo '<li><i class="fas fa-folder"></i> <b>Category:</b> ' . esc_html($categories[0]->name) . '</li>';
                }
                echo '</ul>';

                if (has_post_thumbnail($post_id)) {
                    echo '<div class="featured-image">';
                    echo get_the_post_thumbnail($post_id);
                    echo '</div>';
                }

                $post_content = $post->post_content;
                echo '<div class="post-content">' . apply_filters('get_the_content', $post_content) . '</div>';
                $blocks = parse_blocks($post_content);
                foreach ($blocks as $block) {
                    // Check if the block is a YouTube block
                    if ($block['blockName'] === 'core/embed-youtube') { 
                        $youtube_url = $block['attrs']['url'];
                      
                        $embed_code = wp_oembed_get($youtube_url);
                        if ($embed_code) {
                            echo '<div class="embedded-media">' . $embed_code . '</div>';
                            // Display YouTube URL
                            echo '<p><b>YouTube URL:</b> ' . esc_url($youtube_url) . '</p>';
                        }
                    }
                }

                echo '</div>';
            } else {
                echo '<p>No post found</p>';
            }
        }
    }
}
add_action('mepr_account_nav_content', 'knowledge_post_content');

function knowledge_content($action) {
    if ($action == 'knowledge-base') {
        $department_title = 'Knowledge Base';
        if (isset($_GET['department'])) {
            $selected_department = sanitize_text_field($_GET['department']);
            $department = get_term_by('slug', $selected_department, 'department');

            if ($department && !is_wp_error($department)) {
                $department_title = $department->name . ' Knowledge Base';
            }
        }
        ?>
        <h2 id="knowledge-title" class="knowledge_title"><?php echo esc_html($department_title); ?></h2>

        <div class="knowledge-live-search">
            <form role="search" method="get" id="live-search-form" action="<?php echo home_url('/'); ?>">
                <div class="input-group flex-nowrap mb-3">
                    <input type="text" value="<?php echo get_search_query(); ?>" name="s" id="live-search-input"
                           placeholder="Search" aria-describedby="live-search" />
                    <input type="hidden" name="action" value="live_search">
                    <div class="input-group-append">
                        <button class="btn btn-green" type="button" id="live-search">Search</button>
                    </div>
                </div>
                <div class="knowledge-search-results">
                    <ul id="live-search-results" class="live-search-results"></ul>
                </div>
            </form>
        </div>
<div class="knowledge-departments">
    <?php
    $types = get_terms('type');
    if (!empty($types) && !is_wp_error($types)) {
        echo '<ul>';
        foreach ($types as $index => $type) {
            $class = $index === 0 ? ' default-department' : '';

            echo '<li><a href="#" class="department-link' . $class . '" data-department="' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</a></li>';
            if ($index === 0) {
                $firstDepartment = $type->slug;
            }
        }
        echo '</ul>';
    }
    ?>
</div>

<div id="type-tabs">
    <?php
    $types = get_terms(array('taxonomy' => 'type', 'hide_empty' => false));
    foreach ($types as $type) {
        echo '<div class="type-tab" data-type="' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</div>';
    }
    ?>
</div>

        <div class="knowledge-content">
            <div id="category-and-article-results" class="category-article-results"></div>
        </div>
        <script>
jQuery(document).ready(function ($) {
    function loadCategoryAndArticle(department) {
        $.ajax({
            type: 'GET',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'load_category_and_article',
                department: department,
            },
            success: function (response) {
                $('#category-and-article-results').html(response);
            }
        });
    }

    loadCategoryAndArticle('<?php echo $firstDepartment; ?>');

    // Add the "active" class to the default department link
    var defaultDepartmentLink = $('.department-link[data-department="<?php echo $firstDepartment; ?>"]');
    defaultDepartmentLink.addClass('active');

    // Manually trigger a click event on the default department link with a small delay
    setTimeout(function () {
        defaultDepartmentLink.trigger('click');
    }, 100);

    // Handle click events on department links
    $('.department-link').on('click', function (e) {
        e.preventDefault();
        var departmentName = $(this).text();
        $('#knowledge-title').text('Knowledge Base ' + departmentName);
        var department = $(this).data('department');
        $('.department-link').removeClass('active');
        $(this).addClass('active');
        loadCategoryAndArticle(department);
    });
});


        </script>
        <?php
    }
}
add_action('mepr_account_nav_content', 'knowledge_content');

add_action('wp_ajax_load_category_and_article', 'load_category_and_article_callback');
add_action('wp_ajax_nopriv_load_category_and_article', 'load_category_and_article_callback');

function load_category_and_article_callback() {
    //$department = sanitize_text_field($_GET['department']);
    $department = isset($_GET['department']) ? sanitize_text_field($_GET['department']) : '';

    $args = array(
        'post_type' => 'knowledge_base',
        'tax_query' => array(
            array(
                'taxonomy' => 'department',
                'field'    => 'slug',
                'terms'    => $department,
            ),
        ),
    );

  $query = new WP_Query($args);

if ($query->have_posts()) {
    $posts_with_info = array();

    while ($query->have_posts()) {
        $query->the_post();

        $post_id = get_the_ID();
        $category_name = get_the_category()[0]->name;
        $article_title = get_the_title();

        $posts_with_info[$post_id] = array(
            'post_id' => $post_id, // Add 'post_id' key here
            'category_name' => $category_name,
            'article_title' => $article_title,
        );
    }

    $categories_with_articles = array();

    foreach ($posts_with_info as $post_info) {
        $categories_with_articles[$post_info['category_name']][] = $post_info;
    }
    
    $defaultCategoryName = 'general'; // Change this to the default category name

    echo '<div class="row">';
    foreach ($categories_with_articles as $category_name => $articles) {
        echo '<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">';
        echo '<div class="categorie-list">';
        echo '<h3><i class="far fa-folder"></i>' . esc_html($category_name) . '</h3>';
        echo '<ul class="article-list">';
        
        // Check if the current category is the default category
        $isDefaultCategory = ($category_name === $defaultCategoryName);

        foreach ($articles as $post_info) {
            $post_id = $post_info['post_id'];
            echo '<li><a href="' . esc_url(home_url('/account/?action=post-knowledge&post_id=' . $post_id)) . '" data-post-id="' . $post_id . '"></i>' . esc_html($post_info['article_title'])  . '</a></li>';}
        echo '</ul>';
        echo '</div>';
        echo '</div>';
        
        if ($isDefaultCategory) {
            break;
        }
    }

    echo '</div>';
} else {
    echo '<div class="data_not_found">No results found</div>';
}

wp_reset_postdata();
die();
}

add_action('wp_ajax_live_search', 'live_search_callback');
add_action('wp_ajax_nopriv_live_search', 'live_search_callback');
function live_search_callback() {
    $search_query = sanitize_text_field($_GET['s']);
    $department = sanitize_text_field($_GET['department']);
    $args = array(
        'post_type' => 'knowledge_base',
        's' => $search_query,
    );
    if (!empty($department)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'department',
                'field'    => 'slug',
                'terms'    => $department,
            ),);}
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<li><a href="' . get_permalink() . '" data-post-id="' . get_the_ID() . '"><i class="far fa-rectangle-list"></i>' . get_the_title() . '</a></li>';


        }
    } else {
        echo '<li><div class="data_not_found">No results found</div></li>';
    }

    wp_reset_postdata();

    die();
}

add_action('wp_footer', 'live_search_scripts', 99);
function live_search_scripts() {?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"crossorigin="anonymous" referrerpolicy="no-referrer" />
<script>
   jQuery(document).ready(function($) {
    $('#live-search-input').on('input', function() {
        var searchValue = $(this).val();
        var departmentValue = $('select[name="department"]').val();
        if (searchValue.trim() === '') {
            $('#live-search-results').html('');
            $('#knowledge-content').html('');
            return;
        }
        $.ajax({
            type: 'GET',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'live_search',
                s: searchValue,
                department: departmentValue,
            },
            success: function(response) {
                if (response.trim() === '') {
                    $('#live-search-results').html('<p>No results found</p>');
                } else {
                    $('#live-search-results').html(response);
                    $('#live-search-results').on('click', 'li a', function(e) {
                        e.preventDefault();
                        var postUrl = $(this).attr('href');
                        var postId = $(this).data('post-id'); 
                        window.location.href = '<?php echo esc_url(home_url('/account/?action=knowledge-post')); ?>' + '&post_id=' + postId;
                    });
                }
            }
        });
    });
});
</script>
    <?php
}
function singe_post_knowledge($user) {
  $singlepost = (isset($_GET['action']) && $_GET['action'] == 'post-knowledge') ? 'mepr-active-nav-tab' : '';
  ?>
    <span class="mepr-nav-item knowledge-base  <?php echo $singlepost; ?>">
      <a href="<?php echo esc_url(home_url('/account/?action=post-knowledge')); ?> " style="display: none;"></a>
    </span>
  <?php
}
add_action('mepr_account_nav', 'singe_post_knowledge');


function knowledge_single_post($action) {
    if ($action == 'post-knowledge') {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

        if ($post_id > 0) {
            $post = get_post($post_id);

            if ($post instanceof WP_Post) {
                echo '<a href="' . esc_url(home_url('/account/?action=knowledge-base')) . '" class="back-button btn-green mb-4"><i class="fas fa-arrow-left-long"></i> Back to Knowledge Base</a>';
                echo '<div class="knowledge-post-content">';
                echo '<h2 class="post-title">' . esc_html($post->post_title) . '</h2>';
                echo '<ul class="post-info">';
                echo '<li><i class="fas fa-clock"></i> <b>Created On:</b> ' . esc_html(get_the_time('F j, Y', $post_id)) . '</li>';

                $categories = get_the_category($post_id);
                if (!empty($categories)) {
                    echo '<li><i class="fas fa-folder"></i> <b>Category:</b> ' . esc_html($categories[0]->name) . '</li>';
                }
                echo '</ul>';

                if (has_post_thumbnail($post_id)) {
                    echo '<div class="featured-image">';
                    echo get_the_post_thumbnail($post_id);
                    echo '</div>';
                }

                $post_content = $post->post_content;
echo '<div class="post-content">' . apply_filters('get_the_content', $post_content) . '</div>';

                echo '</div>';
            } else {
                echo '<p>No post found</p>';
            }
        }
    }
}
add_action('mepr_account_nav_content', 'knowledge_single_post');
















