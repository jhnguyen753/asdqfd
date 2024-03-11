<?php defined('ALTUMCODE') || die() ?>

<div id="<?= 'biolink_block_id_' . $data->link->biolink_block_id ?>" data-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="col-12 my-<?= $data->biolink->settings->block_spacing ?? '2' ?>">
    <a href="#" data-toggle="modal" data-target="<?= '#share_' . $data->link->biolink_block_id ?>" data-track-biolink-block-id="<?= $data->link->biolink_block_id ?>" class="btn btn-block btn-primary link-btn <?= ($data->biolink->settings->hover_animation ?? 'smooth') != 'false' ? 'link-hover-animation-' . ($data->biolink->settings->hover_animation ?? 'smooth') : null ?> <?= 'link-btn-' . $data->link->settings->border_radius ?> <?= $data->link->design->link_class ?>" style="<?= $data->link->design->link_style ?>" data-text-color data-border-width data-border-radius data-border-style data-border-color data-border-shadow data-animation data-background-color data-text-alignment>
        <div class="link-btn-image-wrapper <?= 'link-btn-' . $data->link->settings->border_radius ?>" <?= $data->link->settings->image ? null : 'style="display: none;"' ?>>
            <img src="<?= $data->link->settings->image ? \Altum\Uploads::get_full_url('block_thumbnail_images') . $data->link->settings->image : null ?>" class="link-btn-image" loading="lazy" />
        </div>

        <span data-icon>
            <?php if($data->link->settings->icon): ?>
                <i class="<?= $data->link->settings->icon ?> mr-1"></i>
            <?php endif ?>
        </span>

        <span data-name><?= $data->link->settings->name ?></span>
    </a>
</div>

<?php ob_start() ?>
<div class="modal fade" id="<?= 'share_' . $data->link->biolink_block_id ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"><?= $data->link->settings->name ?></h5>
                <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="w-100" data-qr="<?= $data->link->location_url ?>"></div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between flex-wrap my-3">
                    <?= include_view(THEME_PATH . 'views/partials/share_buttons.php', ['url' => $data->link->location_url, 'class' => 'btn btn-gray-100 mb-2', 'print_is_enabled' => false]) ?>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" value="<?= $data->link->location_url ?>" onclick="this.select();" readonly="readonly" />
                </div>
            </div>

        </div>
    </div>
</div>
<?php \Altum\Event::add_content(ob_get_clean(), 'modals') ?>


<?php if(!\Altum\Event::exists_content_type_key('javascript', 'share')): ?>
<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/jquery-qrcode.min.js' ?>"></script>

<script>
    'use strict';

    let generate_qr = (element, data) => {
        let default_options = {
            render: 'image',
            minVersion: 1,
            maxVersion: 40,
            ecLevel: 'L',
            left: 0,
            top: 0,
            size: 1000,
            text: data,
            quiet: 0,
            mode: 0,
            mSize: 0.1,
            mPosX: 0.5,
            mPosY: 0.5,
        };

        /* Delete already existing image generated */
        element.querySelector('img') && element.querySelector('img').remove();
        $(element).qrcode(default_options);

        /* Set class to QR */
        element.querySelector('img').classList.add('w-100');
    }

    let qr_codes = document.querySelectorAll('[data-qr]');

    qr_codes.forEach(element => {
        generate_qr(element, element.getAttribute('data-qr'));
    })
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript', 'share') ?>
<?php endif ?>
