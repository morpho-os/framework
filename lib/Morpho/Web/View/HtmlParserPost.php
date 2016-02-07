<?php
namespace Morpho\Web\View;

use function Morpho\Base\filter;
use function Morpho\Cli\{
    cmdEx
};
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Morpho\Web\ServiceManager;

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

    protected function containerTypeScript($tag) {
        $inDirPath = str_replace('\\', '/', dirname($this->filePath));
        $cacheDirPath = $this->serviceManager->getSiteManager()->getCurrentSite()->getCacheDirPath();
        $scriptTag = [];
        if (isset($tag['index'])) {
            $scriptTag['index'] = $tag['index'];
        }
        $filesToCompile = [];
        $compile = false;
        foreach (array_map('trim', explode(',', $tag['src'])) as $fileName) {
            $inFilePath = $inDirPath . '/' . Path::newExt(basename($fileName), 'ts');
            if (!is_file($inFilePath)) {
                throw new \RuntimeException("The '$inFilePath' does not exist");
            }
            $inFileChangedTime = filemtime($inFilePath);
            $outDirPath = $cacheDirPath . '/' . Path::toRelative(MODULE_DIR_PATH, $inFilePath);
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
            $text[] = implode("\n", filter($removeRefs, File::readArray($inOutFilePaths[1])));
            //$text[] = file_get_contents($inOutFilePaths[1]);
        }
        $scriptTag['_text'] = implode("\n", $text) . $tag['_text'];
        $scriptTag['_tagName'] = 'script';
        return $this->containerScript($scriptTag);
    }

    protected function runTsc(string $inFilePath, string $outFilePath) {
        $options = array_merge($this->tsOptions, ['--out ' . escapeshellarg($outFilePath)]);
        // Note: node and tsc must be in $PATH.
        cmdEx("PATH=\$PATH:{$this->nodeBinDirPath} tsc " . implode(' ', $options) . ' ' . escapeshellarg($inFilePath));
    }

    protected function containerBody($tag) {
        $childScriptsHtml = $this->renderScripts();
        $tag['_text'] = $this->filter($tag['_text'])
            . $this->renderScripts()
            . "\n" . $childScriptsHtml;
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

    protected function renderScripts() {
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
}