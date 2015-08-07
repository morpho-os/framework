<?php
namespace Morpho\Code\ClassDiscoverer;

class TokenStrategy implements IDiscoverStrategy {
    /**
     * The source of this method was copied from the \Composer\Autoload\ClassMapGenerator::findClasses()
     * and changed slightly.
     */
    public function getClassesForFile($filePath) {
        $contents = file_get_contents($filePath);
        try {
            if (!preg_match('{\b(?:class|interface|trait)\b}i', $contents)) {
                return array();
            }
            $tokens = token_get_all($contents);
        } catch (\Exception $e) {
            throw new \RuntimeException('Could not scan for classes inside ' . $filePath . ": \n" . $e->getMessage(), 0, $e);
        }

        $classes = array();

        $namespace = '';
        for ($i = 0, $max = count($tokens); $i < $max; $i++) {
            $token = $tokens[$i];

            if (is_string($token)) {
                continue;
            }

            $class = '';

            switch ($token[0]) {
                case T_NAMESPACE:
                    $namespace = '';
                    // If there is a namespace, extract it
                    while (($t = $tokens[++$i]) && is_array($t)) {
                        if (in_array($t[0], array(T_STRING, T_NS_SEPARATOR))) {
                            $namespace .= $t[1];
                        }
                    }
                    $namespace .= '\\';
                    break;
                case T_CLASS:
                case T_INTERFACE:
                case T_TRAIT:
                    // Find the classname
                    while (($t = $tokens[++$i]) && is_array($t)) {
                        if (T_STRING === $t[0]) {
                            $class .= $t[1];
                        } elseif ($class !== '' && T_WHITESPACE == $t[0]) {
                            break;
                        }
                    }

                    $classes[] = ltrim($namespace . $class, '\\');
                    break;
                default:
                    break;
            }
        }

        return $classes;
    }
}
