<?php
namespace Morpho\Log;

use Morpho\Base\PropertyNotFoundException;
use Zend\Log\Formatter\Simple;

class IconvFormatter extends Simple {
    const DEFAULT_FORMAT = "%timestamp% %priorityName% (%priority%): %message% %info%";

    protected $inputEncoding = 'utf-8';

    protected $outputEncoding = 'utf-8';

    public function __construct(array $options = array()) {
        $format = isset($options['format'])
            ? $options['format']
            : null;
        unset($options['format']);
        parent::__construct($format);
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new PropertyNotFoundException($this, $key);
            }
            $this->$key = $value;
        }
    }

    public function format($event) {
        $output = parent::format($event);

        return iconv($this->inputEncoding, $this->outputEncoding, $output);
    }
}
