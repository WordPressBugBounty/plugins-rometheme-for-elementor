<?php

namespace RTMKit\Modules\Helper;

class SavedTemplateEditor
{

  public function __construct($data = [], $args = null)
  {
    add_action('elementor/editor/before_enqueue_scripts', [$this, 'load_saved_template_editor']);
    add_action('elementor/editor/after_enqueue_styles', [$this, 'register_styles']);
    add_action('elementor/preview/enqueue_styles', [$this, 'register_styles']);
  }

  public function register_styles()
  {
    wp_enqueue_style('rkit-saved-template-editor', RTM_KIT_URL . '/assets/css/saved-template-editor.css', '', RTM_KIT_VERSION);
  }

  public function load_saved_template_editor()
  {
    if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
      wp_enqueue_script('rkit-saved-template-editorjs', RTM_KIT_URL . '/assets/js/saved-template-editor.js', ['jquery'], RTM_KIT_VERSION, true);

      require RTM_KIT_DIR . 'views/saved-template-editor-view.php';
    }
  }
}
