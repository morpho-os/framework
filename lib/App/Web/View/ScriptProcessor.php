<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\View;

use Morpho\Fs\Path;
use Morpho\Ioc\IServiceManager;
use function Morpho\Base\dasherize;
use const Morpho\App\APP_DIR_NAME;

class ScriptProcessor extends HtmlProcessor {
    private array $scripts = [];

    protected const INDEX_ATTR = 'data-index';

    private string $baseUriPath;

    public function __construct(IServiceManager $serviceManager) {
        parent::__construct($serviceManager);
        $this->baseUriPath = '/'; // @todo
    }

    protected function containerBody($tag) {
        if (isset($tag[self::SKIP_ATTR])) {
            return null;
        }
        $childScripts = $this->scripts;
        $this->scripts = [];
        $html = $this->__invoke($tag['_text']); // render the parent page, extract and collect all scripts from it into $this->scripts.

        $splitScripts = function ($scripts) {
            return \array_reduce($scripts, function ($acc, $tag) {
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
        $scripts = \array_merge(
            $mainPageScripts[1],
            $childPageScripts[1],
            $actionScripts[1],
            $mainPageScripts[0],
            \count($childPageScripts[0]) ? $childPageScripts[0] : $actionScripts[0]
        );
        $changed = $this->changeBodyScripts($scripts);
        if (null !== $changed) {
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
        \usort($scripts, function ($prev, $next) use (&$index) {
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
        return \implode("\n", $html);
    }

    /**
     * Includes a file for controller's action.
     */
    private function actionScripts(): array {
        $handler = $this->request()->handler();
        $serviceManager = $this->serviceManager;
        $siteModuleName = $serviceManager['site']->moduleName();
        $clientModuleDirPath = $serviceManager['serverModuleIndex']->module($siteModuleName)->clientModule()->dirPath();
        // @TODO: Add automatic compilation of ts: tsc --emitDecoratorMetadata --experimentalDecorators --forceConsistentCasingInFileNames --inlineSourceMap --jsx preserve --lib es5,es2015,dom --module amd --moduleResolution node --noEmitHelpers --noEmitOnError --strict --noImplicitReturns --preserveConstEnums --removeComments --target es2015 action.ts
        $jsModuleId = $handler['modulePath'] . '/' . APP_DIR_NAME . '/' . $handler['controllerPath'] . '/' . dasherize($handler['method']);
        $relJsFilePath = '/' . $jsModuleId . '.js';
        $jsFilePath = Path::combine([$clientModuleDirPath, $relJsFilePath]);
        $inline = $included = [];
        if (\is_file($jsFilePath)) {
            $included[] = [
                'src' => $this->scriptUri($relJsFilePath),
                '_tagName' => 'script',
                '_text' => '',
            ];
            $inline[] = [
                '_tagName' => 'script',
                '_text' => 'define(["require", "exports", "' . $jsModuleId . '"], function (require, exports, module) { module.main(' . \json_encode($this->jsConf(), JSON_UNESCAPED_SLASHES) . '); });',
            ];
        }
        return [$inline, $included];
    }

    protected function jsConf(): array {
        $request = $this->request();
        if (isset($request['jsConf'])) {
            return (array) $request['jsConf'];
        }
        return [];
    }

    protected function changeBodyScripts(array $scripts): ?array {
        // Do nothing
        return null;
    }

    private function scriptUri(string $relJsFilePath): string {
        return Path::combine($this->baseUriPath, $relJsFilePath);
    }
}
