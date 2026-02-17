<?php

declare(strict_types=1);

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => ['label' => 'lang:admin::lang.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'fields' => [
            'is_enabled' => [
                'label' => 'lang:tipowerup.darkmode::default.label_is_enabled',
                'comment' => 'lang:tipowerup.darkmode::default.help_is_enabled',
                'type' => 'switch',
                'default' => false,
                'span' => 'left',
            ],
            'admin_toolbar_toggle' => [
                'label' => 'lang:tipowerup.darkmode::default.label_admin_toolbar_toggle',
                'comment' => 'lang:tipowerup.darkmode::default.help_admin_toolbar_toggle',
                'type' => 'switch',
                'default' => true,
                'span' => 'right',
            ],
            'apply_to' => [
                'label' => 'lang:tipowerup.darkmode::default.label_apply_to',
                'type' => 'radiotoggle',
                'default' => 'both',
                'span' => 'left',
                'options' => [
                    'admin' => 'lang:tipowerup.darkmode::default.text_admin',
                    'frontend' => 'lang:tipowerup.darkmode::default.text_frontend',
                    'both' => 'lang:tipowerup.darkmode::default.text_both',
                ],
            ],
            'brightness' => [
                'label' => 'lang:tipowerup.darkmode::default.label_brightness',
                'comment' => 'lang:tipowerup.darkmode::default.help_brightness',
                'type' => 'number',
                'default' => 100,
                'span' => 'left',
            ],
            'contrast' => [
                'label' => 'lang:tipowerup.darkmode::default.label_contrast',
                'comment' => 'lang:tipowerup.darkmode::default.help_contrast',
                'type' => 'number',
                'default' => 90,
                'span' => 'right',
            ],
            'sepia' => [
                'label' => 'lang:tipowerup.darkmode::default.label_sepia',
                'comment' => 'lang:tipowerup.darkmode::default.help_sepia',
                'type' => 'number',
                'default' => 10,
                'span' => 'left',
            ],
            'schedule_enabled' => [
                'label' => 'lang:tipowerup.darkmode::default.label_schedule_enabled',
                'comment' => 'lang:tipowerup.darkmode::default.help_schedule_enabled',
                'type' => 'switch',
                'default' => false,
                'span' => 'left',
            ],
            'schedule_type' => [
                'label' => 'lang:tipowerup.darkmode::default.label_schedule_type',
                'type' => 'radiotoggle',
                'default' => 'time',
                'span' => 'right',
                'options' => [
                    'time' => 'lang:tipowerup.darkmode::default.text_time',
                    'sunset_sunrise' => 'lang:tipowerup.darkmode::default.text_sunset_sunrise',
                ],
                'trigger' => [
                    'action' => 'show',
                    'field' => 'schedule_enabled',
                    'condition' => 'checked',
                ],
            ],
            'start_time' => [
                'label' => 'lang:tipowerup.darkmode::default.label_start_time',
                'comment' => 'lang:tipowerup.darkmode::default.help_start_time',
                'type' => 'datepicker',
                'mode' => 'time',
                'default' => '20:00',
                'span' => 'left',
                'trigger' => [
                    'action' => 'show',
                    'field' => 'schedule_enabled',
                    'condition' => 'checked',
                ],
            ],
            'end_time' => [
                'label' => 'lang:tipowerup.darkmode::default.label_end_time',
                'comment' => 'lang:tipowerup.darkmode::default.help_end_time',
                'type' => 'datepicker',
                'mode' => 'time',
                'default' => '06:00',
                'span' => 'right',
                'trigger' => [
                    'action' => 'show',
                    'field' => 'schedule_enabled',
                    'condition' => 'checked',
                ],
            ],
            'latitude' => [
                'label' => 'lang:tipowerup.darkmode::default.label_latitude',
                'comment' => 'lang:tipowerup.darkmode::default.help_latitude',
                'type' => 'text',
                'span' => 'left',
                'trigger' => [
                    'action' => 'show',
                    'field' => 'schedule_enabled',
                    'condition' => 'checked',
                ],
            ],
            'longitude' => [
                'label' => 'lang:tipowerup.darkmode::default.label_longitude',
                'comment' => 'lang:tipowerup.darkmode::default.help_longitude',
                'type' => 'text',
                'span' => 'right',
                'trigger' => [
                    'action' => 'show',
                    'field' => 'schedule_enabled',
                    'condition' => 'checked',
                ],
            ],
        ],
        'rules' => [
            ['is_enabled', 'lang:tipowerup.darkmode::default.label_is_enabled', 'nullable|boolean'],
            ['apply_to', 'lang:tipowerup.darkmode::default.label_apply_to', 'nullable|string|in:admin,frontend,both'],
            ['brightness', 'lang:tipowerup.darkmode::default.label_brightness', 'nullable|integer|min:0|max:200'],
            ['contrast', 'lang:tipowerup.darkmode::default.label_contrast', 'nullable|integer|min:0|max:200'],
            ['sepia', 'lang:tipowerup.darkmode::default.label_sepia', 'nullable|integer|min:0|max:200'],
            ['schedule_enabled', 'lang:tipowerup.darkmode::default.label_schedule_enabled', 'nullable|boolean'],
            ['schedule_type', 'lang:tipowerup.darkmode::default.label_schedule_type', 'nullable|string|in:time,sunset_sunrise'],
            ['start_time', 'lang:tipowerup.darkmode::default.label_start_time', 'nullable|date_format:H:i'],
            ['end_time', 'lang:tipowerup.darkmode::default.label_end_time', 'nullable|date_format:H:i'],
            ['latitude', 'lang:tipowerup.darkmode::default.label_latitude', 'required_if:schedule_type,sunset_sunrise|nullable|numeric|min:-90|max:90'],
            ['longitude', 'lang:tipowerup.darkmode::default.label_longitude', 'required_if:schedule_type,sunset_sunrise|nullable|numeric|min:-180|max:180'],
            ['admin_toolbar_toggle', 'lang:tipowerup.darkmode::default.label_admin_toolbar_toggle', 'nullable|boolean'],
        ],
    ],
];
