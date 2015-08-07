<?php
namespace Morpho\Code\CodeGen;

use PhpParser\NodeAbstract;

class FileNode extends NodeAbstract {
    public $stmts;

    public function __construct(array $stmts) {
        parent::__construct([], []);
        $this->stmts = $stmts;
    }

    public function getType() {
        return 'File';
    }
}
