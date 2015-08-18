<?php
namespace Morpho\Code;

/**
 * The base code for Lexer found at: https://github.com/nikic/Phlexy/blob/master/lib/Phlexy/Lexer/Stateless/Simple.php
 */
class Lexer implements ICompilerPhase {
    protected $regexToToken;

    public function __construct(array $regexToToken, string $additionalModifiers = 'i') {
        $this->regexToToken = [];
        foreach ($regexToToken as $regex => $token) {
            $regex = '~' . str_replace('~', '\~', $regex) . '~A' . $additionalModifiers;
            $this->regexToToken[$regex] = $token;
        }
    }

    public function run(string $string): array {
        $tokens = [];

        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            foreach ($this->regexToToken as $regex => $token) {
                if (!preg_match($regex, $string, $matches, 0, $offset)) {
                    continue;
                }

                $matchedString = $matches[0];

                unset($matches[0]);
                if (!empty($matches)) {
                    $tokens[] = [$token, $line, $matchedString, $matches];
                } else {
                    $tokens[] = [$token, $line, $matchedString];
                }

                $offset += strlen($matchedString);
                $line += substr_count($matchedString, "\n");

                continue 2;
            }

            throw new \UnexpectedValueException(sprintf(
                'Unexpected character "%s" on line %d', $string[$offset], $line
            ));
        }

        return $tokens;
    }
}
