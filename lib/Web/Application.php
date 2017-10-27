<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Core\IModuleIndexer;
use Morpho\Di\IServiceManager;
use Morpho\Core\Application as BaseApplication;
use Morpho\Fs\Directory;
use Morpho\Web\View\Html;

class Application extends BaseApplication {
    protected function init(): IServiceManager {
        $appConfig = $this->config;

        /** @var Site $site */
        [$site, $siteConfig] = $this->newSiteAndConfig($appConfig);

        if (isset($siteConfig['iniSettings'])) {
            $this->applyIniSettings($siteConfig['iniSettings']);
        }
        if (isset($siteConfig['umask'])) {
            umask($siteConfig['umask']);
        }

        $services = [
            'app'  => $this,
            'site' => $site,
            'moduleIndexer' => $this->newModuleIndexer($site->moduleName(), [$appConfig['baseModuleDirPath']], $siteConfig),
        ];
        $serviceManager = $this->newServiceManager($siteConfig['serviceManager'] ?? null, $services);

        $serviceManager->setConfig($siteConfig['services']);

        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();

        return $serviceManager;
    }

    protected function applyIniSettings(array $iniSettings, $parentName = null): void {
        foreach ($iniSettings as $name => $value) {
            $settingName = $parentName ? $parentName . '.' . $name : $name;
            if (is_array($value)) {
                $this->applyIniSettings($value, $settingName);
            } else {
                ini_set($settingName, $value);
            }
        }
        if (!empty($_SERVER['HTTPS']) && !isset($iniSettings['session']['cookie_secure'])) {
            ini_set('cookie_secure', true);
        }
    }

    protected function showError(\Throwable $e): void {
        $header = null;
        if ($e instanceof NotFoundException) {
            $header = Environment::httpProtocolVersion() . ' 404 Not Found';
            $message = "The requested page was not found";
        } elseif ($e instanceof AccessDeniedException) {
            $header = Environment::httpProtocolVersion() . ' 403 Forbidden';
            $message = "You don't have access to the requested resource";
        } elseif ($e instanceof BadRequestException) {
            $header = Environment::httpProtocolVersion() . ' 400 Bad Request';
            $message = "Bad request, please contact site's support";
        } else {
            $header = Environment::httpProtocolVersion() . ' 500 Internal Server Error';
            $message = "Unable to handle the request. Please contact site's support and try to return to this page again later";
        }
        if (!headers_sent()) {
            // @TODO: Use http_response_code()?
            header($header);
        }
        while (\ob_get_level() > 0) {
            \ob_end_clean();
        }
        die(Html::encode($message) . '.');
    }

    protected function newSiteAndConfig($appConfig) {
        return (new SiteFactory())($appConfig);
    }

    protected function newServiceManager(?string $class, $services): IServiceManager {
        if ($class) {
            return new $class($services);
        }
        return new ServiceManager($services);
    }

    protected function newModuleIndexer(string $siteModuleName, iterable $baseModuleDirPaths, $siteConfig): IModuleIndexer {
        $moduleDirsProvider = function () use ($baseModuleDirPaths) {
            foreach ($baseModuleDirPaths as $baseModuleDirPath) {
                foreach (Directory::dirPaths($baseModuleDirPath, null, ['recursive' => false]) as $moduleDirPath) {
                    yield [
                        'baseModuleDirPath' => $baseModuleDirPath,
                        'moduleDirPath' => $moduleDirPath
                    ];
                }
            }
        };
        $cacheDirPath = $siteConfig['paths']['cacheDirPath'];
        $indexFilePath = $cacheDirPath . '/module-index.php';
        return new ModuleIndexer(
            $moduleDirsProvider(),
            $indexFilePath,
            [
                $siteModuleName => $siteConfig,
            ],
            // @TODO: Add module iterator
            array_keys($siteConfig['modules'])
        );
    }
}
