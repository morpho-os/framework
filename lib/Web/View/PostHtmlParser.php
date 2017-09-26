<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use function Morpho\Base\dasherize;
use function Morpho\Base\last;
use const Morpho\Core\APP_DIR_NAME;
use Morpho\Core\Fs;

class PostHtmlParser extends HtmlParser {
    protected $scripts = [];

    private const INDEX_ATTR = '_index';
    private const SKIP_ATTR = '_skip';

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

        // script = included | inline
        // main-page-script | child-page-script | action-script
        // order:
        //     1. main-page-script included
        //     2. child-page-script included
        //     3. action-script included
        //     4. main-page-script inline
        //     5. child-page-script inline | action-script inline
        $scripts = array_merge(
            $mainPageScripts[1],
            $childPageScripts[1],
            $actionScripts[1],
            $mainPageScripts[0],
            count($childPageScripts[0]) ? $childPageScripts[0] : $actionScripts[0]
        );
        $html .= $this->renderScripts($scripts);

        $tag['_text'] = $html;

        return $tag;
    }

    protected function containerScript($tag) {
        if (isset($tag[self::SKIP_ATTR])) {
            return null;
        }
        if ((isset($tag['type']) && $tag['type'] == 'text/javascript') || !isset($tag['type'])) {
            $this->scripts[] = $tag;
        }
        return false;  // remove the original tag, we will add it later.
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
            if (isset($tag['src'])) {
                $tag['src'] = $this->prependUriWithBasePath($tag['src']);
            }
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
        $publicDirPath = $this->serviceManager->get('site')->fs()->publicDirPath();
        // @TODO: Add automatic compilation of ts
        $jsModuleId = dasherize(last($module, '/')) . '/' . APP_DIR_NAME . '/' . dasherize($controller) . '/' . dasherize($action);
        $relJsFilePath = Fs::MODULE_DIR_NAME . '/' . $jsModuleId . '.js';
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
                '_text' => '$(function () {
    define(["require", "exports", "' . $jsModuleId . '"], function (require, exports, module) {
        module.main();
    });
});',
            ];
        }
        return [$inline, $included];
    }
}
