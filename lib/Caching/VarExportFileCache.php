<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

use function Morpho\Base\typeOf;
use Morpho\Code\CodeTool;

class VarExportFileCache extends PhpFileCache {
    protected function save(string $key, $data, $lifeTime = 0): bool {
        if (!is_array($data) && !is_scalar($data) && $data !== null) {
            throw new \RuntimeException('Only arrays and scalars are supported by this class, but $data has type ' . typeOf($data));
        }

        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }

        $cacheFilePath = $this->cacheFilePath($key);

        $value = [
            'lifetime' => $lifeTime,
            'data'     => $data,
        ];
        $code = '<?php return ' . CodeTool::varToStr($value);
        return $this->writeFile($cacheFilePath, $code);
    }
}