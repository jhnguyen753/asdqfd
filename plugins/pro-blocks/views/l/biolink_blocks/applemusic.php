<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-<?= $data->biolink->settings->block_spacing ?? '2' ?>">
    <iframe class="embed-responsive-item" scrolling="no" frameborder="no" style="height: auto;width:100%;overflow:hidden;background:transparent;" src="<?= $data->link->location_url ?>"></iframe>
</div>
