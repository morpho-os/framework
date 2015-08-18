<?php
namespace Morpho\Web;

use function Morpho\Base\last;
use PhpParser\NodeTraverser;
use PhpParser\Parser\Php7 as Parser;
use PhpParser\Lexer;
use PhpParser;
use Morpho\Fs\Directory;

class RouteInfoProvider extends PhpParser\NodeVisitorAbstract {
    protected $baseControllerClasses = [
        'Morpho\\Core\\Controller',
        'Morpho\\Web\\Controller',
    ];

    private $controllers = [];

    private $inController;

    public static function enumerateControllerFiles($moduleDirPath = null) {
        return Directory::listFiles(
            $moduleDirPath,
            '~' . CONTROLLER_DIR_NAME . '/.+' . CONTROLLER_SUFFIX . '\.php$~s'
        );
    }

    public static function buildMetaForControllersInFile($filePath) {
        $parser = new Parser(new Lexer());
        $stmts = $parser->parse(file_get_contents($filePath));
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver());
        $controllerInfoProvider = new static();
        $traverser->addVisitor($controllerInfoProvider);
        $traverser->traverse($stmts);
        return $controllerInfoProvider->getControllers();
    }

    public static function parseDocComment($docComment) {
        $httpMethods = $title = $uri = null;
        if (false !== strpos($docComment, '@')) {
            $httpMethodsRegexpPart = '(?:' . implode('|', Request::getAllMethods()) . ')';
            $routeRegExp = '~'
                . '@(?<httpMethod>' . $httpMethodsRegexpPart . '(?:\|' . $httpMethodsRegexpPart . ')?)    # method (required)
                (\s+(?<uri>([^*\s]+)))?                                                                   # uri    (optional)
                $~xm';
            if (preg_match($routeRegExp, $docComment, $match)) {
                $httpMethods = explode('|', $match['httpMethod']);
                if (!empty($match['uri'])) {
                    $uri = $match['uri'];
                    if ($uri[0] !== '/') {
                        throw new \RuntimeException("Invalid annotations, URI must start with slash (/)");
                    }
                }
            }

            if (preg_match('~^\s*\*\s*@Title\s+(.+)\s*$~m', $docComment, $match)) {
                $title = array_pop($match);
            }
        }
        return [
            'methods' => $httpMethods,
            'uri' => $uri,
            'title' => $title,
        ];
    }

    public function beforeTraverse(array $nodes) {
        $this->controllers = [];
    }

    public function enterNode(PhpParser\Node $node) {
        if ($node instanceof PhpParser\Node\Stmt\Class_) {
            $isController = !$node->isAbstract()
                && (!empty($node->extends) && in_array((string)$node->extends, $this->baseControllerClasses, true));
            if ($isController) {
                $this->inController = true;
                $class = (string)$node->namespacedName;
                $this->controllers[] = [
                    'controller' => $this->classToControllerName($class),
                    'class' => $class,
                ];
            }
        } elseif ($this->inController && $node instanceof PhpParser\Node\Stmt\ClassMethod) {
            $method = (string)$node->name;
            $isAction = $node->isPublic() && !$node->isAbstract()
                && !$node->isStatic() && strtolower(substr($method, -6)) == strtolower(ACTION_SUFFIX);
            if ($isAction) {
                end($this->controllers);
                $key = key($this->controllers);
                $actionMeta = [
                    'action' => substr($method, 0, -strlen(ACTION_SUFFIX)),
                ];
                $docComment = $node->getDocComment();
                if (null !== $docComment) {
                    $actionMeta['docComment'] = $docComment->getText();
                }
                $this->controllers[$key]['actions'][] = $actionMeta;
            }
        }
    }

    public function leaveNode(PhpParser\Node $node) {
        if ($node instanceof PhpParser\Node\Stmt\Class_ && $this->inController) {
            $this->inController = false;
        }
    }

    public function getControllers() {
        return $this->controllers;
    }

    protected function classToControllerName($class) {
        $controllerName = last($class, '\\');
        $suffixLength = strlen(CONTROLLER_SUFFIX);
        if (substr($controllerName, -$suffixLength) !== CONTROLLER_SUFFIX) {
            throw new \RuntimeException("The controller class '$class' must end with the '" . CONTROLLER_SUFFIX . "' suffix");
        }
        return substr($controllerName, 0, -$suffixLength);
    }
}