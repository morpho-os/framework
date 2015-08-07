<?php
namespace Morpho\Code\CodeGen;

use PhpParser\BuilderAbstract;
use PhpParser\Node\Name as NodeName;
use PhpParser\Node\Stmt\Namespace_ as NamespaceStmt;
use PhpParser\Node\Stmt\Use_ as UseStmt;
use PhpParser\Node\Stmt\UseUse as UseUseStmt;

class FileBuilder extends BuilderAbstract {
    const GLOBAL_NS = 'global';

    private $currentNs = self::GLOBAL_NS;

    private $stmts = [];

    private $hasNs = false;

    public function getNode() {
        return new FileNode($this->getStmts());
    }

    public function addStmt($stmt) {
        $this->stmts[$this->currentNs]['stmts'][] = $this->normalizeNode($stmt);
        return $this;
    }

    public function addStmts(array $stmts) {
        foreach ($stmts as $stmt) {
            $this->addStmt($stmt);
        }

        return $this;
    }

    public function addGlobalNs() {
        return $this->addNs(self::GLOBAL_NS);
    }

    public function addNs($name) {
        if (!isset($this->stmts[$name])) {
            $this->stmts[$name] = [];
        }
        $this->currentNs = $name;
        $this->hasNs = true;
        return $this;
    }

    public function addUse($name, $alias = null) {
        $this->stmts[$this->currentNs]['uses'][$name] = $alias;
        return $this;
    }

    protected function getStmts() {
        $stmts = [];
        $hasMultipleNamespaces = count($this->stmts) > 1;
        foreach ($this->stmts as $ns => $meta) {
            $nsNameNode = $ns === self::GLOBAL_NS ? null : new NodeName($ns);
            if ($hasMultipleNamespaces) {
                $stmts[] = new NamespaceStmt(
                    $nsNameNode,
                    $this->getDescendantStmts($meta)
                );
            } else {
                if ($this->hasNs) {
                    $stmts[] = new NamespaceStmt($nsNameNode);
                }
                $stmts = array_merge(
                    $stmts,
                    $this->getDescendantStmts($meta)
                );
            }
        }
        return $stmts;
    }

    private function getDescendantStmts(array $meta) {
        $stmts = [];
        if (isset($meta['uses'])) {
            foreach ($meta['uses'] as $name => $alias) {
                $stmts[] = new UseStmt([new UseUseStmt(new NodeName($name), $alias)]);
            }
        }
        if (isset($meta['stmts'])) {
            $stmts = array_merge($stmts, $meta['stmts']);
        }
        return $stmts;
    }
}
