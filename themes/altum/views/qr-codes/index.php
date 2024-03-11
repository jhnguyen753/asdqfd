<?php defined('ALTUMCODE') || die() ?>

<section class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-qrcode mr-1"></i> <?= l('qr_codes.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('qr_codes.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex">
            <div>
                <?php if($this->user->plan_settings->qr_codes_limit != -1 && $data->total_qr_codes >= $this->user->plan_settings->qr_codes_limit): ?>
                    <button type="button" data-toggle="tooltip" title="<?= l('global.info_message.plan_feature_limit') ?>" class="btn btn-primary disabled">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('qr_codes.create') ?>
                    </button>
                <?php else: ?>
                    <a href="<?= url('qr-code-create') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_qr_codes, $this->user->plan_settings->qr_codes_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('qr_codes.create') ?>
                    </a>
                <?php endif ?>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= count($data->qr_codes) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>">
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('qr-codes?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('qr-codes?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= count($data->qr_codes) && !$data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.filters.header') ?>">
                        <i class="fas fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if($data->filters->has_applied_filters): ?>
                                <a href="<?= url('qr-codes') ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                                <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                <select name="search_by" id="filters_search_by" class="custom-select custom-select-sm">
                                    <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <div class="d-flex justify-content-between">
                                    <label for="filters_project_id" class="small"><?= l('projects.project_id') ?></label>
                                    <a href="<?= url('projects') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                                </div>
                                <select name="project_id" id="filters_project_id" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach($data->projects as $row): ?>
                                        <option value="<?= $row->project_id ?>" <?= isset($data->filters->filters['project_id']) && $data->filters->filters['project_id'] == $row->project_id ? 'selected="selected"' : null ?>><?= $row->name ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_type" class="small"><?= l('global.type') ?></label>
                                <select name="type" id="filters_type" class="custom-select custom-select-sm">
                                    <option value=""><?= l('global.all') ?></option>
                                    <?php foreach(array_keys((require APP_PATH . 'includes/qr_code.php')['type']) as $type): ?>
                                        <option value="<?= $type ?>" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == $type ? 'selected="selected"' : null ?>><?= $data->qr_code_settings['type'][$type]['emoji'] . ' ' . l('qr_codes.type.' . $type) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="filters_results_per_page" class="custom-select custom-select-sm">
                                    <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                        <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4 mt-4">
                                <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($data->qr_codes)): ?>
        <?php foreach($data->qr_codes as $row): ?>
            <div class="custom-row mb-4">
                <div class="row">
                    <div class="col-10 col-lg-5 d-flex align-items-center">
                        <div class="mr-3" data-toggle="tooltip" title="<?= l('global.download') ?>">
                            <a href="<?= \Altum\Uploads::get_full_url('qr_code') . $row->qr_code ?>" download="<?= $row->name . '.svg' ?>" target="_blank">
                                <img src="<?= \Altum\Uploads::get_full_url('qr_code') . $row->qr_code ?>" class="qr-code-avatar" loading="lazy" />
                            </a>
                        </div>

                        <div class="font-weight-bold text-truncate">
                            <a href="<?= url('qr-code-update/' . $row->qr_code_id) ?>"><?= $row->name ?></a>
                        </div>
                    </div>

                    <div class="col-3 d-none d-lg-flex flex-lg-row align-items-center justify-content-between">
                        <div>
                            <span class="badge badge-light">
                                <i class="<?= $data->qr_code_settings['type'][$row->type]['icon'] ?> fa-fw fa-sm mr-1"></i>
                                <?= l('qr_codes.type.' . $row->type) ?>
                            </span>
                        </div>

                        <div>
                            <?php if($row->project_id && isset($data->projects[$row->project_id])): ?>
                                <a href="<?= url('qr-codes?project_id=' . $row->project_id) ?>" class="text-decoration-none">
                                    <span class="badge badge-light" style="color: <?= $data->projects[$row->project_id]->color ?> !important;">
                                        <?= $data->projects[$row->project_id]->name ?>
                                    </span>
                                </a>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="col-2 col-lg-2 d-none d-lg-flex justify-content-center justify-content-lg-end align-items-center">
                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>') ?>">
                            <i class="fas fa-fw fa-calendar text-muted"></i>
                        </span>

                        <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' : '-')) ?>">
                            <i class="fas fa-fw fa-history text-muted"></i>
                        </span>
                    </div>

                    <div class="col-2 col-lg-2 d-flex justify-content-center justify-content-lg-end align-items-center">
                        <div class="dropdown">
                            <button type="button" class="btn btn-block btn-link dropdown-toggle dropdown-toggle-simple" title="<?= l('global.download') ?>" data-toggle="dropdown" aria-expanded="false" data-tooltip data-tooltip-hide-on-click>
                                <i class="fas fa-fw fa-sm fa-download"></i>
                            </button>

                            <div class="dropdown-menu">
                                <a href="<?= \Altum\Uploads::get_full_url('qr_code') . $row->qr_code ?>" class="dropdown-item" download="<?= get_slug($row->name) . '.svg' ?>"><?= sprintf(l('global.download_as'), 'SVG') ?></a>
                                <button type="button" class="dropdown-item" onclick="convert_svg_to_others('<?= \Altum\Uploads::get_full_url('qr_code') . $row->qr_code ?>', 'png', '<?= get_slug($row->name) . '.png' ?>');"><?= sprintf(l('global.download_as'), 'PNG') ?></button>
                                <button type="button" class="dropdown-item" onclick="convert_svg_to_others('<?= \Altum\Uploads::get_full_url('qr_code') . $row->qr_code ?>', 'jpg', '<?= get_slug($row->name) . '.jpg' ?>');"><?= sprintf(l('global.download_as'), 'JPG') ?></button>
                                <button type="button" class="dropdown-item" onclick="convert_svg_to_others('<?= \Altum\Uploads::get_full_url('qr_code') . $row->qr_code ?>', 'webp', '<?= get_slug($row->name) . '.webp' ?>');"><?= sprintf(l('global.download_as'), 'WEBP') ?></button>
                            </div>
                        </div>

                        <?= include_view(THEME_PATH . 'views/qr-codes/qr_code_dropdown_button.php', ['id' => $row->qr_code_id, 'resource_name' => $row->name]) ?>
                    </div>
                </div>
            </div>
        <?php endforeach ?>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>

        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get ?? [],
            'name' => 'qr_codes',
            'has_secondary_text' => true,
        ]); ?>

    <?php endif ?>

</section>

<?php require THEME_PATH . 'views/qr-codes/js_qr_code.php' ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'qr_code',
    'resource_id' => 'qr_code_id',
    'has_dynamic_resource_name' => true,
    'path' => 'qr-codes/delete'
]), 'modals'); ?>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'qr_code_duplicate_modal', 'resource_id' => 'qr_code_id', 'path' => 'qr-codes/duplicate']), 'modals'); ?>
