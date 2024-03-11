<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-<?= $data->biolink->settings->block_spacing ?? '2' ?>">
    <a href="<?= $data->link->location_url . $data->link->utm_query ?>" data-track-biolink-block-id="<?= $data->link->biolink_block_id ?>" target="<?= $data->link->settings->open_in_new_tab ? '_blank' : '_self' ?>" rel="<?= $data->user->plan_settings->dofollow_is_enabled ? 'dofollow' : 'nofollow' ?>" class="btn btn-block btn-primary link-btn link-big-btn <?= ($data->biolink->settings->hover_animation ?? 'smooth') != 'false' ? 'link-hover-animation-' . ($data->biolink->settings->hover_animation ?? 'smooth') : null ?> <?= 'link-btn-' . $data->link->settings->border_radius ?> <?= $data->link->design->link_class ?> d-flex align-items-center" style="<?= $data->link->design->link_style ?>" data-text-color data-border-width data-border-radius data-border-style data-border-color data-border-shadow data-animation data-background-color data-text-alignment>
        <div class="link-big-btn-image-wrapper <?= 'link-btn-' . $data->link->settings->border_radius ?> mr-3" <?= $data->link->settings->image ? null : 'style="display: none;"' ?>>
            <img src="<?= $data->link->settings->image ? \Altum\Uploads::get_full_url('block_thumbnail_images') . $data->link->settings->image : null ?>" class="link-big-btn-image" loading="lazy" />
        </div>

        <div class="link-big-content-wrapper d-flex flex-column">
            <span class="h4" data-name><?= $data->link->settings->name ?></span>
            <small style="color: <?= $data->link->settings->description_color ?>;" data-description data-description-color><?= nl2br($data->link->settings->description) ?></small>
        </div>

        <div class="link-big-icon-wrapper text-center ml-2" data-icon>
            <?php if($data->link->settings->icon): ?>
                <i class="<?= $data->link->settings->icon ?>"></i>
            <?php endif ?>
        </div>
    </a>
</div>
