<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\App\ISite;
use Morpho\Base\Event;
use function array_merge;
use function array_reduce;
use function count;
use function file_exists;
use function implode;
use function json_encode;
use function Morpho\Base\last;
use function usort;
use const Morpho\App\APP_DIR_NAME;

class ScriptProcessor extends HtmlProcessor {
    public const INDEX_ATTR = '_index';

    protected array $scripts = [];

    private ISite $site;

    public function __construct($request, ISite $site) {
        parent::__construct($request);
        $this->site = $site;
    }

    /**
     * Includes a file for controller's action.
     */
    public function actionScripts(string $jsModuleId): array {
        $siteConf = $this->site->conf();
        $shortModuleName = last($this->site->moduleName(), '/');
        $fullJsModuleId = $shortModuleName . '/' . APP_DIR_NAME . '/' . $jsModuleId;
        $relFilePath = $fullJsModuleId . '.js';
        $jsFilePath = $siteConf['paths']['frontendModuleDirPath'] . '/' . $relFilePath;
        $inline = $included = [];
        if (file_exists($jsFilePath)) {
            $jsConf = $this->jsConf();
            $included[] = [
                'src' => '/' . $relFilePath, // Prepend with '/' to prepend base URI path later
                '_tagName' => 'script',
                '_text' => '',
            ];
            $inline[] = [
                '_tagName' => 'script',
                '_text' => 'define(["require", "exports", "' . $fullJsModuleId . '"], function (require, exports, module) { module.main(window.app || {}, ' . json_encode($jsConf, JSON_UNESCAPED_SLASHES) . '); });',
            ];
        }
        return [$inline, $included];
    }

    public function renderScripts(array $scripts): string {
        $html = [];
        $index = 0;
        foreach ($scripts as $key => $script) {
            if (!isset($script[self::INDEX_ATTR])) {
                $script[self::INDEX_ATTR] = $index;
                $index++;
            }
            $script[self::INDEX_ATTR] = floatval($script[self::INDEX_ATTR]);
            $scripts[$key] = $script;
        }
        usort($scripts, function ($prev, $next) {
            $a = $prev[self::INDEX_ATTR];
            $b = $next[self::INDEX_ATTR];
            $diff = $a - $b;
            if (abs($diff) <= PHP_FLOAT_EPSILON && isset($prev['src']) && isset($next['src'])) {
                // Without this sort an exact order can be unknown when indexes are equal.
                return $prev['src'] <=> $next['src'];
            }
            if  ($diff > PHP_FLOAT_EPSILON) {
                return 1;
            }
            if ($diff >= -PHP_FLOAT_EPSILON) { // -PHP_FLOAT_EPSILON <= $diff <= PHP_FLOAT_EPSILON
                return 0;
            }
            return -1; // $diff < -PHP_FLOAT_EPSILON
        });
        foreach ($scripts as $tag) {
            if (isset($tag['src'])) {
                $tag['src'] = $this->request->prependUriWithBasePath($tag['src'])->toStr(null, false);
            }
            unset($tag[self::INDEX_ATTR]);
            $html[] = $this->renderTag($tag);
        }
        return implode("\n", $html);
    }

    protected function containerBody(array $tag): null|array|bool {
        if (isset($tag[self::SKIP_ATTR])) {
            unset($tag[self::SKIP_ATTR]);
            return $tag;
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

        $actionScripts = $this->actionScripts($this->request['view']);

        // script: included | inline
        // script: main-page-script | child-page-script | action-script
        $scripts = array_merge(
            $mainPageScripts[1],         // 1. main-page-script included
            $childPageScripts[1],        // 2. child-page-script included
            $actionScripts[1],           // 3. action-script included
            $mainPageScripts[0],         // 4. main-page-script inline
            count($childPageScripts[0])  // 5. child-page-script inline (has higher priority) | action-script inline
                ? $childPageScripts[0]
                : $actionScripts[0]
        );

        $event = new Event('changeScripts', $scripts);
        $this->trigger($event);

        $html .= $this->renderScripts($event->args);
        $tag['_text'] = $html;
        return $tag;
    }

    protected function containerScript(array $tag): null|array|bool {
        if (isset($tag[self::SKIP_ATTR])) {
            unset($tag[self::SKIP_ATTR]);
            return $tag;
        }
        if (!isset($tag['type']) || (isset($tag['type']) && $tag['type'] == 'text/javascript')) {
            $this->scripts[] = $tag;
            return false;  // remove the original tag, we will add it later.
        }
        return null;
    }

    protected function jsConf(): array {
        $request = $this->request;
        if (isset($request['jsConf'])) {
            return (array) $request['jsConf'];
        }
        return [];
    }
}