<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use function Morpho\Base\dasherize;
use function Morpho\Base\last;
use const Morpho\Core\APP_DIR_NAME;
use const Morpho\Core\MODULE_DIR_NAME;

class ScriptProcessor extends HtmlProcessor {
    private $scripts = [];

    protected const INDEX_ATTR = '_index';

    protected function containerBody($tag) {
        if (isset($tag[self::SKIP_ATTR])) {
            return null;
        }
        $childScripts = $this->scripts;
        $this->scripts = [];
        $html = $this->__invoke($tag['_text']); // render the parent page, extract and collect all scripts from it into $this->scripts.

        $splitScripts = function ($scripts) {
            return array_reduce($scripts, function ($acc, $tag) {
                if (isset($tag['src'])) {
                    $acc[1][] = $tag;
                } else {
                    $acc[0][] = $tag;
                }
                return $acc;
            }, [[], []]);
        };

        $mainPageScripts = $splitScripts($this->scripts);
        $childPageScripts = $splitScripts($childScripts);
        $actionScripts = $this->actionScripts();

        // script = (included | inline)
        // script = (main-page-script | child-page-script | action-script)
        // order:
        //     1. main-page-script included
        //     2. child-page-script included
        //     3. action-script included
        //     4. main-page-script inline
        //     5. child-page-script inline (has higher priority) | action-script inline
        $scripts = array_merge(
            $mainPageScripts[1],
            $childPageScripts[1],
            $actionScripts[1],
            $mainPageScripts[0],
            count($childPageScripts[0]) ? $childPageScripts[0] : $actionScripts[0]
        );
        $changed = $this->changeBodyScripts($scripts);
        if ($changed) {
            $scripts = $changed;
        }
        $html .= $this->renderScripts($scripts);

        $tag['_text'] = $html;

        return $tag;
    }

    protected function containerScript($tag) {
        if (isset($tag[self::SKIP_ATTR])) {
            return null;
        }
        if (!isset($tag['type']) || (isset($tag['type']) && $tag['type'] == 'text/javascript')) {
            $this->scripts[] = $tag;
            return false;  // remove the original tag, we will add it later.
        }
        return null;
    }

    protected function renderScripts(array $scripts): string {
        $html = [];
        $index = 0;
        usort($scripts, function ($prev, $next) use (&$index) {
            $a = isset($prev[self::INDEX_ATTR]) ? $prev[self::INDEX_ATTR] : $index++;
            $b = isset($next[self::INDEX_ATTR]) ? $next[self::INDEX_ATTR] : $index++;
            if ($a === $b && isset($prev['src']) && isset($next['src'])) {
                // Without this sort an exact order can be unknown when indexes are equal.
                return $prev['src'] <=> $next['src'];
            }
            return $a <=> $b;
        });
        //ksort($scripts, SORT_NUMERIC);
        foreach ($scripts as $tag) {
            unset($tag[self::INDEX_ATTR]);
            $html[] = $this->renderTag($tag);
        }
        return implode("\n", $html);
    }

    /**
     * Includes a file for controller's action.
     */
    private function actionScripts(): array {
        [$module, $controller, $action] = $this->request()->handler();
        $serviceManager = $this->serviceManager;
        $siteModuleName = $serviceManager->get('site')->moduleName();
        $publicDirPath = $serviceManager->get('moduleIndex')->moduleMeta($siteModuleName)->publicDirPath();
        // @TODO: Add automatic compilation of ts
        $jsModuleId = dasherize(last($module, '/')) . '/' . APP_DIR_NAME . '/' . dasherize($controller) . '/' . dasherize($action);
        $relJsFilePath = MODULE_DIR_NAME . '/' . $jsModuleId . '.js';
        $jsFilePath = $publicDirPath . '/' . $relJsFilePath;
        $inline = $included = [];
        if (is_file($jsFilePath)) {
            $included[] = [
                'src' => $relJsFilePath,
                '_tagName' => 'script',
                '_text' => '',
            ];
            $inline[] = [
                '_tagName' => 'script',
                '_text' => 'define(["require", "exports", "' . $jsModuleId . '"], function (require, exports, module) { module.main(); });',
            ];
        }
        return [$inline, $included];
    }

    private function changeBodyScripts(array $scripts): ?array {
        // Do nothing
        return null;
    }
}
