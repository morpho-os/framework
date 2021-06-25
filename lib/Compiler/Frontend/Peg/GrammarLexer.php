<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
/**
 * The implementation is based on Python's PEG:
 * 1. https://medium.com/@gvanrossum_83706/peg-parsing-series-de5d41b2ed60
 * 2. https://www.python.org/dev/peps/pep-0617/
 * 3. https://www.youtube.com/watch?v=QppWTvh7_sI
 */
namespace Morpho\Compiler\Frontend\Peg;

use Generator;
use Morpho\Base\NotImplementedException;
use Morpho\Compiler\Frontend\ILexer;
use RuntimeException;

/**
 * https://github.com/python/cpython/blob/main/Tools/peg_generator/pegen/tokenizer.py
 */
class GrammarLexer implements ILexer {
    private int $index = 0;

    /**
     * @var array TokenInfo[]
     */
    private array $tokens = [];

    private iterable $tokenGen;

    //_tokens: List[tokenize.TokenInfo]

    public function __construct(Generator $tokenGen) {
        $this->tokenGen = $tokenGen;
    }

    /**
     * https://github.com/python/cpython/blob/09eb81711597725f853e4f3b659ce185488b0d8c/Lib/tokenize.py#L431
     */
    public static function genTokens(string $filePath): Generator {
        try {
            $handle = @fopen($filePath, "r");
            while (($buffer = fgets($handle, 4096)) !== false) {
                echo $buffer;
            }
            if (!feof($handle)) {
                throw new RuntimeException('fgets() fail');
            }
        } finally {
            if (isset($handle)) {
                fclose($handle);
            }
        }
        /*
            lnum = parenlev = continued = 0
            numchars = '0123456789'
            contstr, needcont = '', 0
            contline = None
            indents = [0]

            last_line = b''
            line = b''
            while True:                                # loop over lines in stream
                try:
                    # We capture the value of the line variable here because
                    # readline uses the empty string '' to signal end of input,
                    # hence `line` itself will always be overwritten at the end
                    # of this loop.
                    last_line = line
                    line = readline()
                except StopIteration:
                    line = b''

                if encoding is not None:
                    line = line.decode(encoding)
                lnum += 1
                pos, max = 0, len(line)

                if contstr:                            # continued string
                    if not line:
                    raise TokenError("EOF in multi-line string", strstart)
                    endmatch = endprog.match(line)
                    if endmatch:
                        pos = end = endmatch.end(0)
                        yield TokenInfo(STRING, contstr + line[:end],
                               strstart, (lnum, end), contline + line)
                        contstr, needcont = '', 0
                        contline = None
                    elif needcont and line[-2:] != '\\\n' and line[-3:] != '\\\r\n':
                        yield TokenInfo(ERRORTOKEN, contstr + line,
                                        strstart, (lnum, len(line)), contline)
                        contstr = ''
                        contline = None
                        continue
                    else:
                        contstr = contstr + line
                        contline = contline + line
                        continue

                        elif parenlev == 0 and not continued:  # new statement
                    if not line: break
                    column = 0
                    while pos < max:                   # measure leading whitespace
                        if line[pos] == ' ':
                            column += 1
                        elif line[pos] == '\t':
                            column = (column//tabsize + 1)*tabsize
                        elif line[pos] == '\f':
                            column = 0
                        else:
                            break
                            pos += 1
                    if pos == max:
                        break

                    if line[pos] in '#\r\n':           # skip comments or blank lines
                        if line[pos] == '#':
                            comment_token = line[pos:].rstrip('\r\n')
                            yield TokenInfo(COMMENT, comment_token,
                                (lnum, pos), (lnum, pos + len(comment_token)), line)
                            pos += len(comment_token)

                        yield TokenInfo(NL, line[pos:],
                                   (lnum, pos), (lnum, len(line)), line)
                        continue

                    if column > indents[-1]:           # count indents or dedents
                        indents.append(column)
                        yield TokenInfo(INDENT, line[:pos], (lnum, 0), (lnum, pos), line)
                    while column < indents[-1]:
                        if column not in indents:
                            raise IndentationError(
                        "unindent does not match any outer indentation level",
                        ("<tokenize>", lnum, pos, line))
                        indents = indents[:-1]

                        yield TokenInfo(DEDENT, '', (lnum, pos), (lnum, pos), line)

                else:                                  # continued statement
                    if not line:
                        raise TokenError("EOF in multi-line statement", (lnum, 0))
                    continued = 0

                while pos < max:
                    pseudomatch = _compile(PseudoToken).match(line, pos)
                    if pseudomatch:                                # scan for tokens
                        start, end = pseudomatch.span(1)
                        spos, epos, pos = (lnum, start), (lnum, end), end
                        if start == end:
                            continue
                            token, initial = line[start:end], line[start]

                        if (initial in numchars or                 # ordinary number
                        (initial == '.' and token != '.' and token != '...')):
                            yield TokenInfo(NUMBER, token, spos, epos, line)
                        elif initial in '\r\n':
                            if parenlev > 0:
                                yield TokenInfo(NL, token, spos, epos, line)
                            else:
                                yield TokenInfo(NEWLINE, token, spos, epos, line)

                        elif initial == '#':
                            assert not token.endswith("\n")
                            yield TokenInfo(COMMENT, token, spos, epos, line)

                        elif token in triple_quoted:
                            endprog = _compile(endpats[token])
                            endmatch = endprog.match(line, pos)
                            if endmatch:                           # all on one line
                                pos = endmatch.end(0)
                                token = line[start:pos]
                                yield TokenInfo(STRING, token, spos, (lnum, pos), line)
                            else:
                                strstart = (lnum, start)           # multiple lines
                                contstr = line[start:]
                                contline = line
                                break

                                    # Check up to the first 3 chars of the token to see if
                                    #  they're in the single_quoted set. If so, they start
                                    #  a string.
                                    # We're using the first 3, because we're looking for
                                    #  "rb'" (for example) at the start of the token. If
                                    #  we switch to longer prefixes, this needs to be
                                    #  adjusted.
                                    # Note that initial == token[:1].
                                    # Also note that single quote checking must come after
                                    #  triple quote checking (above).
                                elif (initial in single_quoted or
                            token[:2] in single_quoted or
                            token[:3] in single_quoted):
                            if token[-1] == '\n':                  # continued string
                                strstart = (lnum, start)
                                # Again, using the first 3 chars of the
                                #  token. This is looking for the matching end
                                #  regex for the correct type of quote
                                #  character. So it's really looking for
                                #  endpats["'"] or endpats['"'], by trying to
                                #  skip string prefix characters, if any.
                                endprog = _compile(endpats.get(initial) or
                                                   endpats.get(token[1]) or
                                                   endpats.get(token[2]))
                                contstr, needcont = line[start:], 1
                                contline = line
                                break
                            else:                                  # ordinary string
                                yield TokenInfo(STRING, token, spos, epos, line)

                        elif initial.isidentifier():               # ordinary name
                            yield TokenInfo(NAME, token, spos, epos, line)
                        elif initial == '\\':                      # continued stmt
                            continued = 1
                        else:
                            if initial in '([{':
                                parenlev += 1
                            elif initial in ')]}':
                                parenlev -= 1
                            yield TokenInfo(OP, token, spos, epos, line)
                    else:
                        yield TokenInfo(ERRORTOKEN, line[pos],
                            (lnum, pos), (lnum, pos+1), line)
                        pos += 1

            # Add an implicit NEWLINE if the input doesn't end in one
            if last_line and last_line[-1] not in '\r\n':
                yield TokenInfo(NEWLINE, '', (lnum - 1, len(last_line)), (lnum - 1, len(last_line) + 1), '')
            for indent in indents[1:]:                 # pop remaining indent levels
                yield TokenInfo(DEDENT, '', (lnum, 0), (lnum, 0), '')
            yield TokenInfo(ENDMARKER, '', (lnum, 0), (lnum, 0), '')
        */
    }

    /*

    def shorttok(tok: tokenize.TokenInfo) -> str:
        return "%-25.25s" % f"{tok.start[0]}.{tok.start[1]}: {token.tok_name[tok.type]}:{tok.string!r}"
    */

    /**
     * getnext() in Python
     */
    public function nextToken(): TokenInfo {
        /*"""Return the next token and updates the index."""
        cached = True
        while self._index == len(self._tokens):
            tok = next(self._tokengen)
            if tok.type in (tokenize.NL, tokenize.COMMENT):
                continue
            if tok.type == token.ERRORTOKEN and tok.string.isspace():
                continue
            self._tokens.append(tok)
            cached = False
        tok = self._tokens[self._index]
        self._index += 1
        if self._verbose:
            self.report(cached, False)
        return tok

    def peek(self) -> tokenize.TokenInfo:*/
    }


    /**
     * Return the next token *without* updating the index.
     * @return TokenInfo
     */
    public function peek(): TokenInfo {
        while ($this->index === count($this->tokens)) {
            $tok = next($this->tokenGen);
            if (in_array($tok->type, [Tokenize . NL, Tokenize . COMMENT])) {
                continue;
            }
            if ($tok->type === Token . ERRORTOKEN && $tok->string->isSpace()) {
                continue;
            }
            $this->tokens[] = $tok;
        }
        return $this->tokens[$this->index];
    }
    /*        def diagnose(self) -> tokenize.TokenInfo:
                if not self._tokens:
                    self.getnext()
                return self._tokens[-1]*/


    // mark() in Python
    public function index(): int {
        return $this->index;
    }

    public function reset(int $index): void {
        $this->index = $index;
    }

    public function __invoke(mixed $context): mixed {
        throw new NotImplementedException();
    }
}