<?php declare(strict_types=1);
namespace Morpho\Test\Unit\Tech\Sql\MySql\SelectQueryTest;

class NorthwindFixture {
    public function load($db): void {
        $dataDirPath = __DIR__ . '/../mywind';
        $this->importFile($db, $dataDirPath . '/northwind-schema.sql');
        $this->importFile($db, $dataDirPath . '/northwind-data.sql');
    }

    private function importFile($db, string $filePath): int {
        // todo: Right now it is too rough approach. Parse file using real SQL parser.
        $contents = file_get_contents($filePath);
        $contents = preg_replace('~^--.*~m', '', $contents); // remove comments
        $stmts = preg_split('~;$~m', $contents, -1, PREG_SPLIT_NO_EMPTY); // split by semicolon
        $i = 0;
        $db->exec('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($stmts as $stmt) {
            $stmt = trim($stmt);
            if (!strlen($stmt)) {
                continue;
            }
            $db->exec($stmt);
            $i++;
        }
        $db->exec('SET FOREIGN_KEY_CHECKS=1;');
        return $i;
    }
}