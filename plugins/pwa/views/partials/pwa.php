<?php if(!isset($_COOKIE['dismiss_pwa_popup'])): ?>

    <?php
    /* Detect extra details about the user */
    $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);

    /* Detect extra details about the user */
    $browser_name = $whichbrowser->browser->name ?? null;
    $os_name = $whichbrowser->os->name ?? null;
    $device_type = get_device_type($_SERVER['HTTP_USER_AGENT']);

    //echo $browser_name . ' - ' . $os_name . ' - ' . $device_type;

    $subheader = null;

    /* Desktop computers */
    if($device_type == 'desktop') {
        $subheader = sprintf(l('pwa_install.subheader.' . $device_type), '<i class="fas fa-fw fa-sm fa-download mx-1"></i>');

        if($os_name == 'OS X' && $browser_name == 'Safari') {
            $subheader = sprintf(l('pwa_install.subheader.ios_safari'), '<i class="fas fa-fw fa-sm fa-external-link-alt mx-1"></i>', '<i class="fas fa-fw fa-sm fa-plus-square mx-1"></i>');
        }
    }

    /* Mobile phones */
    if($device_type == 'mobile' || $device_type == 'tablet') {
        /* Android phones */
        if($os_name == 'Android' && $browser_name == 'Chrome') {
            $subheader = sprintf(l('pwa_install.subheader.android_chrome'), '<i class="fas fa-fw fa-sm fa-ellipsis-v mx-1"></i>', '<i class="fas fa-fw fa-sm fa-download mx-1"></i>');
        }

        if($os_name == 'iOS' && $browser_name == 'Chrome') {
            $subheader = sprintf(l('pwa_install.subheader.ios'), '<i class="fas fa-fw fa-sm fa-external-link-alt mx-1"></i>', '<i class="fas fa-fw fa-sm fa-plus-square mx-1"></i>');
        }

        if($os_name == 'iOS' && $browser_name == 'Safari') {
            $subheader = sprintf(l('pwa_install.subheader.ios'), '<i class="fas fa-fw fa-sm fa-external-link-alt mx-1"></i>', '<i class="fas fa-fw fa-sm fa-plus-square mx-1"></i>');
        }
    }
    ?>

    <div class="announcement-wrapper pwa-wrapper py-3 bg-gray-200">
        <div class="container d-flex justify-content-center position-relative">
            <div class="row w-100">
                <div class="col">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center">
                        <span class="font-weight-bold mr-lg-3 mb-1 mb-lg-0"><?= l('pwa_install.header') ?></span>

                        <span><?= $subheader ?></span>
                    </div>
                </div>
                <div class="col-auto px-0">
                    <div>
                        <button data-pwa-close type="button" class="close" data-dismiss="alert" data-toggle="tooltip" title="<?= l('global.close') ?>" data-tooltip-hide-on-click>
                            <i class="fas fa-sm fa-times" style="opacity: .5;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ob_start() ?>
    <script>
        /* Handle the close button */
        document.querySelector('[data-pwa-close]').addEventListener('click', event => {
            document.querySelector(`.pwa-wrapper`).style.display = 'none';
            set_cookie(`dismiss_pwa_popup`, 1, 90, <?= json_encode(COOKIE_PATH) ?>);
        })

        /* Set the dismiss cookie if pwa was installed */
        document.addEventListener('appinstalled', event => {
            document.querySelector(`.pwa-wrapper`).style.display = 'none';
            set_cookie(`dismiss_pwa_popup`, 1, 90, <?= json_encode(COOKIE_PATH) ?>);
        })

        /* Do not display on PWA opens */
        if(window.matchMedia('(display-mode: standalone)').matches) {
            document.querySelector(`.pwa-wrapper`).style.display = 'none';
        }
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
<?php endif ?>
