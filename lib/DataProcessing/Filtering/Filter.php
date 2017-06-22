<?php
namespace Morpho\DataProcessing\Filtering;

use Zend\Filter\AbstractFilter as BaseFilter;

abstract class Filter extends BaseFilter implements IFilter {
    public function __invoke($value) {
        return $this->filter($value);
    }
}
