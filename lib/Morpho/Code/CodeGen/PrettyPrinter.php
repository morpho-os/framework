<?php
namespace Morpho\Code\CodeGen;

use PhpParser\PrettyPrinter\Standard as BasePrettyPrinter;
use PhpParser\Node\Stmt;

class PrettyPrinter extends BasePrettyPrinter {
    private $stmtPrinted = false;
    private $nsPrinted = false;

    public function prettyPrintFile(array $stmts) {
        $this->stmtPrinted = $this->nsPrinted = false;
        return static::format(parent::prettyPrintFile($stmts));
    }

    public function prettyPrint(array $stmts) {
        $this->stmtPrinted = $this->nsPrinted = false;
        return parent::prettyPrint($stmts);
    }

    protected function pStmts(array $nodes, $indent = true) {
        $this->stmtPrinted = true;
        return parent::pStmts($nodes, $indent);
    }

    public function pStmt_Class(Stmt\Class_ $node) {
        return ($this->stmtPrinted ? "\n" : "") . parent::pStmt_Class($node);
    }

    public function pStmt_Namespace(Stmt\Namespace_ $node) {
        if ($this->canUseSemicolonNamespaces) {
            $output = 'namespace ' . $this->p($node->name) . ';' . "\n" . $this->pStmts($node->stmts, false);
        } else {
            $output = 'namespace' . (null !== $node->name ? ' ' . $this->p($node->name) : '')
                . ' {' . "\n" . $this->pStmts($node->stmts) . "\n" . '}';
        }
        if ($this->nsPrinted) {
            $output = "\n" . $output;
        }
        $this->nsPrinted = true;
        return $output;
    }

    public function pFile(FileNode $node) {
        $stmts = $node->stmts;
        $this->preprocessNodes($stmts);
        return $this->pStmts($stmts, false);
    }

    protected static function format($php) {
        $php = preg_replace('~^<\?php\n\s+~si', "<?php\n", $php);
        $php = preg_replace('~^\s+$~m', '', $php);
        $php = preg_replace('~\{\n\n(\s*)~si', "{\n\\1", $php);
        return $php;
    }
}
