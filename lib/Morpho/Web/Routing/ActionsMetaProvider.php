<?php
namespace Morpho\Web\Routing;

use function Morpho\Base\{last, dasherize};
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;
use PhpParser\NodeTraverser;
use PhpParser\Parser\Php7 as Parser;
use PhpParser\Lexer;
use PhpParser;

class ActionsMetaProvider implements \IteratorAggregate, IServiceManagerAware {
    protected $moduleManager;

    protected $serviceManager;

    public function setModuleManager($moduleManager) {
        $this->moduleManager = $moduleManager;
    }

    public function getIterator() {
        $parser = new Parser(new Lexer());
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new PhpParser\NodeVisitor\NameResolver());
        $controllerVisitor = new ControllerVisitor();
        $traverser->addVisitor($controllerVisitor);
        $modulePathManager = $this->serviceManager->get('modulePathManager');
        $moduleManager = $this->serviceManager->get('moduleManager');
        foreach ($moduleManager->listEnabledModules() as $moduleName) {
            foreach ($modulePathManager->getControllerFilePaths($moduleName) as $controllerFilePath) {
                $stmts = $parser->parse(file_get_contents($controllerFilePath));
                $traverser->traverse($stmts);
                foreach ($controllerVisitor->getActionsMeta() as $actionMeta) {
                    yield array_merge($actionMeta, ['filePath' => $controllerFilePath, 'module' => $moduleName]);
                }
            }
        }
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}

class ControllerVisitor extends PhpParser\NodeVisitorAbstract {
    protected $baseControllerClasses = [
        'Morpho\\Core\\Controller',
        'Morpho\\Web\\Controller',
    ];

    protected $actionsMeta = [];

    private $currentController;

    public function beforeTraverse(array $nodes) {
        $this->actionsMeta = [];
        $this->currentController = null;
    }

    public function enterNode(PhpParser\Node $node) {
        if ($node instanceof PhpParser\Node\Stmt\Class_) {
            $isController = !$node->isAbstract()
                && (!empty($node->extends) && in_array((string)$node->extends, $this->baseControllerClasses, true));
            if ($isController) {
                $class = (string)$node->namespacedName;
                $this->currentController = [
                    'controller' => $this->classToControllerName($class),
                    'class'      => $class,
                ];
            }
        } elseif ($this->currentController && $node instanceof PhpParser\Node\Stmt\ClassMethod) {
            $method = (string)$node->name;
            $isAction = $node->isPublic() && !$node->isAbstract()
                && !$node->isStatic() && strtolower(substr($method, -6)) === strtolower(ACTION_SUFFIX);
            if ($isAction) {
                $actionMeta = [
                    'action' => substr($method, 0, -strlen(ACTION_SUFFIX)),
                ];
                $docComment = $node->getDocComment();
                if (null !== $docComment) {
                    $actionMeta['docComment'] = $docComment->getText();
                }
                $this->actionsMeta[] = array_merge($this->currentController, $actionMeta);
            }
        }
    }

    public function leaveNode(PhpParser\Node $node) {
        if ($node instanceof PhpParser\Node\Stmt\Class_ && $this->currentController) {
            $this->currentController = null;
        }
    }

    public function getActionsMeta() {
        return $this->actionsMeta;
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