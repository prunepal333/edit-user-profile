<?php
/**
 * @package edit_user_profile
 * @version 1.0.0
 */
/*
Plugin Name: Edit User Profile
Plugin URI: http://wordpress.org/plugins/edit-user-profile/
Description: Plugin to edit user profile information like username and profile pictures which are not editable by default.
Author: Purshottam Nepal
Version: 1.0.0
Author URI: http://nepalp.com.np
*/
function custom_user_profile_fields($user) {
    // Add custom fields
    if (in_array('administrator', $user->roles)):
    ?>
    <div style="border: 2px solid #eee; padding: 12px 8px;">
        <h3 style="color: rgba(50, 50, 50);">Update Profile (For admin only)</h3>
        <table class="form-table">
            <tr>
                <th><label for="username_new">New Username</label></th>
                <td>
                    <input type="text" name="username_new" id="username_new" value="<?php echo esc_attr($user->user_login); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="custom_user_image">New Profile Picture</label></th>
                <td>
                    <input type="file" name="custom_user_image" id="custom_user_image" class="regular-text" />
                </td>
            </tr>
        </table>
    </div>
    <?php
    endif;
}
// It is necessary if we are adding new fields to the existing user profile page.
function save_custom_user_profile_fields($user_id) {
    if (current_user_can('edit_user', $user_id)) {
        global $wpdb;
        $new_username = sanitize_text_field($_POST['username_new']);
        if (!empty($new_username) && !username_exists($new_username)) { // if the username doesn't already exists, replace the username
            $wpdb->update(
                $wpdb->users, 
                ['user_login' => $new_username], 
                ['ID' => $user_id]
            );
        }
        if ($_FILES) {
            $mimetype = $_FILES['custom_user_image']['type'];
            $data = file_get_contents($_FILES['custom_user_image']['tmp_name']);
            $encoded_data = base64_encode($data);
            $blob = "data:$mimetype;base64,$encoded_data";
            update_user_meta($user_id, 'custom_user_image', $blob);
        }
    }
}
add_action('show_user_profile', 'custom_user_profile_fields'); // show page for the current user
add_action('edit_user_profile', 'custom_user_profile_fields'); // edit page for all users
add_action('personal_options_update', 'save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'save_custom_user_profile_fields');

//fetches the avatar from user metadata stored as blob
function custom_avatar_url($avatar, $user_id) {
    $custom_avatar = get_user_meta($user_id, 'custom_user_image');
    if ($custom_avatar) {
        $avatar = "<img alt= '' src='{$custom_avatar[0]}' class='avatar avatar-32 photo' height='32' width='32'>";
    }
    return $avatar;
}
add_filter('get_avatar', 'custom_avatar_url', 10, 2);

add_action('user_edit_form_tag', 'user_edit_form');
function user_edit_form() {
    echo ' enctype="multipart/form-data"';
}