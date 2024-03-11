<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><?= l('biolinks_templates.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('biolinks_templates.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex">
            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= count($data->biolinks_templates) && !$data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.filters.header') ?>">
                        <i class="fas fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if($data->filters->has_applied_filters): ?>
                                <a href="<?= url('biolinks-templates') ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
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
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                    <option value="order" <?= $data->filters->order_by == 'order' ? 'selected="selected"' : null ?>><?= l('global.order') ?></option>
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

    <?php if(count($data->biolinks_templates)): ?>
        <div class="row">
            <?php foreach($data->biolinks_templates as $biolink_template): ?>
                <div class="col-lg-6 col-xl-4" <?= in_array($biolink_template->biolink_template_id, $this->user->plan_settings->biolinks_templates ?? []) ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                    <div class="custom-row mb-4 d-flex flex-column justify-content-between <?= in_array($biolink_template->biolink_template_id, $this->user->plan_settings->biolinks_templates ?? []) ? null : 'container-disabled' ?>">
                        <?php if($biolink_template->image): ?>
                            <div class="mb-3">
                                <img src="<?= \Altum\Uploads::get_full_url('biolinks_templates') . $biolink_template->image ?>" class="img-fluid rounded" loading="lazy" />
                            </div>
                        <?php endif ?>

                        <div class="mb-2 text-center">
                            <h2 class="h6"><?= $biolink_template->name ?></h2>
                        </div>

                        <a href="<?= $biolink_template->url ?>" target="_blank" class="btn btn-block btn-sm btn-light"><i class="fas fa-fw fa-sm fa-external-link-alt mr-1"></i> <?= l('biolinks_templates.preview') ?></a>
                        <button type="button" class="btn btn-block btn-sm btn-primary" data-toggle="modal" data-target="#create_biolink" onclick="document.querySelector(`input[name='biolink_template_id']`).value = <?= $biolink_template->biolink_template_id ?>;"><i class="fas fa-fw fa-sm fa-plus-circle mr-1"></i> <?= l('biolinks_templates.choose') ?></button>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>

        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get ?? [],
            'name' => 'global',
            'has_secondary_text' => false,
        ]); ?>

    <?php endif ?>
</div>

