<?php
namespace RTMKit\Modules\Helper;


class Control_Builder {
    protected $widget;

    public function __construct($widget) {
        $this->widget = $widget;
    }

    public function build(array $controls, $section = null, $tab = 'content') {
        $grouped = [
            'content' => [],
            'style' => [],
            'advanced' => [],
        ];

        foreach ($controls as $control) {
            $tabKey = $control['tab'] ?? $tab;
            $grouped[$tabKey][] = $control;
        }

        foreach ($grouped as $tabKey => $controlsGroup) {
            if (empty($controlsGroup)) continue;

            $tabConst = $this->getElementorTabConstant($tabKey);
            $sectionId = $section ? "{$section}_{$tabKey}" : "auto_section_{$tabKey}";
            $sectionLabel = $section ? ucfirst($section) : ucfirst($tabKey);

            $this->widget->start_controls_section($sectionId, [
                'label' => $sectionLabel,
                'tab' => $tabConst,
            ]);

            foreach ($controlsGroup as $control) {
                if (!isset($control['name']) && ($control['type'] ?? '') !== 'tabs') continue;

                if ($control['type'] === 'tabs' && isset($control['tabs'])) {
                    $this->widget->start_controls_tabs($control['id'] ?? 'custom_tabs');

                    foreach ($control['tabs'] as $tab_item) {
                        $this->widget->start_controls_tab($tab_item['id'], [
                            'label' => $tab_item['label'],
                        ]);

                        foreach ($tab_item['controls'] as $tab_control) {
                            $this->addSingleControl($tab_control);
                        }

                        $this->widget->end_controls_tab();
                    }

                    $this->widget->end_controls_tabs();
                    continue;
                }

                if ($control['type'] === 'repeater') {
                    $repeater = new \Elementor\Repeater();
                    foreach ($control['fields'] as $field) {
                        $args = array_merge([
                            'label' => ucfirst($field['name']),
                            'type' => $field['type'],
                        ], $field);

                        if (!empty($field['responsive_control'])) {
                            $repeater->add_responsive_control($field['name'], $args);
                        } else {
                            $repeater->add_control($field['name'], $args);
                        }
                    }

                    $this->widget->add_control(
                        $control['name'],
                        [
                            'label' => $control['label'] ?? ucfirst($control['name']),
                            'type' => \Elementor\Controls_Manager::REPEATER,
                            'fields' => $repeater->get_controls(),
                            'title_field' => $control['title_field'] ?? '{{{ ' . $control['fields'][0]['name'] . ' }}}',
                            'default' => $control['default'] ?? [],
                        ]
                    );
                    continue;
                }

                $this->addSingleControl($control);
            }

            $this->widget->end_controls_section();
        }

        if ($tab) {
            $this->widget->end_controls_tabs();
        }
    }

    protected function getElementorTabConstant($tabKey) {
        switch ($tabKey) {
            case 'style':
                return \Elementor\Controls_Manager::TAB_STYLE;
            case 'advanced':
                return \Elementor\Controls_Manager::TAB_ADVANCED;
            case 'content':
            default:
                return \Elementor\Controls_Manager::TAB_CONTENT;
        }
    }

    protected function addSingleControl(array $control) {
        if (!isset($control['name']) || !isset($control['type'])) return;

        if ($control['type'] === 'group' && isset($control['group_type'])) {
            $group_type = $control['group_type'];
            $options = $control['options'] ?? [];

            if (isset($control['types'])) {
                $options['types'] = $control['types'];
            }

            $this->widget->add_group_control($group_type, array_merge(
                ['name' => $control['name']],
                $options
            ));

            return;
        }

        $args = array_merge([
            'label' => ucfirst($control['name']),
            'type' => $control['type'],
        ], $control);

        if (!empty($control['responsive_control'])) {
            $this->widget->add_responsive_control($control['name'], $args);
        } else {
            $this->widget->add_control($control['name'], $args);
        }
    }
}
