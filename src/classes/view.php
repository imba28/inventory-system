<?php
namespace App;

class View {
    private $data = array();
    private $template;

    public function assign($key, $value) {
        $this->data[$key] = $value;
    }

    public function setTemplate($template) {
        $this->template = strtolower($template);
    }

    public function render() {
        $template_path = ABS_PATH."/src/views/{$this->template}.php";
        if(file_exists($template_path)) {
            extract($this->data);

            ob_start();
            include($template_path);
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }
        throw new \Exception("Template `{$template_path}` not found!");
    }
}
?>