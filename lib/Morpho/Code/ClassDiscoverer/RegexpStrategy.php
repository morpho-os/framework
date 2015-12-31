<?php
namespace Morpho\Code\ClassDiscoverer;

class RegexpStrategy implements IDiscoverStrategy {
    private $nsRegexp = '/^\s*namespace\s+([a-z_]\w*(?:\\\\[a-z_]\w*)*);/si';

    private $typeRegexp = '/^\s*(?:abstract|final)?\s*(class|interface|trait)\s+([a-zA-Z_]\w*)/si';

    private $currentNs;

    public function getClassesForFile($filePath) {
        $lines = file($filePath);
        $type = $ns = null;
        $classes = [];
        $this->currentNs = null;
        foreach ($lines as $line) {
            if ($this->isNs($line, $ns)) {
                $this->currentNs = $ns;
            } elseif ($this->isType($line, $type)) {
                if (null !== $this->currentNs) {
                    $classes[] = $this->currentNs . '\\' . $type;
                } else {
                    $classes[] = $type;
                }
            }
        }

        return $classes;
    }

    /**
     * @return bool
     */
    private function isNs($line, &$ns) {
        $isNs = false !== strpos($line, 'namespace') && preg_match($this->nsRegexp, $line, $m);
        if ($isNs) {
            $ns = array_pop($m);
        }

        return $isNs;
    }

    /**
     * @return bool
     */
    private function isType($line, &$type) {
        $isType = (
                false !== strpos($line, 'class')
                || false !== strpos($line, 'interface')
                || false !== strpos($line, 'trait')
            )
            && preg_match($this->typeRegexp, $line, $m);
        if ($isType) {
            $type = array_pop($m);
        }

        return $isType;
    }
}
