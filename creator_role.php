<?php
// Creator role with capabilities limited to the 'video' and 'lesson' CPTs.

// Map selected CPTs to custom capability_type before they are registered.
add_filter( 'register_post_type_args', function ( $args, $post_type ) {

    $configured_cpts = [
        'video'  => [ 'video', 'videos' ],
        'lesson' => [ 'lesson', 'lessons' ],
    ];

    if ( ! isset( $configured_cpts[ $post_type ] ) ) {
        return $args;
    }

    $args['capability_type'] = $configured_cpts[ $post_type ];
    $args['map_meta_cap']    = true;

    if ( ! isset( $args['supports'] ) ) {
        $args['supports'] = [ 'title', 'editor', 'thumbnail', 'author' ];
    } elseif ( is_array( $args['supports'] ) && ! in_array( 'author', $args['supports'], true ) ) {
        $args['supports'][] = 'author';
    }

    return $args;

}, 1, 2 );


// Create/update the Creator role and ensure Admin has full video caps.
add_action( 'init', function () {

    // Creator role
    $caps_grant = [
        'read'                    => true,
        'upload_files'            => true,
        'level_1'                 => true,
        
        'create_videos'           => true,
        'edit_videos'             => true,
        'edit_published_videos'   => true,
        'publish_videos'          => true,
        'delete_videos'           => true,
        'delete_published_videos' => true,
        'create_lessons'           => true,
        'edit_lessons'             => true,
        'edit_published_lessons'   => true,
        'publish_lessons'          => true,
        'delete_lessons'           => true,
        'delete_published_lessons' => true,
    ];

    $caps_deny = [
        'edit_others_videos',
        'edit_private_videos',
        'delete_others_videos',
        'delete_private_videos',
        'read_private_videos',
        'edit_others_lessons',
        'edit_private_lessons',
        'delete_others_lessons',
        'delete_private_lessons',
        'read_private_lessons',
        'edit_posts',
        'edit_others_posts',
        'publish_posts',
        'delete_posts',
        'delete_others_posts',
        'manage_options',
    ];

    if ( ! get_role( 'creator' ) ) {
        add_role( 'creator', __( 'Creator', 'your-textdomain' ), $caps_grant );
    } else {
        $role = get_role( 'creator' );
        foreach ( $caps_grant as $cap => $grant ) {
            $role->add_cap( $cap, $grant );
        }
        foreach ( $caps_deny as $cap ) {
            if ( isset( $role->capabilities[ $cap ] ) ) {
                $role->remove_cap( $cap );
            }
        }
    }

    // Administrator capabilities
    $admin = get_role( 'administrator' );
    if ( ! $admin ) {
        return;
    }

    $admin_caps = [
        'read_video', 'edit_video', 'delete_video',
        'create_videos', 'edit_videos', 'edit_others_videos',
        'edit_private_videos', 'edit_published_videos',
        'publish_videos', 'read_private_videos',
        'delete_videos', 'delete_others_videos',
        'delete_private_videos', 'delete_published_videos',
        'read_lesson', 'edit_lesson', 'delete_lesson',
        'create_lessons', 'edit_lessons', 'edit_others_lessons',
        'edit_private_lessons', 'edit_published_lessons',
        'publish_lessons', 'read_private_lessons',
        'delete_lessons', 'delete_others_lessons',
        'delete_private_lessons', 'delete_published_lessons',

    ];

    foreach ( $admin_caps as $cap ) {
        if ( empty( $admin->capabilities[ $cap ] ) ) {
            $admin->add_cap( $cap, true );
        }
    }

}, 20 );


// Author dropdown - Classic editor, Quick Edit, and Gutenberg.
function vwt_authors_dropdown_classic( $query_args, $r ) {
    if ( isset( $r['name'] ) && ( $r['name'] === 'post_author_override' || $r['name'] === 'post_author' ) ) {
        unset( $query_args['who'], $query_args['capability'] );
        $query_args['role__in'] = [ 'administrator', 'creator' ];
    }
    return $query_args;
}
add_filter( 'wp_dropdown_users_args', 'vwt_authors_dropdown_classic', 10, 2 );

function vwt_authors_dropdown_gutenberg( $prepared_args, $request = null ) {
    if ( isset( $prepared_args['who'] ) && $prepared_args['who'] === 'authors' ) {
        unset( $prepared_args['who'], $prepared_args['capability'] );
        $prepared_args['role__in'] = [ 'administrator', 'creator' ];
    }
    return $prepared_args;
}
add_filter( 'rest_user_query', 'vwt_authors_dropdown_gutenberg', 10, 2 );
