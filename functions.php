<?php

function optimiselearning_titlecase_buttons() {
    wp_enqueue_script('jquery');
    $js = <<<JS
  (function(){
    function titleCaseButtons(ctx){
      var els = (ctx || document).querySelectorAll('.vc_general.vc_btn3');
      var stop = new Set(['a','an','and','as','at','but','by','for','from','in','of','on','or','the','to','with']);
      els.forEach(function(el){
        var t = (el.textContent || '').trim();
        if (!t) return;
        if (t === t.toUpperCase()) { // only convert shouty strings
          var words = t.toLowerCase().split(/\\s+/).map(function(w,i,arr){
            if (i!==0 && i!==arr.length-1 && stop.has(w)) return w;
            return w.charAt(0).toUpperCase() + w.slice(1);
          });
          el.textContent = words.join(' ');
        }
      });
    }
  
    function run(){ titleCaseButtons(); }
  
    // DOM ready + after load (covers delayed JS)
    if (document.readyState !== 'loading') run();
    else document.addEventListener('DOMContentLoaded', run);
    window.addEventListener('load', run);
  
    // WPBakery sometimes re-inits; listen + observe
    document.addEventListener('vc_js', run);
    new MutationObserver(function(m){ titleCaseButtons(); })
      .observe(document.documentElement, {childList:true,subtree:true});
  })();
  JS;
    wp_add_inline_script('jquery', $js);
  }
  add_action('wp_enqueue_scripts', 'optimiselearning_titlecase_buttons', 20);

  
/* Classrooms link */
function add_classroom_search_link() {
    add_menu_page(
        '2025 Classrooms', // Page title
        '2025 Classrooms', // Menu title
        'edit_posts',      // Capability required
        'edit.php?post_type=page&s=classroom&author=89&paged=1', // Menu slug with search parameter
        '',                // Function to output content
        'dashicons-desktop', // Computer screen icon
        1                  // Position (top)
    );
}
add_action('admin_menu', 'add_classroom_search_link');
/* endof */

add_shortcode( 'comment_form', 'comment_form_funs' );
function comment_form_funs() {

    global $wpdb;
    $current_user = wp_get_current_user();

    $validate = false;
    if ( isset( $_POST['student_id'] ) ) {
        $student_id = $_POST['student_id'];
		$student_name = $_POST['student_name'];
        $date = $_POST['comment_date'];
        $course_id = $_POST['course_id'];
        $general_goal_id = $_POST['general_goals'];
        $special_goals_id = $_POST['special_goals'];
        $special_comments_id = $_POST['special_comments'];
        $general_comments_id = $_POST['general_comments'];

        if ($student_id != '' && $date != '' && $course_id != '' && $special_goals_id != '' && $general_goal_id != '' && $special_comments_id != '' && $general_comments_id != '' && $student_name != '') {
            $validate = true;
        }

        if ($validate) {
            $wpdb->insert('student_comments',
                array(
                    'tutor_id' => $current_user->data->ID,
                    'student_id' => $student_id,
					'student_name' => $student_name,
                    'date' => $date,
                    'course_id' => $course_id,
                    'special_goals_id' => $special_goals_id,
                    'general_goal_id' => $general_goal_id,
                    'special_comments_id' => $special_comments_id,
                    'general_comments_id' => $general_comments_id
                ),
                array( '%d', '%s', '%s', '%s', '%s')
            );

            $student_id = '';
            $date = '';
			$student_name = '';
            $course_id = '';
            $special_goals_id = '';
            $general_goal_id = '';
            $special_comments_id = '';
            $general_comments_id = '';
        }
    }

    $students = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `mm_user_data` WHERE membership_level_id=1 AND status = 1 ORDER BY first_name ASC, last_name ASC")
             );

    $courses = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `mm_products` WHERE status = 1"));

    $generalgoalsdb = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `goals` WHERE `type` = 'general'"));

    $generalgoals = [];
    foreach ($generalgoalsdb as $goal) {
        $generalgoals[$goal->type][] = $goal;
    }

    $goalsdb = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `goals` WHERE `type` != 'general'"));

    $goals = [];
    foreach ($goalsdb as $goal) {
        $goals[] = $goal;
    }

    $generalcommentsdb = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `comments` WHERE `type` = 'general'"));

    $generalcomments = [];
    foreach ($generalcommentsdb as $comment) {
        $generalcomments[$comment->type][] = $comment;
    }

    $commentsdb = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `comments` WHERE `type` != 'general'"));

    $comments = [];
    foreach ($commentsdb as $comment) {
        $comments[] = $comment;
    }

    ob_start();
    ?>
    <style>
        .hiddenfield { display: none; }
    </style>
    <div>
    <h3 style="font-weight: bold">Lesson Feedback</h3>
    <p id="success_msg" class="<?php echo ($validate?'':'hiddenfield'); ?>" style="color: green">Your comment have been submitted. </p>
    <p id="fail_msg" class="<?php echo ($validate == false && isset( $_POST['comment_submit'] )?'':'hiddenfield'); ?>" style="color: red">Please enter all fields! </p>
    <form id="comment_form" method="post" action="">
    <div class="tut-fm">
        <select id="student_id" name="student_id">
            <option value="">Parent Account Name</option>
        <?php foreach ($students as $student) { ?>
            <option <?php echo ($student->wp_user_id==$student_id?'selected="selected"':''); ?> value="<?=$student->wp_user_id; ?>"><?=$student->first_name;?> <?=$student->last_name; ?></option>
        <?php } ?>
        </select>
    </div>
	<div class="tut-fm">
		<input type="text" placeholder="Student Name" name="student_name" value="<?=$student_name; ?>"/>
	</div>
    <div class="tut-fm">
        <input type="text" id="datepicker" placeholder="Date" name="comment_date" value="<?=$date; ?>">
    </div>
	<div class="tut-fm">
        <select id="course_id" name="course_id" style="margin-bottom: 0px !important">
        <option value="">Course</option>
        <?php foreach ($courses as $course) { ?>
            <option <?php echo ($course->id==$course_id?'selected="selected"':''); ?> value="<?=$course->id; ?>"><?=$course->name;?></option>
        <?php } ?>
        </select>
	</div>
	<div class="tut-fm">
		<select id="general_goals" name="general_goals">
		<option value="">General Curriculum Goals</option>
		<?php foreach ($generalgoals['general'] as $goal) { ?>
			<option <?php echo ($goal->id==$general_goal_id?'selected="selected"':''); ?> value="<?=$goal->id; ?>"><?=$goal->name;?></option>
		<?php } ?>
		</select>
	</div>
	<div class="hiddenfield goals tut-fm" id="special_goals">
		<select id="special_goals_field" name="special_goals">
		<option id="base_speical_goals"  value="">Goals</option>
		<?php foreach ($goals as $goal) { ?>
			<option data-type="<?=$goal->type; ?>" <?php echo ($goal->id==$special_goals_id?'selected="selected"':''); ?> value="<?=$goal->id; ?>"><?=$goal->name;?></option>
		<?php } ?>
		</select>
	</div>
	<div class="hiddenfield comments tut-fm" id="special_comments">
		<select id="special_comments_field" name="special_comments">
		<option id="base_speical_comments" value="">Comments</option>
		<?php foreach ($comments as $comment) { ?>
			<option data-type="<?=$comment->type; ?>" <?php echo ($comment->id==$special_comments_id?'selected="selected"':''); ?> value="<?=$comment->id; ?>"><?=$comment->name;?></option>
		<?php } ?>
		</select>
	</div>

	<div class="hiddenfield general_comments tut-fm" id="general_comments">
		<select id="general_comments_field" name="general_comments">
		<option value="">General Comments</option>
		<?php foreach ($generalcomments['general'] as $comment) { ?>
			<option <?php echo ($comment->id==$general_comments_id?'selected="selected"':''); ?> value="<?=$comment->id; ?>"><?=$comment->name;?></option>
		<?php } ?>
		</select>
	</div>
        <input id="comment_form_submit" style="border: 0px; border-radius: 3px;margin-bottom: 30px;width: 100%; text-align: center" type="submit" name="comment_submit" value="SUMMIT COMMENTS">
    </form>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'course_comments', 'course_comments_funs');
function course_comments_funs() {

    global $wpdb;
    $current_user = wp_get_current_user();

   /* $items = $wpdb->get_results(
                $wpdb->prepare("SELECT mi.item_id as itemId FROM `mm_order_items` mi, `mm_orders` o WHERE o.id = mi.order_id AND o.user_id = %d ORDER BY o.id DESC", $current_user->data->ID)
             );

    $register_courses = [];
    foreach ($items as $item) {
        $register_courses[] = $item->itemId;
    }

    $comments = [];
    $detaiail_comments = [];
    if (!empty($register_courses) && count($register_courses) > 0) {
        $comments = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM student_comments WHERE student_id = %d AND course_id IN (".implode(',',$register_courses).") ORDER BY date DESC LIMIT 3", $current_user->data->ID)
                 );
    }
*/

	$comments = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM student_comments WHERE student_id = %d ORDER BY date DESC LIMIT 4", $current_user->data->ID)
                 );

	
    ob_start();
    ?>
    <?php if (!empty($comments) && count($comments) > 0) { ?>
    <div>
    <h3 style="color:#1A2846;font-weight:bold">Lesson Feedback</h3>
    <?php foreach ($comments as $comment) { ?>
        <div style="margin: 20px 0">
        <p style="line-height:1.1em;color:#1A2846">
           <strong>Course</strong>: <?=getCourseName($comment->course_id);?>
		   <br/><strong>Student</strong>: <?=$comment->student_name; ?>
           <br/><strong>Tutor</strong>: <?=getTutorName($comment->tutor_id); ?>
           <br/><strong>Date</strong>: <?=$comment->date; ?>
           <?=getGoal($comment->general_goal_id); ?>
           <?=getGoal($comment->special_goals_id); ?>
           <?=getComment($comment->special_comments_id); ?>
           <?=getComment($comment->general_comments_id); ?>
        <div style="height:3px; width: 100px; background-color: #1A2846; margin: 50px 0;"></div>
        </div>
    <?php } ?>
    </div>
    <?php } ?>
    <?php
    return ob_get_clean();
}

function getComment($comment_id) {
    global $wpdb;

    $comment = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM comments WHERE id = %d",$comment_id)
    );
    if ($comment->name) {
        return '<br/><strong>'.ucfirst($comment->type).' Comments</strong>: '.$comment->name;
    } else {
        return '';
    }
}

function getGoal($goal_id) {
    global $wpdb;

    $goal = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM goals WHERE id = %d",$goal_id)
    );
    if ($goal->name) {
        return '<br/><strong>'.ucfirst($goal->type).' Goals</strong>: '.$goal->name;
    } else {
        return '';
    }
}


function getTutorName($tutor_id) {

    global $wpdb;

    $tutor = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `mm_user_data` WHERE wp_user_id = %d",$tutor_id)
         );
    return $tutor->first_name.' '.$tutor->last_name;
}

function getCourseName($course_id) {

    global $wpdb;

    $course = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `mm_products` WHERE status = 1 AND id = %d",$course_id)
         );
    return $course->name;

}

add_filter( 'presscore_nav_menu_item', 'new_menu_item' , 10, 5 );
function new_menu_item( $menu_item, $title, $description, $item, $depth ) {

    $current_user = wp_get_current_user();

    if ( isset( $item->the7_mega_menu['menu-item-image-position'], $item->the7_mega_menu['menu-item-icon-type'] ) && in_array( $item->the7_mega_menu['menu-item-icon-type'], array( 'image', 'icon' ), true ) && in_array( $item->the7_mega_menu['menu-item-image-position'], array( 'right_top', 'left_top' ), true ) ) {
        $menu_item = '<span class="menu-item-text"><span class="menu-text">' . $title . '</span></span>' . $description;
    } else {
        if ($title == 'LOGIN' && $current_user->ID > 0) {
            $title = 'LOGOUT';
        }
        $menu_item = '<span class="menu-item-text"><span class="menu-text">' . $title . '</span>' . $description . '</span>';
    }

    return $menu_item;
}

add_filter( 'presscore_nav_menu_link_attributes' , 'new_menu_item_attr', 10, 5);
function new_menu_item_attr($atts, $item, $args, $depth ) {

    $current_user = wp_get_current_user();

    if ($atts['href'] == 'https://optimiselearning.com/login/'&& $current_user->ID > 0) {
        $atts['href'] = 'https://optimiselearning.com/logout/';
    }

    return $atts;
}

add_action('wp_footer','custom_script');
function custom_script(){
 echo "<script type='text/javascript'>
    jQuery(document).ready(function(){
        jQuery( '#datepicker').datepicker();
        jQuery('#general_goals').change(function() {
            jQuery('.comments').hide();
            jQuery('.comments option:eq(0)').prop('selected', true);
            jQuery('#general_comments').hide();
            jQuery('#general_comments option:eq(0)').prop('selected', true);
            jQuery('.goals').hide();
            let selected = $(this).val();
            jQuery('#special_goals').hide();
            switch (selected) {
                case '1':
                    jQuery('.goals option').hide();
                    jQuery('.goals option:eq(0)').text('Reading Goals').prop('selected', true).show();
                    jQuery('.goals option[data-type=\"reading\"]').show();
                    jQuery('#special_goals').show();
                    break;
                case '2':
                    jQuery('.goals option').hide();
                    jQuery('.goals option:eq(0)').text('Spelling Goals').prop('selected', true).show();
                    jQuery('.goals option[data-type=\"spelling\"]').show();
                    jQuery('#special_goals').show();
                    break;
                case '3':
                    jQuery('.goals option').hide();
                    jQuery('.goals option:eq(0)').text('Writing Goals').prop('selected', true).show();
                    jQuery('.goals option[data-type=\"writing\"]').show();
                    jQuery('#special_goals').show();
                    break;
                case '4':
                    jQuery('.goals option').hide();
                    jQuery('.goals option:eq(0)').text('Mathematics Goals').prop('selected', true).show();
                    jQuery('.goals option[data-type=\"math\"]').show();
                    jQuery('#special_goals').show();
                    break;
                case '5':
                case '6':
                case '7':
                    jQuery('.goals option').hide();
                    jQuery('.goals option:eq(0)').text('Language Goals').prop('selected', true).show();
                    jQuery('.goals option[data-type=\"language\"]').show();
                    jQuery('#special_goals').show();
                    break;
                default:
                    jQuery('#special_goals').hide();
                    break;
            }
        });

        jQuery('#special_goals').change(function() {
            jQuery('.comments').hide();
            jQuery('.comments option:eq(0)').prop('selected', true);
            jQuery('#general_comments').hide();
            jQuery('#general_comments option:eq(0)').prop('selected', true);

            let selected_type = jQuery(this).find('option:selected').attr('data-type');
            if (typeof selected_type != 'undefined') {
                jQuery('.comments option').hide();
                jQuery('.comments option:eq(0)').text(selected_type.replace(/\b\w/g, l => l.toUpperCase())+' Comments').prop('selected', true).show();
                jQuery('.comments option[data-type=\"'+selected_type+'\"]').show();

                jQuery('.comments').show();
                jQuery('.general_comments').show();
            }
        });

        jQuery('#comment_form').submit(function(e) {
            e.preventDefault();
            let student_id = jQuery('#student_id').val();
            let date = jQuery('#datepicker').val();
            let course_id = jQuery('#course_id').val();
            let general_goals = jQuery('#general_goals').val();
            let special_goals = jQuery('#special_goals_field').val();
            let general_comments = jQuery('#general_comments_field').val();
            let speical_comments = jQuery('#special_comments_field').val();

            if (student_id == '' || date == '' || course_id == '' || general_goals == '' || special_goals == '' || special_goals == '' || general_comments == '' || speical_comments == '') {
                jQuery('#fail_msg').show();
                jQuery('#success_msg').hide();
            } else {
                e.currentTarget.submit();
            }
        });
    });
</script>";
}

// -----------------------------------------------------------------------------
// [ol_blog_grid per_page="9" cols="3" category="" show_excerpt="true"]
// Simple, performant blog grid that matches your .ol- styles in style.css
// -----------------------------------------------------------------------------
function ol_blog_grid_shortcode( $atts ) {
    $a = shortcode_atts([
        'per_page'     => 9,        // posts per page
        'cols'         => 3,        // 1–4, controls CSS grid columns via data-attr
        'category'     => '',       // optional category slug (or comma-separated slugs)
        'show_excerpt' => 'true',   // "true" | "false"
        'orderby'      => 'date',
        'order'        => 'DESC',
    ], $atts, 'ol_blog_grid' );

    // Pagination works even on a static page
    $paged = max( 1, get_query_var('paged') ?: get_query_var('page') ?: 1 );

    $args = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => (int) $a['per_page'],
        'paged'               => $paged,
        'orderby'             => sanitize_text_field( $a['orderby'] ),
        'order'               => sanitize_text_field( $a['order'] ),
        'ignore_sticky_posts' => true,
    ];
    if ( ! empty( $a['category'] ) ) {
        $args['category_name'] = sanitize_title( $a['category'] );
    }

    $q = new WP_Query( $args );
    if ( ! $q->have_posts() ) {
        return '';
    }

    ob_start();

    // Outer container + grid (your CSS targets these classes)
    printf(
        '<section class="ol-hero ol-hero--blog"><div class="ol-hero__inner"><div class="ol-blog-archive" data-cols="%d"><div class="ol-blog-grid">',
        min(4, max(1, (int) $a['cols']))
    );

    while ( $q->have_posts() ) : $q->the_post();
        // Reading time ~200wpm
        $words = str_word_count( wp_strip_all_tags( get_the_content() ) );
        $mins  = max( 1, ceil( $words / 200 ) );

        $thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
        ?>
        <article class="ol-card">
            <a class="ol-card__media" href="<?php the_permalink(); ?>">
                <?php if ( $thumb_url ) : ?>
                    <img src="<?php echo esc_url( $thumb_url ); ?>"
                         alt="<?php the_title_attribute(); ?>" loading="lazy">
                <?php else : ?>
                    <div class="ol-card__placeholder" aria-hidden="true"></div>
                <?php endif; ?>
            </a>

            <div class="ol-card__body">
                <div class="ol-card__cats">
                    <?php
                    $cats = get_the_category();
                    if ( $cats ) {
                        foreach ( $cats as $c ) {
                            printf(
                                '<a href="%s">%s</a>',
                                esc_url( get_category_link( $c->term_id ) ),
                                esc_html( $c->name )
                            );
                        }
                    }
                    ?>
                </div>

                <h3 class="ol-card__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>

                <?php if ( 'true' === strtolower( $a['show_excerpt'] ) ) : ?>
                    <p class="ol-card__excerpt">
                        <?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?>
                    </p>
                <?php endif; ?>

                <div class="ol-card__meta">
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                        <?php echo esc_html( get_the_date() ); ?>
                    </time>
                    <span class="sep">•</span>
                    <span><?php echo esc_html( $mins ); ?> min read</span>
                </div>
            </div>
        </article>
        <?php
    endwhile;

    echo '</div>'; // .ol-blog-grid

    // Pagination
    $links = paginate_links( [
        'total'     => $q->max_num_pages,
        'current'   => $paged,
        'type'      => 'array',
        'mid_size'  => 1,
        'prev_text' => '‹ Prev',
        'next_text' => 'Next ›',
    ] );

    if ( $links ) {
        echo '<nav class="ol-pagination"><ul>';
        foreach ( $links as $link ) {
            echo '<li>' . $link . '</li>';
        }
        echo '</ul></nav>';
    }

    echo '</div></div></section>'; // close .ol-blog-archive, .ol-hero__inner, .ol-hero

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'ol_blog_grid', 'ol_blog_grid_shortcode' );