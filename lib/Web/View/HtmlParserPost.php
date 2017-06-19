<?php
namespace Morpho\Web\View;

use function Morpho\Base\dasherize;
use function Morpho\Base\last;
use const Morpho\Core\APP_DIR_NAME;
use const Morpho\Core\MODULE_DIR_NAME;
use Morpho\Di\ServiceManager;
use Morpho\Fs\Path;

class HtmlParserPost extends HtmlParser {
    protected $scripts = [];

    protected $forceCompileTs;

    protected $nodeBinDirPath;

    protected $tsOptions;

    private $scriptIndex = 0;

    public function __construct(ServiceManager $serviceManager, bool $forceCompileTs, string $nodeBinDirPath, array $tsOptions) {
        parent::__construct($serviceManager);
        $this->forceCompileTs = $forceCompileTs;
        $this->nodeBinDirPath = $nodeBinDirPath;
        $this->tsOptions = $tsOptions;
    }

    protected function containerBody($tag) {
        $childScriptsHtml = $this->renderChildScripts();
        $tag['_text'] = $this->filter($tag['_text']) // collect scripts on the current page
            . $this->renderScripts()                 // then render the collected scripts
            . "\n" . $childScriptsHtml;              // then append the rendered child scripts
        return $tag;
    }

    protected function containerScript($tag) {
        if (isset($tag['skip'])) {
            unset($tag['skip']);
            return $tag;
        }
        if ((isset($tag['type']) && $tag['type'] == 'text/javascript') || !isset($tag['type'])) {
            if (isset($tag['index'])) {
                $index = intval($tag['index']);
                unset($tag['index']);
                $this->scripts[$index] = $tag;
            } else {
                $this->scripts[$this->scriptIndex++] = $tag;
            }
        }
        return false;  // remove the original tag, we will add it later.
    }

    protected function renderScripts(): string {
        $html = [];
        $scripts = $this->scripts;
        ksort($scripts, SORT_NUMERIC);
        foreach ($scripts as $scriptTag) {
            if (isset($scriptTag['src'])) {
                $scriptTag['src'] = $this->prependUriWithBasePath($scriptTag['src']);
            }
            $html[] = $this->makeTag($scriptTag);
        }
        $this->scripts = [];
        return implode("\n", $html);
    }

    private function renderChildScripts(): string {
        $htmlOfScripts = $this->renderScripts();
        if ($htmlOfScripts === '') {
            [$module, $controller, $action] = $this->request()->handler();
            $publicDirPath = $this->serviceManager->get('site')->publicDirPath();
            // @TODO: Add automatic compilation of ts
            $jsModuleId = dasherize(last($module, '/')) . '/' . APP_DIR_NAME . '/' . dasherize($controller) . '/' . dasherize($action);
            $relJsFilePath = MODULE_DIR_NAME . '/' . $jsModuleId . '.js';
            $jsFilePath = $publicDirPath . '/' . $relJsFilePath;
            if (is_file($jsFilePath)) {
                $htmlOfScripts .= '<script src="' . $this->prependUriWithBasePath($relJsFilePath) . '"></script>' . "\n";
                $htmlOfScripts .= '<script>
$(function () {
    define(["require", "exports", "' . $jsModuleId . '"], function (require, exports, module) {
        module.main();
    });
});
</script>';
            }
        }
        return $htmlOfScripts;
    }
    /*
        protected function containerTypeScript($tag) {
            $inDirPath = str_replace('\\', '/', dirname($this->filePath));
            $cacheDirPath = $this->serviceManager->siteManager()->currentSite()->cacheDirPath();
            $scriptTag = [];
            if (isset($tag['index'])) {
                $scriptTag['index'] = $tag['index'];
            }
            $filesToCompile = [];
            $compile = false;
            $baseModuleDirPath = $this->serviceManager->get('moduleFs')->baseModuleDirPath();
            foreach (array_map('trim', explode(',', $tag['src'])) as $fileName) {
                $inFilePath = $inDirPath . '/' . Path::changeExt(basename($fileName), 'ts');
                if (!is_file($inFilePath)) {
                    throw new \RuntimeException("The '$inFilePath' does not exist");
                }
                $inFileChangedTime = filemtime($inFilePath);
                $outDirPath = $cacheDirPath . '/' . Path::toRelative($baseModuleDirPath, $inFilePath);
                $outFilePath = $outDirPath . '/' . $inFileChangedTime . '.js';
                if ($this->forceCompileTs || !is_file($outFilePath)) {
                    Directory::recreate($outDirPath);
                    $compile = true;
                }
                $filesToCompile[] = [$inFilePath, $outFilePath];
            }

            $text = [];
            $removeRefs = function ($line) {
                return substr($line, 0, 3) !== '///';
            };
            foreach ($filesToCompile as $inOutFilePaths) {
                if ($compile) {
                    $this->runTsc($inOutFilePaths[0], $inOutFilePaths[1]);
                }
                $text[] = implode("\n", filter($removeRefs, File::readLines($inOutFilePaths[1])));
                //$text[] = file_get_contents($inOutFilePaths[1]);
            }
            $scriptTag['_text'] = implode("\n", $text) . $tag['_text'];
            $scriptTag['_tagName'] = 'script';
            return $this->containerScript($scriptTag);
        }

        protected function runTsc(string $inFilePath, string $outFilePath) {
            $options = array_merge($this->tsOptions, ['--out ' . escapeshellarg($outFilePath)]);
            // Note: node and tsc must be in $PATH.
            cmd("PATH=\$PATH:{$this->nodeBinDirPath} tsc " . implode(' ', $options) . ' ' . escapeshellarg($inFilePath));
        }
    */
}
