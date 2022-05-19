<?php
function sapnews_breadcrumbs($inSecondaryNavigation = true)
{
    global $post;

    if (is_front_page()) { ?>
        <span class="highlight"><?php bloginfo('name'); ?></span>
    <?php return;}
    ?>

    <span>
        <a title="<?php bloginfo(
            'name'
        ); ?>" href="<?php bloginfo('url'); ?>" class="home">
            <?php bloginfo('name'); ?>
        </a>
    </span>

    <span class="slash second">/</span>
    <?php
    if (is_post_type_archive('sap-tv')) {<?php
        /* CPT SAP TV archive page */
        ?>
        <span class="highlight"><?php _e('SAP TV', 'sapn-theme'); ?></span>
    <?php return;}

    if (is_search()) { ?>
        <span class="bold"><?php _e(
            'Search results for:',
            'sapn-theme'
        ); ?> '<?php echo get_search_query(); ?>'</span>
    <?php return;}

    if (is_tag()) { ?>
        <span class="highlight"><?php single_tag_title(); ?></span>
    <?php return;}

    if (is_tax('region')) { ?>
        <span class="highlight"><?php single_term_title(); ?></span>
    <?php return;}

    if (is_category()) { ?>
        <span class="highlight"><?php single_cat_title(); ?></span>
        <?php return;}

    /* Get the Breadcrumb-group if any */

    if (
        is_single() ||
        is_tax('sapn-type') ||
        is_post_type_archive('sap-tv') ||
        isCorporateBlogContributor()
    ) {
        $sapnTypeTerms = wp_get_post_terms(get_the_ID(), 'sapn-type', [
            'fields' => 'ids',
        ]);
        if (!empty($sapnTypeTerms)) {
            $group = get_term_meta($sapnTypeTerms[0], 'sapn_type_group', true);
            if (isset(SAPN_TYPE_GROUPS[$group])) {
                if (is_single()) {
                    $termArchiveLink = get_term_link($sapnTypeTerms[0]);
                }

                echo is_single()
                    ? '<a href="' .
                        $termArchiveLink .
                        '"><span class="highlight">' .
                        SAPN_TYPE_GROUPS[$group] .
                        '</span></a>'
                    : '<span class="highlight">' .
                        SAPN_TYPE_GROUPS[$group] .
                        '</span>';
            }

            if (empty($group)) {

                $sapnTypeTerm = get_term($sapnTypeTerms[0]);
                $termArchiveLink = get_term_link($sapnTypeTerms[0]);
                $aClass = $inSecondaryNavigation ? 'raw' : 'taxonomy category';
                echo is_archive() ? '<span class="highlight">' : '';
                ?>
                <a class="<?php echo esc_attr(
                    $aClass
                ); ?>" title="<?php echo esc_attr(
    $sapnTypeTerm->name
); ?>" href="<?php echo esc_url($termArchiveLink); ?>">
                    <?php echo $sapnTypeTerm->name; ?>
                </a>
            <?php echo is_archive() ? '</span>' : '';
            }
        }
    }

    if (is_author()) {
        global $wp_query;
        $author = $wp_query->get_queried_object();

        if (!$author instanceof \WP_User) {
            return;
        }
        if (
            function_exists('hasSecondaryProfileImage') &&
            hasSecondaryProfileImage($author)
        ): ?>

            <span class="highlight"><?php esc_html_e(
                'Contributor Profile',
                'sapnews-theme'
            ); ?></span>
        <?php elseif (!empty($author->display_name)): ?>
            <span class="highlight author"><?php echo sprintf(
                esc_html_x(
                    'Articles by: %s',
                    'Describes the archive for a specific author',
                    'sapn-theme'
                ),
                $author->display_name
            ); ?></span>
        <?php endif;
        return;
    }

    if (is_page_template('page-pressroom.php')) { ?>
        <span class="highlight"><?php _e('Press Room', 'sapn-theme'); ?></span>
    <?php return;}

    /* Get the ID of the Press Room page, selected in the Customizer */
    $press_room_page = get_theme_mod('sap_news_center_press_room_page', 0);
    if (
        is_page() &&
        property_exists($post, 'post_parent') &&
        is_numeric($post->post_parent) &&
        (int) $post->post_parent === (int) $press_room_page
    ) { ?>
        <span><a href="<?php echo get_permalink($press_room_page); ?>"><?php _e(
    'Press Room',
    'sapn-theme'
); ?></a></span>
        <span class="slash second">/</span>
        <span class="highlight"><?php echo $post->post_title; ?></span>
    <?php return;}

    if (is_page()) { ?>
        <span class="highlight"><?php the_title(); ?></span>
        <?php }

    if (is_single()) {
        /* get the primary category first to be displayed in the breadcrumb */
        $primaryCategoryID = sapn_get_primary_category();
        $category = get_term($primaryCategoryID);
        $category_link = get_category_link($primaryCategoryID);
        $category_title = sprintf(
            _x(
                'Go to the %s archives',
                'Title-attribute for a specific category/post-type archive link',
                'sapn-theme'
            ),
            is_a($category, \WP_Term::class) ? $category->name : ''
        );

        if (is_singular('sap-tv')) {<?php
            /* check if the post is a single view of CPT 'SAP TV', it needs to show different */
            ?>
            <span><a href="<?php bloginfo('url'); ?>/sap-tv/">SAP TV</a></span>
            <span class="slash second">/</span>
            <?php if (is_a($category, \WP_Term::class)): ?>
                <span class="highlight">
                    <strong>
                        <a href="<?php echo esc_url(
                            $category_link
                        ); ?>" title="<?php echo esc_attr(
    $category_title
); ?>"><?php echo $category->name; ?></a>
                    </strong>
                </span>
            <?php endif;} elseif (is_singular('podcast')) {
            /* Check if there is a Podcast page */
            $podcastPageID = get_theme_mod('sap_news_center_podcast_page');
            if (!empty($podcastPageID)) {
                $podcastPageObject = get_post($podcastPageID);
            }

            if (
                !empty($podcastPageObject) &&
                $podcastPageObject instanceof WP_Post
            ): ?>
                <span><a href="<?php echo get_permalink(
                    $podcastPageObject
                ); ?>"><?php echo wp_kses_post(
    $podcastPageObject->post_title
); ?></a></span>

            <?php else:
                $podcastPostType = get_post_type_object('podcast');
                $podcastArchiveLinkTitle = !empty(
                    $podcastPostType->labels->name
                )
                    ? $podcastPostType->labels->name
                    : _x(
                        'Podcasts',
                        'Fallback title for podcast archive',
                        'sapn-theme'
                    );
                ?>
                <span><a title="<?php echo sprintf(
                    _x(
                        'Go to the %s archives',
                        'Title-attribute for a specific category/post-type archive link',
                        'sapn-theme'
                    ),
                    $podcastArchiveLinkTitle
                ); ?>                                                                                                                 href=" <?php echo get_post_type_archive_link(
                                                                                                                     'podcast'
                                                                                                                 ); ?>"><?php echo esc_html(
    $podcastArchiveLinkTitle
); ?></a></span>

            <?php endif;
        } elseif (isset($group) && empty($group)) {

            $aClass = $inSecondaryNavigation ? 'raw' : 'taxonomy category';
            echo $inSecondaryNavigation ? '<span class="highlight">' : '';
            ?>
            <a class="<?php echo esc_attr(
                $aClass
            ); ?>" title="<?php echo esc_attr(
    $category_title
); ?>" href="<?php echo esc_url($category_link); ?>">
                <?php echo $category->name; ?>
            </a>
<?php echo $inSecondaryNavigation ? '</span>' : '';
        }
    }
}
