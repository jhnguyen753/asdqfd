<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-<?= $data->biolink->settings->block_spacing ?? '2' ?> d-flex justify-content-center">
    <blockquote
            class="snapchat-embed"
            data-snapchat-embed-width="416" data-snapchat-embed-height="692"
            data-snapchat-embed-url="<?= $data->link->location_url . '/embed' ?>"
            data-snapchat-embed-style="border-radius: 40px;"
            data-test="<?= $data->link->location_url ?>"
            style="background:#C4C4C4; border:0; border-radius:40px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:416px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px); display: flex; flex-direction: column; position: relative; height:650px;"
    ></blockquote>

    <?php if(!\Altum\Event::exists_content_type_key('javascript', 'snapchat')): ?>
        <?php ob_start() ?>
        <script async src="https://www.snapchat.com/embed.js"></script>
        <?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'snapchat') ?>
    <?php endif ?>
</div>
