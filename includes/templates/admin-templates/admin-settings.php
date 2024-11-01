<div class="wrap">
    <h1>ZigNotch Settings</h1>

    <form action="options.php" method="post">
        <?php
        settings_fields('apisettings');

        do_settings_sections('api-settings');


        submit_button();
        ?>

    </form>
    <form action="<?php echo esc_url(admin_url('admin-post.php')) ?>" method="post">
        <input type="hidden" name="action" value="zignotch_revoke_key">
        <input type="hidden" name="api_key_revoke">
        <input type="submit" value="Revoke key" class="button button-primary" style="background: #b12222; border-color: #ae2222;">
    </form>
</div>