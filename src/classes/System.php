<?php
namespace App;

final class System
{
    use Traits\Singleton;

    private $status = array(
        'error' => array(),
        'success' => array(),
        'warning' => array(),
        'info' => array()
    );

    public function addMessage($type, $msg)
    {
        $statusTypes = array_keys($this->status);

        if (in_array($type, $statusTypes)) {
            if (!in_array($msg, $this->status[$type])) {
                $this->status[$type][] = $msg;
            }
        } else {
            throw new \InvalidArgumentException('Unknown type!');
        }
    }

    private function getTitle($type)
    {
        $titles = array(
            'danger' => 'Fehler',
            'success' => 'Erfolg',
            'warning' => 'Warnung',
            'info' => 'Info'
        );

        return $titles[$type];
    }

    private function renderMessages($type, array $data)
    {
        $html = '';

        if ($type == 'error') {
            $type = 'danger';
        }

        for ($i = 0; $i < count($data); $i++) {
            $msg = $data[$i];
            $html .= "
            <div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
                <strong>{$this->getTitle($type)}</strong> {$msg}
            </div>";
        }

        return $html;
    }

    public static function getStatus($type = 'all')
    {
        $html = '';
        $instance = self::getInstance();

        foreach ($instance->status as $type => $data) {
            $html .= $instance->renderMessages($type, $data);
        }

        return $html;
    }
}
